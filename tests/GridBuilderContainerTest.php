<?php

declare(strict_types=1);

use FlexGrid\Enums\ContentAlignment;
use FlexGrid\Enums\ItemAlignment;
use FlexGrid\GridBuilder;
use FlexGrid\GridTemplate;

describe('GridBuilder container', function () {
    it('defaults to display: grid', function () {
        expect(GridBuilder::make()->buildProperties()['display'])->toBe('grid');
    });

    it('switches to inline-grid', function () {
        expect(GridBuilder::make()->inline()->buildProperties()['display'])->toBe('inline-grid');
    });

    it('joins column tracks with spaces', function () {
        $props = GridBuilder::make()->columns('1fr', '2fr', '1fr')->buildProperties();
        expect($props['grid-template-columns'])->toBe('1fr 2fr 1fr');
    });

    it('joins row tracks with spaces', function () {
        $props = GridBuilder::make()->rows('auto', '1fr', 'auto')->buildProperties();
        expect($props['grid-template-rows'])->toBe('auto 1fr auto');
    });

    it('accumulates columns across multiple calls', function () {
        $props = GridBuilder::make()->columns('1fr')->columns('2fr')->buildProperties();
        expect($props['grid-template-columns'])->toBe('1fr 2fr');
    });

    it('uses gap shorthand when row and column gap are equal', function () {
        $props = GridBuilder::make()->gap('1rem')->buildProperties();
        expect($props)->toHaveKey('gap', '1rem')
            ->and($props)->not->toHaveKey('row-gap')
            ->and($props)->not->toHaveKey('column-gap');
    });

    it('defaults columnGap to rowGap when null', function () {
        $props = GridBuilder::make()->gap('1.5rem')->buildProperties();
        expect($props['gap'])->toBe('1.5rem');
    });

    it('uses gap shorthand with two values when they differ', function () {
        $props = GridBuilder::make()->gap('1rem', '2rem')->buildProperties();
        expect($props['gap'])->toBe('1rem 2rem');
    });

    it('sets row-gap independently', function () {
        $props = GridBuilder::make()->rowGap('1rem')->buildProperties();
        expect($props)->toHaveKey('row-gap', '1rem')
            ->and($props)->not->toHaveKey('gap');
    });

    it('sets column-gap independently', function () {
        $props = GridBuilder::make()->columnGap('2rem')->buildProperties();
        expect($props)->toHaveKey('column-gap', '2rem')
            ->and($props)->not->toHaveKey('gap');
    });

    it('builds auto-fill columns', function () {
        $props = GridBuilder::make()->autoFillColumns('200px')->buildProperties();
        expect($props['grid-template-columns'])->toBe('repeat(auto-fill, minmax(200px, 1fr))');
    });

    it('builds auto-fit columns with custom max', function () {
        $props = GridBuilder::make()->autoFitColumns('150px', '300px')->buildProperties();
        expect($props['grid-template-columns'])->toBe('repeat(auto-fit, minmax(150px, 300px))');
    });

    it('builds repeat columns', function () {
        $props = GridBuilder::make()->repeatColumns(4)->buildProperties();
        expect($props['grid-template-columns'])->toBe('repeat(4, 1fr)');
    });

    it('builds repeat rows', function () {
        $props = GridBuilder::make()->repeatRows(3, 'minmax(100px, auto)')->buildProperties();
        expect($props['grid-template-rows'])->toBe('repeat(3, minmax(100px, auto))');
    });

    it('sets grid-auto-rows', function () {
        $props = GridBuilder::make()->autoRows('minmax(100px, auto)')->buildProperties();
        expect($props['grid-auto-rows'])->toBe('minmax(100px, auto)');
    });

    it('sets grid-auto-columns', function () {
        $props = GridBuilder::make()->autoColumns('200px')->buildProperties();
        expect($props['grid-auto-columns'])->toBe('200px');
    });

    it('sets grid-auto-flow', function () {
        $props = GridBuilder::make()->autoFlow('row dense')->buildProperties();
        expect($props['grid-auto-flow'])->toBe('row dense');
    });

    it('sets individual alignment properties', function () {
        $props = GridBuilder::make()
            ->justifyItems(ItemAlignment::Center)
            ->alignItems(ItemAlignment::Start)
            ->buildProperties();

        expect($props['place-items'])->toBe('start center');
    });

    it('sets place-items shorthand with one value', function () {
        $props = GridBuilder::make()->placeItems(ItemAlignment::Center)->buildProperties();
        expect($props['place-items'])->toBe('center');
    });

    it('sets place-items shorthand with two different values', function () {
        $props = GridBuilder::make()
            ->placeItems(ItemAlignment::Start, ItemAlignment::End)
            ->buildProperties();

        expect($props['place-items'])->toBe('start end');
    });

    it('sets content alignment', function () {
        $props = GridBuilder::make()
            ->justifyContent(ContentAlignment::SpaceBetween)
            ->alignContent(ContentAlignment::Start)
            ->buildProperties();

        expect($props['place-content'])->toBe('start space-between');
    });

    it('sets place-content shorthand with one value', function () {
        $props = GridBuilder::make()->placeContent(ContentAlignment::Center)->buildProperties();
        expect($props['place-content'])->toBe('center');
    });

    it('sets template areas via areas()', function () {
        $template = GridTemplate::create()->row(['main']);
        $props = GridBuilder::make()->areas($template)->buildProperties();
        expect($props['grid-template-areas'])->toBe('"main"');
    });

    it('omits unset properties', function () {
        $props = GridBuilder::make()->buildProperties();
        expect($props)->not->toHaveKey('grid-template-columns')
            ->and($props)->not->toHaveKey('gap')
            ->and($props)->not->toHaveKey('grid-auto-flow');
    });

    it('sets align-items without justify-items', function () {
        $props = GridBuilder::make()
            ->alignItems(ItemAlignment::Start)
            ->buildProperties();

        expect($props['align-items'])->toBe('start')
            ->and($props)->not->toHaveKey('justify-items')
            ->and($props)->not->toHaveKey('place-items');
    });

    it('sets justify-items without align-items', function () {
        $props = GridBuilder::make()
            ->justifyItems(ItemAlignment::End)
            ->buildProperties();

        expect($props['justify-items'])->toBe('end')
            ->and($props)->not->toHaveKey('align-items')
            ->and($props)->not->toHaveKey('place-items');
    });

    it('sets align-content without justify-content', function () {
        $props = GridBuilder::make()
            ->alignContent(ContentAlignment::Start)
            ->buildProperties();

        expect($props['align-content'])->toBe('start')
            ->and($props)->not->toHaveKey('justify-content')
            ->and($props)->not->toHaveKey('place-content');
    });

    it('sets justify-content without align-content', function () {
        $props = GridBuilder::make()
            ->justifyContent(ContentAlignment::End)
            ->buildProperties();

        expect($props['justify-content'])->toBe('end')
            ->and($props)->not->toHaveKey('align-content')
            ->and($props)->not->toHaveKey('place-content');
    });
});
