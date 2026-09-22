<?php

declare(strict_types=1);

use FlexGrid\Enums\ContentAlignment;
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\ItemAlignment;
use FlexGrid\FlexBuilder;
use FlexGrid\FlexItem;
use FlexGrid\GridBuilder;
use FlexGrid\GridItem;

describe('Exact CSS output', function () {
    it('renders the full grid rule, item and responsive delta', function () {
        $css = GridBuilder::make('.page')
            ->columns('220px', '1fr')
            ->gap('1rem')
            ->item(GridItem::select('.page__hd')->namedArea('header'))
            ->responsive(768, fn(GridBuilder $g) => $g->columns('1fr'))
            ->build();

        $expected = <<<CSS
        .page {
          display: grid;
          grid-template-columns: 220px 1fr;
          gap: 1rem;
        }

        .page__hd {
          grid-area: header;
        }

        @media (min-width: 768px) {
        .page {
            grid-template-columns: 1fr;
        }
        }
        CSS;

        expect($css)->toBe($expected);
    });

    it('renders the full flex rule with merged variants under one query', function () {
        $css = FlexBuilder::make('.bar')
            ->direction(FlexDirection::Row)
            ->gap('1rem')
            ->responsive(768, fn(FlexBuilder $f) => $f->gap('2rem'))
            ->responsive(768, fn(FlexBuilder $f) => $f
                ->justifyContent(ContentAlignment::SpaceBetween)
                ->item(FlexItem::select('.bar > .item')->grow(1)))
            ->build();

        $expected = <<<CSS
        .bar {
          display: flex;
          flex-direction: row;
          gap: 1rem;
        }

        @media (min-width: 768px) {
        .bar {
            gap: 2rem;
        }

        .bar {
            justify-content: space-between;
        }

        .bar > .item {
            flex-grow: 1;
        }
        }
        CSS;

        expect($css)->toBe($expected);
    });

    it('merges two responsive() calls sharing the same breakpoint into one block', function () {
        $css = GridBuilder::make('.grid')
            ->columns('1fr')
            ->responsive(600, fn(GridBuilder $g) => $g->columns('1fr', '1fr'))
            ->responsive(600, fn(GridBuilder $g) => $g->gap('2rem'))
            ->build();

        expect(substr_count($css, '@media (min-width: 600px)'))->toBe(1)
            ->and($css)->toContain('grid-template-columns: 1fr 1fr;')
            ->and($css)->toContain('gap: 2rem;');
    });

    it('keeps distinct breakpoints in separate blocks in insertion order', function () {
        $css = GridBuilder::make('.grid')
            ->columns('1fr')
            ->responsive(1024, fn(GridBuilder $g) => $g->columns('1fr', '1fr', '1fr'))
            ->responsive(600, fn(GridBuilder $g) => $g->columns('1fr', '1fr'))
            ->build();

        expect(strpos($css, '@media (min-width: 1024px)'))
            ->toBeLessThan(strpos($css, '@media (min-width: 600px)'));
    });

    it('renders a grid item block exactly via toCss()', function () {
        $css = GridItem::select('.box')
            ->place(2, 1)
            ->placeSelf(ItemAlignment::Center, ItemAlignment::End)
            ->order(3)
            ->toCss();

        $expected = <<<CSS
        .box {
          grid-row: 2 / auto;
          grid-column: 1 / auto;
          place-self: center end;
          order: 3;
        }
        CSS;

        expect($css)->toBe($expected);
    });

    it('renders inline style without selector or braces', function () {
        $style = GridBuilder::make()
            ->columns('1fr', '2fr')
            ->gap('1rem')
            ->toInlineStyle();

        expect($style)->toBe('display: grid; grid-template-columns: 1fr 2fr; gap: 1rem');
    });
});
