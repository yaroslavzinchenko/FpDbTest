<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        // Обработка спецификаторов.
        $argIndex = 0;
        $query = preg_replace_callback(
            '/\?([d|f|a|#]?)/',
            function ($matches) use (&$args, &$argIndex) {
                $type = $matches[1];
                $value = $args[$argIndex++] ?? null;

                return $this->formatValue($value, $type);
            },
            $query
        );

        // Обработка условных блоков.
        $query = preg_replace_callback(
            '/{(.+?)}/',
            function ($matches) {
                $content = $matches[1];
                if (str_contains($content, $this->skip())) {
                    return '';
                }
                return $content;
            },
            $query
        );

        return $query;
    }

    public function skip(): int
    {
        return 2222222;
    }

    private function formatValue($value, string $type = null): string
    {
        switch ($type) {
            case 'd':
                return (int)$value;
            case 'f':
                return (float)$value;
            case 'a':
                if (!is_array($value)) {
                    throw new Exception('Expected an array.');
                }
                return $this->formatArray($value);
            case '#':
                return $this->formatIdentifier($value);
            default:
                return $this->formatScalar($value);
        }
    }

    private function formatArray(array $array): string
    {
        if (array_is_list($array)) {
            $formatted = array_map(function ($item) {
                return $this->formatScalar($item);
            }, $array);
        } else {
            $formatted = [];
            foreach ($array as $key => $value) {
                $formatted[] = $this->formatIdentifier($key) . ' = ' . $this->formatValue($value);
            }
        }
        return implode(', ', $formatted);
    }

    private function formatIdentifier($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, 'formatIdentifier'], $value));
        }
        if (!is_string($value)) {
            throw new Exception('Identifier must be a string.');
        }
        return '`' . $this->mysqli->real_escape_string($value) . '`';
    }

    private function formatScalar($value): string
    {
        return match (true) {
            is_null($value) => 'NULL',
            is_bool($value) => $value ? '1' : '0',
            is_int($value) || is_float($value) => $value,
            is_string($value) => '\'' . $this->mysqli->real_escape_string($value) . '\'',
            default => throw new Exception('Unsupported data type.'),
        };
    }
}