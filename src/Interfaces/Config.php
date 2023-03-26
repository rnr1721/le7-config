<?php

declare(strict_types=1);

namespace Core\Interfaces;

use Psr\SimpleCache\CacheInterface;
use \Countable;
use \IteratorAggregate;
use \JsonSerializable;
use \ArrayAccess;

/**
 * @phpstan-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 * @template-extends IteratorAggregate<TKey, T>
 * @template-extends ArrayAccess<TKey|null, T>
 */
interface Config extends Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{

    /**
     * Get value as object property as magick method
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed;

    /**
     * Get data by path without strict check
     * @param string $path Path divided by separator
     * @param mixed $default Default value
     * @param string $separator Separator
     * @return mixed
     */
    public function path(string $path, mixed $default = null, string $separator = '.'): mixed;

    /**
     * Set if strict check data type in config source (ini, file or array)
     * @param bool $value
     * @return self
     */
    public function setStrictCheckOn(bool $value): self;

    /**
     * Turn on or off readonly in config array
     * @param bool $value
     * @return self
     */
    public function setReadOnlyOn(bool $value): self;

    /**
     * Get string item from config
     * if default is null and value not in config you will got exception
     * @param string $path Path to value in array with separator
     * @param string|null $default Default value
     * @return string|null
     */
    public function string(string $path, string|null $default = null): string|null;

    /**
     * Get filtered string item from config
     * if default is null and value not in config you will got exception
     * Will be applied var filters after applyFilter() method.
     * can replace {variable} in string to some other
     * @param string $path Path to value in array with separator
     * @param string|null $default Default value
     * @return string|null
     */
    public function stringf(string $path, string|null $default = null): string|null;

    /**
     * Get int item from config
     * if default is null and value not in config you will got exception
     * @param string $path Path to value in array with separator
     * @param int|null $default Default value
     * @return int|null
     */
    public function int(string $path, int|null $default = null): int|null;

    /**
     * Get float item from config
     * if default is null and value not in config you will got exception
     * @param string $path Path to value in array with separator
     * @param float|null $default Default value
     * @return float|null
     */
    public function float(string $path, float $default = null): float|null;

    /**
     * Get bool value from config
     * if value not in config you will get false
     * @param string $path Path to value in array with separator
     * @param bool $default Default value
     * @return bool
     */
    public function bool(string $path, bool $default = false): bool;

    /**
     * Get array item from config
     * if default is null and value not in config you will got exception
     * @param string $path Path to value in array with separator
     * @param array|null $default Default value
     * @return array|null
     */
    public function array(string $path, array|null $default = null): array|null;

    /**
     * Append from another config
     * @param ConfigAdapter $configAdapter Config Adapter
     * @return self
     */
    public function append(ConfigAdapter $configAdapter): self;

    /**
     * If config loaded from cache
     * @return bool
     */
    public function isLoadedFromCache(): bool;

    /**
     * Try add to cache if cache enable
     * @return void
     */
    public function addToCache(): void;

    /**
     * Try load from cache if cache enable
     * @return bool
     */
    public function loadFromCache(): bool;

    /**
     * Apply filter
     * After applying all "variables" will be replaced with some strings
     * Example: applyFilter('myvar','123');
     * Result: "/{myvar}/some_string" will be replaced with "/123/some_string"
     * @param string $var
     * @param string $replace
     * @return self
     */
    public function applyFilter(string $var, string $replace): self;

    /**
     * Set PSR CacheInterface
     * @param CacheInterface $cache
     * @return self
     */
    public function setCache(CacheInterface $cache): self;

    /**
     * Register own dynamic parameter
     * @param string $path
     * @param mixed $value
     * @param string $separator
     * @return self
     */
    public function registerParam(string $path, mixed $value, string $separator = '.'): self;
}
