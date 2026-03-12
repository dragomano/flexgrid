<?php

declare(strict_types=1);

use FlexGrid\GridArea;

describe('GridArea', function () {
    it('builds named area', function () {
        expect(GridArea::named('header')->build())->toBe(['grid-area' => 'header']);
    });

    it('builds row/column with spans', function () {
        $props = GridArea::at(1, 1)->spanRows(2)->spanColumns(3)->build();
        expect($props['grid-row'])->toBe('1 / span 2')
            ->and($props['grid-column'])->toBe('1 / span 3');
    });

    it('builds explicit end lines', function () {
        $props = GridArea::at(2, 3)->rowEnd(5)->columnEnd(7)->build();
        expect($props['grid-row'])->toBe('2 / 5')
            ->and($props['grid-column'])->toBe('3 / 7');
    });

    it('uses auto end when no span or end is given', function () {
        $props = GridArea::at(1, 1)->build();
        expect($props['grid-row'])->toBe('1 / auto')
            ->and($props['grid-column'])->toBe('1 / auto');
    });

    it('prefers explicit end over span', function () {
        $props = GridArea::at(1, 1)->spanRows(2)->rowEnd(4)->build();
        expect($props['grid-row'])->toBe('1 / 4');
    });

    it('outputs inline CSS via toCss()', function () {
        $css = GridArea::named('main')->toCss();
        expect($css)->toContain('grid-area: main');
    });

    it('creates area from row line', function () {
        $props = GridArea::atRow('header-start')->build();
        expect($props['grid-row'])->toBe('header-start / auto')
            ->and($props)->not->toHaveKey('grid-column');
    });

    it('creates area from column line', function () {
        $props = GridArea::atColumn('sidebar-start')->build();
        expect($props['grid-column'])->toBe('sidebar-start / auto')
            ->and($props)->not->toHaveKey('grid-row');
    });

    it('sets row and column start individually', function () {
        $props = (new GridArea())
            ->rowStart(2)
            ->columnStart(3)
            ->build();

        expect($props['grid-row'])->toBe('2 / auto')
            ->and($props['grid-column'])->toBe('3 / auto');
    });

    it('sets row and column end individually', function () {
        $props = GridArea::at(1, 1)
            ->rowEnd(3)
            ->columnEnd(4)
            ->build();

        expect($props['grid-row'])->toBe('1 / 3')
            ->and($props['grid-column'])->toBe('1 / 4');
    });
});
