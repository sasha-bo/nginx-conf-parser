<?php

namespace SashaBo\NginxConfParser;

use Iterator;

/**
 * @implements Iterator<non-negative-int, string>
 *
 * @internal
 */
class IterableString implements \Iterator
{
    private int $length;

    /** @var non-negative-int */
    private int $position = 0;

    /** @var non-negative-int */
    private int $line = 0;

    /** @var non-negative-int */
    private int $linePosition = 0;
    private ?string $current = null;

    public function __construct(
        private readonly string $source
    ) {
        $this->length = strlen($this->source);
        $this->read();
    }

    public function rewind(): void
    {
        $this->position = 0;
        $this->line = 0;
        $this->linePosition = 0;
        $this->read();
    }

    public function current(): string
    {
        return (string) $this->current;
    }

    private function read(): void
    {
        $this->current = substr($this->source, $this->position, 1);
    }

    public function next(): void
    {
        $newLine = "\n" == $this->current;
        ++$this->position;
        if ($newLine) {
            ++$this->line;
            $this->linePosition = 0;
        } else {
            ++$this->linePosition;
        }
        $this->read();
    }

    /** @return non-negative-int */
    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return $this->position < $this->length;
    }

    public function isLast(): bool
    {
        return $this->position == $this->length - 1;
    }

    /** @return non-negative-int */
    public function line(): int
    {
        return $this->line;
    }

    /** @return non-negative-int */
    public function linePosition(): int
    {
        return $this->linePosition;
    }
}
