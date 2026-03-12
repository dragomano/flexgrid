<?php

declare(strict_types=1);

namespace FlexGrid\Enums;

enum GridValue: string
{
    case Auto        = 'auto';
    case MaxContent  = 'max-content';
    case MinContent  = 'min-content';
    case FitContent  = 'fit-content';
    case Dense       = 'dense';
    case Row         = 'row';
    case Column      = 'column';
    case RowDense    = 'row dense';
    case ColumnDense = 'column dense';

    public static function fr(float $n): string
    {
        return "{$n}fr";
    }

    public static function minmax(string $min, string $max): string
    {
        return "minmax($min, $max)";
    }

    public static function repeat(int|string $count, string $track = '1fr'): string
    {
        return "repeat($count, $track)";
    }

    public static function fitContent(string $size): string
    {
        return "fit-content($size)";
    }
}
