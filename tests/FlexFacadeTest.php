<?php

declare(strict_types=1);

use FlexGrid\Flex;
use FlexGrid\FlexBuilder;
use FlexGrid\FlexItem;

describe('Flex facade', function () {
    it('exposes container factory', function () {
        $builder = Flex::container('.menu');
        expect($builder)->toBeInstanceOf(FlexBuilder::class)
            ->and($builder->build())->toContain('.menu {');
    });

    it('exposes item factory', function () {
        $item = Flex::item('.menu__item')->flex(1, 1, '200px');
        expect($item)->toBeInstanceOf(FlexItem::class)
            ->and($item->buildProperties())->toBe(['flex' => '1 1 200px']);
    });

    it('row preset builds row layout', function () {
        $props = Flex::row('', '0.5rem')->buildProperties();
        expect($props['flex-direction'])->toBe('row')
            ->and($props['gap'])->toBe('0.5rem');
    });

    it('column preset builds column layout', function () {
        $props = Flex::column()->buildProperties();
        expect($props['flex-direction'])->toBe('column')
            ->and($props['gap'])->toBe('1rem');
    });

    it('cards preset enables wrapping and child flex rule', function () {
        $css = Flex::cards('.cards', '240px')->build();
        expect($css)->toContain('flex-flow: row wrap;')
            ->and($css)->toContain('.cards > * {')
            ->and($css)->toContain('flex: 1 1 240px;');
    });

    it('cards preset without selector skips child items', function () {
        $css = Flex::cards('', '240px')->build();
        expect($css)->toContain('flex-flow: row wrap;')
            ->and($css)->not->toContain(' > * {');
    });

    it('sidebar preset adds first and last child item rules', function () {
        $css = Flex::sidebar('.layout')->build();
        expect($css)->toContain('.layout > :first-child {')
            ->and($css)->toContain('flex: 0 0 260px;')
            ->and($css)->toContain('.layout > :last-child {')
            ->and($css)->toContain('flex: 1 1 0;');
    });

    it('sidebar preset without selector skips child items', function () {
        $css = Flex::sidebar()->build();
        expect($css)->toContain('flex-direction: row;')
            ->and($css)->not->toContain(' > :first-child {')
            ->and($css)->not->toContain(' > :last-child {');
    });
});
