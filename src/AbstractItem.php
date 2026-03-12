<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\ItemAlignment;

/**
 * @psalm-consistent-constructor
 * @phpstan-consistent-constructor
 */
abstract class AbstractItem implements CssItem
{
    public function __construct(private readonly string $selector = '') {}

    public static function select(string $selector): static
    {
        return new static($selector);
    }

    /**
     * @return array<string, string>
     */
    abstract public function buildProperties(): array;

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function toCss(string $indent = '  '): string
    {
        /** @var array<string, string> $props */
        $props = $this->buildProperties();

        if ($props === []) {
            return '';
        }

        $keys   = [];
        $values = [];

        foreach ($props as $prop => $val) {
            $keys[]   = $prop;
            $values[] = $val;
        }

        $lines = array_map(
            static fn(string $prop, string $val): string => "$indent$prop: $val;",
            $keys,
            $values
        );

        return $this->selector !== ''
            ? "$this->selector {\n" . implode("\n", $lines) . "\n}"
            : implode("\n", $lines);
    }

    /**
     * @param array<string, string> $props
     */
    protected function appendAlignSelfAndOrder(array &$props, ?ItemAlignment $alignSelf, ?int $order): void
    {
        if ($alignSelf !== null) {
            $props['align-self'] = $alignSelf->value;
        }

        if ($order !== null) {
            $props['order'] = (string) $order;
        }
    }

    /**
     * @param array<string, string> $props
     */
    protected function buildPlaceSelfProperty(
        array &$props,
        ?ItemAlignment $alignSelf,
        ?ItemAlignment $justifySelf
    ): void {
        if ($justifySelf !== null && $alignSelf !== null) {
            if ($justifySelf === $alignSelf) {
                $props['place-self'] = $justifySelf->value;
            } else {
                $props['place-self'] = $alignSelf->value . ' ' . $justifySelf->value;
            }
        } elseif ($justifySelf !== null) {
            $props['justify-self'] = $justifySelf->value;
        } elseif ($alignSelf !== null) {
            $props['align-self'] = $alignSelf->value;
        }
    }
}
