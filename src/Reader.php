<?php

namespace SashaBo\NginxConfParser;

class Reader
{
    private SourceInterface $source;
    private int $lineNumber;
    private int $nextCharLineNumber = 1;
    private int $linePosition;
    private int $nextCharLinePosition = 1;
    private ?string $currentChar = null;
    private ?string $nextChar = null;

    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
        $this->currentChar = $this->readSource();
        if (!is_null($this->currentChar)) {
            $this->nextChar = $this->readSource();
        }
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function getLinePosition(): int
    {
        return $this->linePosition;
    }

    public function getNextChar(): string
    {
        if (is_null($this->nextChar)) {
            throw new OverEndException();
        }

        return $this->nextChar;
    }

    /**
     * @throws OverEndException
     */
    public function getCurrentChar(): string
    {
        if (is_null($this->currentChar)) {
            throw new OverEndException();
        }

        return $this->currentChar;
    }

    public function finished(): bool
    {
        return is_null($this->currentChar);
    }

    public function isLast(): bool
    {
        return is_null($this->nextChar);
    }

    /**
     * @throws OverEndException
     */
    public function move(): void
    {
        $this->currentChar = $this->nextChar;
        $this->nextChar = $this->readSource();
    }

    private function readSource(): ?string
    {
        $this->lineNumber = $this->nextCharLineNumber;
        $this->linePosition = $this->nextCharLinePosition;
        $char = $this->source->getChar();
        if (is_null($char)) {
            return null;
        } elseif ($char == "\n") {
            $this->nextCharLineNumber ++;
            $this->nextCharLinePosition = 1;
        } else {
            ++$this->nextCharLinePosition;
            if ($char == '#') {
                do {
                    $char = $this->readSource();
                } while (!is_null($char) && $char != "\n");
            }
        }
        return $char;
    }
}
