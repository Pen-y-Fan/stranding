<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeliveryCategoryResource\Pages;

use App\Filament\Resources\DeliveryCategoryResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryCategories extends ListRecords
{
    protected static string $resource = DeliveryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
