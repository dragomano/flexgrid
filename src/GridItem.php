<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\ItemAlignment;

/**
 * Represents a single grid item (child element) with its placement and self-alignment.
 */
final class GridItem extends AbstractItem
{
    private ?GridArea $area = null;

    private int|string|null $rowStart = null;

    private int|string|null $rowEnd = null;

    private int|string|null $columnStart = null;

    private int|string|null $columnEnd = null;

    private ?ItemAlignment $justifySelf = null;

    private ?ItemAlignment $alignSelf = null;

    private ?int $order = null;

    public function area(GridArea $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function place(int $row, int $col): self
    {
        $this->area = GridArea::at($row, $col);

        return $this;
    }

    public function span(int $rowSpan, int $colSpan): self
    {
        if ($this->area === null) {
            $this->area = new GridArea();
        }

        $this->area->spanRows($rowSpan)->spanColumns($colSpan);

        return $this;
    }

    public function namedArea(string $name): self
    {
        $this->area = GridArea::named($name);

        return $this;
    }

    public function justifySelf(ItemAlignment $alignment): self
    {
        $this->justifySelf = $alignment;

        return $this;
    }

    public function alignSelf(ItemAlignment $alignment): self
    {
        $this->alignSelf = $alignment;

        return $this;
    }

    public function placeSelf(ItemAlignment $align, ?ItemAlignment $justify = null): self
    {
        $this->alignSelf   = $align;
        $this->justifySelf = $justify ?? $align;

        return $this;
    }

    public function order(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function rowStart(int|string $line): self
    {
        $this->rowStart = $line;

        return $this;
    }

    public function rowEnd(int|string $line): self
    {
        $this->rowEnd = $line;

        return $this;
    }

    public function columnStart(int|string $line): self
    {
        $this->columnStart = $line;

        return $this;
    }

    public function columnEnd(int|string $line): self
    {
        $this->columnEnd = $line;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function buildProperties(): array
    {
        /** @var array<string, string> $props */
        $props = [];

        if ($this->area !== null) {
            $props = array_merge($props, $this->area->build());
        }

        if ($this->rowStart !== null) {
            $props['grid-row-start'] = is_int($this->rowStart) ? (string) $this->rowStart : $this->rowStart;
        }

        if ($this->rowEnd !== null) {
            $props['grid-row-end'] = is_int($this->rowEnd) ? (string) $this->rowEnd : $this->rowEnd;
        }

        if ($this->columnStart !== null) {
            $props['grid-column-start'] = is_int($this->columnStart) ? (string) $this->columnStart : $this->columnStart;
        }

        if ($this->columnEnd !== null) {
            $props['grid-column-end'] = is_int($this->columnEnd) ? (string) $this->columnEnd : $this->columnEnd;
        }

        $this->buildPlaceSelfProperty($props, $this->alignSelf, $this->justifySelf);

        if ($this->order !== null) {
            $props['order'] = (string) $this->order;
        }

        return $props;
    }
}
