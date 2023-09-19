<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\DeliveryResource\Pages;

use App\Filament\App\Resources\DeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;
}
