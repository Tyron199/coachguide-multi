<?php

namespace App\Enums\Tenant;

enum TaskReminderStatus: int
{
    case PENDING = 0;
    case SENT = 1;
    case FAILED = 2;
}