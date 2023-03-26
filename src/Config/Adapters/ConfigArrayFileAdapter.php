<?php

declare(strict_types=1);

namespace Core\Config\Adapters;

use Core\Interfaces\ConfigAdapter;

use \Exception;

class ConfigArrayFileAdapter implements ConfigAdapter
{

    private string $source = 'Array file config';
    private string $filename;

    public function __construct(string $filename, ?string $source = null)
    {
        $this->filename = $filename;
        if ($source !== null) {
            $this->source = $source;
        }
    }

    public function get(): array
    {
        if (file_exists($this->filename)) {
            $result = require $this->filename;
            if (!is_array($result)) {
                throw new Exception($this->source.": is not array:".$this->filename);
            }
            return $result;
        } else {
            throw new Exception($this->source.": File not exists:".$this->filename);
        }
        return [];
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
