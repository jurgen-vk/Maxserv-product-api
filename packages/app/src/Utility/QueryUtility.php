<?php

declare(strict_types=1);

namespace MaxServ\App\Utility;

final class QueryUtility
{
    public static function buildPositionalPlaceholders(int $rowCount, int $columnCount): string
    {
        if ($rowCount < 1) {
            throw new \InvalidArgumentException(message: '$rowCount must be at least 1.');
        }

        $placeholders = implode(
            separator: ', ',
            array: array_fill(start_index: 0, count: $columnCount, value: '?'),
        );

        $group = "($placeholders)";
        return implode(
            separator: ', ',
            array: array_fill(start_index: 0, count: $rowCount, value: $group),
        );
    }

    /** @return array{0: string, 1: array<string, mixed>} [placeholder list, bindings] */
    public static function buildNamedPlaceholders(string $prefix, array $values): array
    {
        if ($values === []) {
            throw new \InvalidArgumentException(message: '$values must not be empty.');
        }

        $placeholders = [];
        $bindings = [];

        foreach (array_values(array: $values) as $i => $value) {
            $name = ":{$prefix}{$i}";
            $placeholders[] = $name;
            $bindings[$name] = $value;
        }

        return [implode(separator: ', ', array: $placeholders), $bindings];
    }
}
