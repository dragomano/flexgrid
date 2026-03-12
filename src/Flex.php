<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;

/**
 * Static facade and preset factory for common flex patterns.
 */
final class Flex
{
    public static function container(string $selector = ''): FlexBuilder
    {
        return FlexBuilder::make($selector);
    }

    public static function item(string $selector = ''): FlexItem
    {
        return FlexItem::select($selector);
    }

    /**
     * Horizontal flex flow with configurable gap.
     *
     * Flex::row('.menu', '1rem')
     */
    public static function row(string $selector = '', string $gap = '1rem'): FlexBuilder
    {
        return FlexBuilder::make($selector)
            ->direction(FlexDirection::Row)
            ->gap($gap);
    }

    /**
     * Vertical flex flow with configurable gap.
     *
     * Flex::column('.stack', '0.75rem')
     */
    public static function column(string $selector = '', string $gap = '1rem'): FlexBuilder
    {
        return FlexBuilder::make($selector)
            ->direction(FlexDirection::Column)
            ->gap($gap);
    }

    /**
     * Responsive cards preset with wrapping and child flex basis.
     *
     * Flex::cards('.cards', '240px', '1rem')
     */
    public static function cards(string $selector = '', string $minWidth = '250px', string $gap = '1rem'): FlexBuilder
    {
        $builder = FlexBuilder::make($selector)
            ->direction(FlexDirection::Row)
            ->wrap(FlexWrap::Wrap)
            ->gap($gap);

        if ($selector !== '') {
            $builder->item(FlexItem::select("$selector > *")->flex(1, 1, $minWidth));
        }

        return $builder;
    }

    /**
     * Sidebar preset: first child fixed, last child flexible.
     *
     * Flex::sidebar('.layout', '260px')
     */
    public static function sidebar(string $selector = '', string $sideWidth = '260px', string $gap = '1.5rem'): FlexBuilder
    {
        $builder = FlexBuilder::make($selector)
            ->direction(FlexDirection::Row)
            ->gap($gap);

        if ($selector !== '') {
            $builder
                ->item(FlexItem::select("$selector > :first-child")->flex(0, 0, $sideWidth))
                ->item(FlexItem::select("$selector > :last-child")->flex(1, 1, '0'));
        }

        return $builder;
    }
}
