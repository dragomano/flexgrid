<?php

declare(strict_types=1);

/**
 * End-to-end CSS build benchmark.
 *
 * Measures the cost of build() for containers with a large number of items
 * and responsive variants, so the string-assembly cost can be tracked over
 * time. Run with: composer bench (or: php benchmarks/build.php).
 */

use FlexGrid\Benchmarks\Bench;
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;
use FlexGrid\Flex;
use FlexGrid\Grid;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/bench.php';

$bench = new Bench();

$bench->section('Grid: build() with many items');

foreach ([500, 2000, 8000] as $count) {
    $bench->run("grid build, $count items", static function () use ($count): string {
        $grid = Grid::container('.grid')
            ->columns('1fr', '1fr', '1fr', '1fr')
            ->gap('1rem');

        for ($i = 0; $i < $count; $i++) {
            $grid->item(
                Grid::item(".cell-$i")
                    ->place($i % 6 + 1, $i % 4 + 1)
                    ->span(1, 2)
                    ->order($i)
            );
        }

        return $grid->build();
    });
}

$bench->report();

$bench->section('Flex: build() with many items');

foreach ([500, 2000, 8000] as $count) {
    $bench->run("flex build, $count items", static function () use ($count): string {
        $flex = Flex::container('.flex')
            ->flow(FlexDirection::Row, FlexWrap::Wrap)
            ->gap('0.5rem');

        for ($i = 0; $i < $count; $i++) {
            $flex->item(
                Flex::item(".cell-$i")
                    ->grow(1)
                    ->shrink(0)
                    ->basis('120px')
                    ->order($i)
            );
        }

        return $flex->build();
    });
}

$bench->report();

$bench->section('Grid: build() with many responsive variants');

foreach ([25, 100, 400] as $count) {
    $bench->run("grid build, $count media variants", static function () use ($count): string {
        $grid = Grid::container('.grid')->columns('1fr', '1fr');

        for ($i = 0; $i < $count; $i++) {
            $minWidth = 320 + $i * 16;
            $grid->responsive(
                $minWidth,
                static fn($variant) => $variant
                    ->columns('1fr', '1fr', '1fr')
                    ->gap("{$i}px")
            );
        }

        return $grid->build();
    });
}

$bench->report();

echo "\n" . Bench::peakMemory() . "\n";
