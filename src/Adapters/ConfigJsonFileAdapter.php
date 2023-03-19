<?php

declare(strict_types=1);

namespace Core\Config\Adapters;

use Core\Config\Interfaces\ConfigAdapter;
use \Exception;

class ConfigJsonFileAdapter implements ConfigAdapter
{

    private string $source = 'Json config';
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
        if (!file_exists($this->filename)) {
            throw new Exception($this->source . ' File not exists:' . $this->filename);
        }
        $data = file_get_contents($this->filename);
        return json_decode($data, true);
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
