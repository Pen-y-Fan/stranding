<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeliveryCategoryResource\Pages;

use App\Filament\Resources\DeliveryCategoryResource;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryCategory extends ViewRecord
{
    protected static string $resource = DeliveryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
