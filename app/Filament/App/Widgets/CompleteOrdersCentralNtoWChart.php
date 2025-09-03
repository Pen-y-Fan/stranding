<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enum\DeliveryStatus;
use App\Models\District;
use App\Models\Location;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class CompleteOrdersCentralNtoWChart extends ChartWidget
{
    protected static ?string $heading = "Central region (Novelist's Son to Weather Station)";

    protected function getData(): array
    {
        $centralDistrict = District::query()
            ->firstWhere('name', 'Central');

        assert($centralDistrict instanceof District);

        $ordersByLocation = Location::query()
            ->isPhysical()
            ->where('name', '>', 'N')
            ->where('district_id', $centralDistrict->id)
            ->withCount([
                'clientOrders as incomplete_orders_count' => static fn (Builder $query) => $query->whereDoesntHave(
                    'deliveries',
                    static fn (Builder $query) => $query->whereIn('status', [DeliveryStatus::COMPLETE, DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
                        ->where('user_id', auth()->id())
                ),
                'clientOrders as complete_orders_count' => static fn (Builder $query) => $query->whereHas(
                    'deliveries',
                    static fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE)
                        ->where('user_id', auth()->id())
                ),
                'clientOrders as accepted_orders_count' => static fn (Builder $query) => $query->whereHas(
                    'deliveries',
                    static fn (Builder $query) => $query->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
                        ->where('user_id', auth()->id())
                ),
                'destinationOrders as dest_incomplete_orders_count' => static fn (Builder $query) => $query->whereDoesntHave(
                    'deliveries',
                    static fn (Builder $query) => $query->whereIn('status', [DeliveryStatus::COMPLETE, DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
                        ->where('user_id', auth()->id())
                ),
                'destinationOrders as dest_complete_orders_count' => static fn (Builder $query) => $query->whereHas(
                    'deliveries',
                    static fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE)
                        ->where('user_id', auth()->id())
                ),
                'destinationOrders as dest_accepted_orders_count' => static fn (Builder $query) => $query->whereHas(
                    'deliveries',
                    static fn (Builder $query) => $query->whereIn('status', [DeliveryStatus::STASHED, DeliveryStatus::IN_PROGRESS])
                        ->where('user_id', auth()->id())
                ),
            ])
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Client Delivered',
                    'stack'           => 'Client',
                    'data'            => $ordersByLocation->map(static fn (Location $location): int => $location->complete_orders_count ?? 0),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor'     => 'rgba(75, 192, 192, 0.7)',
                ],
                [
                    'label'           => 'Client In progress',
                    'stack'           => 'Client',
                    'data'            => $ordersByLocation->map(static fn (Location $location): int => $location->accepted_orders_count ?? 0),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor'     => 'rgba(54, 162, 235, 0.7)',
                ],
                [
                    'label'           => 'Client Incomplete',
                    'stack'           => 'Client',
                    'data'            => $ordersByLocation->map(static fn (Location $location): int => $location->incomplete_orders_count ?? 0),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor'     => 'rgba(255, 99, 132, 0.7)',
                ],
                [
                    'label'           => 'Destination Delivered',
                    'stack'           => 'Destination',
                    'data'            => $ordersByLocation->map(static fn (Location $location): int => $location->dest_complete_orders_count ?? 0),
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'borderColor'     => 'rgba(153, 102, 255, 0.7)',
                ],
                [
                    'label'           => 'Destination In progress',
                    'stack'           => 'Destination',
                    'data'            => $ordersByLocation->map(static fn (Location $location): int => $location->dest_accepted_orders_count ?? 0),
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor'     => 'rgba(255, 159, 64, 0.7)',
                ],
                [
                    'label'           => 'Destination Incomplete',
                    'stack'           => 'Destination',
                    'data'            => $ordersByLocation->map(static fn (Location $location): int => $location->dest_incomplete_orders_count ?? 0),
                    'backgroundColor' => 'rgba(201, 203, 207, 0.2)',
                    'borderColor'     => 'rgba(201, 203, 207, 0.7)',
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
