<?php

declare(strict_types=1);

namespace FlexGrid;

/**
 * Represents a CSS grid-area placement for an item.
 * Supports both named areas and line-based placement.
 */
final class GridArea
{
    private ?string $name = null;

    private int|string|null $rowStart = null;

    private int|string|null $rowEnd = null;

    private int|string|null $columnStart = null;

    private int|string|null $columnEnd = null;

    private ?int $rowSpan = null;

    private ?int $columnSpan = null;

    public static function named(string $name): self
    {
        $area = new self();
        $area->name = $name;

        return $area;
    }

    public static function at(int $rowStart, int $columnStart): self
    {
        $area = new self();
        $area->rowStart    = $rowStart;
        $area->columnStart = $columnStart;

        return $area;
    }

    public static function atRow(string $rowLine): self
    {
        $area = new self();
        $area->rowStart = $rowLine;

        return $area;
    }

    public static function atColumn(string $columnLine): self
    {
        $area = new self();
        $area->columnStart = $columnLine;

        return $area;
    }

    public function rowStart(int|string $line): self
    {
        $this->rowStart = $line;

        return $this;
    }

    public function rowEnd(int|string $line): self
    {
        $this->rowEnd = $line;
        $this->rowSpan = null;

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
        $this->columnSpan = null;

        return $this;
    }

    public function spanRows(int $span): self
    {
        $this->rowSpan = $span;
        $this->rowEnd  = null;

        return $this;
    }

    public function spanColumns(int $span): self
    {
        $this->columnSpan = $span;
        $this->columnEnd  = null;

        return $this;
    }

    /**
     * Builds the CSS properties for this area.
     *
     * @return array<string, string>
     */
    public function build(): array
    {
        if ($this->name !== null) {
            return ['grid-area' => $this->name];
        }

        $properties = [];

        if ($this->rowStart !== null) {
            $rowEndValue = match (true) {
                $this->rowEnd  !== null => "$this->rowEnd",
                $this->rowSpan !== null => "span $this->rowSpan",
                default                 => 'auto',
            };

            $properties['grid-row'] = "$this->rowStart / $rowEndValue";
        }

        if ($this->columnStart !== null) {
            $colEndValue = match (true) {
                $this->columnEnd  !== null => "$this->columnEnd",
                $this->columnSpan !== null => "span $this->columnSpan",
                default                    => 'auto',
            };

            $properties['grid-column'] = "$this->columnStart / $colEndValue";
        }

        return $properties;
    }

    public function toCss(): string
    {
        return implode('; ', array_map(
            fn($prop, $val): string => "$prop: $val",
            array_keys($this->build()),
            $this->build()
        ));
    }
}
