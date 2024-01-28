<?php

namespace SashaBo\NginxConfParser;

class Parser
{
    /**
     * @return array<Row>
     *
     * @throws \Exception
     */
    public static function parseString(string $source): array
    {
        return self::getBlock(new IterableString($source), null);
    }

    /**
     * @return array<Row>
     *
     * @throws \Exception
     */
    public static function parseFile(string $path, bool $followIncludes = false): array
    {
        $string = file_get_contents($path);
        if (false === $string) {
            throw new \Exception('Can\'t read ' . $path);
        }
        $rows = self::getBlock(new IterableString($string), $path);

        return $followIncludes ? self::followIncludes($rows, $path) : $rows;
    }

    // Private methods

    /**
     * @return array<Row>
     *
     * @throws \Exception
     */
    private static function getBlock(IterableString $source, ?string $file): array
    {
        $ret = [];
        self::skipSpacesAndComments($source);
        while ($source->valid()) {
            if ('}' == $source->current()) {
                $source->next();

                break;
            }
            $ret[] = self::getRow($source, $file);
            self::skipSpacesAndComments($source);
        }

        return $ret;
    }

    private static function getRow(IterableString $source, ?string $file): Row
    {
        $startPosition = $source->key();
        $startLine = $source->line();
        $startLinePosition = $source->linePosition();
        $name = self::getWord($source);
        self::skipSpacesAndComments($source);
        $value = [];
        while ($source->valid() && !in_array($source->current(), ['{', ';', '}'], true)) {
            $value[] = self::getWord($source);
            self::skipSpacesAndComments($source);
        }
        $rows = [];
        if ('{' == $source->current()) {
            $source->next();
            $rows = self::getBlock($source, $file);
        } elseif (';' == $source->current()) {
            $source->next();
        } else {
            throw new \Exception('Missed ; on ' . $file . '[' . ($source->line() + 1) . ':' . ($source->linePosition() + 1) . ']');
        }

        /** @var positive-int $length */
        $length = $source->key() - $startPosition;

        return new Row(
            $name,
            $value,
            $rows,
            $file,
            $startPosition,
            $startLine,
            $startLinePosition,
            $length
        );
    }

    private static function getWord(IterableString $source): string
    {
        if (in_array($source->current(), ['\'', '"'], true)) {
            $quote = $source->current();
            $source->next();

            return self::getTillQuote($source, $quote);
        }
        $value = '';
        while ($source->valid()) {
            $char = $source->current();
            if ('#' == $source->current()) {
                self::skipTillEndOfLine($source);
                self::skipSpacesAndComments($source);

                break;
            }
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
            if ('\\' == $char) {
                $source->next();
                if ($source->valid() && in_array($source->current(), [$quote, '\\'], true)) {
                    $value .= $source->current();
                    $source->next();
                } else {
                    $value .= '\\';
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

    private static function skipSpacesAndComments(IterableString $source): void
    {
        self::skipSpaces($source);
        if ('#' == $source->current()) {
            self::skipTillEndOfLine($source);
            self::skipSpacesAndComments($source);
        }
    }

    private static function skipSpaces(IterableString $source): void
    {
        while ($source->valid() && '' == trim($source->current())) {
            $source->next();
        }
    }

    private static function skipTillEndOfLine(IterableString $source): void
    {
        do {
            $source->next();
        } while ($source->valid() && "\n" != $source->current());
        $source->next();
    }

    /**
     * @param array<Row> $rows
     *
     * @return array<Row>
     *
     * @throws \Exception
     */
    private static function followIncludes(array $rows, string $parentFile): array
    {
        $rowsWithIncludes = [];
        foreach ($rows as $row) {
            if (count($row->rows) > 0) {
                $row = new Row(
                    $row->name,
                    $row->values,
                    self::followIncludes($row->rows, $parentFile),
                    $row->file,
                    $row->position,
                    $row->line,
                    $row->linePosition,
                    $row->length
                );
            }
            if ('include' == $row->name) {
                foreach ($row->values as $pattern) {
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
