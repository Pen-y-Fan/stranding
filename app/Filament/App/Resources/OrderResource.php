<?php

declare(strict_types=1);

namespace App\Filament\App\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\App\Actions\AcceptOrderBulkAction;
use App\Filament\App\Actions\CompleteOrderBulkAction;
use App\Filament\App\Resources\LocationResource\RelationManagers\ClientOrdersRelationManager;
use App\Filament\App\Resources\LocationResource\RelationManagers\DestinationOrdersRelationManager;
use App\Filament\App\Resources\OrderResource\Pages\EditOrder;
use App\Filament\App\Resources\OrderResource\Pages\ListOrders;
use App\Filament\App\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\App\Resources\OrderResource\Widgets\OrdersOverview;
use App\Models\Delivery;
use App\Models\DeliveryCategory;
use App\Models\Location;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->numeric(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required(),
                Select::make('destination_id')
                    ->relationship('destination', 'name')
                    ->required(),
                Select::make('delivery_category_id')
                    ->relationship('deliveryCategory', 'name')
                    ->required(),
                TextInput::make('max_likes')
                    ->required()
                    ->numeric(),
                TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->size(TextColumn\TextColumnSize::Large)
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('max_likes')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('client.name')
                    ->visibleOn([ListOrders::class, DestinationOrdersRelationManager::class])
                    ->url(static fn (Order $record): string => LocationResource::getUrl('view', [
                        'record' => $record->client_id,
                    ]))
                    ->wrap()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('destination.name')
                    ->visibleOn([ListOrders::class, ClientOrdersRelationManager::class])
                    ->url(static fn (Order $record): string => LocationResource::getUrl('view', [
                        'record' => $record->destination_id,
                    ]))
                    ->wrap()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('destination.district.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('deliveryCategory.name')
                    ->wrap()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('userDeliveries.status')
                    ->label('Deliveries')
                    ->badge()
                    ->default('None')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // district
                SelectFilter::make('districts')
                    ->relationship('districts', 'name', static fn (Builder $query) => $query->whereHas('locations'))
                    ->visibleOn(ListOrders::class)
                    ->label('District'),
                // client
                SelectFilter::make('client_id')
                    ->label('Client')
                    ->visibleOn([ListOrders::class, DestinationOrdersRelationManager::class])
                    ->options(
                        Location::query()
                            ->orderBy('name')
                            ->isPhysical()
                            ->pluck('name', 'id')
                    ),
                // destination
                SelectFilter::make('destination_id')
                    ->label('Destination')
                    ->visibleOn([ListOrders::class, ClientOrdersRelationManager::class])
                    ->options(
                        Location::query()
                            ->orderBy('name')
                            ->isPhysical()
                            ->pluck('name', 'id')
                    ),
                // delivery_category
                SelectFilter::make('delivery_category_id')
                    ->label('Delivery category')
                    ->options(
                        DeliveryCategory::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    ),
                // delivery status
                SelectFilter::make('deliveries')
                    ->label('Delivery status')
                    ->options(
                        DeliveryStatus::class
                    )
                    ->query(static fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['value'],
                            static fn (Builder $query, $value): Builder => $query->whereHas(
                                'userDeliveries',
                                static fn (Builder $query) => $query->where('status', $value)
                            )
                        )),
                // completion status
                SelectFilter::make('completion')
                    ->label('Completion status')
                    ->options([
                        'complete'   => 'Complete',
                        'incomplete' => 'Incomplete',
                    ])
                    ->query(static fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['value'],
                            static fn (Builder $query, $value): Builder => $value === 'complete'
                            ?
                            $query->whereHas(
                                'userDeliveries',
                                static fn (Builder $query): Builder => $query->where('status', DeliveryStatus::COMPLETE)
                            )
                                :
                                $query->whereDoesntHave(
                                    'userDeliveries',
                                    static fn (Builder $query): Builder => $query->where('status', DeliveryStatus::COMPLETE)
                                )
                        )),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Action::make('Accept')
