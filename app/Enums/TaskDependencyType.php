<?php

namespace App\Enums;

enum TaskDependencyType: string
{
    case Blocks = 'blocks';
    case RelatesTo = 'relates_to';

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
