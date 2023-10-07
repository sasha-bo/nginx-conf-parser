<?php

namespace SashaBo\NginxConfParser;

class Parser
{
    /**
     * @param SourceInterface $source
     * @return array<Row>
     */
    public static function parse(SourceInterface $source): array
    {
        $reader = new Reader($source);
        return static::getBlock($reader);
    }

    /**
     * @param string $source
     * @return array<Row>
     */
    public static function parseString(string $source): array
    {
        return static::parse(new StringSource($source));
    }

    /**
     * @param string $source
     * @return array<Row>
     */
    public static function parseFile(string $path, bool $followIncludes = false): array
    {
        $rows = static::parse(new FileSource($path));
        return $followIncludes ? self::followIncludes($rows, $path) : $rows;
    }

    /****************************************************************************
     * Private methods
     ***************************************************************************/

    /**
     * @return array<Row>
     */
    private static function getBlock(Reader $reader): array
    {
        $ret = [];
        self::skipSpaces($reader);
        while (!$reader->finished()) {
            if ($reader->getCurrentChar() == '}') {
                $reader->move();
                break;
            }
            $ret[] = self::getRow($reader);
            self::skipSpaces($reader);
        }

        return $ret;
    }

    private static function getRow(Reader $reader): Row
    {
        $name = self::getWord($reader);
        self::skipSpaces($reader);
        $value = [];
        while (!$reader->finished() && !in_array($reader->getCurrentChar(), ['{', ';'])) {
            $value[] = self::getWord($reader);
            self::skipSpaces($reader);
        }
        if ($reader->getCurrentChar() == '{') {
            $reader->move();
            $rows = self::getBlock($reader);
        } elseif ($reader->getCurrentChar() != ';') {
            throw new \Exception('Expected ; on '.$reader->getLineNumber().'.'.$reader->getLinePosition());
        } else {
            $rows = [];
        }
        $reader->move();
        return new Row($name, $value, $rows);
    }

    private static function getWord(Reader $reader): string
    {
        $value = '';
        if (in_array($reader->getCurrentChar(), ['\'', '"'], true)) {
            $quote = $reader->getCurrentChar();
            $reader->move();
            return self::getTillQuote($reader, $quote);
        }
        while (!$reader->finished()) {
            $char = $reader->getCurrentChar();
            if (preg_match('/^[^\s;\{]+$/i', $char)) {
                $value .= $char;
                $reader->move();
            } else {
                break;
            }
        }

        return $value;
    }

    private static function getTillQuote(Reader $reader, string $quote = '\''): string
    {
        $value = '';
        while (!$reader->finished()) {
            $char = $reader->getCurrentChar();
            if ($char == '\\') {
                if (!$reader->isLast() && in_array($reader->getNextChar(), [$quote, '\\'], true)) {
                    $value .= $reader->getNextChar();
                    $reader->move();
                    $reader->move();
                } else {
                    $value .= '\\';
                    $reader->move();
                }
            } elseif ($char == $quote) {
                $reader->move();
                break;
            } else {
                $value .= $char;
                $reader->move();
            }
        }

        return $value;
    }

    public static function skipSpaces(Reader $reader): void
    {
        while (!$reader->finished() && trim($reader->getCurrentChar()) == '') {
            $reader->move();
        }
    }

    /**
     * @param array<Row> $rows
     * @return array<Row>
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
