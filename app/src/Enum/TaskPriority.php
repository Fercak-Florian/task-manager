<?php

namespace App\Enum;
enum TaskPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Basse',
            self::NORMAL => 'Normale',
            self::HIGH => 'Haute',
        };
    }
}
