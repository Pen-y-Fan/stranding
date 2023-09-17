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

    public function getLabel(): string
    {
        return match ($this) {
            self::UNAVAILABLE => 'Unavailable',
            self::AVAILABLE   => 'Available',
            self::ON_STANDBY  => 'On standby',
            self::IN_PROGRESS => 'In progress',
            self::COMPLETE    => 'Complete',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function toArrayString(): array
    {
        return [
            self::UNAVAILABLE->getLabel() => self::UNAVAILABLE->getLabel(),
            self::AVAILABLE->getLabel()   => self::AVAILABLE->getLabel(),
            self::ON_STANDBY->getLabel()  => self::ON_STANDBY->getLabel(),
            self::IN_PROGRESS->getLabel() => self::IN_PROGRESS->getLabel(),
            self::COMPLETE->getLabel()    => self::COMPLETE->getLabel(),
        ];
    }

    /**
     * @return array<string, OrderStatus>
     */
    public static function toArrayEnum(): array
    {
        return [
            self::UNAVAILABLE->getLabel() => self::UNAVAILABLE,
            self::AVAILABLE->getLabel()   => self::AVAILABLE,
            self::ON_STANDBY->getLabel()  => self::ON_STANDBY,
            self::IN_PROGRESS->getLabel() => self::IN_PROGRESS,
            self::COMPLETE->getLabel()    => self::COMPLETE,
        ];
    }
}
