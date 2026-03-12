<?php

declare(strict_types=1);

use FlexGrid\Enums\ItemAlignment;
use FlexGrid\GridArea;
use FlexGrid\GridItem;

describe('GridItem', function () {
    it('applies an explicit area object via area()', function () {
        $props = GridItem::select('.hero')
            ->area(GridArea::at(2, 3)->rowEnd(4)->columnEnd(5))
            ->buildProperties();

        expect($props['grid-row'])->toBe('2 / 4')
            ->and($props['grid-column'])->toBe('3 / 5');
    });

    it('sets start lines via place()', function () {
        $props = GridItem::select('.tile')->place(3, 2)->buildProperties();
        expect($props['grid-row'])->toBe('3 / auto')
            ->and($props['grid-column'])->toBe('2 / auto');
    });

    it('creates area implicitly in span()', function () {
        $props = GridItem::select('.tile')->span(2, 4)->buildProperties();
        expect($props)->toBe([]);
    });

    it('span() creates area when none exists', function () {
        $item = GridItem::select('.tile');
        $item->span(2, 3);
        $props = $item->buildProperties();
        expect($props)->toBe([]);
    });

    it('sets named area', function () {
        $props = GridItem::select('.header')->namedArea('header')->buildProperties();
        expect($props)->toBe(['grid-area' => 'header']);
    });

    it('sets self-alignment', function () {
        $props = GridItem::select('.box')
            ->justifySelf(ItemAlignment::End)
            ->alignSelf(ItemAlignment::Center)
            ->buildProperties();

        expect($props['place-self'])->toBe('center end');
    });

    it('sets place-self shorthand', function () {
        $props = GridItem::select('.box')
            ->placeSelf(ItemAlignment::Center)
            ->buildProperties();

        expect($props['place-self'])->toBe('center');
    });

    it('placeSelf defaults justify to align when null', function () {
        $props = GridItem::select('.box')
            ->placeSelf(ItemAlignment::Start)
            ->buildProperties();

        expect($props['place-self'])->toBe('start');
    });

    it('sets order', function () {
        expect(GridItem::select('.a')->order(3)->buildProperties()['order'])->toBe('3');
    });

    it('sets individual row and column lines', function () {
        $props = GridItem::select('.box')
            ->rowStart(2)
            ->rowEnd(4)
            ->columnStart(1)
            ->columnEnd(3)
            ->buildProperties();

        expect($props['grid-row-start'])->toBe('2')
            ->and($props['grid-row-end'])->toBe('4')
            ->and($props['grid-column-start'])->toBe('1')
            ->and($props['grid-column-end'])->toBe('3');
    });

    it('sets individual row and column lines with string values', function () {
        $props = GridItem::select('.box')
            ->rowStart('header-start')
            ->rowEnd('header-end')
            ->columnStart('sidebar-start')
            ->columnEnd('sidebar-end')
            ->buildProperties();

        expect($props['grid-row-start'])->toBe('header-start')
            ->and($props['grid-row-end'])->toBe('header-end')
            ->and($props['grid-column-start'])->toBe('sidebar-start')
            ->and($props['grid-column-end'])->toBe('sidebar-end');
    });

    it('sets justify-self without align-self', function () {
        $props = GridItem::select('.box')
            ->justifySelf(ItemAlignment::End)
            ->buildProperties();

        expect($props['justify-self'])->toBe('end')
            ->and($props)->not->toHaveKey('align-self');
    });

    it('sets align-self without justify-self', function () {
        $props = GridItem::select('.box')
            ->alignSelf(ItemAlignment::Start)
            ->buildProperties();

        expect($props['align-self'])->toBe('start')
            ->and($props)->not->toHaveKey('justify-self');
    });

    it('returns selector', function () {
        expect(GridItem::select('.foo')->getSelector())->toBe('.foo');
    });

    it('renders CSS block with selector', function () {
        $css = GridItem::select('.nav')->namedArea('nav')->toCss();
        expect($css)->toContain('.nav {')
            ->and($css)->toContain('grid-area: nav');
    });

    it('renders properties only without selector', function () {
        $css = (new GridItem())->namedArea('sidebar')->toCss();
        expect($css)->not->toContain('{')
            ->and($css)->toContain('grid-area: sidebar');
    });

    it('returns empty css when no properties are set', function () {
        $css = GridItem::select('.empty')->toCss();
        expect($css)->toBe('');
    });
});
