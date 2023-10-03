<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enum\DeliveryStatus;
use App\Models\District;
use App\Models\Location;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class CompleteOrdersCentralFtoMChart extends ChartWidget
{
    protected static ?string $heading = 'Central region (Film Directory to Mountaineer)';

    protected function getData(): array
    {
        $centralDistrict = District::query()
            ->firstWhere('name', 'Central');

        assert($centralDistrict instanceof District);

        $ordersByLocation = Location::query()
            ->isPhysical()
            ->where('name', '>', 'F')
            ->where('name', '<', 'N')
            ->where('district_id', $centralDistrict->id)
            ->withCount([
                'clientOrders as incomplete_orders_count' => static fn (Builder $query) => $query->whereDoesntHave(
                    'deliveries',
                    static fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE)
                        ->where('user_id', auth()->id())
                ),
                'clientOrders as complete_orders_count' => static fn (Builder $query) => $query->whereHas(
                    'deliveries',
                    static fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE)
                        ->where('user_id', auth()->id())
                ),
            ])
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Delivered',
                    'data'            => $ordersByLocation->map(static fn (Location $location) => $location->complete_orders_count),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor'     => 'rgba(75, 192, 192, 0.7)',
                ],
                [
                    'label'           => 'Incomplete',
                    'data'            => $ordersByLocation->map(static fn (Location $location) => $location->incomplete_orders_count),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor'     => 'rgba(255, 99, 132, 0.7)',
                ],
            ],
            'labels' => $ordersByLocation->map(static fn (Location $location) => $location->name),
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
