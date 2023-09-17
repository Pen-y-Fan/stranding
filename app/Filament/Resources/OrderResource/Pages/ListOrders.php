<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enum\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrdersOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    #[On('setStatusFilter')]
    public function setStatusFilter(string $filter): void
    {
        if (! OrderStatus::tryFrom($filter) instanceof OrderStatus) {
            Log::warning(sprintf('%s is not a valid OrderStatus Enum value', $filter));
            return;
        }

        foreach (OrderStatus::toArrayString() as $item) {
            $this->tableFilters[$item]['isActive'] = $filter === $item;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrdersOverview::class,
        ];
    }
}
