<?php

declare(strict_types=1);

namespace FlexGrid;

use FlexGrid\Enums\ContentAlignment;
use FlexGrid\Enums\FlexDirection;
use FlexGrid\Enums\FlexWrap;
use FlexGrid\Enums\ItemAlignment;

final class FlexBuilder extends AbstractBuilder
{
    private string $display = 'flex';

    private ?FlexDirection $direction = null;

    private ?FlexWrap $wrap = null;

    private ?ItemAlignment $alignItems = null;

    public function inline(): self
    {
        $this->display = 'inline-flex';

        return $this;
    }

    public function direction(FlexDirection $value): self
    {
        $this->direction = $value;

        return $this;
    }

    public function wrap(FlexWrap $value): self
    {
        $this->wrap = $value;

        return $this;
    }

    public function noWrap(): self
    {
        return $this->wrap(FlexWrap::NoWrap);
    }

    public function wrapReverse(): self
    {
        return $this->wrap(FlexWrap::WrapReverse);
    }

    public function flow(FlexDirection $direction, FlexWrap $wrap): self
    {
        $this->direction = $direction;
        $this->wrap      = $wrap;

        return $this;
    }

    /**
     * Sets align-items property for flex items.
     */
    public function alignItems(ItemAlignment $alignment): self
    {
        $this->alignItems = $alignment;

        return $this;
    }

    /**
     * Sets align-content property for multi-line flex containers.
     *
     * Note: This property only works when flex-wrap is set to 'wrap' or 'wrap-reverse'.
     * In single-line flex containers (nowrap), this property has no effect.
     *
     * @see https://www.w3.org/TR/css-flexbox-1/#align-content-property
     */
    public function alignContent(ContentAlignment $alignment): self
    {
        $this->alignContent = $alignment;

        return $this;
    }

    /**
     * Sets justify-content property for flex items distribution.
     *
     * Note: The 'stretch' value behaves as 'start' in Flexbox.
     * Stretching in Flexbox is controlled by the flex-grow property instead.
     *
     * @see https://www.w3.org/TR/css-flexbox-1/#justify-content-property
     */
    public function justifyContent(ContentAlignment $alignment): self
    {
        $this->justifyContent = $alignment;

        return $this;
    }

    public function item(FlexItem $item): self
    {
        $this->pushItem($item);

        return $this;
    }

    /** @param list<FlexItem> $items */
    public function items(array $items): self
    {
        $this->pushItems($items);

        return $this;
    }

    /** @return array<string, string> */
    public function buildProperties(): array
    {
        $props = ['display' => $this->display];

        if ($this->direction !== null && $this->wrap !== null) {
            $props['flex-flow'] = $this->direction->value . ' ' . $this->wrap->value;
        } elseif ($this->direction !== null) {
            $props['flex-direction'] = $this->direction->value;
        } elseif ($this->wrap !== null) {
            $props['flex-wrap'] = $this->wrap->value;
        }

        $this->buildGapProperties($props);

        if ($this->alignItems !== null) {
            $props['align-items'] = $this->alignItems->value;
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
