<?php

declare(strict_types=1);

namespace FlexGrid;

final readonly class BreakpointVariant
{
    public function __construct(
        public string $query,
        public AbstractBuilder $variant
    ) {}
}
