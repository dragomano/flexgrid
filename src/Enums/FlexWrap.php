<?php

declare(strict_types=1);

namespace FlexGrid\Enums;

enum FlexWrap: string
{
    case Wrap        = 'wrap';
    case NoWrap      = 'nowrap';
    case WrapReverse = 'wrap-reverse';
}
