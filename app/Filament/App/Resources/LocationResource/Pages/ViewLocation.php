<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\LocationResource\Pages;

use App\Filament\App\Resources\LocationResource;
use App\Filament\App\Resources\LocationResource\Widgets\LocationOverview;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewLocation extends ViewRecord
{
    protected static string $resource = LocationResource::class;

    public function getTitle(): string | Htmlable
    {
        if (
            is_array($this->data) && array_key_exists(key: 'name', array: $this->data) && is_string($this->data['name'])
        ) {
            return $this->data['name'];
        }
        return 'Location';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LocationOverview::class,
        ];
    }
}
