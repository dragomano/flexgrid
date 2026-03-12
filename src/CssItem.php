<?php

declare(strict_types=1);

namespace FlexGrid;

interface CssItem
{
    public function getSelector(): string;

    public function toCss(string $indent = '  '): string;
}
