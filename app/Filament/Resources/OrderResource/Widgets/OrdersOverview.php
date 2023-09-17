<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enum\DeliveryStatus;
use App\Enum\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class OrdersOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $completeOrdersCount = Order::query()
            ->whereHas(
                'deliveries',
                static fn (Builder $query) => $query->whereUserId(auth()->id())
                    ->whereStatus(DeliveryStatus::COMPLETE)
            )->count();

        $deliveriesOnGoingCount = Delivery::query()
            ->whereUserId(auth()->id())
            ->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
            ->count();

        return [
            Stat::make('Orders complete', $completeOrdersCount)
                ->description(sprintf('Complete %d of 540 = %0.1f%%', $completeOrdersCount, $completeOrdersCount / 540 * 100))
                ->descriptionIcon('heroicon-m-gift')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf("\$dispatch('setStatusFilter', { filter: '%s'})", OrderStatus::COMPLETE->getLabel()),
                ])
                ->color('success'),
            Stat::make('Deliveries', $deliveriesOnGoingCount)
                ->description('On going deliveries')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf("\$dispatch('setStatusFilter', { filter: '%s'})", OrderStatus::IN_PROGRESS->getLabel()),
                ])
                ->descriptionIcon('heroicon-m-truck'),
        ];
    }
}
