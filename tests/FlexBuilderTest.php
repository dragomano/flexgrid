<?php

declare(strict_types=1);

use FlexGrid\Enums\ContentAlignment;
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;
use FlexGrid\Enums\ItemAlignment;
use FlexGrid\FlexBuilder;
use FlexGrid\FlexItem;

describe('FlexBuilder', function () {
    it('switches to inline-flex', function () {
        $props = FlexBuilder::make()->inline()->buildProperties();
        expect($props['display'])->toBe('inline-flex');
    });

    it('builds container rule', function () {
        $css = FlexBuilder::make('.toolbar')
            ->direction(FlexDirection::Row)
            ->wrap(FlexWrap::Wrap)
            ->gap('1rem')
            ->build();

        expect($css)->toContain('.toolbar {')
            ->and($css)->toContain('display: flex;')
            ->and($css)->toContain('flex-flow: row wrap;')
            ->and($css)->toContain('gap: 1rem;');
    });

    it('flow sets direction and wrap', function () {
        $props = FlexBuilder::make()
            ->flow(FlexDirection::Column, FlexWrap::NoWrap)
            ->buildProperties();

        expect($props['flex-flow'])->toBe('column nowrap');
    });

    it('supports direction enum values', function () {
        $props = FlexBuilder::make()
            ->direction(FlexDirection::ColumnReverse)
            ->buildProperties();

        expect($props['flex-direction'])->toBe('column-reverse');
    });

    it('supports nowrap and wrap-reverse helper methods', function () {
        $noWrapProps = FlexBuilder::make()->noWrap()->buildProperties();
        $reverseWrapProps = FlexBuilder::make()->wrapReverse()->buildProperties();

        expect($noWrapProps['flex-wrap'])->toBe('nowrap')
            ->and($reverseWrapProps['flex-wrap'])->toBe('wrap-reverse');
    });

    it('sets content alignment properties', function () {
        $props = FlexBuilder::make()
            ->justifyContent(ContentAlignment::SpaceBetween)
            ->alignItems(ItemAlignment::Center)
            ->alignContent(ContentAlignment::SpaceAround)
            ->buildProperties();

        expect($props['place-content'])->toBe('space-around space-between')
            ->and($props['align-items'])->toBe('center');
    });

    it('placeContent uses explicit justify when provided', function () {
        $props = FlexBuilder::make()
            ->placeContent(ContentAlignment::Start, ContentAlignment::End)
            ->buildProperties();

        expect($props['place-content'])->toBe('start end');
    });

    it('includes item rules in output', function () {
        $css = FlexBuilder::make('.cards')
            ->item(FlexItem::select('.cards > .card')->flex(1, 1, '240px'))
            ->build();

        expect($css)->toContain('.cards > .card {')
            ->and($css)->toContain('flex: 1 1 240px;');
    });

    it('includes multiple items added via items()', function () {
        $css = FlexBuilder::make('.cards')
            ->items([
                FlexItem::select('.cards > .a')->flex(1, 1, '200px'),
                FlexItem::select('.cards > .b')->flex(2, 1, '260px'),
            ])
            ->build();

        expect($css)->toContain('.cards > .a {')
            ->and($css)->toContain('flex: 1 1 200px;')
            ->and($css)->toContain('.cards > .b {')
            ->and($css)->toContain('flex: 2 1 260px;');
    });

    it('emits responsive media queries', function () {
        $css = FlexBuilder::make('.layout')
            ->direction(FlexDirection::Column)
            ->responsive(768, fn(FlexBuilder $g) => $g->direction(FlexDirection::Row))
            ->build();

        expect($css)->toContain('@media (min-width: 768px)')
            ->and($css)->toContain('flex-direction: row;');
    });

    it('emits custom media queries via media()', function () {
        $css = FlexBuilder::make('.layout')
            ->media('(max-width: 600px)', fn(FlexBuilder $g) => $g->wrap(FlexWrap::Wrap))
            ->build();

        expect($css)->toContain('@media (max-width: 600px)')
            ->and($css)->toContain('flex-wrap: wrap;');
    });

    it('includes responsive item rules inside media blocks', function () {
        $css = FlexBuilder::make('.layout')
            ->responsive(1024, fn(FlexBuilder $f) => $f->item(FlexItem::select('.layout__side')->flex(0, 0, '280px')))
            ->build();

        expect($css)->toContain('@media (min-width: 1024px)')
            ->and($css)->toContain('.layout__side {')
            ->and($css)->toContain('flex: 0 0 280px;');
    });

    it('skips items without selector in root and responsive blocks', function () {
        $css = FlexBuilder::make('.layout')
            ->item(FlexItem::select('')->flex(7, 7, '777px'))
            ->responsive(900, fn(FlexBuilder $f) => $f->item(FlexItem::select('')->flex(8, 8, '888px')))
            ->build();

        expect($css)->not->toContain('flex: 7 7 777px;')
            ->and($css)->not->toContain('flex: 8 8 888px;');
    });

    it('outputs inline style without selector', function () {
        $style = FlexBuilder::make()
            ->direction(FlexDirection::Row)
            ->gap('1rem')
            ->toInlineStyle();

        expect($style)->toContain('display: flex')
            ->and($style)->toContain('flex-direction: row')
            ->and($style)->toContain('gap: 1rem')
            ->and($style)->not->toContain('{');
    });

    it('outputs inline-flex in inline style when inline() is used', function () {
        $style = FlexBuilder::make()
            ->inline()
            ->toInlineStyle();

        expect($style)->toContain('display: inline-flex');
    });

    it('uses last gap call when set multiple times', function () {
        $props = FlexBuilder::make()
            ->gap('1rem')
            ->gap('2rem')
            ->buildProperties();

        expect($props['gap'])->toBe('2rem');
    });

    it('supports gap with two values', function () {
        $props = FlexBuilder::make()
            ->gap('1rem', '2rem')
            ->buildProperties();

        expect($props['gap'])->toBe('1rem 2rem');
    });

    it('supports rowGap and columnGap independently', function () {
        $rowGapProps = FlexBuilder::make()->rowGap('1rem')->buildProperties();
        $columnGapProps = FlexBuilder::make()->columnGap('2rem')->buildProperties();

        expect($rowGapProps['row-gap'])->toBe('1rem')
            ->and($columnGapProps['column-gap'])->toBe('2rem');
    });

    it('returns empty string from buildRule when properties are empty', function () {
        $builder = FlexBuilder::make('.layout');
        $method = new ReflectionMethod($builder, 'buildRule');

        $result = $method->invoke($builder, '.layout', [], '  ');

        expect($result)->toBe('');
    });

    it('sets align-content without justify-content', function () {
        $props = FlexBuilder::make()
            ->alignContent(ContentAlignment::Center)
            ->buildProperties();

        expect($props['align-content'])->toBe('center')
            ->and($props)->not->toHaveKey('justify-content');
    });

    it('sets justify-content without align-content', function () {
        $props = FlexBuilder::make()
            ->justifyContent(ContentAlignment::SpaceBetween)
            ->buildProperties();

        expect($props['justify-content'])->toBe('space-between')
            ->and($props)->not->toHaveKey('align-content');
    });
});
