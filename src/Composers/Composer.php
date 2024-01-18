<?php

namespace SashaBo\NginxConfParser\Composers;

use SashaBo\NginxConfParser\Row;

class Composer
{
    protected const NEW_LINE = "\n";
    protected const TAB = '    ';

    /**
     * @param array<Row> $rows
     */
    public static function compose(array $rows): string
    {
        return static::composeRec($rows);
    }

    /**
     * @param array<Row> $rows
     */
    protected static function composeRec(array $rows, string $tab = ''): string
    {
        $ret = '';
        foreach ($rows as $row) {
            $ret .= $tab . static::composeName($row->getName());
            $values = static::composeValues($row);
            if ('' != $values) {
                $ret .= ' ' . $values;
            }
            $rows = $row->getRows();
            if (count($rows) > 0) {
                $ret .= ' {' . static::NEW_LINE . static::composeRec($rows, $tab . static::TAB) . $tab . '}' . static::NEW_LINE;
            } else {
                $ret .= ';' . static::NEW_LINE;
            }
        }

        return $ret;
    }

    protected static function composeName(string $name): string
    {
        return $name;
    }

    protected static function composeValues(Row $row): string
    {
        $values = [];
        foreach ($row->getValue() as $value) {
            $values[] = static::composeValue($value);
        }

        return implode(' ', $values);
    }

    protected static function composeValue(string $value): string
    {
        return preg_match('/[\s\'"]/', $value) ? static::addQuotes($value) : $value;
    }

    protected static function addQuotes(string $value): string
    {
        return '\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $value) . '\'';
    }
}
