<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Closed => 'Cerrado',
            self::Archived => 'Archivado',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
