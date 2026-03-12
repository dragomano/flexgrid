<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\ContentAlignment;

/**
 * @psalm-consistent-constructor
 * @phpstan-consistent-constructor
 */
abstract class AbstractBuilder
{
    private readonly CssItemList $items;

    private readonly BreakpointVariantList $variants;

    protected ?ContentAlignment $alignContent = null;

    protected ?ContentAlignment $justifyContent = null;

    protected ?string $rowGap = null;

    protected ?string $columnGap = null;

    public function __construct(protected readonly string $selector = '')
    {
        $this->items    = new CssItemList();
        $this->variants = new BreakpointVariantList();
    }

    public static function make(string $selector = ''): static
    {
        return new static($selector);
    }

    /** @return array<string, string> */
    abstract public function buildProperties(): array;

    public function responsive(int $minWidth, callable $configure): self
    {
        return $this->media("{$minWidth}px", $configure);
    }

    public function media(string $query, callable $configure): self
    {
        $variant = static::make($this->selector);

        $configure($variant);

        $this->variants->push(new BreakpointVariant($query, $variant));

        return $this;
    }

    public function alignContent(ContentAlignment $alignment): self
    {
        $this->alignContent = $alignment;

        return $this;
    }

    public function justifyContent(ContentAlignment $alignment): self
    {
        $this->justifyContent = $alignment;

        return $this;
    }

    public function placeContent(ContentAlignment $align, ?ContentAlignment $justify = null): self
    {
        $this->alignContent   = $align;
        $this->justifyContent = $justify ?? $align;

        return $this;
    }

    /**
     * @return $this
     */
    public function gap(string $rowGap, ?string $columnGap = null): self
    {
        $this->rowGap    = $rowGap;
        $this->columnGap = $columnGap ?? $rowGap;

        return $this;
    }

    public function rowGap(string $gap): self
    {
        $this->rowGap = $gap;

        return $this;
    }

    public function columnGap(string $gap): self
    {
        $this->columnGap = $gap;

        return $this;
    }

    public function build(string $indent = '  '): string
    {
        $parts = [];

        $parts[] = $this->buildRule($this->selector, $this->buildProperties(), $indent);

        foreach ($this->items as $item) {
            if ($item->getSelector() === '') {
                continue;
            }

            $parts[] = $item->toCss($indent);
        }

        $groupedVariants = [];

        foreach ($this->variants as $entry) {
            $groupedVariants[$entry->query][] = $entry->variant;
        }

        foreach ($groupedVariants as $query => $variants) {
            $mediaQuery = str_contains($query, '(')
                ? $query
                : "(min-width: $query)";

            $innerParts = [];

            foreach ($variants as $variant) {
                $innerParts[] = $variant->buildRule(
                    $variant->selector,
                    $variant->buildProperties(),
                    $indent . '  '
                );

                foreach ($variant->items as $item) {
                    if ($item->getSelector() === '') {
                        continue;
                    }

                    $innerParts[] = $item->toCss($indent . '  ');
                }
            }

            $inner = implode("\n\n", $innerParts);

            $parts[] = "@media $mediaQuery {\n$inner\n}";
        }

        return implode("\n\n", array_filter($parts));
    }

    public function toInlineStyle(): string
    {
        $parts = [];

        foreach ($this->buildProperties() as $prop => $val) {
            $parts[] = "$prop: $val";
        }

        return implode('; ', $parts);
    }

    protected function pushItem(CssItem $item): void
    {
        $this->items->push($item);
    }

    /** @param list<CssItem> $items */
    protected function pushItems(array $items): void
    {
        foreach ($items as $item) {
            $this->pushItem($item);
        }
    }

    /** @param array<string, string> $props */
    protected function buildRule(string $selector, array $props, string $indent): string
    {
        if (empty($props)) {
            return '';
        }

        $lines = [];

        foreach ($props as $prop => $val) {
            $lines[] = "$indent$prop: $val;";
        }

        $block = implode("\n", $lines);

        return $selector
            ? "$selector {\n$block\n}"
            : $block;
    }

    /**
     * @param array<string, string> $props
     */
    protected function buildPlaceProperty(
        array &$props,
        string $placeName,
        string $alignName,
        string $justifyName,
        ?ContentAlignment $align,
        ?ContentAlignment $justify
    ): void {
        if ($justify !== null && $align !== null) {
            if ($justify === $align) {
                $props[$placeName] = $justify->value;
            } else {
                $props[$placeName] = $align->value . ' ' . $justify->value;
            }
        } elseif ($align !== null) {
            $props[$alignName] = $align->value;
        } elseif ($justify !== null) {
            $props[$justifyName] = $justify->value;
        }
    }

    /**
     * @param array<string, string> $props
     */
    protected function buildGapProperties(array &$props): void
    {
        if ($this->rowGap !== null && $this->columnGap !== null) {
            if ($this->rowGap === $this->columnGap) {
                $props['gap'] = $this->rowGap;
            } else {
                $props['gap'] = "$this->rowGap $this->columnGap";
            }
        } elseif ($this->rowGap !== null) {
            $props['row-gap'] = $this->rowGap;
        } elseif ($this->columnGap !== null) {
            $props['column-gap'] = $this->columnGap;
        }
    }
}
