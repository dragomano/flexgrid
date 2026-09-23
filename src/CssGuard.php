<?php

declare(strict_types=1);

namespace FlexGrid;

use InvalidArgumentException;

/**
 * Minimal deny-list guard for user-supplied CSS fragments.
 *
 * Rejects only the characters that can break out of the CSS/HTML context
 * (rule structure, <style> element, style="" attribute). It is not a CSS
 * validator: passing otherwise valid CSS remains the caller's responsibility.
 */
final class CssGuard
{
    private const STRUCTURE_FORBIDDEN = "{}<;\t\n\r\f\v\0";

    private const VALUE_FORBIDDEN = "{}<>;\"'\t\n\r\f\v\0";

    public static function assertSelector(string $selector): void
    {
        self::reject($selector, self::STRUCTURE_FORBIDDEN, 'selector');
    }

    public static function assertValue(string $value): void
    {
        self::reject($value, self::VALUE_FORBIDDEN, 'value');
    }

    public static function assertQuery(string $query): void
    {
        self::reject($query, self::STRUCTURE_FORBIDDEN, 'media query');
    }

    private static function reject(string $input, string $forbidden, string $label): void
    {
        if (strpbrk($input, $forbidden) !== false) {
            throw new InvalidArgumentException("The $label contains forbidden characters.");
        }
    }
}
