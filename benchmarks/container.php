<?php

declare(strict_types=1);

/**
 * Container micro-benchmark: SplDoublyLinkedList (current) vs a plain array.
 *
 * The builders store items and responsive variants in CssItemList /
 * BreakpointVariantList, both of which extend SplDoublyLinkedList. Their only
 * usage pattern is "push, then iterate once during build()". This script
 * isolates that pattern so the choice of container can be revisited with real
 * numbers rather than intuition. Run with: php benchmarks/container.php.
 */

use FlexGrid\Benchmarks\Bench;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/bench.php';

$bench = new Bench();

foreach ([1000, 10000, 50000] as $count) {
    $bench->section("push + single iterate, N = $count");

    $bench->run('SplDoublyLinkedList', static function () use ($count): int {
        $list = new SplDoublyLinkedList();

        for ($i = 0; $i < $count; $i++) {
            $list->push($i);
        }

        $sum = 0;

        foreach ($list as $value) {
            $sum += $value;
        }

        return $sum;
    });

    $bench->run('array (list<int>)', static function () use ($count): int {
        $list = [];

        for ($i = 0; $i < $count; $i++) {
            $list[] = $i;
        }

        $sum = 0;

        foreach ($list as $value) {
            $sum += $value;
        }

        return $sum;
    });

    $bench->report();

    $splMemory = measureMemory(static function () use ($count): object {
        $list = new SplDoublyLinkedList();

        for ($i = 0; $i < $count; $i++) {
            $list->push($i);
        }

        return $list;
    });

    $arrMemory = measureMemory(static function () use ($count): array {
        $list = [];

        for ($i = 0; $i < $count; $i++) {
            $list[] = $i;
        }

        return $list;
    });

    printf("  %-46s %9.1f KB retained\n", 'SplDoublyLinkedList', $splMemory / 1024);
    printf("  %-46s %9.1f KB retained\n", 'array (list<int>)', $arrMemory / 1024);
}

/** Retained bytes for the structure returned by $factory. */
function measureMemory(callable $factory): int
{
    $before = memory_get_usage();
    $held   = $factory();
    $after  = memory_get_usage();

    // Keep $held alive across the measurement, then release it.
    unset($held);

    return $after - $before;
}
