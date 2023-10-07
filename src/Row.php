<?php

namespace SashaBo\NginxConfParser;

class Row
{
    private string $name;
    /**
     * @var array<int, string>
     */
    private array $value;
    /**
     * @var array<int, self>
     */
    private array $rows;

    /**
     * @param string $name
     * @param array<int, string> $value
     * @param array<int, self> $rows
     */
    public function __construct(string $name, array $value, array $rows = [])
    {
        $this->name = $name;
        $this->value = $value;
        $this->rows = $rows;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
