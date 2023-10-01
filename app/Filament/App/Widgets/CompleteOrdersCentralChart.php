<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enum\DeliveryStatus;
use App\Models\District;
use App\Models\Location;
use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class CompleteOrdersCentralChart extends ChartWidget
{
    protected static ?string $heading = 'Central region';

    protected function getData(): array
    {
        $centralDistrict = District::query()
            ->firstWhere('name', 'Central');

        assert($centralDistrict instanceof District);

        $incompleteOrdersByLocation = Location::query()
            ->isPhysical()
            ->where('district_id', $centralDistrict->id)
            ->withCount([
                'clientOrders' => fn (Builder $query) => $query->whereDoesntHave(
                    'deliveries',
                    fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE)
                ),
            ])
            ->get();

        $deliveredOrderByLocation = Location::query()
            ->isPhysical()
            ->where('district_id', $centralDistrict->id)
            ->withCount([
                'clientOrders' => fn (Builder $query) => $query->whereHas(
                    'deliveries',
                    fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE)
                ),
            ])
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Delivered',
                    'data'            => $deliveredOrderByLocation->map(fn (Location $location) => $location->client_orders_count),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor'     => 'rgba(75, 192, 192, 0.7)',
                ],
                [
                    'label'           => 'Incomplete',
                    'data'            => $incompleteOrdersByLocation->map(fn (Location $location) => $location->client_orders_count),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor'     => 'rgba(255, 99, 132, 0.7)',
                ],
            ],
            'labels' => $incompleteOrdersByLocation->map(fn (Location $location) => $location->name),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array | RawJs | null
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
        ];
    }
}
