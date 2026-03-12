<?php

declare(strict_types=1);

use FlexGrid\Grid;
use FlexGrid\GridArea;
use FlexGrid\GridBuilder;
use FlexGrid\GridItem;
use FlexGrid\GridTemplate;

describe('Grid presets', function () {
    it('exposes container() factory', function () {
        $builder = Grid::container('.grid');
        expect($builder)->toBeInstanceOf(GridBuilder::class)
            ->and($builder->build())->toContain('.grid {');
    });

    it('exposes item() factory', function () {
        $item = Grid::item('.cell')->namedArea('content');
        expect($item)->toBeInstanceOf(GridItem::class)
            ->and($item->buildProperties())->toBe(['grid-area' => 'content']);
    });

    it('exposes template() and area() factories', function () {
        $template = Grid::template()->row(['header']);
        $area = Grid::area()->rowStart(1)->columnStart(2);

        expect($template)->toBeInstanceOf(GridTemplate::class)
            ->and($template->build())->toBe('"header"')
            ->and($area)->toBeInstanceOf(GridArea::class)
            ->and($area->build()['grid-row'])->toBe('1 / auto')
            ->and($area->build()['grid-column'])->toBe('2 / auto');
    });

    it('columns() creates equal-width layout', function () {
        $props = Grid::columns(3)->buildProperties();
        expect($props['grid-template-columns'])->toBe('repeat(3, 1fr)')
            ->and($props['gap'])->toBe('1rem');
    });

    it('columns() accepts custom gap', function () {
        $props = Grid::columns(4, '', '2rem')->buildProperties();
        expect($props['gap'])->toBe('2rem');
    });

    it('fluid() creates auto-fill layout', function () {
        $props = Grid::fluid('', '300px')->buildProperties();
        expect($props['grid-template-columns'])
            ->toContain('auto-fill')
            ->toContain('300px');
    });

    it('holyGrail() defines all five areas', function () {
        $props = Grid::holyGrail()->buildProperties();
        expect($props['grid-template-areas'])
            ->toContain('header')
            ->toContain('sidebar')
            ->toContain('main')
            ->toContain('aside')
            ->toContain('footer');
    });

    it('holyGrail() template has correct structure', function () {
        $areas = Grid::holyGrail()->buildProperties()['grid-template-areas'];
        expect($areas)->toContain('"header header header"')
            ->and($areas)->toContain('"sidebar main aside"')
            ->and($areas)->toContain('"footer footer footer"');
    });

    it('holyGrail() has three columns and three rows', function () {
        $props = Grid::holyGrail()->buildProperties();
        expect($props['grid-template-columns'])->toBe('200px 1fr 160px')
            ->and($props['grid-template-rows'])->toBe('auto 1fr auto');
    });

    it('sidebar() places sidebar left of content', function () {
        $props = Grid::sidebar('', '300px')->buildProperties();
        expect($props['grid-template-columns'])->toBe('300px 1fr');
    });

    it('centered() wraps content with fluid gutters', function () {
        $props = Grid::centered('', '900px')->buildProperties();
        expect($props['grid-template-columns'])->toContain('900px');
    });

    it('dashboard() defines header/nav/main/footer areas', function () {
        $props = Grid::dashboard()->buildProperties();
        expect($props['grid-template-areas'])
            ->toContain('header')
            ->toContain('nav')
            ->toContain('main')
            ->toContain('footer');
    });

    it('dashboard() template has correct structure', function () {
        $areas = Grid::dashboard()->buildProperties()['grid-template-areas'];
        expect($areas)->toContain('"header header"')
            ->and($areas)->toContain('"nav main"')
            ->and($areas)->toContain('"nav footer"');
    });

    it('masonry() uses row dense auto-flow', function () {
        $props = Grid::masonry()->buildProperties();
        expect($props['grid-auto-flow'])->toBe('row dense')
            ->and($props['grid-auto-rows'])->toBe('10px');
    });
});
