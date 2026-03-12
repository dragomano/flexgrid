<?php

declare(strict_types=1);

namespace FlexGrid\Enums;

enum FlexDirection: string
{
    case Row            = 'row';
    case RowReverse     = 'row-reverse';
    case Column         = 'column';
    case ColumnReverse  = 'column-reverse';
}
