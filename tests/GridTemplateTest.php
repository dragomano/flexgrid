<?php

declare(strict_types=1);

use FlexGrid\GridTemplate;

describe('GridTemplate', function () {
    it('serialises rows into quoted strings', function () {
        $value = GridTemplate::create()
            ->row(['header', 'header'])
            ->row(['sidebar', 'main'])
            ->row(['footer', 'footer'])
            ->build();

        expect($value)->toContain('"header header"')
            ->and($value)->toContain('"sidebar main"')
            ->and($value)->toContain('"footer footer"');
    });

    it('accepts rows in bulk', function () {
        $t = GridTemplate::create()->rows([
            ['a', 'b'],
            ['c', 'd'],
        ]);
        expect($t->rowCount())->toBe(2);
    });

    it('returns unique area names, ignoring dots', function () {
        $names = GridTemplate::create()
            ->row(['header', 'header'])
            ->row(['sidebar', 'main'])
            ->row(['.', 'footer'])
            ->getAreaNames();

        expect($names)->toContain('header')
            ->and($names)->toContain('sidebar')
            ->and($names)->toContain('main')
            ->and($names)->toContain('footer')
            ->and($names)->not->toContain('.');
    });

    it('infers column count from first row', function () {
        expect(GridTemplate::create()->row(['a', 'b', 'c'])->columnCount())->toBe(3);
    });

    it('counts rows correctly', function () {
        $t = GridTemplate::create()->row(['a'])->row(['b'])->row(['c']);
        expect($t->rowCount())->toBe(3);
    });

    it('accepts null cells expressed as dots', function () {
        $value = GridTemplate::create()
            ->row(['header', 'header'])
            ->row(['.', 'main'])
            ->build();

        expect($value)->toContain('". main"');
    });

    it('rejects an empty row', function () {
        expect(fn() => GridTemplate::create()->row([]))
            ->toThrow(InvalidArgumentException::class, 'Grid template row must not be empty.');
    });

    it('rejects an empty cell', function () {
        expect(fn() => GridTemplate::create()->row(['header', '']))
            ->toThrow(InvalidArgumentException::class, 'Grid area cell must be a non-empty string.');
    });

    it('rejects a cell containing whitespace', function () {
        expect(fn() => GridTemplate::create()->row(['header main']))
            ->toThrow(InvalidArgumentException::class, 'Invalid grid area name: "header main".');
    });

    it('rejects a cell containing a quote', function () {
        expect(fn() => GridTemplate::create()->row(['he"ader']))
            ->toThrow(InvalidArgumentException::class);
    });

    it('rejects rows with mismatched column counts', function () {
        expect(fn() => GridTemplate::create()
            ->row(['header', 'header'])
            ->row(['nav', 'main', 'aside']))
            ->toThrow(InvalidArgumentException::class, 'All grid template rows must have the same number of columns.');
    });
});
