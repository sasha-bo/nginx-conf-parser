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
     * @param array<int, string> $value
     * @param array<int, self>   $rows
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

    /**
     * @return string[]
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}
