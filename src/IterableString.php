<?php

namespace SashaBo\NginxConfParser;

/** @implements \Iterator<int, string> */
class IterableString implements \Iterator
{
    protected int $length;
    protected int $position = 0;
    protected ?string $current = null;

    public function __construct(
        protected readonly string $source
    ) {
        $this->setLength();
    }

    protected function setLength(): void
    {
        $this->length = strlen($this->source);
    }

    public function rewind(): void
    {
        $this->position = 0;
        $this->current = null;
    }

    public function current(): string
    {
        return $this->current ?? $this->current = substr($this->source, $this->position, 1);
    }

    public function next(int $steps = 1): void
    {
        $this->position += $steps;
        $this->current = null;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return $this->position >= 0 && $this->position < $this->length;
    }

    public function isLast(): bool
    {
        return $this->position == $this->length - 1;
    }
}
