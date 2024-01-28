<?php

namespace SashaBo\NginxConfParser;

class VariableReplacer
{
    public const MISSED_SET_EMPTY = 'set-empty';
    public const MISSED_IGNORE = 'ignore';
    public const MISSED_THROW_EXCEPTION = 'exception';

    /**
     * @param array<Row>            $rows
     * @param array<string, string> $variables
     *
     * @return array<Row>
     *
     * @throws \Exception
     */
    public static function replace(array $rows, array $variables, string $missedMode = self::MISSED_IGNORE): array
    {
        $ret = [];
        foreach ($rows as $row) {
            $ret[] = self::replaceRow($row, $variables, $missedMode);
        }

        return $ret;
    }

    /**
     * @param array<string, string> $variables
     *
     * @throws \Exception
     */
    public static function replaceRow(Row $row, array $variables, string $missedMode = self::MISSED_IGNORE): Row
    {
        $values = [];
        foreach ($row->values as $value) {
            $value = (string) preg_replace_callback('/\$([a-z0-9_]+)/i', function (array $matches) use ($row, $variables, $missedMode) {
                if (isset($variables[$matches[1]])) {
                    return $variables[$matches[1]];
                }
                if (self::MISSED_THROW_EXCEPTION == $missedMode) {
                    throw new \Exception(
                        $matches[0] . ' for parameter ' . $row->name . ' on line ' . $row->line
                        . ' of ' . $row->file . ' is not set'
                    );
                }
                if (self::MISSED_SET_EMPTY == $missedMode) {
                    return '';
                }

                return $matches[0];
            }, $value);
            $values[] = $value;
        }

        return new Row(
            $row->name,
            $values,
            self::replace($row->rows, $variables, $missedMode),
            $row->file,
            $row->position,
            $row->line,
            $row->linePosition,
            $row->length
        );
    }
}
