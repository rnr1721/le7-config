<?php

namespace Core\Config;

use Core\Config\Interfaces\ConfigFactory;
use Core\Config\Adapters\ConfigArrayAdapter;
use Core\Config\Adapters\ConfigArrayFileAdapter;
use Core\Config\Adapters\ConfigJsonFileAdapter;
use Core\Config\Adapters\ConfigIniFileAdapter;
use Core\Config\Interfaces\Config;
use Psr\SimpleCache\CacheInterface;

class ConfigFactoryGeneric implements ConfigFactory
{

    private ?CacheInterface $cache = null;

    public function __construct(?CacheInterface $cache = null)
    {
        if ($cache) {
            $this->cache = $cache;
        }
    }

    public function fromArray(array $array, ?string $source = null): Config
    {
        $adapter = new ConfigArrayAdapter($array, $source);
        return new ConfigGeneric($adapter);
    }

    public function fromArrayFile(string $filename, ?string $source = null, string $cacheKey = 'config'): Config
    {
        $adapter = new ConfigArrayFileAdapter($filename, $source);
        return new ConfigGeneric($adapter, $this->cache, $cacheKey);
    }

    public function fromJsonFile(string $filename, ?string $source = null, string $cacheKey = 'config'): Config
    {
        $adapter = new ConfigJsonFileAdapter($filename, $source);
        return new ConfigGeneric($adapter, $this->cache, $cacheKey);
    }

    public function fromIniFile(string $filename, ?string $source = null, string $cacheKey = 'config'): Config
    {
        $adapter = new ConfigIniFileAdapter($filename, $source);
        return new ConfigGeneric($adapter, $this->cache, $cacheKey);
    }

    public function harvest(string|array $directory, string $filenameSuffix = '', string $cacheKey = 'config'): Config
    {
        $configHarvester = new ConfigHarvesterGeneric($this->cache);
        return $configHarvester->getConfig($directory, $filenameSuffix, $cacheKey);
    }

}
