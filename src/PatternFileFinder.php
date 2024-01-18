<?php

namespace SashaBo\NginxConfParser;

class PatternFileFinder
{
    /**
     * @return array<string>
     *                       for example ['/etc/nginx/conf.d/default.conf']
     */
    public static function find(string $pattern, string $parentFile): array
    {
        if ('/' != substr($pattern, 0, 1)) {
            [$rootDir] = self::getDirAndFile($parentFile);
            $pattern = $rootDir . '/' . $pattern;
        }
        [$directory, $filePattern] = self::getDirAndFile($pattern);
        if ('*' == substr($filePattern, 0, 1)) {
            $fileEnd = substr($filePattern, 1);

            return self::scan($directory, $fileEnd);
        }

        return [$pattern];
    }

    /**
     * @return array<string>
     */
    private static function scan(string $directory, string $fileEnd): array
    {
        $fileEndLength = strlen($fileEnd);
        $dp = opendir($directory);
        if (false === $dp) {
            throw new \Exception('Can\'t open directory ' . $directory);
        }
        $ret = [];
        while (($fileName = readdir($dp)) !== false) {
            if ('.' != $fileName[0] && substr($fileName, -1 * $fileEndLength) == $fileEnd) {
                $ret[] = $directory . '/' . $fileName;
            }
        }

        return $ret;
    }

    /**
     * @return array<string>
     *                       [/dir, filename]
     */
    private static function getDirAndFile(string $filePath): array
    {
        $lastSlashPos = strrpos($filePath, '/');

        return (false === $lastSlashPos)
            ? ['', $filePath]
            : [
                substr($filePath, 0, $lastSlashPos),
                substr($filePath, $lastSlashPos + 1),
            ];
    }
}
