<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case AwaitingDeposit = 'awaiting_deposit';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function filterLabel(): string
    {
        return match ($this) {
            self::Draft => 'Drafts',
            self::Pending => 'Awaiting driver',
            self::AwaitingDeposit => 'Awaiting deposit',
            self::Confirmed => 'Confirmed',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
        };
    }
}
