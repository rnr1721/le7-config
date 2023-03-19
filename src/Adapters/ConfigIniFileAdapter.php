<?php

declare(strict_types=1);

namespace Core\Config\Adapters;

use Core\Config\Interfaces\ConfigAdapter;
use \Exception;

class ConfigIniFileAdapter implements ConfigAdapter
{

    private string $source = 'Ini config';
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
        $data = parse_ini_file($this->filename, true, INI_SCANNER_TYPED);
        if (!is_array($data)) {
            throw new Exception($this->source.": is not array:".$this->filename);
        }
        return $data;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getStrctCheck(): bool
    {
        return false;
    }

}
