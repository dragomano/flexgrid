<?php

declare(strict_types=1);

use FlexGrid\Enums\GridValue;

describe('GridValue', function () {
    it('generates fr units', function () {
        expect(GridValue::fr(1))->toBe('1fr')
            ->and(GridValue::fr(1.5))->toBe('1.5fr')
            ->and(GridValue::fr(0.5))->toBe('0.5fr');
    });

    it('generates minmax()', function () {
        expect(GridValue::minmax('200px', '1fr'))->toBe('minmax(200px, 1fr)')
            ->and(GridValue::minmax('min-content', 'max-content'))->toBe('minmax(min-content, max-content)');
    });

    it('generates repeat()', function () {
        expect(GridValue::repeat(3))->toBe('repeat(3, 1fr)')
            ->and(GridValue::repeat('auto-fill', GridValue::minmax('200px', '1fr')))
            ->toBe('repeat(auto-fill, minmax(200px, 1fr))');
    });

    it('generates fit-content()', function () {
        expect(GridValue::fitContent('300px'))->toBe('fit-content(300px)');
    });

    it('exposes keyword enum cases', function () {
        expect(GridValue::Auto->value)->toBe('auto')
            ->and(GridValue::MaxContent->value)->toBe('max-content')
            ->and(GridValue::MinContent->value)->toBe('min-content');
    });

    it('allows zero fraction unit', function () {
        expect(GridValue::fr(0))->toBe('0fr');
    });

    it('rejects negative fraction unit', function () {
        expect(fn() => GridValue::fr(-1))
            ->toThrow(InvalidArgumentException::class, 'Fraction unit must not be negative.');
    });

    it('rejects zero repeat count', function () {
        expect(fn() => GridValue::repeat(0))
            ->toThrow(InvalidArgumentException::class, 'Repeat count must be a positive integer.');
    });

    it('rejects negative repeat count', function () {
        expect(fn() => GridValue::repeat(-3))
            ->toThrow(InvalidArgumentException::class);
    });

    it('allows keyword repeat counts', function () {
        expect(GridValue::repeat('auto-fit', '1fr'))->toBe('repeat(auto-fit, 1fr)');
    });
});
