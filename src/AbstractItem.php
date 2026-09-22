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
    use RendersCssRule;

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

    public function toCss(string $indent = ''): string
    {
        return $this->renderRule($this->selector, $this->buildProperties(), $indent);
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
