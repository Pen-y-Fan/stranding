<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DeliveryStatus: string implements HasLabel, HasColor, HasIcon
{
    // in progress, failed, complete, stashed
    case IN_PROGRESS = 'In progress';
    case FAILED      = 'Failed';
    case COMPLETE    = 'Complete';
    case STASHED     = 'Stashed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In progress',
            self::FAILED      => 'Failed',
            self::COMPLETE    => 'Complete',
            self::STASHED     => 'Stashed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::IN_PROGRESS => 'info',
            self::FAILED      => 'danger',
            self::COMPLETE    => 'success',
            self::STASHED     => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::IN_PROGRESS => 'heroicon-o-truck',
            self::FAILED      => 'heroicon-o-x-circle',
            self::COMPLETE    => 'heroicon-o-shield-check',
            self::STASHED     => 'heroicon-o-information-circle',
        };
    }

    public static function toArrayEnum(): array
    {
        return [
            self::IN_PROGRESS->getLabel() => self::IN_PROGRESS,
            self::FAILED->getLabel()      => self::FAILED,
            self::COMPLETE->getLabel()    => self::COMPLETE,
            self::STASHED->getLabel()     => self::STASHED,
        ];
    }

    public static function toArrayString(): array
    {
        return [
            self::IN_PROGRESS->getLabel() => self::IN_PROGRESS->getLabel(),
            self::FAILED->getLabel()      => self::FAILED->getLabel(),
            self::COMPLETE->getLabel()    => self::COMPLETE->getLabel(),
            self::STASHED->getLabel()     => self::STASHED->getLabel(),
        ];
    }
}
