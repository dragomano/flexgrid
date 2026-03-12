<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\GridValue;
use FlexGrid\Enums\ItemAlignment;
use InvalidArgumentException;

final class GridBuilder extends AbstractBuilder
{
    private string $display = 'grid';

    /** @var list<string> */
    private array $templateColumns = [];

    /** @var list<string> */
    private array $templateRows = [];

    private ?GridTemplate $templateAreas = null;

    private ?string $autoColumns = null;

    private ?string $autoRows = null;

    private ?string $autoFlow = null;

    private ?ItemAlignment $justifyItems = null;

    private ?ItemAlignment $alignItems = null;

    public function inline(): self
    {
        $this->display = 'inline-grid';

        return $this;
    }

    public function columns(string ...$tracks): self
    {
        foreach ($tracks as $track) {
            $this->templateColumns[] = $track;
        }

        return $this;
    }

    public function repeatColumns(int $count, string $track = '1fr'): self
    {
        $this->templateColumns[] = GridValue::repeat($count, $track);

        return $this;
    }

    public function autoFillColumns(string $min, string $max = '1fr'): self
    {
        $this->templateColumns[] = GridValue::repeat('auto-fill', GridValue::minmax($min, $max));

        return $this;
    }

    public function autoFitColumns(string $min, string $max = '1fr'): self
    {
        $this->templateColumns[] = GridValue::repeat('auto-fit', GridValue::minmax($min, $max));

        return $this;
    }

    public function rows(string ...$tracks): self
    {
        foreach ($tracks as $track) {
            $this->templateRows[] = $track;
        }

        return $this;
    }

    public function repeatRows(int $count, string $track = '1fr'): self
    {
        $this->templateRows[] = GridValue::repeat($count, $track);

        return $this;
    }

    public function areas(GridTemplate $template): self
    {
        $this->templateAreas = $template;

        return $this;
    }

    public function areaRows(mixed ...$rows): self
    {
        $template = GridTemplate::create();

        foreach ($rows as $row) {
            if (is_string($row)) {
                $cells = [];

                foreach (explode(' ', trim($row)) as $cell) {
                    if ($cell !== '') {
                        $cells[] = $cell;
                    }
                }

                $template->row($cells);

                continue;
            }

            if (! is_array($row)) {
                throw new InvalidArgumentException('Each area row must be a string or an array of strings.');
            }

            $cells = array_values(array_filter(array_map(
                static function (mixed $cell): string {
                    if (! is_string($cell)) {
                        throw new InvalidArgumentException('Each area cell must be a string.');
                    }

                    return $cell;
                },
                $row
            ), static fn(string $cell): bool => $cell !== ''));

            $template->row($cells);
        }

        $this->templateAreas = $template;

        return $this;
    }

    public function autoRows(string $size): self
    {
        $this->autoRows = $size;

        return $this;
    }

    public function autoColumns(string $size): self
    {
        $this->autoColumns = $size;

        return $this;
    }

    public function autoFlow(string $flow): self
    {
        $this->autoFlow = $flow;

        return $this;
    }

    public function alignItems(ItemAlignment $alignment): self
    {
        $this->alignItems = $alignment;

        return $this;
    }

    public function justifyItems(ItemAlignment $alignment): self
    {
        $this->justifyItems = $alignment;

        return $this;
    }

    public function placeItems(ItemAlignment $align, ?ItemAlignment $justify = null): self
    {
        $this->alignItems   = $align;
        $this->justifyItems = $justify ?? $align;

        return $this;
    }

    public function item(GridItem $item): self
    {
        $this->pushItem($item);

        return $this;
    }

    /** @param list<GridItem> $items */
    public function items(array $items): self
    {
        $this->pushItems($items);

        return $this;
    }

    /** @return array<string, string> */
    public function buildProperties(): array
    {
        $props = ['display' => $this->display];

        if (! empty($this->templateColumns)) {
            $props['grid-template-columns'] = implode(' ', $this->templateColumns);
        }

        if (! empty($this->templateRows)) {
            $props['grid-template-rows'] = implode(' ', $this->templateRows);
        }

        if ($this->templateAreas !== null) {
            $props['grid-template-areas'] = $this->templateAreas->build();
        }

        $this->buildGapProperties($props);

        if ($this->autoRows !== null) {
            $props['grid-auto-rows'] = $this->autoRows;
        }

        if ($this->autoColumns !== null) {
            $props['grid-auto-columns'] = $this->autoColumns;
        }

        if ($this->autoFlow !== null) {
            $props['grid-auto-flow'] = $this->autoFlow;
        }

        if ($this->justifyItems !== null && $this->alignItems !== null) {
            if ($this->justifyItems === $this->alignItems) {
                $props['place-items'] = $this->justifyItems->value;
            } else {
                $props['place-items'] = $this->alignItems->value . ' ' . $this->justifyItems->value;
            }
        } elseif ($this->alignItems !== null) {
            $props['align-items'] = $this->alignItems->value;
        } elseif ($this->justifyItems !== null) {
            $props['justify-items'] = $this->justifyItems->value;
        }

        $this->buildPlaceProperty(
            $props,
            'place-content',
            'align-content',
            'justify-content',
            $this->alignContent,
            $this->justifyContent
        );

        return $props;
    }
}
