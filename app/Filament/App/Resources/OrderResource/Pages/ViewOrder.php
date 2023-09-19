<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\OrderResource\Pages;

use App\Enum\DeliveryStatus;
use App\Filament\App\Resources\OrderResource;
use App\Models\Delivery;
use App\Models\Location;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

//use Filament\Tables\Actions\Action;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('Start delivery')
                ->requiresConfirmation()
                ->button()
                ->color('info')
                ->visible(
                    static fn (Order $record) => ! Delivery::query()
                        ->whereIn(
                            'status',
                            [DeliveryStatus::IN_PROGRESS->getLabel(), DeliveryStatus::STASHED->getLabel()]
                        )
                        ->whereUserId(auth()->id())
                        ->whereOrderId($record->id)
                        ->exists()
                )
                ->action(function (Order $record) {
                    Delivery::create([
                        'order_id'    => $record->id,
                        'user_id'     => auth()->id(),
                        'started_at'  => now(),
                        'ended_at'    => null,
                        'status'      => DeliveryStatus::IN_PROGRESS,
                        'location_id' => $record->client->district->name === 'Central' ? Location::whereName('In progress (Central)')->get('id')->firstOrFail()->id
                            : Location::whereName('In progress (West)')->get('id')->firstOrFail()->id,
                    ]);

                    Notification::make()
                        ->title('Delivery started')
                        ->success()
                        ->send();
                }),
            Action::make('Complete delivery')
                ->requiresConfirmation()
                ->button()
                ->color('success')
                ->visible(
                    static fn (Order $record) => Delivery::query()
                        ->whereIn(
                            'status',
                            [DeliveryStatus::IN_PROGRESS->getLabel(), DeliveryStatus::STASHED->getLabel()]
                        )
                        ->whereUserId(auth()->id())
                        ->whereOrderId($record->id)
                        ->exists()
                )
                ->action(fn (Order $record) => Delivery::where([
                    'order_id' => $record->id,
                    'user_id'  => auth()->id(),
                    'ended_at' => null,
                ])
                    ->update([
                        'ended_at'    => now(),
                        'status'      => DeliveryStatus::COMPLETE,
                        'location_id' => $record->client_id,
                    ])),
            Action::make('Fail delivery')
                ->requiresConfirmation()
                ->button()
                ->color('danger')
                ->visible(
                    static fn (Order $record) => Delivery::query()
                        ->whereIn(
                            'status',
                            [DeliveryStatus::IN_PROGRESS->getLabel(), DeliveryStatus::STASHED->getLabel()]
                        )
                        ->whereUserId(auth()->id())
                        ->whereOrderId($record->id)
                        ->exists()
                )
                ->action(fn (Order $record) => Delivery::where([
                    'order_id' => $record->id,
                    'user_id'  => auth()->id(),
                    'ended_at' => null,
                ])
                    ->update([
                        'ended_at'    => now(),
                        'status'      => DeliveryStatus::FAILED,
                        'location_id' => $record->client_id,
                    ])),
            Action::make('Lost delivery')
                ->requiresConfirmation()
                ->button()
                ->color('warning')
                ->visible(
                    static fn (Order $record) => Delivery::query()
                        ->whereIn(
                            'status',
                            [DeliveryStatus::IN_PROGRESS->getLabel(), DeliveryStatus::STASHED->getLabel()]
                        )
                        ->whereUserId(auth()->id())
                        ->whereOrderId($record->id)
                        ->exists()
                )
                ->action(fn (Order $record) => Delivery::where([
                    'order_id' => $record->id,
                    'user_id'  => auth()->id(),
                    'ended_at' => null,
                ])
                    ->update([
                        'ended_at'    => now(),
                        'status'      => DeliveryStatus::LOST,
                        'location_id' => $record->client_id,
                    ])),
        ];
    }
}
