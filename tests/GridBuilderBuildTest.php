<?php

declare(strict_types=1);

use FlexGrid\GridBuilder;
use FlexGrid\GridItem;

describe('GridBuilder::build()', function () {
    it('wraps properties in selector block', function () {
        $css = GridBuilder::make('.grid')->columns('1fr', '1fr')->build();
        expect($css)->toContain('.grid {')
            ->and($css)->toContain('display: grid;')
            ->and($css)->toContain('grid-template-columns: 1fr 1fr;');
    });

    it('includes item rules after container', function () {
        $css = GridBuilder::make('.grid')
            ->columns('1fr', '1fr')
            ->item(GridItem::select('.grid__header')->namedArea('header'))
            ->build();

        expect($css)->toContain('.grid__header {')
            ->and($css)->toContain('grid-area: header;');
    });

    it('includes multiple items added via items()', function () {
        $css = GridBuilder::make('.grid')
            ->items([
                GridItem::select('.grid__a')->namedArea('a'),
                GridItem::select('.grid__b')->namedArea('b'),
            ])
            ->build();

        expect($css)->toContain('.grid__a {')
            ->and($css)->toContain('grid-area: a;')
            ->and($css)->toContain('.grid__b {')
            ->and($css)->toContain('grid-area: b;');
    });

    it('includes template areas in output', function () {
        $css = GridBuilder::make('.page')
            ->areaRows(['header', 'header'], ['nav', 'main'])
            ->build();

        expect($css)->toContain('grid-template-areas')
            ->and($css)->toContain('"header header"')
            ->and($css)->toContain('"nav main"');
    });

    it('parses string rows in areaRows()', function () {
        $css = GridBuilder::make('.page')
            ->areaRows(' header   header ', ' nav main ')
            ->build();

        expect($css)->toContain('"header header"')
            ->and($css)->toContain('"nav main"');
    });

    it('trims whitespace in areaRows string parsing', function () {
        $css = GridBuilder::make('.page')
            ->areaRows('  header  ')
            ->build();

        expect($css)->toContain('"header"')
            ->and($css)->not->toContain('""');
    });

    it('throws when areaRows() row is not string or array', function () {
        expect(fn() => GridBuilder::make('.page')->areaRows(123))
            ->toThrow(InvalidArgumentException::class, 'Each area row must be a string or an array of strings.');
    });

    it('throws when areaRows() row contains non-string cell', function () {
        expect(fn() => GridBuilder::make('.page')->areaRows(['header', 42]))
            ->toThrow(InvalidArgumentException::class, 'Each area cell must be a string.');
    });

    it('emits responsive media queries', function () {
        $css = GridBuilder::make('.layout')
            ->columns('1fr')
            ->responsive(768, fn(GridBuilder $g) => $g->columns('1fr', '1fr'))
            ->build();

        expect($css)->toContain('@media (min-width: 768px)')
            ->and($css)->toContain('grid-template-columns: 1fr 1fr;');
    });

    it('emits custom media queries via media()', function () {
        $css = GridBuilder::make('.layout')
            ->columns('1fr')
            ->media('(prefers-reduced-motion: reduce)', fn(GridBuilder $g) => $g->autoFlow('row'))
            ->build();

        expect($css)->toContain('@media (prefers-reduced-motion: reduce)');
    });

    it('omits unchanged base properties from media blocks', function () {
        $css = GridBuilder::make('.layout')
            ->columns('1fr')
            ->gap('1rem')
            ->responsive(768, fn(GridBuilder $g) => $g->columns('1fr', '1fr'))
            ->build();

        $mediaBlock = substr($css, strpos($css, '@media'));

        expect($mediaBlock)->not->toContain('display: grid;')
            ->and($mediaBlock)->not->toContain('gap: 1rem;')
            ->and($mediaBlock)->toContain('grid-template-columns: 1fr 1fr;');
    });

    it('skips a media block when the variant changes nothing', function () {
        $css = GridBuilder::make('.layout')
            ->columns('1fr')
            ->responsive(768, fn(GridBuilder $g) => $g->columns('1fr'))
            ->build();

        expect($css)->not->toContain('@media');
    });

    it('merges variants sharing a custom media() query into one block', function () {
        $css = GridBuilder::make('.layout')
            ->media('(orientation: landscape)', fn(GridBuilder $g) => $g->columns('1fr', '1fr'))
            ->media('(orientation: landscape)', fn(GridBuilder $g) => $g->gap('2rem'))
            ->build();

        expect(substr_count($css, '@media (orientation: landscape)'))->toBe(1)
            ->and($css)->toContain('grid-template-columns: 1fr 1fr;')
            ->and($css)->toContain('gap: 2rem;');
    });

    it('emits item rules, gap and alignment together inside a media block', function () {
        $css = GridBuilder::make('.layout')
            ->responsive(900, fn(GridBuilder $g) => $g
                ->gap('2rem')
                ->justifyItems(FlexGrid\Enums\ItemAlignment::Center)
                ->item(GridItem::select('.layout__cell')->namedArea('cell')))
            ->build();

        $mediaBlock = substr($css, strpos($css, '@media'));

        expect($mediaBlock)->toContain('gap: 2rem;')
            ->and($mediaBlock)->toContain('justify-items: center;')
            ->and($mediaBlock)->toContain('.layout__cell {')
            ->and($mediaBlock)->toContain('grid-area: cell;');
    });

    it('includes responsive item rules inside media blocks', function () {
        $css = GridBuilder::make('.layout')
            ->responsive(1024, fn(GridBuilder $g) => $g->item(GridItem::select('.layout__side')->namedArea('side')))
            ->build();

        expect($css)->toContain('@media (min-width: 1024px)')
            ->and($css)->toContain('.layout__side {')
            ->and($css)->toContain('grid-area: side;');
    });

    it('skips grid items without selector in root and responsive blocks', function () {
        $css = GridBuilder::make('.layout')
            ->item(GridItem::select('')->namedArea('hidden-root'))
            ->responsive(900, fn(GridBuilder $g) => $g->item(GridItem::select('')->namedArea('hidden-responsive')))
            ->build();

        expect($css)->not->toContain('grid-area: hidden-root;')
            ->and($css)->not->toContain('grid-area: hidden-responsive;');
    });

    it('outputs inline style without selector', function () {
        $style = GridBuilder::make()
            ->columns('1fr', '2fr')
            ->gap('1rem')
            ->toInlineStyle();

        expect($style)->toContain('display: grid')
            ->and($style)->toContain('grid-template-columns: 1fr 2fr')
            ->and($style)->toContain('gap: 1rem')
            ->and($style)->not->toContain('{');
    });

    it('returns empty string from buildRule when properties are empty', function () {
        $builder = GridBuilder::make('.layout');
        $method = new ReflectionMethod($builder, 'buildRule');

        $result = $method->invoke($builder, '.layout', [], '  ');

        expect($result)->toBe('');
    });
});
