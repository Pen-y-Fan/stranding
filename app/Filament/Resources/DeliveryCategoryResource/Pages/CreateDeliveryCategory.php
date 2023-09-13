<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeliveryCategoryResource\Pages;

use App\Filament\Resources\DeliveryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryCategory extends CreateRecord
{
    protected static string $resource = DeliveryCategoryResource::class;
}
