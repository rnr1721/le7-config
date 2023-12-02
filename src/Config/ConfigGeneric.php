<?php

declare(strict_types=1);

namespace Core\Config;

use Core\Interfaces\ConfigAdapterInterface;
use Core\Interfaces\ConfigInterface;
use Psr\SimpleCache\CacheInterface;
use \ArrayIterator;
use \Traversable;
use \stdClass;
use \Exception;
use function array_key_exists,
             count,
             json_encode,
             is_array,
             strlen,
             explode,
             is_null,
             is_bool,
             is_string,
             is_numeric,
             is_int,
             floatval,
             strval,
             boolval,
             intval,
             str_replace,
             array_pop;

/**
 *
 * @phpstan-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 * @template-implements ConfigInterface<TKey,T>
 */
class ConfigGeneric implements ConfigInterface
{

    private array $fsearch = [];
    private array $freplace = [];
    private string $cacheKey;
    private ?CacheInterface $cache = null;
    private bool $loadedFromCache = false;

    /**
     * Readonly data in config array
     * @var bool
     */
    private bool $readOnly = false;

    /**
     * Strict check source from ini,json,array etc
     * @var bool
     */
    private bool $strictCheck = true;

    /**
     * Source - name of config source
     * @var string
     */
    private string $source = '';

    /**
     * Config data
     * @var array
     */
    private array $data = [];

