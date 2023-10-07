<?php

namespace SashaBo\NginxConfParser;

interface SourceInterface
{
    /**
     * @return string|null
     *      Returns a char until reaches the end, then null
     */
    public function getChar(): ?string;
}
