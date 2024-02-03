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
            $ret .= static::composeRowRec($row, $tab);
        }

        return $ret;
    }

    protected static function composeRowRec(Row $row, string $tab = ''): string
    {
        $ret = $tab . static::composeName($row->name);
        $values = static::composeValues($row);
        if ('' != $values) {
            $ret .= ' ' . $values;
        }
        if (count($row->rows) > 0) {
            $ret .= ' {' . static::NEW_LINE . static::composeRec($row->rows, $tab . static::TAB) . $tab . '}' . static::NEW_LINE;
        } else {
            $ret .= ';' . static::NEW_LINE;
        }

        return $ret;
    }

    protected static function composeName(string $name): string
    {
        return self::quoteIfNecessary($name);
    }

    protected static function composeValues(Row $row): string
    {
        $values = [];
        foreach ($row->values as $value) {
            $values[] = static::composeValue($value);
        }

        return implode(' ', $values);
    }

    protected static function composeValue(string $value): string
    {
        return self::quoteIfNecessary($value);
    }

    protected static function quoteIfNecessary(string $value): string
    {
        return '' == $value || preg_match('/[\s\'"]/', $value) ? static::addQuotes($value) : $value;
    }

    protected static function addQuotes(string $value): string
    {
        return '\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $value) . '\'';
    }
}