    public function __construct(
            ?ConfigAdapterInterface $configAdapter = null,
            ?CacheInterface $cache = null,
            string $cacheKey = 'config'
    )
    {
        $this->cacheKey = $cacheKey;
        $this->cache = $cache;
        if ($configAdapter !== null && !$this->loadFromCache()) {
            $this->data = $configAdapter->get();
            $this->source = $configAdapter->getSource();
            $this->strictCheck = $configAdapter->getStrctCheck();
            if ($this->cache) {
                $this->addToCache();
            }
        }
    }

    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->toObject($this->data[$key]);
        }
        return null;
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return Traversable<TKey,T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function jsonSerialize(): mixed
    {
        return json_encode($this->data);
    }

    private function toObject(mixed $array): mixed
    {
        if (!is_array($array)) {
            return $array;
        }
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (strlen($key)) {
                if (is_array($value)) {
                    $object->{$key} = $this->toObject($value);
                } else {
                    $object->{$key} = $value;
                }
            }
        }
        return $object;
    }

    public function path(string $path, mixed $default = null, string $separator = '.'): mixed
    {

        if (empty($path)) {
            throw new Exception("ConfigGeneric::path() empty path");
        }

        if ($separator === '') {
            $separator = '.';
        }
        $cPath = explode($separator, $path);
        $temp = $this->data;
        foreach ($cPath as $key) {
            if (isset($temp[$key])) {
                $temp = $temp[$key];
            } else {
                $temp = null;
            }
        }
        if ($temp !== null) {
            return $temp;
        }
        return $default;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function bool(string $path, bool $default = false): bool
    {
        $result = $this->path($path);
        if ($this->checkValue($result, $default, $path, 'bool')) {
            $result = $default;
        }
        if ($this->strictCheck) {
            if (!is_bool($result)) {
                throw new Exception('ConfigGeneric::bool() Config value must be bool:' . $path);
            }
        }
        return boolval($result);
    }

    public function float(string $path, float $default = null): float|null
    {
        $result = $this->path($path);
        if ($this->checkValue($result, $default, $path, 'float')) {
            $result = $default;
        }
        if ($this->strictCheck) {
            if (!is_float($result)) {
                throw new Exception('ConfigGeneric::float() Config value must be float:' . $path);
            }
        }
        if (!is_numeric($result)) {
            throw new Exception('ConfigGeneric::float() Config value must be numeric:' . $path);
        }
        return floatval($result);
    }

    public function int(string $path, int|null $default = null): int
    {
        $result = $this->path($path);
        if ($this->checkValue($result, $default, $path, 'int')) {
            $result = $default;
        }
        if ($this->strictCheck) {
            if (!is_int($result)) {
                throw new Exception('ConfigGeneric::int() Config value must be integer:' . $path);
            }
        }
        if (!is_numeric($result)) {
            throw new Exception('ConfigGeneric::int() Config value must be numeric:' . $path);
        }
        return intval($result);
    }

    public function string(string $path, string|null $default = null): string|null
    {
        $result = $this->path($path);
        if ($this->checkValue($result, $default, $path, 'string')) {
            $result = $default;
        }
        if ($this->strictCheck) {
            if (!is_string($result)) {
                throw new Exception('ConfigGeneric::string() Config value must be string:' . $path);
            }
        }
        return strval($result);
    }

    public function stringf(string $path, string|null $default = null): string|null
    {
        $result = $this->string($path, $default);
        if ($result) {
            return str_replace($this->fsearch, $this->freplace, $result);
        }
        return null;
    }

    public function array(string $path, array|null $default = null): array|null
    {
        $result = $this->path($path);
        if ($this->checkValue($result, $default, $path, 'array')) {
            $result = $default;
        }
        if (!is_array($result)) {
            throw new Exception('ConfigGeneric::array() Config value must be array:' . $path);
        }
        return $result;
    }

    public function arrayWithKeyStartWith(string $keyStartWith, string|null $path = null): array|null
    {
        if ($path === null) {
            $values = $this->data;
        } else {
            $values = $this->array($path);
        }
        if (!is_array($values)) {
            return null;
        }

        $filteredArray = array_filter(
                $values,
                function ($key) use ($keyStartWith) {
                    return strpos($key, $keyStartWith) === 0;
                },
                ARRAY_FILTER_USE_KEY
        );
        return $filteredArray;
    }

    private function checkValue(
            mixed $result,
            mixed $default,
            string $path,
            string $context
    ): mixed
    {
        if ($result === null && $default === null) {
            throw new Exception('ConfigGeneric::checkValue() Config value (' . $context . ') not exists in config:' . $path);
        }
        if ($result === null && $default !== null) {
            return true;
        }
        return false;
    }

    public function setStrictCheckOn(bool $value): self
    {
        $this->strictCheck = $value;
        return $this;
    }

    public function setReadOnlyOn(bool $value): self
    {
        $this->readOnly = $value;
        return $this;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->readOnly) {
            throw new Exception("ConfigGeneric::offsetSet() Setting values to config not allowed:" . ($offset === null ? '' : $offset) . $value);
        } else {
            if (is_null($offset)) {
                throw new Exception("ConfigGeneric::offsetSet() Key is required");
            } else {
                $this->data[$offset] = $value;
            }
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
        } else {
            throw new Exception("ConfigGeneric::offsetUnset key not exists:" . $offset);
        }
    }

    public function append(ConfigAdapterInterface $configAdapter): self
    {
        $data = $configAdapter->get();
        foreach ($data as $item => $value) {
            if (array_key_exists($item, $this->data)) {
                throw new Exception($configAdapter->getSource() . ": Duplicate item:" . $item);
            }
            $this->data[$item] = $value;
        }
        return $this;
    }

    public function isLoadedFromCache(): bool
    {
        return $this->loadedFromCache;
    }

    public function addToCache(): void
    {
        if ($this->cache) {
            $this->cache->set($this->cacheKey, $this->data);
        }
    }

    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    public function loadFromCache(): bool
    {
        if ($this->cache) {
            if ($this->cache->has($this->cacheKey)) {
                $this->data = $this->cache->get($this->cacheKey);
                $this->loadedFromCache = true;
                return true;
            }
        }
        return false;
    }

    public function applyFilter(string $var, string $replace): self
    {
        $value = '{' . $var . '}';
        if (in_array($value, $this->fsearch)) {
            throw new Exception("ConfigGeneric::applyFilter() filter is exists:" . $var);
        }
        $this->fsearch[] = $value;
        $this->freplace[] = $replace;
        return $this;
    }

    public function registerParam(
            string $path,
            mixed $value,
            ?string $filter = null,
            string $separator = '.'
    ): self
    {

        if (empty($path)) {
            throw new Exception("ConfigGeneric::registerParam() empty path");
        }

        if (empty($separator)) {
            $separator = '.';
        }

        $cPath = explode($separator, $path);
        $key = array_pop($cPath);

        /** @psalm-suppress UnsupportedPropertyReferenceUsage */
        $temp = &$this->data;
        foreach ($cPath as $item) {
            $temp = &$temp[$item];
        }
        if (isset($temp[$key])) {
            throw new Exception("ConfigGeneric::registerParam() duplicate key:" . $key);
        }
        if (!is_array($temp)) {
            throw new Exception("ConfigGeneric::registerParam() cannot register here:" . $path);
        }
        $temp[$key] = $value;

        if ($filter !== null) {
            $this->applyFilter($filter, $value);
        }

        return $this;
    }
}
