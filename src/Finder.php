<?php

namespace SashaBo\NginxConfParser;

class Finder
{
    /**
     * @param array<Row> $rows
     *
     * @return array<Row>
     */
    public static function findByName(array $rows, string $name): array
    {
        $ret = [];
        foreach ($rows as $row) {
            if ($row->name == $name) {
                $ret[] = $row;
            }
        }

        return $ret;
    }

    /**
     * @param array<Row> $rows
     *
     * @return ?Row
     */
    public static function findOneByName(array $rows, string $name): ?Row
    {
        foreach ($rows as $row) {
            if ($row->name == $name) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param array<Row>    $rows
     * @param array<string> $path
     *
     * @return array<Row>
     */
    public static function findByPath(array $rows, array $path): array
    {
        $ret = [];
        $name = array_shift($path);
        if (is_string($name)) {
            if ([] === $path) {
                return self::findByName($rows, $name);
            }
            foreach (self::findByName($rows, $name) as $row) {
                $ret = [...$ret, ...self::findByPath($row->rows, $path)];
            }
        }

        return $ret;
    }

    /**
     * @param array<Row>    $rows
     * @param array<string> $path
     *
     * @return ?Row
     */
    public static function findOneByPath(array $rows, array $path): ?Row
    {
        $name = array_shift($path);
        if (is_string($name)) {
            if ([] === $path) {
                return self::findOneByName($rows, $name);
            }
            foreach (self::findByName($rows, $name) as $row) {
                $ret = self::findOneByPath($row->rows, $path);
                if (!is_null($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }
}
