<?php

namespace SashaBo\NginxConfParser;

/** @internal */
class Row
{
    /**
     * @param array<int, string> $values
     * @param array<int, self>   $rows
     * @param non-negative-int   $position
     * @param non-negative-int   $line
     * @param non-negative-int   $linePosition
     * @param positive-int       $length
     */
    public function __construct(
        public readonly string $name,
        public readonly array $values,
        public readonly array $rows,
        public readonly ?string $file,
        public readonly int $position,
        public readonly int $line,
        public readonly int $linePosition,
        public readonly int $length,
    ) {}

    /** @deprecated 1.1.0 Use public readonly property instead */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated 1.1.0 Use public readonly property instead
     *
     * @return string[]
     */
    public function getValue(): array
    {
        return $this->values;
    }

    /**
     * @deprecated 1.1.0 Use public readonly property instead
     *
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}
