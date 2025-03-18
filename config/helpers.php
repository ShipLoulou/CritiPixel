<?php

declare(strict_types=1);

/**
 * @template T
 * @param int $start_index
 * @param int $count
 * @param callable(int): T $callback
 * @return array<T>
 */
function array_fill_callback(int $startIndex, int $count, callable $callback): array
{
    /** @var array<int, T> $data */
    $data = [];

    for ($i = $startIndex; $i < $startIndex + $count; ++$i) {
        $data[$i] = $callback($i);
    }

    return $data;
}
