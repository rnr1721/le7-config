<?php

declare(strict_types=1);

namespace Core\Config\Interfaces;

interface ConfigAdapter
{

    /**
     * Get data from adapter as array
     * @return array
     */
    public function get(): array;

    /**
     * Get source. It will be placed in exceptions
     * @return string
     */
    public function getSource(): string;

    /**
     * Strict check data type in source - file,ini or json
     * @return bool
     */
    public function getStrctCheck(): bool;
}
