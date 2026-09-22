<?php

declare(strict_types=1);

namespace FlexGrid;

/**
 * Shared rendering of a single CSS rule (selector block or bare declarations).
 */
trait RendersCssRule
{
    /**
     * @param array<string, string> $props
     */
    private function renderRule(string $selector, array $props, string $indent): string
    {
        if ($props === []) {
            return '';
        }

        $propIndent = $indent . '  ';

        $lines = [];

        foreach ($props as $prop => $val) {
            $lines[] = "$propIndent$prop: $val;";
        }

        $block = implode("\n", $lines);

        return $selector !== ''
            ? "$indent$selector {\n$block\n$indent}"
            : $block;
    }
}
