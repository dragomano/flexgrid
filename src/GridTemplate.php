<?php

declare(strict_types=1);

namespace FlexGrid;

use InvalidArgumentException;

/**
 * Fluent builder for CSS grid-template-areas.
 *
 * Usage:
 *   GridTemplate::create()
 *       ->row(['header', 'header'])
 *       ->row(['sidebar', 'main'])
 *       ->row(['footer', 'footer'])
 *       ->build();
 */
final class GridTemplate
{
    /** @var list<list<string>> */
    private array $rows = [];

    public static function create(): self
    {
        return new self();
    }

    /**
     * Add a row of area names.
     *
     * @param list<string> $areas
     */
    public function row(array $areas): self
    {
        if ($areas === []) {
            throw new InvalidArgumentException('Grid template row must not be empty.');
        }

        foreach ($areas as $cell) {
            $this->assertValidCell($cell);
        }

        if ($this->rows !== [] && count($areas) !== count($this->rows[0])) {
            throw new InvalidArgumentException('All grid template rows must have the same number of columns.');
        }

        $this->rows[] = $areas;

        return $this;
    }

    /**
     * Add multiple rows at once.
     *
     * @param list<list<string>> $rows
     */
    public function rows(array $rows): self
    {
        foreach ($rows as $row) {
            $this->row($row);
        }

        return $this;
    }

    /**
     * Returns the CSS value for grid-template-areas.
     */
    public function build(): string
    {
        $values = array_map(
            fn(array $row): string => '"' . implode(' ', $row) . '"',
            $this->rows
        );

        if (count($values) <= 1) {
            return $values[0] ?? '';
        }

        return "\n    " . implode("\n    ", $values);
    }

    /**
     * Returns unique named areas across all rows.
     *
     * @return list<string>
     */
    public function getAreaNames(): array
    {
        $names = [];

        foreach ($this->rows as $row) {
            foreach ($row as $cell) {
                if ($cell !== '.' && ! in_array($cell, $names, true)) {
                    $names[] = $cell;
                }
            }
        }

        return $names;
    }

    /**
     * Infers the number of columns from the first row.
     */
    public function columnCount(): int
    {
        return count($this->rows[0] ?? []);
    }

    /**
     * Infers the number of rows.
     */
    public function rowCount(): int
    {
        return count($this->rows);
    }

    private function assertValidCell(string $cell): void
    {
        if ($cell === '') {
            throw new InvalidArgumentException('Grid area cell must be a non-empty string.');
        }

        if (strpbrk($cell, " \t\n\r\f\v") !== false || str_contains($cell, '"')) {
            throw new InvalidArgumentException("Invalid grid area name: \"$cell\".");
        }
    }
}
