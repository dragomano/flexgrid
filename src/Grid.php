<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\GridValue;

/**
 * Static facade and preset factory for common grid patterns.
 */
final class Grid
{
    public static function container(string $selector = ''): GridBuilder
    {
        return GridBuilder::make($selector);
    }

    public static function item(string $selector = ''): GridItem
    {
        return GridItem::select($selector);
    }

    public static function template(): GridTemplate
    {
        return GridTemplate::create();
    }

    public static function area(): GridArea
    {
        return new GridArea();
    }

    /**
     * Classic N-column equal layout.
     *
     * Grid::columns(3, '.grid', '1rem')
     */
    public static function columns(int $n, string $selector = '', string $gap = '1rem'): GridBuilder
    {
        return GridBuilder::make($selector)
            ->columns(GridValue::repeat($n, GridValue::fr(1)))
            ->gap($gap);
    }

    /**
     * Responsive fluid grid using auto-fill.
     *
     * Grid::fluid('.cards', '280px')
     */
    public static function fluid(string $selector = '', string $minWidth = '250px', string $gap = '1rem'): GridBuilder
    {
        return GridBuilder::make($selector)
            ->autoFillColumns($minWidth)
            ->gap($gap);
    }

    /**
     * Classic Holy Grail layout: header / (sidebar + main + aside) / footer.
     *
     * Grid::holyGrail('.page')
     */
    public static function holyGrail(
        string $selector   = '',
        string $sideWidth  = '200px',
        string $asideWidth = '160px',
        string $gap        = '0'
    ): GridBuilder {
        return GridBuilder::make($selector)
            ->columns($sideWidth, '1fr', $asideWidth)
            ->rows('auto', '1fr', 'auto')
            ->areas(GridTemplate::create()
                ->row(['header', 'header', 'header'])
                ->row(['sidebar', 'main', 'aside'])
                ->row(['footer', 'footer', 'footer']))
            ->gap($gap);
    }

    /**
     * Masonry-like dense auto-flow grid.
     *
     * Grid::masonry('.masonry', '220px', '1rem')
     */
    public static function masonry(string $selector = '', string $minWidth = '220px', string $gap = '1rem'): GridBuilder
    {
        return GridBuilder::make($selector)
            ->autoFillColumns($minWidth)
            ->autoRows('10px')
            ->gap($gap)
            ->autoFlow('row dense');
    }

    /**
     * Sidebar layout: fixed sidebar + flexible content.
     *
     * Grid::sidebar('.layout', '260px')
     */
    public static function sidebar(string $selector = '', string $sideWidth = '260px', string $gap = '1.5rem'): GridBuilder
    {
        return GridBuilder::make($selector)
            ->columns($sideWidth, '1fr')
            ->gap($gap);
    }

    /**
     * Centered single-column content layout with max-width.
     *
     * Grid::centered('.layout', '720px')
     */
    public static function centered(string $selector = '', string $maxWidth = '720px', string $gap = '1rem'): GridBuilder
    {
        return GridBuilder::make($selector)
            ->columns('1fr', GridValue::minmax('0', $maxWidth), '1fr')
            ->gap($gap);
    }

    /**
     * Dashboard layout preset with header/sidebar/main/footer.
     *
     * Grid::dashboard('.dashboard')
     */
    public static function dashboard(
        string $selector     = '',
        string $sidebarWidth = '240px',
        string $headerHeight = '60px'
    ): GridBuilder {
        return GridBuilder::make($selector)
            ->columns($sidebarWidth, '1fr')
            ->rows($headerHeight, '1fr', 'auto')
            ->areas(GridTemplate::create()
                ->row(['header', 'header'])
                ->row(['nav', 'main'])
                ->row(['nav', 'footer']));
    }
}
