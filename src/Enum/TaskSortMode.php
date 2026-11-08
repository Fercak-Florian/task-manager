<?php

namespace App\Enum;
enum TaskSortMode: string
{
    case CREATED_DESC = 'created_desc';
    case CREATED_ASC = 'created_asc';
    case DEADLINE_DESC = 'deadline_desc';
    case DEADLINE_ASC = 'deadline_asc';
    case HIGH_PRIORITY = 'high_desc';
    case LOW_PRIORITY = 'low_desc';
}
