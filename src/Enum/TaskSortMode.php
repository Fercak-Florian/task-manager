<?php

namespace App\Enum;
enum TaskSortMode: string
{
    case CREATED_DESC = 'created_desc';
    case CREATED_ASC = 'created_asc';
    case DEADLINE_DESC = 'deadline_desc';
    case DEADLINE_ASC = 'deadline_asc';
    case PRIORITY_DESC = 'priority_desc';
    case PRIORITY_ASC = 'priority_asc';

    public function getField(): string
    {
        return match ($this) {
            self::CREATED_ASC,
            self::CREATED_DESC => 'createdAt',

            self::DEADLINE_ASC,
            self::DEADLINE_DESC => 'deadline',

            self::PRIORITY_ASC,
            self::PRIORITY_DESC => 'priority',
        };
    }

    public function getDirection(): string
    {
        return match ($this) {
            self::CREATED_ASC,
            self::DEADLINE_ASC,
            self::PRIORITY_ASC => 'ASC',

            self::CREATED_DESC,
            self::DEADLINE_DESC,
            self::PRIORITY_DESC => 'DESC',
        };
    }
}
