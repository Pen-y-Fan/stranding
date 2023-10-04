<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\DeliveryResource\Pages;

use App\Enum\DeliveryStatus;
use App\Filament\App\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDelivery extends EditRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('Complete')
                ->requiresConfirmation()
                ->button()
                ->color('success')
                ->visible(
                    static fn (Delivery $record): bool => $record->status === DeliveryStatus::IN_PROGRESS
                        || $record->status                                === DeliveryStatus::STASHED
                )
                ->action(static fn (Delivery $record) => $record
                    ->update([
                        'ended_at'    => now(),
                        'status'      => DeliveryStatus::COMPLETE,
                        'location_id' => $record->order->client_id,
                    ])),
            Action::make('Fail')
                ->requiresConfirmation()
                ->button()
                ->color('danger')
                ->visible(
                    static fn (Delivery $record): bool => $record->status === DeliveryStatus::IN_PROGRESS
                        || $record->status                                === DeliveryStatus::STASHED
                )
                ->action(static fn (Delivery $record) => $record
                    ->update([
                        'ended_at'    => now(),
                        'status'      => DeliveryStatus::FAILED,
                        'location_id' => $record->order->destination_id,
                    ])),
            Action::make('Lost')
                ->requiresConfirmation()
                ->button()
                ->color('warning')
                ->visible(
                    static fn (Delivery $record): bool => $record->status === DeliveryStatus::IN_PROGRESS
                        || $record->status                                === DeliveryStatus::STASHED
                )
                ->action(static fn (Delivery $record) => $record
                    ->update([
                        'ended_at'    => now(),
                        'status'      => DeliveryStatus::LOST,
                        'location_id' => $record->order->client_id,
                    ])),
        ];
    }
}
