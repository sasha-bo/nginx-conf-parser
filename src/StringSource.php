<?php

namespace SashaBo\NginxConfParser;

class StringSource implements SourceInterface
{
    private string $source;
    private int $position = 0;
    private int $length;

    public function __construct(string $source)
    {
        $this->source = $source;
        $this->length = strlen($source);
    }

    public function getChar(): ?string
    {
        if ($this->position < $this->length) {
            $ret = (string) substr($this->source, $this->position, 1);
            ++$this->position;
            return $ret;
        }

        return null;
    }
}
