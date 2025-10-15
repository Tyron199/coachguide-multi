<?php

namespace App\Enums\Tenant;

enum CoachingTaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}