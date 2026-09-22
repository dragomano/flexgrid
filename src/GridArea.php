<?php

declare(strict_types=1);

namespace FlexGrid;

use InvalidArgumentException;

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
        $this->assertLineAllowed();

        $this->rowStart = $line;

        return $this;
    }

    public function rowEnd(int|string $line): self
    {
        $this->assertLineAllowed();

        $this->rowEnd = $line;
        $this->rowSpan = null;

        return $this;
    }

    public function columnStart(int|string $line): self
    {
        $this->assertLineAllowed();

        $this->columnStart = $line;

        return $this;
    }

    public function columnEnd(int|string $line): self
    {
        $this->assertLineAllowed();

        $this->columnEnd = $line;
        $this->columnSpan = null;

        return $this;
    }

    public function spanRows(int $span): self
    {
        $this->assertLineAllowed();
        $this->assertPositiveSpan($span);

        $this->rowSpan = $span;
        $this->rowEnd  = null;

        return $this;
    }

    public function spanColumns(int $span): self
    {
        $this->assertLineAllowed();
        $this->assertPositiveSpan($span);

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

        $row = $this->buildAxis($this->rowStart, $this->rowEnd, $this->rowSpan);

        if ($row !== null) {
            $properties['grid-row'] = $row;
        }

        $column = $this->buildAxis($this->columnStart, $this->columnEnd, $this->columnSpan);

        if ($column !== null) {
            $properties['grid-column'] = $column;
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

    private function buildAxis(int|string|null $start, int|string|null $end, ?int $span): ?string
    {
        if ($start !== null) {
            $endValue = match (true) {
                $end  !== null => "$end",
                $span !== null => "span $span",
                default        => 'auto',
            };

            return "$start / $endValue";
        }

        if ($span !== null) {
            return "span $span";
        }

        if ($end !== null) {
            return "auto / $end";
        }

        return null;
    }

    private function assertLineAllowed(): void
    {
        if ($this->name !== null) {
            throw new InvalidArgumentException(
                'Cannot combine a named grid area with line-based placement.'
            );
        }
    }

    private function assertPositiveSpan(int $span): void
    {
        if ($span < 1) {
            throw new InvalidArgumentException('Span must be a positive integer.');
        }
    }
}