//                    ->requiresConfirmation()
                    ->button()
                    ->color('info')
                    ->form([
                        Textarea::make('comment')
                            ->maxLength(65_535)
                            ->columnSpanFull(),
                    ])
                    ->visible(
                        static fn (Order $record): bool => ! Delivery::query()
                            ->whereIn(
                                'status',
                                [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                            )
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->action(static function (array $data, Order $record): void {
                        Delivery::create([
                            'order_id'    => $record->id,
                            'user_id'     => auth()->id(),
                            'started_at'  => now('Europe/London'),
                            'ended_at'    => null,
                            'status'      => DeliveryStatus::IN_PROGRESS,
                            'location_id' => Location::whereDistrictId($record->client->district_id)
                                ->where('name', 'like', 'In progress%')
                                ->firstOrFail('id')->id,
                            'comment' => $data['comment'],
                        ]);
                        Notification::make()
                            ->title('Standard delivery order taken')
                            ->success()
                            ->send();
                    }),
                Action::make('Stash')
                    ->requiresConfirmation()
                    ->button()
                    ->color('warning')
                    ->visible(
                        static fn (Order $record) => Delivery::query()
                            ->where('status', DeliveryStatus::IN_PROGRESS->value)
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->form(
                        static function (array $data, Order $record): array {
                            $delivery = Delivery::query()
                                ->where('status', DeliveryStatus::IN_PROGRESS->value)
                                ->whereUserId(auth()->id())
                                ->whereOrderId($record->id)
                                ->first();

                            return [
                                Select::make('location_id')
                                    ->relationship('client', 'name')
                                    ->required(),
                                Textarea::make('comment')
                                    ->maxLength(65_535)
                                    ->default($delivery->comment ?? '')
                                    ->columnSpanFull(),
                            ];
                        }
                    )
                    ->action(static function (array $data, Order $record): void {
                        Delivery::where([
                            'order_id' => $record->id,
                            'status'   => DeliveryStatus::IN_PROGRESS->value,
                            'user_id'  => auth()->id(),
                            'ended_at' => null,
                        ])
                            ->update([
                                'status'      => DeliveryStatus::STASHED,
                                'location_id' => $data['location_id'],
                                'comment'     => $data['comment'],
                            ]);
                        Notification::make()
                            ->title('cargo delivered')
                            ->success()
                            ->send();
                    }),
                Action::make('Continue')
                    ->button()
                    ->color('info')
                    ->visible(
                        static fn (array $data, Order $record) => Delivery::query()
                            ->where(
                                'status',
                                DeliveryStatus::STASHED->value
                            )
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->form(
                        static function (array $data, Order $record): array {
                            $delivery = Delivery::query()
                                ->where('status', DeliveryStatus::STASHED->value)
                                ->whereUserId(auth()->id())
                                ->whereOrderId($record->id)
                                ->first();

                            return [
                                Textarea::make('comment')
                                    ->maxLength(65_535)
                                    ->default($delivery->comment ?? '')
                                    ->columnSpanFull(),
                            ];
                        }
                    )
                    ->action(static function (array $data, Order $record): void {
                        Delivery::where([
                            'order_id' => $record->id,
                            'status'   => DeliveryStatus::STASHED->value,
                            'user_id'  => auth()->id(),
                            'ended_at' => null,
                        ])
                            ->update([
                                'status'      => DeliveryStatus::IN_PROGRESS,
                                'location_id' => Location::whereDistrictId($record->client->district_id)
                                    ->where('name', 'like', 'In progress%')
                                    ->firstOrFail('id')->id,
                                'comment' => $data['comment'],
                            ]);
                        Notification::make()
                            ->title('order delivered')
                            ->success()
                            ->send();
                    }),
                Action::make('Fail')
                    ->requiresConfirmation()
                    ->button()
                    ->color('danger')
                    ->visible(
                        static fn (Order $record) => Delivery::query()
                            ->whereIn(
                                'status',
                                [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                            )
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->form(
                        static function (array $data, Order $record): array {
                            $delivery = Delivery::query()
                                ->whereIn(
                                    'status',
                                    [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                                )
                                ->whereUserId(auth()->id())
                                ->whereOrderId($record->id)
                                ->first();

                            return [
                                Textarea::make('comment')
                                    ->maxLength(65_535)
                                    ->default($delivery->comment ?? '')
                                    ->columnSpanFull(),
                            ];
                        }
                    )

                    ->action(static function (array $data, Order $record): void {
                        Delivery::where([
                            'order_id' => $record->id,
                            'user_id'  => auth()->id(),
                            'ended_at' => null,
                        ])->whereIn(
                            'status',
                            [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                        )
                            ->update([
                                'ended_at'    => now('Europe/London'),
                                'status'      => DeliveryStatus::FAILED,
                                'location_id' => $record->client_id,
                                'comment'     => $data['comment'],
                            ]);
                        Notification::make()
                            ->title('order failed')
                            ->success()
                            ->send();
                    }),
                Action::make('Deliver')
                    ->form(
                        static function (array $data, Order $record): array {
                            $delivery = Delivery::query()
                                ->whereIn(
                                    'status',
                                    [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                                )
                                ->whereUserId(auth()->id())
                                ->whereOrderId($record->id)
                                ->first();

                            return [
                                Textarea::make('comment')
                                    ->maxLength(65_535)
                                    ->default($delivery->comment ?? '')
                                    ->columnSpanFull(),
                            ];
                        }
                    )
                    ->button()
                    ->color('success')
                    ->visible(
                        static fn (Order $record) => Delivery::query()
                            ->whereIn(
                                'status',
                                [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                            )
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->action(static function (array $data, Order $record): void {
                        Delivery::where([
                            'order_id' => $record->id,
                            'user_id'  => auth()->id(),
                            'ended_at' => null,
                        ])->whereIn(
                            'status',
                            [DeliveryStatus::IN_PROGRESS->value, DeliveryStatus::STASHED->value]
                        )
                            ->update([
                                'ended_at'    => now('Europe/London'),
                                'status'      => DeliveryStatus::COMPLETE,
                                'location_id' => $record->client_id,
                                'comment'     => $data['comment'],
                            ]);
                        Notification::make()
                            ->title('order delivered')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                AcceptOrderBulkAction::make(),
                CompleteOrderBulkAction::make(),
            ])
            ->emptyStateActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view'  => ViewOrder::route('/{record}'),
            'edit'  => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrdersOverview::class,
        ];
    }
}
