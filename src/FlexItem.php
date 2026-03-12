<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\ItemAlignment;

/**
 * Represents a single flex item (child element) with sizing and self-alignment.
 *
 * Note: The justify-self property does not exist in Flexbox specification.
 * Use margin: auto or justify-content on the container instead.
 *
 * @see https://www.w3.org/TR/css-flexbox-1/
 */
final class FlexItem extends AbstractItem
{
    private ?string $flex = null;

    private int|float|null $grow = null;

    private int|float|null $shrink = null;

    private ?string $basis = null;

    private ?int $order = null;

    private ?ItemAlignment $alignSelf = null;

    /**
     * Set flex-grow.
     */
    public function grow(int|float $value): self
    {
        $this->flex = null;
        $this->grow = $value;

        return $this;
    }

    /**
     * Set flex-shrink.
     */
    public function shrink(int|float $value): self
    {
        $this->flex   = null;
        $this->shrink = $value;

        return $this;
    }

    /**
     * Set flex-basis.
     */
    public function basis(string $value): self
    {
        $this->flex  = null;
        $this->basis = $value;

        return $this;
    }

    /**
     * Set flex shorthand: grow shrink basis.
     */
    public function flex(int|float $grow, int|float $shrink, string $basis): self
    {
        $this->flex   = "$grow $shrink $basis";
        $this->grow   = null;
        $this->shrink = null;
        $this->basis  = null;

        return $this;
    }

    /**
     * Set align-self for this item.
     */
    public function alignSelf(ItemAlignment $alignment): self
    {
        $this->alignSelf = $alignment;

        return $this;
    }

    /**
     * Set order among siblings.
     */
    public function order(int $value): self
    {
        $this->order = $value;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function buildProperties(): array
    {
        /** @var array<string, string> $props */
        $props = [];

        if ($this->flex !== null) {
            $props['flex'] = $this->flex;
        } else {
            if ($this->grow !== null) {
                $props['flex-grow'] = (string) $this->grow;
            }

            if ($this->shrink !== null) {
                $props['flex-shrink'] = (string) $this->shrink;
            }

            if ($this->basis !== null) {
                $props['flex-basis'] = $this->basis;
            }
        }

        $this->appendAlignSelfAndOrder($props, $this->alignSelf, $this->order);

        return $props;
    }
}
