<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enum\DeliveryStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class OrdersDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'Orders';

    protected function getData(): array
    {
        $completeCount = Order::whereHas(
            'deliveries',
            static fn (Builder $query): Builder => $query->where('status', DeliveryStatus::COMPLETE)
                ->where('user_id', auth()->id())
        )->count();

        $incompleteCount = Order::whereDoesntHave(
            'deliveries',
            static fn (Builder $query): Builder => $query->where('status', DeliveryStatus::COMPLETE)
                ->where('user_id', auth()->id())
        )->count();

        $inProgress = Order::whereHas(
            'deliveries',
            static fn (Builder $query): Builder => $query->whereNull('ended_at')
                ->where('user_id', auth()->id())
        )->count();

        return [
            'datasets' => [
                [
                    'data'            => [$completeCount, $inProgress, $incompleteCount - $inProgress],
                    'backgroundColor' => ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                    'borderColor'     => ['rgba(75, 192, 192, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)'],
                ],
            ],
            'labels' => ['Complete', 'In progress', 'Incomplete'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
