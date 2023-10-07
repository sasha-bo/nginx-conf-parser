<?php

namespace SashaBo\NginxConfParser;

class FileSource implements SourceInterface
{
    private $file;
    public function __construct(string $path)
    {
        $this->file = fopen($path, 'r');
        if ($this->file === false) {
            throw new CantOpenFileException('Can\'t open file '.$path);
        }
    }

    public function getChar(): ?string
    {
        return feof($this->file) ? null : fgetc($this->file);
    }
}
