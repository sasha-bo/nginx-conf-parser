<?php

namespace SashaBo\NginxConfParser;

use SashaBo\IterableString\IterableString;

class Parser
{
    /**
     * @param string $source
     * @return array<Row>
     */
    public static function parse(string $source): array
    {
        return static::getBlock(new IterableString($source));
    }

    /**
     * @param string $path
     * @param bool $followIncludes
     * @return array<Row>
     * @throws \Exception
     */
    public static function parseFile(string $path, bool $followIncludes = false): array
    {
        $string = file_get_contents($path);
        if ($string === false) {
            throw new \Exception('Can\'t read '.$path);
        }
        $rows = static::parse($string);
        return $followIncludes ? self::followIncludes($rows, $path) : $rows;
    }

    /****************************************************************************
     * Private methods
     ***************************************************************************/

    /**
     * @return array<Row>
     * @throws \Exception
     */
    private static function getBlock(IterableString $source): array
    {
        $ret = [];
        self::skipSpaces($source);
        while ($source->valid()) {
            if ($source->current() == '}') {
                $source->next();
                break;
            }
            $ret[] = self::getRow($source);
            self::skipSpaces($source);
        }

        return $ret;
    }

    private static function getRow(IterableString $source): Row
    {
        $name = self::getWord($source);
        self::skipSpaces($source);
        $value = [];
        while ($source->valid() && !in_array($source->current(), ['{', ';'])) {
            $value[] = self::getWord($source);
            self::skipSpaces($source);
        }
        if ($source->current() == '{') {
            $source->next();
            $rows = self::getBlock($source);
        } elseif ($source->current() != ';') {
            throw new \Exception('Expected ; on '.$source->key());
        } else {
            $rows = [];
        }
        $source->next();
        return new Row($name, $value, $rows);
    }

    private static function getWord(IterableString $source): string
    {
        $value = '';
        if (in_array($source->current(), ['\'', '"'], true)) {
            $quote = $source->current();
            $source->next();
            return self::getTillQuote($source, $quote);
        }
        while ($source->valid()) {
            $char = $source->current();
            if (preg_match('/^[^\s;\{]+$/i', $char)) {
                $value .= $char;
                $source->next();
            } else {
                break;
            }
        }

        return $value;
    }

    private static function getTillQuote(IterableString $source, string $quote = '\''): string
    {
        $value = '';
        while ($source->valid()) {
            $char = $source->current();
            if ($char == '\\') {
                if (!$source->isLast() && in_array($source->current(2), ['\\'.$quote, '\\\\'], true)) {
                    $source->next();
                    $value .= $source->current();
                    $source->next();
                } else {
                    $value .= '\\';
                    $source->next();
                }
            } elseif ($char == $quote) {
                $source->next();
                break;
            } else {
                $value .= $char;
                $source->next();
            }
        }

        return $value;
    }

    public static function skipSpaces(IterableString $source): void
    {
        while ($source->valid() && trim($source->current()) == '') {
            $source->next();
        }
    }

    /**
     * @param array<Row> $rows
     * @return array<Row>
     * @throws \Exception
     */
    public static function followIncludes(array $rows, string $parentFile): array
    {
        $rowsWithIncludes = [];
        foreach ($rows as $row) {
            if (count($row->getRows()) > 0) {
                $row = new Row($row->getName(), $row->getValue(), self::followIncludes($row->getRows(), $parentFile));
            }
            if ($row->getName() == 'include') {
                foreach ($row->getValue() as $pattern) {
                    foreach (PatternFileFinder::find($pattern, $parentFile) as $includeFile) {
                        foreach (self::parseFile($includeFile, true) as $includeRow) {
                            $rowsWithIncludes[] = $includeRow;
                        }
                    }
                }
            } else {
                $rowsWithIncludes[] = $row;
            }
        }
        return $rowsWithIncludes;
    }
}
