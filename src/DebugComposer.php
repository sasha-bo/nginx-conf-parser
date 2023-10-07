<?php

namespace SashaBo\NginxConfParser;

class DebugComposer extends Composer
{
    protected static function composeName(string $name): string
    {
        return '['.parent::composeName($name).']';
    }

    protected static function composeValue(string $value): string
    {
        return '['.parent::composeValue($value).']';
    }
}
