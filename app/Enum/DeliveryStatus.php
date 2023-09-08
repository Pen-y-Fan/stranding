<?php

declare(strict_types=1);

namespace App\Enum;

enum DeliveryStatus: string
{
    // in progress, failed, complete, stashed
    case IN_PROGRESS = 'In progress';
    case FAILED      = 'Failed';
    case COMPLETE    = 'Complete';
    case STASHED     = 'Stashed';
    case LOST        = 'Lost';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In progress',
            self::FAILED      => 'Failed',
            self::COMPLETE    => 'Complete',
            self::STASHED     => 'Stashed',
            self::LOST        => 'Lost',
        };
    }
}
