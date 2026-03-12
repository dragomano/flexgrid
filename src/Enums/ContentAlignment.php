<?php

declare(strict_types=1);

namespace FlexGrid\Enums;

/**
 * CSS alignment values for content distribution.
 *
 * Note: In Flexbox, the 'stretch' value for justify-content behaves as 'start'.
 * Stretching in Flexbox is controlled by the flex-grow property instead.
 *
 * @see https://www.w3.org/TR/css-align-3/
 */
enum ContentAlignment: string
{
    case Start        = 'start';
    case End          = 'end';
    case Center       = 'center';
    case Stretch      = 'stretch';
    case SpaceBetween = 'space-between';
    case SpaceAround  = 'space-around';
    case SpaceEvenly  = 'space-evenly';
    case Left         = 'left';
    case Right        = 'right';
    case Normal       = 'normal';
}
