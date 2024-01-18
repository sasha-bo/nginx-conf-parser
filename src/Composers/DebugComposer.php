<?php

namespace SashaBo\NginxConfParser\Composers;

use SashaBo\NginxConfParser\Row;

class DebugComposer extends Composer
{
    protected static function composeName(string $name): string
    {
        return '[' . parent::composeName($name) . ']';
    }

    protected static function composeValue(string $value): string
    {
        return '[' . parent::composeValue($value) . ']';
    }

    protected static function composeRowRec(Row $row, string $tab = ''): string
    {
        return $tab . '# ' . $row->file . ': ' . $row->position . ' (' . $row->line . ':' . $row->linePosition . ') '
            . $row->length . " symbols\n"
            . parent::composeRowRec($row, $tab);
    }
}
