<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\OrderResource\Widgets;

use App\Enum\DeliveryStatus;
use App\Enum\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class OrdersOverview extends BaseWidget
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
        $completeOrdersFromCount = Order::query()
            ->whereHas(
                'deliveries',
                static fn (Builder $query) => $query->whereUserId(auth()->id())
                    ->whereStatus(DeliveryStatus::COMPLETE)
            )->count();

        $deliveriesOnGoingFromCount = Delivery::query()
            ->whereUserId(auth()->id())
            ->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
            ->count();

        $orderCount = Order::query()->count();

        return [
            Stat::make('Orders complete', $completeOrdersFromCount)
                ->description(
                    sprintf(
                        'Complete %d of %d = %0.1f%%',
                        $completeOrdersFromCount,
                        $orderCount,
                        $completeOrdersFromCount / $orderCount * 100
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

            Stat::make('Deliveries', $deliveriesOnGoingFromCount)
                ->description('On going deliveries')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf(
                        "\$dispatch('setStatusFilter', { filter: '%s'})",
                        OrderStatus::IN_PROGRESS->value
                    ),
                ])
                ->descriptionIcon('heroicon-m-truck'),
        ];
    }
}
