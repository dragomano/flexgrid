<?php

declare(strict_types=1);

namespace FlexGrid\Enums;

enum ItemAlignment: string
{
    case Auto     = 'auto';
    case Start    = 'start';
    case End      = 'end';
    case Center   = 'center';
    case Stretch  = 'stretch';
    case Baseline = 'baseline';
}
