<?php

declare(strict_types=1);

namespace App\Filament\App\Actions;

use App\Enum\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Location;
use App\Models\Order;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AcceptOrderBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    protected int $acceptCount = 0;

    protected int $failCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Accept');

        $this->modalHeading('Take on standard delivery orders');

        $this->modalSubmitActionLabel('Accept');

        $this->successNotificationTitle(fn (): string => sprintf(
            '%s %s assigned',
            $this->acceptCount,
            Str::plural('order', $this->acceptCount)
        ));
        $this->failureNotificationTitle(
            fn (): string => sprintf(
                '%s %s already assigned',
                $this->failCount,
                Str::plural('order', $this->failCount)
            )
        );

        $this->color('info');

        $this->icon('heroicon-m-truck');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-truck');

        $this->action(function (): void {
            $this->process(fn (Collection $records) => $records->each(function (Model $record): void {
                assert($record instanceof Order);
                if ($this->hasExisingOrderForDelivery($record)) {
                    ++$this->failCount;
                    return;
                }

                $this->takeOrder($record);
                ++$this->acceptCount;
            }));

            if ($this->acceptCount > 0) {
                $this->success();
            }

            if ($this->failCount > 0) {
                $this->failure();
            }
        });

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): ?string
    {
        return 'Accept';
    }

    protected function takeOrder(Order $order): void
    {
        Delivery::create([
            'order_id'    => $order->id,
            'user_id'     => auth()->id(),
            'started_at'  => now('Europe/London'),
            'ended_at'    => null,
            'status'      => DeliveryStatus::IN_PROGRESS,
            'location_id' => Location::whereDistrictId($order->client->district_id)
                ->where('name', 'like', 'In progress%')
                ->firstOrFail('id')->id,
        ]);
    }

    protected function hasExisingOrderForDelivery(Order $order): bool
    {
        return Delivery::query()
            ->whereIn('status', [DeliveryStatus::IN_PROGRESS->getLabel(), DeliveryStatus::STASHED->getLabel()])
            ->whereUserId(auth()->id())
            ->whereOrderId($order->id)
            ->exists();
    }
}
