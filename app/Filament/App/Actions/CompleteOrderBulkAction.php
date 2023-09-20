<?php

declare(strict_types=1);

namespace App\Filament\App\Actions;

use App\Enum\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompleteOrderBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    protected int $successCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Complete orders');

        $this->modalHeading('Complete the orders for delivery');

        $this->modalSubmitActionLabel('Deliver');

        $this->successNotificationTitle(fn () => sprintf(
            '%s %s delivered 🎉',
            $this->successCount,
            Str::plural('order', $this->successCount)
        ));

        $this->color('success');

        $this->icon('heroicon-m-shield-check');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-shield-check');

        $this->action(function (): void {
            $this->process(fn (Collection $records) => $records->each(function (Model $record): void {
                assert($record instanceof Order);
                $this->completeDelivery($record);

                $this->successCount++;
            }));

            $this->success();
        });
        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'Deliver orders';
    }

    protected function completeDelivery(Order $record): void
    {
        $success = Delivery::where([
            'order_id' => $record->id,
            'user_id'  => auth()->id(),
            'ended_at' => null,
        ])
            ->update([
                'ended_at'    => now(),
                'status'      => DeliveryStatus::COMPLETE,
                'location_id' => $record->client_id,
            ]);

        if ($success > 0) {
            return;
        }

        // Allow the user to complete an order even if they forgot to mark it collected
        Delivery::create([
            'order_id'    => $record->id,
            'user_id'     => auth()->id(),
            'started_at'  => now(),
            'ended_at'    => now(),
            'status'      => DeliveryStatus::COMPLETE,
            'location_id' => $record->client_id,
        ]);
    }
}
