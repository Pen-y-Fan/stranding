<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeliveryCategoryResource\Pages;

use App\Filament\Resources\DeliveryCategoryResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryCategory extends EditRecord
{
    protected static string $resource = DeliveryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
