<?php

declare(strict_types=1);

namespace MaxServ\App\Utility;

final class QueryUtility
{
    public static function buildPositionalPlaceholders(int $rowCount, int $columnsPerRow): string
    {
        if ($rowCount < 1) {
            throw new \InvalidArgumentException('$rowCount must be at least 1.');
        }

        $group = '(' . implode(', ', array_fill(0, $columnsPerRow, '?')) . ')';
        return implode(', ', array_fill(0, $rowCount, $group));
    }

    /** @return array{0: string, 1: array<string, mixed>} [placeholder list, bindings] */
    public static function buildNamedPlaceholders(string $prefix, array $values): array
    {
        if ($values === []) {
            throw new \InvalidArgumentException('$values must not be empty.');
        }

        $placeholders = [];
        $bindings = [];

        foreach (array_values($values) as $i => $value) {
            $name = ":{$prefix}{$i}";
            $placeholders[] = $name;
            $bindings[$name] = $value;
        }

        return [implode(', ', $placeholders), $bindings];
    }
}
