<?php

declare(strict_types=1);

namespace Core\Config\Adapters;

use Core\Config\Interfaces\ConfigAdapter;

class ConfigArrayAdapter implements ConfigAdapter
{

    private string $source = 'Array config';
    private array $config;

    public function __construct(array $config = [], ?string $source = null)
    {
        $this->config = $config;
        if ($source !== null) {
            $this->source = $source;
        }
    }

    public function get(): array
    {
        return $this->config;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getStrctCheck(): bool
    {
        return true;
    }

}
