<?php

namespace Core\Config\Interfaces;

interface ConfigHarvester
{

    /**
     * Add apapter for config files with some extension
     * @param string $extension File extension for process
     * @param string $adapter Class that implements ConfigAdapter interface
     * @return self
     */
    public function addAdapter(string $extension, string $adapter): self;

    /**
     * Get Config object that implements Config interface with
     * harvested configuration
     * @param string|array $directory Directory or directories for find config
     * @param string $fileSuffix
     * @return Config
     */
    public function getConfig(string|array $directory, string $fileSuffix = '', string $cacheKey = 'config'): Config;

    /**
     * Set filenames that couldnt not be included to config
     * @param string $filename Filename without path
     * @return self
     */
    public function setExcludes(string $filename): self;
}
