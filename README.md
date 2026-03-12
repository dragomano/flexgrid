# FlexGrid PHP Builder

![PHP](https://img.shields.io/badge/PHP-^8.2-blue.svg?style=flat)
[![Coverage Status](https://coveralls.io/repos/github/dragomano/flexgrid/badge.svg?branch=main)](https://coveralls.io/github/dragomano/flexgrid?branch=main)

Fluent PHP library for generating CSS Grid and Flexbox layouts. Supports named areas, line-based placement, responsive breakpoints, and ready-made presets for common patterns.

---

## Installation

```bash
composer require bugo/flexgrid
```
---

## Quick start

```php
use FlexGrid\Grid;

echo Grid::columns(3, '.grid', '1.5rem')->build();
```

```css
.grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}
```

---

## GridBuilder

`GridBuilder` is the main class. All methods return `static`, so they chain freely.

### Columns and rows

```php
use FlexGrid\GridBuilder;
use FlexGrid\Enums\GridValue;

GridBuilder::make('.layout')
    ->columns('200px', '1fr', '200px')   // fixed values
    ->rows('64px', '1fr', '48px')        // row tracks
    ->gap('1rem')
    ->build();
```

Use `GridValue` helpers to avoid writing CSS strings by hand:

```php
GridBuilder::make('.layout')
    ->columns(
        GridValue::fr(1),                          // "1fr"
        GridValue::minmax('200px', '1fr'),         // "minmax(200px, 1fr)"
        GridValue::repeat(3, GridValue::fr(1)),    // "repeat(3, 1fr)"
    )
    ->autoRows(GridValue::minmax('100px', 'auto')) // grid-auto-rows
    ->build();
```

Shorthand methods for repeated tracks:

```php
GridBuilder::make('.grid')
    ->repeatColumns(4, '1fr')         // repeat(4, 1fr)
    ->repeatRows(3, '200px')          // repeat(3, 200px)
    ->autoFillColumns('250px')        // repeat(auto-fill, minmax(250px, 1fr))
    ->autoFitColumns('250px', '1fr')  // repeat(auto-fit,  minmax(250px, 1fr))
    ->build();
```

### Gap

```php
->gap('1rem')           // gap: 1rem  (both axes)
->gap('1rem', '2rem')   // gap: 1rem 2rem  (row, column)
->rowGap('1rem')        // row-gap only
->columnGap('2rem')     // column-gap only
```

### Named template areas

Use `GridTemplate` to define the visual layout as an ASCII-art grid:

```php
use FlexGrid\GridTemplate;

GridBuilder::make('.page')
    ->columns('220px', '1fr')
    ->rows('60px', '1fr', '40px')
    ->areas(GridTemplate::create()
        ->row(['header', 'header'])
        ->row(['nav',    'main'])
        ->row(['nav',    'footer']))
    ->build();
```

```css
.page {
  display: grid;
  grid-template-columns: 220px 1fr;
  grid-template-rows: 60px 1fr 40px;
  grid-template-areas:
    "header header"
    "nav main"
    "nav footer";
}
```

For a more compact syntax, pass the area names as strings directly:

```php
GridBuilder::make('.page')
    ->areaRows(
        'header header',
        'nav    main',
        'nav    footer',
    )
    ->build();
```

### Grid items (child elements)

Attach `GridItem` objects to the builder to generate child selectors alongside the container:

```php
use FlexGrid\GridItem;
use FlexGrid\Enums\ItemAlignment;

GridBuilder::make('.page')
    ->columns('220px', '1fr')
    ->rows('60px', '1fr', '40px')
    ->areaRows('header header', 'nav main', 'nav footer')
    ->item(GridItem::select('.page__header')->namedArea('header'))
    ->item(GridItem::select('.page__nav')->namedArea('nav'))
    ->item(GridItem::select('.page__main')->namedArea('main'))
    ->item(
        GridItem::select('.page__aside')
            ->justifySelf(ItemAlignment::End)
            ->alignSelf(ItemAlignment::Start)
    )
    ->build();
```

```css
.page {
  display: grid;
  grid-template-columns: 220px 1fr;
  grid-template-rows: 60px 1fr 40px;
  grid-template-areas:
    "header header"
    "nav main"
    "nav footer";
}

.page__header { grid-area: header; }
.page__nav    { grid-area: nav; }
.page__main   { grid-area: main; }
.page__aside  { justify-self: end; align-self: start; }
```

### Line-based placement

When named areas are not used, place items by grid line numbers:

```php
use FlexGrid\GridArea;

GridBuilder::make('.gallery')
    ->repeatColumns(4, '1fr')
    ->gap('1rem')
    ->item(
        GridItem::select('.gallery__hero')
            ->area(GridArea::at(1, 1)->spanRows(2)->spanColumns(2))
    )
    ->item(
        GridItem::select('.gallery__wide')
            ->area(GridArea::at(3, 1)->spanColumns(3))
    )
    ->item(
        GridItem::select('.gallery__tall')
            ->area(GridArea::at(1, 4)->rowEnd(4))
    )
    ->build();
```

```css
.gallery { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.gallery__hero  { grid-row: 1 / span 2; grid-column: 1 / span 2; }
.gallery__wide  { grid-row: 3 / auto;   grid-column: 1 / span 3; }
.gallery__tall  { grid-row: 1 / 4;      grid-column: 4 / auto;   }
```

### Alignment

Grid alignment is split into two enums:
- `ItemAlignment`: `align-items`, `justify-items`, `align-self`, `justify-self`
- `ContentAlignment`: `align-content`, `justify-content`

```php
use FlexGrid\Enums\ContentAlignment;
use FlexGrid\Enums\ItemAlignment;

GridBuilder::make('.grid')
    ->columns(GridValue::repeat(3, '200px'))
    ->placeItems(ItemAlignment::Center)                  // align-items + justify-items
    ->placeContent(ContentAlignment::Center)             // align-content + justify-content
    ->build();

// Or set each axis individually:
GridBuilder::make('.grid')
    ->alignItems(ItemAlignment::Start)
    ->justifyItems(ItemAlignment::End)
    ->alignContent(ContentAlignment::SpaceBetween)
    ->justifyContent(ContentAlignment::SpaceAround)
    ->build();
```

`ItemAlignment` cases: `Start`, `End`, `Center`, `Stretch`, `Baseline`.

`ContentAlignment` cases: `Start`, `End`, `Center`, `Stretch`, `SpaceBetween`, `SpaceAround`, `SpaceEvenly`.

Self-alignment on items:

```php
GridItem::select('.box')
    ->placeSelf(ItemAlignment::Center)       // align-self + justify-self
    ->build();

GridItem::select('.box')
    ->alignSelf(ItemAlignment::Start)
    ->justifySelf(ItemAlignment::End)
    ->build();
```

### Auto flow and implicit tracks

```php
GridBuilder::make('.masonry')
    ->autoFillColumns('220px')
    ->autoRows('10px')           // fine-grained implicit rows for JS masonry
    ->autoFlow('row dense')      // fill gaps greedily
    ->build();
```

### Responsive breakpoints

`responsive(int $minWidth, callable)` wraps a variant in `@media (min-width: вЂ¦)`. `media(string $query, callable)` accepts any media query string.

```php
GridBuilder::make('.layout')
    ->columns('1fr')
    ->gap('1rem')
    ->responsive(640, fn(GridBuilder $g) =>
        $g->columns('1fr', '1fr')
    )
    ->responsive(1024, fn(GridBuilder $g) =>
        $g->columns('1fr', '1fr', '1fr')
          ->gap('2rem')
    )
    ->media('(prefers-reduced-motion: reduce)', fn(GridBuilder $g) =>
        $g->autoFlow('row')
    )
    ->build();
```

```css
.layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 640px) {
  .layout { grid-template-columns: 1fr 1fr; }
}

@media (min-width: 1024px) {
  .layout { grid-template-columns: 1fr 1fr 1fr; gap: 2rem; }
}

@media (prefers-reduced-motion: reduce) {
  .layout { grid-auto-flow: row; }
}
```

### Inline styles

`toInlineStyle()` returns a string suitable for the HTML `style` attribute вЂ” no selector, no braces:

```php
$style = GridBuilder::make()
    ->columns('1fr', '2fr')
    ->gap('1rem')
    ->toInlineStyle();

// "display: grid; grid-template-columns: 1fr 2fr; gap: 1rem"
```

```html
<div style="<?= $style ?>">вЂ¦</div>
```

### Inline grid

```php
GridBuilder::make('.widget')
    ->inline()          // display: inline-grid
    ->columns('auto', '1fr')
    ->build();
```

---

## Presets

The `Grid` facade provides one-liner factory methods for the most common layouts. Every preset returns a `GridBuilder` you can keep chaining.

### `Grid::columns()`

Equal N-column layout.

```php
Grid::columns(3, '.grid', '1.5rem')->build();
// grid-template-columns: repeat(3, 1fr); gap: 1.5rem
```

### `Grid::fluid()`

Responsive fluid columns using `auto-fill`. Columns collapse automatically when the container is too narrow.

```php
Grid::fluid('.cards', '280px', '1.25rem')->build();
// grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))
```

### `Grid::sidebar()`

Fixed-width sidebar on the left, fluid content on the right.

```php
Grid::sidebar('.layout', '260px', '2rem')->build();
// grid-template-columns: 260px 1fr
```

### `Grid::centered()`

Centers content at a max-width by placing fluid gutters on either side.

```php
Grid::centered('.page', '860px')->build();
// grid-template-columns: 1fr minmax(0, 860px) 1fr
```

Place your content in the middle column:

```php
GridItem::select('.page__content')->place(1, 2)->build();
// grid-row: 1 / auto; grid-column: 2 / auto
```

### `Grid::holyGrail()`

Classic five-area layout: header across the top, sidebar + main content + aside in the middle, footer across the bottom.

```php
Grid::holyGrail('.page', sideWidth: '220px', asideWidth: '160px')->build();
```

```css
.page {
  display: grid;
  grid-template-columns: 220px 1fr 160px;
  grid-template-rows: auto 1fr auto;
  grid-template-areas:
    "header  header  header"
    "sidebar main    aside"
    "footer  footer  footer";
}
```

### `Grid::dashboard()`

Two-column dashboard with a persistent sidebar and a three-row main area.

```php
Grid::dashboard('.app', sidebarWidth: '240px', headerHeight: '64px')->build();
```

```css
.app {
  display: grid;
  grid-template-columns: 240px 1fr;
  grid-template-rows: 64px 1fr auto;
  grid-template-areas:
    "header header"
    "nav    main"
    "nav    footer";
}
```

### `Grid::masonry()`

Dense auto-flow grid for JavaScript masonry: items are placed greedily to fill gaps. Pair with JS to calculate `grid-row-end` per item.

```php
Grid::masonry('.wall', '240px', '1rem')->build();
// grid-template-columns: repeat(auto-fill, minmax(240px, 1fr))
// grid-auto-rows: 10px
// grid-auto-flow: row dense
```

---

## GridValue reference

Static helpers for CSS Grid value functions. All return plain strings.

| Call | Output |
|---|---|
| `GridValue::fr(1)` | `"1fr"` |
| `GridValue::fr(2.5)` | `"2.5fr"` |
| `GridValue::minmax('200px', '1fr')` | `"minmax(200px, 1fr)"` |
| `GridValue::repeat(3, '1fr')` | `"repeat(3, 1fr)"` |
| `GridValue::repeat('auto-fill', '1fr')` | `"repeat(auto-fill, 1fr)"` |
| `GridValue::fitContent('300px')` | `"fit-content(300px)"` |
| `GridValue::Auto->value` | `"auto"` |
| `GridValue::MaxContent->value` | `"max-content"` |
| `GridValue::MinContent->value` | `"min-content"` |

---

## GridArea reference

```php
// Named area (outputs grid-area)
GridArea::named('header');

// Line-based (outputs grid-row + grid-column)
GridArea::at(rowStart: 1, columnStart: 1)
    ->spanRows(2)
    ->spanColumns(3);

// Explicit end lines
GridArea::at(2, 1)
    ->rowEnd(5)
    ->columnEnd(4);

// Set lines individually
(new GridArea())
    ->rowStart(1)
    ->columnStart(3)
    ->spanRows(2);
```

---

## GridItem reference

```php
GridItem::select('.selector')
    ->namedArea('main')                      // grid-area: main
    ->place(2, 1)                            // grid-row: 2; grid-column: 1
    ->span(rowSpan: 2, colSpan: 3)           // span 2 rows, 3 columns
    ->area(GridArea::at(1, 2)->spanRows(2))  // full GridArea object
    ->alignSelf(ItemAlignment::Start)
    ->justifySelf(ItemAlignment::End)
    ->placeSelf(ItemAlignment::Center)       // both axes
    ->order(2)
    ->toCss();                               // returns CSS string
```

---

## GridTemplate reference

```php
$template = GridTemplate::create()
    ->row(['header', 'header', 'header'])
    ->row(['nav',    'main',   'aside'])
    ->row(['footer', 'footer', 'footer']);

$template->build();          // CSS value string for grid-template-areas
$template->getAreaNames();   // ['header', 'nav', 'main', 'aside', 'footer']
$template->columnCount();    // 3
$template->rowCount();       // 3
```

---

## Flex Examples

### Basic row with gap

```php
use FlexGrid\Flex;

Flex::row('.menu', '1rem')->build();
```

```css
.menu {
  display: flex;
  flex-direction: row;
  gap: 1rem;
}
```

### Flexible cards with wrapping

```php
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.cards')
    ->direction(FlexDirection::Row)
    ->wrap(FlexWrap::Wrap)
    ->gap('1rem')
    ->item(FlexItem::select('.cards > .card')->flex(1, 1, '240px'))
    ->build();
```

```css
.cards {
  display: flex;
  flex-flow: row wrap;
  gap: 1rem;
}

.cards > .card { flex: 1 1 240px; }
```

### Toolbar alignment

```php
use FlexGrid\Enums\ContentAlignment;
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\ItemAlignment;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.toolbar')
    ->direction(FlexDirection::Row)
    ->justifyContent(ContentAlignment::SpaceBetween)
    ->alignItems(ItemAlignment::Center)
    ->build();
```

```css
.toolbar {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}
```

### Responsive direction switch

```php
use FlexGrid\Enums\FlexDirection;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.layout')
    ->direction(FlexDirection::Column)
    ->gap('1rem')
    ->responsive(768, fn(FlexBuilder $f) => $f->direction(FlexDirection::Row))
    ->build();
```

```css
.layout {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .layout { flex-direction: row; }
}
```

### Direction/wrap helpers and repeated gap calls

```php
use FlexGrid\Enums\FlexDirection;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.rail')
    ->direction(FlexDirection::ColumnReverse)
    ->noWrap()         // flex-wrap: nowrap
    ->gap('0.5rem')
    ->gap('1rem')      // last call wins
    ->build();
```

```css
.rail {
  display: flex;
  flex-flow: column-reverse nowrap;
  gap: 1rem;
}
```

---

## Flex Presets

The `Flex` facade provides one-liner factory methods for common Flexbox layouts.

### `Flex::row()`

```php
Flex::row('.menu', '0.75rem')->build();
// display: flex; flex-direction: row; gap: 0.75rem
```

### `Flex::column()`

```php
Flex::column('.stack', '0.5rem')->build();
// display: flex; flex-direction: column; gap: 0.5rem
```

### `Flex::cards()`

```php
Flex::cards('.cards', '240px', '1rem')->build();
// container: row + wrap + gap
// children (.cards > *): flex: 1 1 240px
```

### `Flex::sidebar()`

```php
Flex::sidebar('.layout', '260px', '1.5rem')->build();
// first child: flex: 0 0 260px
// last child:  flex: 1 1 0
```

### `FlexBuilder` wrapping helpers

```php
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.list')
    ->direction(FlexDirection::RowReverse)
    ->wrapReverse();   // flex-wrap: wrap-reverse
```

`noWrap()` is shorthand for `wrap(FlexWrap::NoWrap)`.
Repeated `gap(...)` calls do not accumulate: the last call replaces the previous value.

---

## References

- [Flexbox (MDN)](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/CSS_layout/Flexbox)
- [CSS Grid layout (MDN)](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/CSS_layout/Grids)
- [Flexbox vs Grid in CSS – Which Should You Use?](https://www.freecodecamp.org/news/flexbox-vs-grid-in-css/)
- [An Interactive Guide to CSS Grid](https://www.joshwcomeau.com/css/interactive-guide-to-grid/)
- [An Interactive Guide to Flexbox](https://www.joshwcomeau.com/css/interactive-guide-to-flexbox/)
- [Learn CSS Grid](https://learncssgrid.com)
- [Grid by Example](https://gridbyexample.com/examples/)
- [CSS Grid Layout Module Level 1](https://www.w3.org/TR/css-grid-1/)
- [CSS Flexible Box Layout Module Level 1](https://www.w3.org/TR/css-flexbox-1/)
