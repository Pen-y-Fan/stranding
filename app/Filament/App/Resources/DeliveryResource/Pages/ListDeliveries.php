<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\DeliveryResource\Pages;

use App\Filament\App\Resources\DeliveryResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveries extends ListRecords
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
