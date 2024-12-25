<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\LocationResource\Widgets;

use App\Enum\DeliveryStatus;
use App\Enum\OrderStatus;
use App\Models\Delivery;
use App\Models\Location;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class LocationOverview extends BaseWidget
{
    public string|int|null|Model $record = null;

    protected static ?string $pollingInterval = '30s';

    #[On('deliveryCreated'), On('deliveryUpdated')]
    public function updateWidget(): void
    {
        // widget will automatically update
    }

    protected function getStats(): array
    {
        if (! $this->record instanceof Location) {
            abort(403);
        }
        $completeOrdersFromCount = Order::query()
            ->where('client_id', $this->record->id)
            ->whereHas(
                'deliveries',
                static fn (Builder $query) => $query->whereUserId(auth()->id())
                    ->whereStatus(DeliveryStatus::COMPLETE)
            )->count();

        $completeOrdersToCount = Order::query()
            ->where('destination_id', $this->record->id)
            ->whereHas(
                'deliveries',
                static fn (Builder $query) => $query->whereUserId(auth()->id())
                    ->whereStatus(DeliveryStatus::COMPLETE)
            )->count();

        $deliveriesOnGoingFromCount = Delivery::query()
            ->whereUserId(auth()->id())
            ->whereHas(
                'order',
                fn (Builder $query) => $query->whereClientId($this->record->id)
            )

            ->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
            ->count();

        $orderToCount = $this->record->destinationOrders()->count();

        $orderFromCount = $this->record->clientOrders()->count();

        $deliveriesToCount = Delivery::query()
            ->whereUserId(auth()->id())
            ->whereHas(
                'order',
                fn (Builder $query) => $query->whereDestinationId($this->record->id)
            )
            ->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
            ->count();

        $orderCompleteToTitle   = 'Orders completed to ' . $this->record->name;
        $orderCompleteFromTitle = 'Orders completed from ' . $this->record->name;
        $deliveriesFromTitle    = 'Deliveries from ' . $this->record->name;
        $deliveriesToTitle      = 'Deliveries to ' . $this->record->name;

        return [
            Stat::make($orderCompleteFromTitle, $completeOrdersFromCount)
                ->description(
                    sprintf(
                        'Complete %d of %d = %0.1f%%',
                        $completeOrdersFromCount,
                        $orderFromCount,
                        $completeOrdersFromCount / $orderFromCount * 100
                    )
                )
                ->descriptionIcon('heroicon-m-gift')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf(
                        "\$dispatch('setStatusFilter', { filter: '%s'})",
                        OrderStatus::COMPLETE->value
                    ),
                ])
                ->color('success'),

            Stat::make($orderCompleteToTitle, $completeOrdersToCount)
                ->description(
                    sprintf(
                        'Complete %d of %d = %0.1f%%',
                        $completeOrdersToCount,
                        $orderToCount,
                        $completeOrdersToCount / $orderToCount * 100
                    )
                )
                ->descriptionIcon('heroicon-m-gift')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf(
                        "\$dispatch('setStatusFilter', { filter: '%s'})",
                        OrderStatus::COMPLETE->value
                    ),
                ])
                ->color('success'),

            Stat::make($deliveriesFromTitle, $deliveriesOnGoingFromCount)
                ->description('On going deliveries')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf(
                        "\$dispatch('setStatusFilter', { filter: '%s'})",
                        OrderStatus::IN_PROGRESS->value
                    ),
                ])
                ->descriptionIcon('heroicon-m-truck'),

            Stat::make($deliveriesToTitle, $deliveriesToCount)
                ->description('On going deliveries')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf(
                        "\$dispatch('setStatusToFilter', { filter: '%s'})",
                        OrderStatus::IN_PROGRESS->value
                    ),
                ])
                ->descriptionIcon('heroicon-m-truck'),
        ];
    }
}
