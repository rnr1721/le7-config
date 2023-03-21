<?php

namespace Core\Config;

use Core\Config\Interfaces\ConfigHarvester;
use Core\Config\Interfaces\Config;
use Core\Config\Interfaces\ConfigAdapter;
use Psr\SimpleCache\CacheInterface;
use \Exception;

class ConfigHarvesterGeneric implements ConfigHarvester
{

    private array $excludeFiles = [];
    private ?CacheInterface $cache = null;
    private array $adapters = [
        'php' => 'Core\Config\Adapters\ConfigArrayFileAdapter',
        'ini' => 'Core\Config\Adapters\ConfigIniFileAdapter',
        'json' => 'Core\Config\Adapters\ConfigJsonFileAdapter'
    ];

    public function __construct(?CacheInterface $cache = null)
    {
        if ($cache) {
            $this->cache = $cache;
        }
    }

    public function addAdapter(string $extension, string $adapter): self
    {
        if (!class_exists($adapter)) {
            throw new Exception('ConfigHarvesterGeneric::addAdapter Class not exists:' . $adapter);
        }
        if (!$adapter instanceof ConfigAdapter) {
            throw new Exception('ConfigHarvesterGeneric::addAdapter Class not instance of ConfigAdapter:' . $adapter);
        }
        if (!array_key_exists($extension, $this->adapters)) {
            throw new Exception('ConfigHarvesterGeneric::addAdapter Extension already added:' . $extension);
        }
        $this->adapters[$extension] = $adapter;
        return $this;
    }

    public function getConfig(string|array $directory, string $fileSuffix = '', string $cacheKey = 'config'): Config
    {
        $config = new ConfigGeneric(null, $this->cache, $cacheKey);
        if (!$config->isLoadedFromCache()) {
            $allFiles = $this->getFiles($directory, $fileSuffix);
            foreach ($allFiles as $extension => $files) {
                foreach ($files as $file) {
                    $fileBasename = basename($file);
                    if (!in_array($fileBasename, $this->excludeFiles)) {
                        $class = $this->adapters[$extension];
                        /** @var ConfigAdapter $object */
                        $object = new $class($file, $file);
                        $config->append($object);
                    }
                }
            }
        }
        return $config;
    }

    private function getFiles(string|array $directory, string $fileSuffix = null): array
    {
        $ds = DIRECTORY_SEPARATOR;

        if (is_string($directory)) {
            $directory = [$directory];
        }

        $extensions = [];
        foreach ($directory as $dir) {
            $pDir = rtrim($dir, $ds);
            if (!is_dir($pDir)) {
                throw new Exception('ConfigHarvesterGeneric::getFiles() ' . $pDir . ' not exists');
            }
            foreach (array_keys($this->adapters) as $extension) {
                $files = glob($pDir . $ds . '*' . $fileSuffix . '.' . $extension);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && !is_dir($file)) {
                        $extensions[$extension][] = $file;
                    }
                }
            }
        }
        return $extensions;
    }

    public function setExcludes(string $filename): self
    {
        $this->excludeFiles[] = $filename;
        return $this;
    }

}
