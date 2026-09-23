<?php

declare(strict_types=1);

use FlexGrid\CssGuard;

describe('CssGuard', function () {
    describe('assertSelector', function () {
        it('allows valid selectors', function (string $selector) {
            expect(fn() => CssGuard::assertSelector($selector))
                ->not->toThrow(InvalidArgumentException::class);
        })->with([
            '',
            '.grid',
            '#app',
            '.cards > *',
            '.a + .b',
            '.a ~ .b',
            '.list :first-child',
            '[data-role=main]',
            '[type="text"]',
            "input[value='x']",
        ]);

        it('rejects selectors with forbidden characters', function (string $selector) {
            expect(fn() => CssGuard::assertSelector($selector))
                ->toThrow(InvalidArgumentException::class, 'The selector contains forbidden characters.');
        })->with([
            'a{b',
            'a}b',
            'a<b',
            'a;b',
            "a\tb",
            "a\nb",
            "a\rb",
            "a\fb",
            "a\vb",
            "a\0b",
        ]);
    });

    describe('assertValue', function () {
        it('allows valid values', function (string $value) {
            expect(fn() => CssGuard::assertValue($value))
                ->not->toThrow(InvalidArgumentException::class);
        })->with([
            '',
            '1fr',
            'minmax(0, 1fr)',
            'calc(100% - 20px)',
            'var(--gap)',
            'row dense',
            '260px 1fr',
        ]);

        it('rejects values with forbidden characters', function (string $value) {
            expect(fn() => CssGuard::assertValue($value))
                ->toThrow(InvalidArgumentException::class, 'The value contains forbidden characters.');
        })->with([
            '1fr{',
            '1fr}',
            '1fr<',
            '1fr>',
            '1fr;',
            '1fr"',
            "1fr'",
            "1fr\t",
            "1fr\n",
            "1fr\r",
            "1fr\f",
            "1fr\v",
            "1fr\0",
        ]);
    });

    describe('assertQuery', function () {
        it('allows valid media queries', function (string $query) {
            expect(fn() => CssGuard::assertQuery($query))
                ->not->toThrow(InvalidArgumentException::class);
        })->with([
            '',
            '(min-width: 640px)',
            'screen and (max-width: 900px)',
            '(prefers-reduced-motion: reduce)',
            '(width >= 640px)',
        ]);

        it('rejects media queries with forbidden characters', function (string $query) {
            expect(fn() => CssGuard::assertQuery($query))
                ->toThrow(InvalidArgumentException::class, 'The media query contains forbidden characters.');
        })->with([
            '(min-width: 640px){',
            '(min-width: 640px)}',
            'screen<',
            'screen;',
            "screen\t",
            "screen\n",
            "screen\r",
            "screen\f",
            "screen\v",
            "screen\0",
        ]);
    });
});
