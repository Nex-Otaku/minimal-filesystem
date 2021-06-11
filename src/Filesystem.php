<?php

namespace NexOtaku\MinimalFilesystem;

class Filesystem
{
    public function createDirectory(string $path): void
    {
        /**
         * First we check that folder exists.
         * If it does not exist, we try to create folder, and check that it was created.
         * Do not remove first check, it is important.
         */
        if (is_dir($path)) {
            return;
        }

        /**
         * Permissions are modified by current umask,
         * so even here provided 777 permissions (0777 octal)
         * it does not mean that permissions for newly created folder will be 777 (rwx).
         *
         * See docs: https://www.php.net/manual/en/function.mkdir.php
         *
         * If you want to set specific permissions to folder, call chmod().
         */
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException("Failed to create directory \"{$path}\"");
        }
    }

    public function writeFile(string $filePath, string $content): void
    {
        $this->createDirectoryForFile($filePath);
        $result = file_put_contents($filePath, $content);

        if ($result === false) {
            throw new \RuntimeException("Failed to write file \"{$filePath}\"");
        }
    }

    public function appendToFile(string $filePath, string $content): void
    {
        $this->createDirectoryForFile($filePath);
        $result = file_put_contents($filePath, $content, FILE_APPEND);

        if ($result === false) {
            throw new \RuntimeException("Failed to append to file \"{$filePath}\"");
        }
    }

    private function createDirectoryForFile(string $filePath): void
    {
        $dirname = dirname($filePath);
        $this->createDirectory($dirname);
    }

    public function isReadableFile(string $path): bool
    {
        return file_exists($path)
            && is_file($path)
            && is_readable($path);
    }

    public function readFile(string $path): ?string
    {
        try {
            $content = file_get_contents($path);
        } catch (\Throwable $throwable) {
            return null;
        }

        if (!is_string($content)) {
            return null;
        }

        return $content;
    }

    public function renameFile(string $source, string $destination): void
    {
        try {
            $result = rename($source, $destination);
        } catch (\Exception $exception) {
            throw new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($result === false) {
            throw new \RuntimeException("Failed to rename \"{$source}\" to \"{$destination}\"");
        }
    }

    public function getCurrentDirectory(): string
    {
        $currentDirectory = getcwd();

        if (!is_string($currentDirectory)) {
            throw new \RuntimeException('Failed to get current directory');
        }

        return $currentDirectory;
    }

    public function deleteFile(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $result = unlink($path);

        if (!$result) {
            throw new \RuntimeException("Failed to delete file: \"{$path}\"");
        }
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function existsFile(string $path): bool
    {
        return file_exists($path)
            && is_file($path);
    }

    public function isFile(string $path): bool
    {
        return $this->existsFile($path);
    }

    public function existsDirectory(string $path): bool
    {
        return file_exists($path)
            && is_dir($path);
    }

    public function isDirectory(string $path): bool
    {
        return $this->existsDirectory($path);
    }

    public function listFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $entries = scandir($path);
        $filtered = array_diff($entries, ['.', '..']);

        // Reindexing array after "array_diff".
        return array_values($filtered);
    }

    public function searchFiles(string $path, string $mask): array
    {
        $contents = $this->getDirectoryContents($path, false);

        return $this->filterByMask($contents, $mask);
    }

    public function searchFilesRecursively(string $path, string $mask): array
    {
        $contents = $this->getDirectoryContents($path, true);

        return $this->filterByMask($contents, $mask);
    }

    private function getDirectoryContents(string $path, bool $recursive): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $entries = scandir($path);

        if (!is_array($entries)) {
            throw new \RuntimeException("Failed to read directory contents at \"{$path}\"");
        }

        $filtered = array_diff($entries, ['.', '..']);

        $fullPathEntries = array_map(function ($entry) use ($path) {
            return $path . DIRECTORY_SEPARATOR . $entry;
        }, $filtered);

        if ($recursive) {
            $subEntries = [];

            foreach ($fullPathEntries as $entry) {
                if (!$this->isDirectory($entry)) {
                    continue;
                }

                $subEntries[] = $this->getDirectoryContents($entry, true);
            }

            $fullPathEntries = array_merge($fullPathEntries, ...$subEntries);
        }

        return $fullPathEntries;
    }

    private function getLastPathPart(string $path): string
    {
        $trimmed = trim($path);

        if ($trimmed === '') {
            return '';
        }

        $delimiter = '';

        if ($this->contains($trimmed, '\\')) {
            $delimiter = '\\';
        }

        if ($this->contains($trimmed, '/')) {
            $delimiter = '/';
        }

        if ($delimiter === '') {
            return $trimmed;
        }

        $parts = explode(DIRECTORY_SEPARATOR, $trimmed);

        $filtered = [];
        foreach ($parts as $part) {
            $trimmed = trim($part);

            if ($trimmed === '') {
                continue;
            }

            $filtered[] = $part;
        }

        if (count($filtered) === 0) {
            return '';
        }

        return array_pop($filtered);
    }

    private function filterByMask(array $entries, string $mask): array
    {
        $result = [];

        foreach ($entries as $entry) {
            $name = $this->getLastPathPart($entry);

            if ($this->startsWith($mask, '*')) {
                $stripped = $this->stripPrefix($mask, '*');
                if ($this->endsWith($name, $stripped)) {
                    $result[] = $entry;
                }
            } else if ($this->endsWith($mask, '*')) {
                $stripped = $this->stripSuffix($mask, '*');
                if ($this->startsWith($name, $stripped)) {
                    $result[] = $entry;
                }
            } else if ($name === $mask) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    private function startsWith(string $text, string $with): bool
    {
        $bytes = strlen($with);

        if ($bytes === 0) {
            return true;
        }

        return strncmp($text, $with, $bytes) === 0;
    }

    private function endsWith(string $text, string $with): bool
    {
        if (!$bytes = strlen($with)) {
            return true;
        }

        if (strlen($text) < $bytes) {
            return false;
        }

        return substr_compare($text, $with, -$bytes, $bytes) === 0;
    }

    private function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }

    private function stripPrefix(string $prefixedText, string $prefix): string
    {
        if (strpos($prefixedText, $prefix) !== 0) {
            return $prefixedText;
        }

        return substr($prefixedText, strlen($prefix));
    }

    private function stripSuffix(string $text, string $suffix): string
    {
        if (!$this->endsWith($text, $suffix)) {
            return $text;
        }

        return substr($text, 0, -strlen($suffix));
    }
}