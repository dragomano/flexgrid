<?php

declare(strict_types=1);

use FlexGrid\Enums\ItemAlignment;
use FlexGrid\FlexItem;

describe('FlexItem', function () {
    it('sets grow, shrink and basis separately', function () {
        $props = FlexItem::select('.card')
            ->grow(1)
            ->shrink(0)
            ->basis('240px')
            ->buildProperties();

        expect($props['flex-grow'])->toBe('1')
            ->and($props['flex-shrink'])->toBe('0')
            ->and($props['flex-basis'])->toBe('240px');
    });

    it('sets flex shorthand', function () {
        $props = FlexItem::select('.card')->flex(1, 1, '240px')->buildProperties();
        expect($props)->toBe(['flex' => '1 1 240px']);
    });

    it('sets order and align-self', function () {
        $props = FlexItem::select('.card')
            ->order(2)
            ->alignSelf(ItemAlignment::Center)
            ->buildProperties();

        expect($props['order'])->toBe('2')
            ->and($props['align-self'])->toBe('center');
    });

    it('returns selector', function () {
        expect(FlexItem::select('.foo')->getSelector())->toBe('.foo');
    });

    it('renders css block with selector', function () {
        $css = FlexItem::select('.card')->flex(1, 1, '240px')->toCss();
        expect($css)->toContain('.card {')
            ->and($css)->toContain('flex: 1 1 240px;');
    });

    it('renders properties only without selector', function () {
        $css = (new FlexItem())->basis('20rem')->toCss();
        expect($css)->not->toContain('{')
            ->and($css)->toContain('flex-basis: 20rem;');
    });

    it('returns empty css when no properties are set', function () {
        expect(FlexItem::select('.empty')->toCss())->toBe('');
    });

    it('rejects negative flex-grow', function () {
        expect(fn() => FlexItem::select('.card')->grow(-1))
            ->toThrow(InvalidArgumentException::class, 'The flex-grow value must not be negative.');
    });

    it('rejects negative flex-shrink', function () {
        expect(fn() => FlexItem::select('.card')->shrink(-0.5))
            ->toThrow(InvalidArgumentException::class, 'The flex-shrink value must not be negative.');
    });

    it('rejects negative grow in flex shorthand', function () {
        expect(fn() => FlexItem::select('.card')->flex(-1, 1, '240px'))
            ->toThrow(InvalidArgumentException::class, 'The flex-grow value must not be negative.');
    });

    it('rejects negative shrink in flex shorthand', function () {
        expect(fn() => FlexItem::select('.card')->flex(1, -1, '240px'))
            ->toThrow(InvalidArgumentException::class, 'The flex-shrink value must not be negative.');
    });

    it('rejects a selector with forbidden characters', function () {
        expect(fn() => FlexItem::select('.card<script>'))
            ->toThrow(InvalidArgumentException::class, 'The selector contains forbidden characters.');
    });

    it('rejects a basis with forbidden characters', function () {
        expect(fn() => FlexItem::select('.card')->basis('240px"'))
            ->toThrow(InvalidArgumentException::class, 'The value contains forbidden characters.');
    });

    it('rejects a forbidden basis in the flex shorthand', function () {
        expect(fn() => FlexItem::select('.card')->flex(1, 1, '240px; }'))
            ->toThrow(InvalidArgumentException::class, 'The value contains forbidden characters.');
    });
});
