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

        $this->label('Make delivery');

        $this->modalHeading('Deliver requested cargo');

        $this->modalSubmitActionLabel('Deliver');

        $this->successNotificationTitle(fn (): string => sprintf(
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
                $this->makeDelivery($record);

                ++$this->successCount;
            }));

            $this->success();
        });
        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'Make delivery';
    }

    protected function makeDelivery(Order $order): void
    {
        $success = Delivery::where([
            'order_id' => $order->id,
            'user_id'  => auth()->id(),
            'ended_at' => null,
        ])
            ->update([
                'ended_at'    => now('Europe/London'),
                'status'      => DeliveryStatus::COMPLETE,
                'location_id' => $order->client_id,
            ]);

        if ($success > 0) {
            return;
        }

        // For bulk orders: allow the user to complete an order even if they forgot to mark it collected
        Delivery::create([
            'order_id'    => $order->id,
            'user_id'     => auth()->id(),
            'started_at'  => now('Europe/London'),
            'ended_at'    => now('Europe/London'),
            'status'      => DeliveryStatus::COMPLETE,
            'location_id' => $order->client_id,
        ]);
    }
}
