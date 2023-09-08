<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatus: string
{
    case UNAVAILABLE = 'Unavailable';
    case AVAILABLE   = 'Available';
    case ON_STANDBY  = 'On standby';
    case IN_PROGRESS = 'In progress';
    case COMPLETE    = 'Complete';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNAVAILABLE => 'Unavailable',
            self::AVAILABLE   => 'Available',
            self::ON_STANDBY  => 'On standby',
            self::IN_PROGRESS => 'In progress',
            self::COMPLETE    => 'Complete',
        };
    }
}
