<?php

namespace Core\Interfaces;

use Core\Interfaces\ConfigInterface;

interface ConfigFactoryInterface
{

    /**
     * Get configured Config object from array
     * @param array $array Array for config
     * @param string|null $source Source name
     * @return ConfigInterface
     */
    public function fromArray(array $array, ?string $source = null): ConfigInterface;

    /**
     * Get configured Config object from filename (PHP array)
     * It require php file with returned array inside
     * @param string $filename PHP file name
     * @param string|null $source Source name
     * @param string $cacheKey Key for cache
     * @return ConfigInterface
     */
    public function fromArrayFile(string $filename, ?string $source = null, string $cacheKey = 'config'): ConfigInterface;

    /**
     * Get configured Config object from filename (JSON)
     * @param string $filename Json file with path
     * @param string|null $source Source name
     * @param string $cacheKey Key for cache
     * @return ConfigInterface
     */
    public function fromJsonFile(string $filename, ?string $source = null, string $cacheKey = 'config'): ConfigInterface;

    /**
     * Get configured Config object from filename (INI file)
     * @param string $filename INI file with path
     * @param string|null $source Source name
     * @param string $cacheKey Key for cache
     * @return ConfigInterface
     */
    public function fromIniFile(string $filename, ?string $source = null, string $cacheKey = 'config'): ConfigInterface;

    /**
     * Compile data from all configs in different formats in directories
     * @param string|array $directory Directory or directories that exists
     * @param string $filenameSuffix Suffix after filename before extension
     * @param string $cacheKey Key for cache
     * @return ConfigInterface
     */
    public function harvest(string|array $directory, string $filenameSuffix = '', string $cacheKey = 'config'): ConfigInterface;
}
