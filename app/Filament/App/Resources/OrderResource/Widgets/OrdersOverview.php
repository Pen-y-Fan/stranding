<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\OrderResource\Widgets;

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
        $completeOrdersCount = Order::query()
            ->when(
                $this->record instanceof Location,
                // @phpstan-ignore-next-line
                fn (Builder $query) => $query->where('client_id', $this->record->id)
            )
            ->whereHas(
                'deliveries',
                static fn (Builder $query) => $query->whereUserId(auth()->id())
                    ->whereStatus(DeliveryStatus::COMPLETE)
            )->count();

        $deliveriesOnGoingCount = Delivery::query()
            ->whereUserId(auth()->id())
            ->when(
                $this->record instanceof Location,
                fn (Builder $query) => $query->whereHas(
                    'order',
                    // @phpstan-ignore-next-line
                    fn (Builder $query) => $query->whereClientId($this->record->id)
                )
            )
            ->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
            ->count();

        $orderCount = $this->record instanceof Location ? $this->record->clientOrders()->count() : Order::query()->count();

        $orderCompleteTitle = $this->record instanceof Location ? 'Orders completed to ' . $this->record->name : 'Orders complete';

        $deliveriesFromTitle = $this->record instanceof Location ? 'Deliveries from ' . $this->record->name : 'Deliveries';

        $stats = [
            Stat::make($orderCompleteTitle, $completeOrdersCount)
                ->description(
                    sprintf(
                        'Complete %d of %d = %0.1f%%',
                        $completeOrdersCount,
                        $orderCount,
                        $completeOrdersCount / $orderCount * 100
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

            Stat::make($deliveriesFromTitle, $deliveriesOnGoingCount)
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

        if ($this->record instanceof Location) {
            $deliveriesToCount = Delivery::query()
                ->whereUserId(auth()->id())
                ->whereHas(
                    'order',
                    fn (Builder $query) => $query->whereDestinationId($this->record->id)
                )
                ->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
                ->count();

            $stats[] = Stat::make('Deliveries to ' . $this->record->name, $deliveriesToCount)
                ->description('On going deliveries')
                ->extraAttributes([
                    'class'      => 'cursor-pointer',
                    'wire:click' => sprintf(
                        "\$dispatch('setStatusToFilter', { filter: '%s'})",
                        OrderStatus::IN_PROGRESS->value
                    ),
                ])
                ->descriptionIcon('heroicon-m-truck');
        }

        return $stats;
    }
}
