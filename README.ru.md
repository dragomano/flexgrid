# FlexGrid PHP Builder

![PHP](https://img.shields.io/badge/PHP-^8.2-blue.svg?style=flat)
[![Coverage Status](https://coveralls.io/repos/github/dragomano/flexgrid/badge.svg?branch=main)](https://coveralls.io/github/dragomano/flexgrid?branch=main)

[English](README.md) | **Русский**

Fluent-библиотека на PHP для генерации раскладок CSS Grid и Flexbox. Поддерживает именованные области, размещение по линиям сетки, адаптивные брейкпоинты и готовые пресеты для распространённых сценариев.

---

## Установка

```bash
composer require bugo/flexgrid
```
---

## Быстрый старт

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

`GridBuilder` — основной класс. Все методы возвращают `static`, поэтому свободно объединяются в цепочки.

### Колонки и строки

```php
use FlexGrid\GridBuilder;
use FlexGrid\Enums\GridValue;

GridBuilder::make('.layout')
    ->columns('200px', '1fr', '200px')   // фиксированные значения
    ->rows('64px', '1fr', '48px')        // треки строк
    ->gap('1rem')
    ->build();
```

Используйте помощники `GridValue`, чтобы не писать CSS-строки вручную:

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

Сокращённые методы для повторяющихся треков:

```php
GridBuilder::make('.grid')
    ->repeatColumns(4, '1fr')         // repeat(4, 1fr)
    ->repeatRows(3, '200px')          // repeat(3, 200px)
    ->autoFillColumns('250px')        // repeat(auto-fill, minmax(250px, 1fr))
    ->autoFitColumns('250px', '1fr')  // repeat(auto-fit,  minmax(250px, 1fr))
    ->build();
```

### Отступы (gap)

```php
->gap('1rem')           // gap: 1rem  (обе оси)
->gap('1rem', '2rem')   // gap: 1rem 2rem  (строка, колонка)
->rowGap('1rem')        // только row-gap
->columnGap('2rem')     // только column-gap
```

### Именованные области шаблона

Используйте `GridTemplate`, чтобы описать визуальную раскладку как ASCII-сетку:

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

Для более компактного синтаксиса передавайте имена областей строками напрямую:

```php
GridBuilder::make('.page')
    ->areaRows(
        'header header',
        'nav    main',
        'nav    footer',
    )
    ->build();
```

### Элементы сетки (дочерние элементы)

Прикрепляйте объекты `GridItem` к builder, чтобы сгенерировать дочерние селекторы вместе с контейнером:

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

.page__header {
  grid-area: header;
}

.page__nav {
  grid-area: nav;
}

.page__main {
  grid-area: main;
}

.page__aside {
  place-self: start end;
}
```

Каждый элемент выводится как отдельное многострочное правило. Обратите внимание: одновременная установка `alignSelf()` и `justifySelf()` сворачивается в сокращение `place-self` (в порядке `align justify`).

### Размещение по линиям

Когда именованные области не используются, размещайте элементы по номерам линий сетки:

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
.gallery {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.gallery__hero {
  grid-row: 1 / span 2;
  grid-column: 1 / span 2;
}

.gallery__wide {
  grid-row: 3 / auto;
  grid-column: 1 / span 3;
}

.gallery__tall {
  grid-row: 1 / 4;
  grid-column: 4 / auto;
}
```

Размещение по линиям бывает двух видов, которые нельзя смешивать на одном элементе:

- Размещение через область: `area()`, `place()`, `namedArea()` или `span()` (выводит сокращения `grid-row`/`grid-column`/`grid-area`).
- Отдельные свойства линий: `rowStart()`, `rowEnd()`, `columnStart()`, `columnEnd()` (выводит longhand-свойства `grid-*-start`/`grid-*-end`).

Комбинация обоих способов на одном `GridItem` бросает `InvalidArgumentException` в момент настройки, поэтому неоднозначное размещение никогда не попадёт в сгенерированный CSS. Аналогично, именованная `GridArea` и координаты по линиям взаимоисключающи: вызов `rowStart()`, `columnEnd()`, `spanRows()` и подобных на `GridArea::named(...)` бросает исключение.

### Выравнивание

Выравнивание в сетке разделено на два enum:
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

// Либо задать каждую ось по отдельности:
GridBuilder::make('.grid')
    ->alignItems(ItemAlignment::Start)
    ->justifyItems(ItemAlignment::End)
    ->alignContent(ContentAlignment::SpaceBetween)
    ->justifyContent(ContentAlignment::SpaceAround)
    ->build();
```

Значения `ItemAlignment`: `Start`, `End`, `Center`, `Stretch`, `Baseline`.

Значения `ContentAlignment`: `Start`, `End`, `Center`, `Stretch`, `SpaceBetween`, `SpaceAround`, `SpaceEvenly`.

Само-выравнивание на элементах:

```php
GridItem::select('.box')
    ->placeSelf(ItemAlignment::Center)       // align-self + justify-self
    ->build();

GridItem::select('.box')
    ->alignSelf(ItemAlignment::Start)
    ->justifySelf(ItemAlignment::End)
    ->build();
```

### Auto flow и неявные треки

```php
GridBuilder::make('.masonry')
    ->autoFillColumns('220px')
    ->autoRows('10px')           // мелкие неявные строки для JS-masonry
    ->autoFlow('row dense')      // жадно заполнять пропуски
    ->build();
```

### Адаптивные брейкпоинты

`responsive(int $minWidth, callable)` оборачивает вариант в `@media (min-width: …)`. `media(string $query, callable)` принимает любую строку media-запроса.

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
  .layout {
    grid-template-columns: 1fr 1fr;
  }
}

@media (min-width: 1024px) {
  .layout {
    grid-template-columns: 1fr 1fr 1fr;
    gap: 2rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .layout {
    grid-auto-flow: row;
  }
}
```

Каждый вариант настраивается с нуля, но внутри `@media`-блока выводятся только свойства, которые реально отличаются от базового контейнера. Неизменённые объявления (например, `display`) отбрасываются, а вариант, ничего не меняющий, вообще не создаёт `@media`-блок.

### Inline-стили

`toInlineStyle()` возвращает строку, пригодную для HTML-атрибута `style` — без селектора и фигурных скобок:

```php
$style = GridBuilder::make()
    ->columns('1fr', '2fr')
    ->gap('1rem')
    ->toInlineStyle();

// "display: grid; grid-template-columns: 1fr 2fr; gap: 1rem"
```

```html
<div style="<?= $style ?>">…</div>
```

### Inline-сетка

```php
GridBuilder::make('.widget')
    ->inline()          // display: inline-grid
    ->columns('auto', '1fr')
    ->build();
```

---

## Пресеты

Фасад `Grid` предоставляет однострочные фабричные методы для самых распространённых раскладок. Каждый пресет возвращает `GridBuilder`, который можно продолжать в цепочке.

### `Grid::columns()`

Раскладка из N равных колонок.

```php
Grid::columns(3, '.grid', '1.5rem')->build();
// grid-template-columns: repeat(3, 1fr); gap: 1.5rem
```

### `Grid::fluid()`

Адаптивные «резиновые» колонки на основе `auto-fill`. Колонки автоматически схлопываются, когда контейнер слишком узкий.

```php
Grid::fluid('.cards', '280px', '1.25rem')->build();
// grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))
```

### `Grid::sidebar()`

Боковая панель фиксированной ширины слева, «резиновый» контент справа.

```php
Grid::sidebar('.layout', '260px', '2rem')->build();
// grid-template-columns: 260px 1fr
```

### `Grid::centered()`

Центрирует контент по максимальной ширине, размещая «резиновые» отступы по обе стороны.

```php
Grid::centered('.page', '860px')->build();
// grid-template-columns: 1fr minmax(0, 860px) 1fr
```

Разместите контент в средней колонке:

```php
GridItem::select('.page__content')->place(1, 2)->build();
// grid-row: 1 / auto; grid-column: 2 / auto
```

### `Grid::holyGrail()`

Классическая раскладка из пяти областей: header по всей ширине сверху, sidebar + main + aside в середине, footer по всей ширине снизу.

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

Двухколоночная панель управления с постоянной боковой панелью и трёхстрочной основной областью.

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

Плотная auto-flow сетка для JavaScript-masonry: элементы размещаются жадно, заполняя пропуски. Используйте вместе с JS для расчёта `grid-row-end` каждого элемента.

```php
Grid::masonry('.wall', '240px', '1rem')->build();
// grid-template-columns: repeat(auto-fill, minmax(240px, 1fr))
// grid-auto-rows: 10px
// grid-auto-flow: row dense
```

---

## Справочник GridValue

Статические помощники для функций-значений CSS Grid. Все возвращают обычные строки.

| Вызов | Результат |
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

## Справочник GridArea

```php
// Именованная область (выводит grid-area)
GridArea::named('header');

// По линиям (выводит grid-row + grid-column)
GridArea::at(rowStart: 1, columnStart: 1)
    ->spanRows(2)
    ->spanColumns(3);

// Явные конечные линии
GridArea::at(2, 1)
    ->rowEnd(5)
    ->columnEnd(4);

// Задать линии по отдельности
(new GridArea())
    ->rowStart(1)
    ->columnStart(3)
    ->spanRows(2);
```

---

## Справочник GridItem

Размещение выбирается **одним** из следующих способов (они взаимоисключающи — см. [Размещение по линиям](#размещение-по-линиям)):

```php
// Именованная область
GridItem::select('.selector')->namedArea('main');       // grid-area: main

// Размещение по координатам линий
GridItem::select('.selector')->place(2, 1);              // grid-row: 2 / auto; grid-column: 1 / auto

// Авторазмещение только по span
GridItem::select('.selector')->span(rowSpan: 2, colSpan: 3);  // grid-row: span 2; grid-column: span 3

// Полный объект GridArea
GridItem::select('.selector')->area(GridArea::at(1, 2)->spanRows(2));
```

Само-выравнивание и `order` не зависят от размещения и могут быть добавлены к любому из вариантов выше:

```php
GridItem::select('.selector')
    ->place(2, 1)
    ->alignSelf(ItemAlignment::Start)        // align-self: start
    ->justifySelf(ItemAlignment::End)        // justify-self: end
    ->order(2)                               // order: 2
    ->toCss();                               // возвращает строку CSS
```

Одновременная установка `alignSelf()` и `justifySelf()` сворачивается в сокращение `place-self`. `placeSelf(ItemAlignment $align, ?ItemAlignment $justify = null)` задаёт обе оси сразу.

---

## Справочник GridTemplate

```php
$template = GridTemplate::create()
    ->row(['header', 'header', 'header'])
    ->row(['nav',    'main',   'aside'])
    ->row(['footer', 'footer', 'footer']);

$template->build();          // строка CSS-значения для grid-template-areas
$template->getAreaNames();   // ['header', 'nav', 'main', 'aside', 'footer']
$template->columnCount();    // 3
$template->rowCount();       // 3
```

---

## Примеры Flex

### Базовая строка с отступом

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

### Гибкие карточки с переносом

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

.cards > .card {
  flex: 1 1 240px;
}
```

### Выравнивание тулбара

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

### Адаптивная смена направления

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
  .layout {
    flex-direction: row;
  }
}
```

### Помощники direction/wrap и повторные вызовы gap

```php
use FlexGrid\Enums\FlexDirection;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.rail')
    ->direction(FlexDirection::ColumnReverse)
    ->noWrap()         // flex-wrap: nowrap
    ->gap('0.5rem')
    ->gap('1rem')      // побеждает последний вызов
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

## Пресеты Flex

Фасад `Flex` предоставляет однострочные фабричные методы для распространённых раскладок Flexbox.

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
// контейнер: row + wrap + gap
// дочерние (.cards > *): flex: 1 1 240px
```

### `Flex::sidebar()`

```php
Flex::sidebar('.layout', '260px', '1.5rem')->build();
// первый дочерний: flex: 0 0 260px
// последний дочерний:  flex: 1 1 0
```

### Помощники переноса `FlexBuilder`

```php
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;
use FlexGrid\FlexBuilder;

FlexBuilder::make('.list')
    ->direction(FlexDirection::RowReverse)
    ->wrapReverse();   // flex-wrap: wrap-reverse
```

`noWrap()` — это сокращение для `wrap(FlexWrap::NoWrap)`.
Повторные вызовы `gap(...)` не накапливаются: последний вызов заменяет предыдущее значение.

---

## Валидация и ошибки

Конфигурация валидируется немедленно: некорректный ввод бросает `InvalidArgumentException` в момент вызова метода, а не позже при генерации CSS.

### Числовые ограничения

| Метод | Правило |
|---|---|
| `GridValue::fr($n)` | `$n` не должно быть отрицательным (`0fr` допустимо). |
| `GridValue::repeat($count, ...)` | целочисленный `$count` должен быть `>= 1`; ключевые слова вроде `auto-fill`/`auto-fit` пропускаются как есть. |
| `GridBuilder::repeatColumns()` / `repeatRows()`, `Grid::columns()` | количество колонок/строк должно быть `>= 1`. |
| `GridArea::spanRows()` / `spanColumns()`, `GridItem::span()` | span должен быть `>= 1`. |
| `FlexItem::grow()` / `shrink()` / `flex()` | grow и shrink не должны быть отрицательными (`0` допустимо). |

`order()` принимает любое целое число, включая отрицательные, потому что отрицательный `order` — валидный CSS.

### Вызовы треков и размещения

- `columns(...)` / `rows(...)` накапливают свои треки между вызовами; вызов без аргументов ничего не добавляет.
- Размещение через область и отдельные свойства линий взаимоисключающи на `GridItem` (см. [Размещение по линиям](#размещение-по-линиям)).
- Именованная `GridArea` не может одновременно нести координаты по линиям.

### Ограничения `GridTemplate`

`grid-template-areas` должен быть прямоугольным, поэтому `GridTemplate::row()` (и сокращение `areaRows()`) требуют:

- строки не должны быть пустыми;
- каждая ячейка должна быть непустым токеном без пробелов и кавычек (используйте `.` для пустой ячейки);
- каждая строка должна иметь то же число колонок, что и первая.

### Безопасность CSS-строк

Пользовательские строки (селекторы, размеры треков, отступы, media-запросы, значения элементов) **не** валидируются как CSS — передача корректного CSS остаётся ответственностью вызывающей стороны. Но библиотека применяет минимальный deny-list, который в момент настройки отклоняет только те символы, что могут «вырваться» из окружающего CSS/HTML-контекста:

| Позиция | Отклоняемые символы |
|---|---|
| Селекторы, media-запросы | `{` `}` `<` `;` и управляющие символы (табуляция, перевод строки, `\0`, …) |
| Значения свойств (треки, отступы, размеры, `flex-basis`, именованные линии, …) | `{` `}` `<` `>` `;` `"` `'` и управляющие символы |

Это защита от breakout, а не валидатор CSS: легитимный CSS вроде `minmax(0, 1fr)`, `calc(100% - 20px)`, `var(--x)`, дочерний комбинатор `.a > .b` и селекторы по атрибутам с кавычками `[type="text"]` проходят проверку. Два следствия, о которых стоит помнить:

- Значения также должны быть безопасны внутри HTML-атрибута `style="…"`, поэтому `"` и `'` в значениях отклоняются.
- Range-синтаксис Media Queries Level 4 с `<` (например, `(width < 640px)`) отклоняется; используйте `(min-width: …)` / `(max-width: …)`.

### Ограничения

- У `FlexItem` нет `justifySelf()`: `justify-self` не работает в Flexbox. Используйте `margin: auto` на элементе или `justifyContent()` на контейнере.
- Внутри `@media`-блока выводится только дельта относительно базового контейнера; адаптивный вариант, ничего не меняющий, не создаёт блок (см. [Адаптивные брейкпоинты](#адаптивные-брейкпоинты)).
- Библиотека генерирует только текст CSS. Помимо breakout-deny-list выше, она не парсит и не валидирует произвольные CSS-значения.

---

## Справочник API

Компактные сигнатуры всех публичных методов builders и facades с CSS, который каждый из них производит. У `GridValue`, `GridArea`, `GridItem`, `FlexItem` и `GridTemplate` также есть отдельные разделы с примерами выше.

### Методы контейнера (общие для `GridBuilder` и `FlexBuilder`)

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `make(string $selector = '')` | `static` | Фабрика; аргумент становится селектором правила (пустой = без селектора). |
| `gap(string $rowGap, ?string $columnGap = null)` | `$this` | `gap: <row>` или `gap: <row> <column>`, если они различаются. |
| `rowGap(string $gap)` | `self` | `row-gap: <gap>` |
| `columnGap(string $gap)` | `self` | `column-gap: <gap>` |
| `alignContent(ContentAlignment $a)` | `self` | `align-content: <a>` |
| `justifyContent(ContentAlignment $a)` | `self` | `justify-content: <a>` |
| `placeContent(ContentAlignment $align, ?ContentAlignment $justify = null)` | `self` | `place-content: <align> [<justify>]`; сворачивается в одно значение при равенстве. |
| `responsive(int $minWidth, callable $configure)` | `self` | Оборачивает дельту варианта в `@media (min-width: <minWidth>px)`. |
| `media(string $query, callable $configure)` | `self` | Оборачивает дельту варианта в `@media <query>`. |
| `build(string $indent = '')` | `string` | Полный CSS: правило контейнера, дочерние правила и `@media`-блоки. |
| `toInlineStyle()` | `string` | `prop: val; …` только для контейнера — без селектора и скобок. |

### `GridBuilder`

Добавляет grid-специфичные методы поверх общих методов контейнера.

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `inline()` | `self` | `display: inline-grid` |
| `columns(string ...$tracks)` | `self` | Добавляет треки в `grid-template-columns` (накапливает между вызовами). |
| `rows(string ...$tracks)` | `self` | Добавляет треки в `grid-template-rows` (накапливает между вызовами). |
| `repeatColumns(int $count, string $track = '1fr')` | `self` | Добавляет `repeat(<count>, <track>)` в колонки. |
| `repeatRows(int $count, string $track = '1fr')` | `self` | Добавляет `repeat(<count>, <track>)` в строки. |
| `autoFillColumns(string $min, string $max = '1fr')` | `self` | Добавляет `repeat(auto-fill, minmax(<min>, <max>))`. |
| `autoFitColumns(string $min, string $max = '1fr')` | `self` | Добавляет `repeat(auto-fit, minmax(<min>, <max>))`. |
| `areas(GridTemplate $template)` | `self` | `grid-template-areas: <template>` |
| `areaRows(mixed ...$rows)` | `self` | Строит `grid-template-areas` из строк или массивов имён. |
| `autoRows(string $size)` | `self` | `grid-auto-rows: <size>` |
| `autoColumns(string $size)` | `self` | `grid-auto-columns: <size>` |
| `autoFlow(string $flow)` | `self` | `grid-auto-flow: <flow>` |
| `alignItems(ItemAlignment $a)` | `self` | `align-items: <a>` |
| `justifyItems(ItemAlignment $a)` | `self` | `justify-items: <a>` |
| `placeItems(ItemAlignment $align, ?ItemAlignment $justify = null)` | `self` | `place-items: <align> [<justify>]`; сворачивается в одно значение при равенстве. |
| `item(GridItem $item)` | `self` | Добавляет одно дочернее правило. |
| `items(list<GridItem> $items)` | `self` | Добавляет несколько дочерних правил. |

### `FlexBuilder`

Добавляет flex-специфичные методы поверх общих методов контейнера.

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `inline()` | `self` | `display: inline-flex` |
| `direction(FlexDirection $value)` | `self` | `flex-direction: <value>` (сливается в `flex-flow`, если задан и `wrap`). |
| `wrap(FlexWrap $value)` | `self` | `flex-wrap: <value>` (сливается в `flex-flow`, если задан и `direction`). |
| `noWrap()` | `self` | `flex-wrap: nowrap` |
| `wrapReverse()` | `self` | `flex-wrap: wrap-reverse` |
| `flow(FlexDirection $direction, FlexWrap $wrap)` | `self` | `flex-flow: <direction> <wrap>` |
| `alignItems(ItemAlignment $a)` | `self` | `align-items: <a>` |
| `item(FlexItem $item)` | `self` | Добавляет одно дочернее правило. |
| `items(list<FlexItem> $items)` | `self` | Добавляет несколько дочерних правил. |

Когда заданы и `direction()`, и `wrap()` (напрямую или через `flow()`), они сворачиваются в одно объявление `flex-flow`.

### Фасад `Grid`

Каждый пресет возвращает готовый к цепочке `GridBuilder`.

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `Grid::container(string $selector = '')` | `GridBuilder` | Пустой grid-контейнер. |
| `Grid::item(string $selector = '')` | `GridItem` | Новый элемент сетки. |
| `Grid::template()` | `GridTemplate` | Новый шаблон областей. |
| `Grid::area()` | `GridArea` | Новая область размещения. |
| `Grid::columns(int $n, string $selector = '', string $gap = '1rem')` | `GridBuilder` | `grid-template-columns: repeat(<n>, 1fr)` + `gap`. |
| `Grid::fluid(string $selector = '', string $minWidth = '250px', string $gap = '1rem')` | `GridBuilder` | `repeat(auto-fill, minmax(<minWidth>, 1fr))` + `gap`. |
| `Grid::sidebar(string $selector = '', string $sideWidth = '260px', string $gap = '1.5rem')` | `GridBuilder` | `grid-template-columns: <sideWidth> 1fr` + `gap`. |
| `Grid::centered(string $selector = '', string $maxWidth = '720px', string $gap = '1rem')` | `GridBuilder` | `grid-template-columns: 1fr minmax(0, <maxWidth>) 1fr` + `gap`. |
| `Grid::holyGrail(string $selector = '', string $sideWidth = '200px', string $asideWidth = '160px', string $gap = '0')` | `GridBuilder` | области header / (sidebar + main + aside) / footer + `gap`. |
| `Grid::dashboard(string $selector = '', string $sidebarWidth = '240px', string $headerHeight = '60px')` | `GridBuilder` | области header / (nav + main) / (nav + footer). |
| `Grid::masonry(string $selector = '', string $minWidth = '220px', string $gap = '1rem')` | `GridBuilder` | auto-fill колонки + `grid-auto-rows: 10px` + `grid-auto-flow: row dense`. |

### Фасад `Flex`

Каждый пресет возвращает готовый к цепочке `FlexBuilder`.

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `Flex::container(string $selector = '')` | `FlexBuilder` | Пустой flex-контейнер. |
| `Flex::item(string $selector = '')` | `FlexItem` | Новый flex-элемент. |
| `Flex::row(string $selector = '', string $gap = '1rem')` | `FlexBuilder` | `flex-direction: row` + `gap`. |
| `Flex::column(string $selector = '', string $gap = '1rem')` | `FlexBuilder` | `flex-direction: column` + `gap`. |
| `Flex::cards(string $selector = '', string $minWidth = '250px', string $gap = '1rem')` | `FlexBuilder` | row + wrap + `gap`; дочерний `<selector> > *`: `flex: 1 1 <minWidth>`. |
| `Flex::sidebar(string $selector = '', string $sideWidth = '260px', string $gap = '1.5rem')` | `FlexBuilder` | row + `gap`; первый дочерний `flex: 0 0 <sideWidth>`, последний `flex: 1 1 0`. |

`Flex::cards()` и `Flex::sidebar()` выводят дочерние правила только при непустом `$selector`.

### `GridItem`

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `GridItem::select(string $selector)` | `static` | Фабрика. |
| `area(GridArea $area)` | `self` | `grid-*`-свойства области. |
| `place(int $row, int $col)` | `self` | `grid-row: <row> / auto; grid-column: <col> / auto` |
| `span(int $rowSpan, int $colSpan)` | `self` | `grid-row: span <rowSpan>; grid-column: span <colSpan>` |
| `namedArea(string $name)` | `self` | `grid-area: <name>` |
| `rowStart / rowEnd / columnStart / columnEnd (int\|string $line)` | `self` | longhand-свойства `grid-<axis>-start` / `grid-<axis>-end`. |
| `alignSelf(ItemAlignment $a)` | `self` | `align-self: <a>` |
| `justifySelf(ItemAlignment $a)` | `self` | `justify-self: <a>` |
| `placeSelf(ItemAlignment $align, ?ItemAlignment $justify = null)` | `self` | `place-self: <align> [<justify>]`; сворачивается в одно значение при равенстве. |
| `order(int $order)` | `self` | `order: <order>` |
| `toCss(string $indent = '')` | `string` | CSS-правило элемента. |

Размещение через область (`area()`/`place()`/`span()`/`namedArea()`) и отдельные longhand-свойства линий взаимоисключающи; `alignSelf()` + `justifySelf()` сворачиваются в `place-self`.

### `FlexItem`

| Метод | Возвращает | CSS / эффект |
|---|---|---|
| `FlexItem::select(string $selector)` | `static` | Фабрика. |
| `grow(int\|float $value)` | `self` | `flex-grow: <value>` |
| `shrink(int\|float $value)` | `self` | `flex-shrink: <value>` |
| `basis(string $value)` | `self` | `flex-basis: <value>` |
| `flex(int\|float $grow, int\|float $shrink, string $basis)` | `self` | `flex: <grow> <shrink> <basis>` |
| `alignSelf(ItemAlignment $a)` | `self` | `align-self: <a>` |
| `order(int $value)` | `self` | `order: <value>` |
| `toCss(string $indent = '')` | `string` | CSS-правило элемента. |

`flex()` и longhand-методы `grow()`/`shrink()`/`basis()` взаимоисключающи — установка одной стороны сбрасывает другую. У `FlexItem` нет `justifySelf()` (см. [Ограничения](#ограничения)).

---

## Ссылки

- [Flexbox (MDN)](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/CSS_layout/Flexbox)
- [CSS Grid layout (MDN)](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/CSS_layout/Grids)
- [Flexbox vs Grid in CSS – Which Should You Use?](https://www.freecodecamp.org/news/flexbox-vs-grid-in-css/)
- [An Interactive Guide to CSS Grid](https://www.joshwcomeau.com/css/interactive-guide-to-grid/)
- [An Interactive Guide to Flexbox](https://www.joshwcomeau.com/css/interactive-guide-to-flexbox/)
- [Learn CSS Grid](https://learncssgrid.com)
- [Grid by Example](https://gridbyexample.com/examples/)
- [CSS Grid Layout Module Level 1](https://www.w3.org/TR/css-grid-1/)
- [CSS Flexible Box Layout Module Level 1](https://www.w3.org/TR/css-flexbox-1/)