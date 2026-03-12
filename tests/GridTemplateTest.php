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
});
