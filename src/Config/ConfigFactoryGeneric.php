<?php

namespace Core\Config;

use Core\Interfaces\ConfigInterface;
use Core\Interfaces\ConfigFactoryInterface;
use Core\Config\Adapters\ConfigArrayAdapter;
use Core\Config\Adapters\ConfigArrayFileAdapter;
use Core\Config\Adapters\ConfigJsonFileAdapter;
use Core\Config\Adapters\ConfigIniFileAdapter;
use Psr\SimpleCache\CacheInterface;

class ConfigFactoryGeneric implements ConfigFactoryInterface
{

    private ?CacheInterface $cache = null;

    public function __construct(?CacheInterface $cache = null)
    {
        if ($cache) {
            $this->cache = $cache;
        }
    }

    public function fromArray(array $array, ?string $source = null): ConfigInterface
    {
        $adapter = new ConfigArrayAdapter($array, $source);
        return new ConfigGeneric($adapter);
    }

    public function fromArrayFile(string $filename, ?string $source = null, string $cacheKey = 'config'): ConfigInterface
    {
        $adapter = new ConfigArrayFileAdapter($filename, $source);
        return new ConfigGeneric($adapter, $this->cache, $cacheKey);
    }

    public function fromJsonFile(string $filename, ?string $source = null, string $cacheKey = 'config'): ConfigInterface
    {
        $adapter = new ConfigJsonFileAdapter($filename, $source);
        return new ConfigGeneric($adapter, $this->cache, $cacheKey);
    }

    public function fromIniFile(string $filename, ?string $source = null, string $cacheKey = 'config'): ConfigInterface
    {
        $adapter = new ConfigIniFileAdapter($filename, $source);
        return new ConfigGeneric($adapter, $this->cache, $cacheKey);
    }

    public function harvest(string|array $directory, string $filenameSuffix = '', string $cacheKey = 'config'): ConfigInterface
    {
        $configHarvester = new ConfigHarvesterGeneric($this->cache);
        return $configHarvester->getConfig($directory, $filenameSuffix, $cacheKey);
    }

}
