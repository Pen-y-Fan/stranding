<?php

declare(strict_types=1);

namespace App\Filament\App\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\App\Actions\AcceptOrderBulkAction;
use App\Filament\App\Actions\CompleteOrderBulkAction;
use App\Filament\App\Resources\OrderResource\Pages\CreateOrder;
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
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                    ->wrap()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('destination.name')
                    ->wrap()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('destination.district.name')
                    ->toggleable()
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
                // District
                SelectFilter::make('districts')
                    ->relationship('districts', 'name', static fn (Builder $query) => $query->whereHas('locations'))
                    ->label('District'),

                // client
                SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(
                        Location::query()
                            ->orderBy('name')
                            ->isPhysical()
                            ->pluck('name', 'id')
                    ),
                // destination
                SelectFilter::make('destination_id')
                    ->label('Destination')
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
                // status
                Filter::make('In progress')
                    ->label(DeliveryStatus::IN_PROGRESS->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->whereHas(
                        'userDeliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::IN_PROGRESS->value)
                            ->whereUserId(auth()->id())
                    )),
                //     case FAILED      = 'Failed';
                Filter::make('Failed')
                    ->label(DeliveryStatus::FAILED->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->whereHas(
                        'userDeliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::FAILED->value)
                    )),
                //    case COMPLETE    = 'Complete';
                Filter::make('Complete')
                    ->label(DeliveryStatus::COMPLETE->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->whereHas(
                        'userDeliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE->value)
                    )),

                //    case STASHED     = 'Stashed';
                Filter::make('Stashed')
                    ->label(DeliveryStatus::STASHED->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->whereHas(
                        'userDeliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::STASHED->value)
                    )),

                //    case LOST        = 'Lost';
                Filter::make('Lost')
                    ->label(DeliveryStatus::LOST->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->whereHas(
                        'userDeliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::LOST->value)
                    )),
            ])
            ->actions([
                Action::make('Take on order')
                    ->requiresConfirmation()
                    ->button()
                    ->color('info')
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
                    ->action(static function (Order $record): void {
                        Delivery::create([
                            'order_id'    => $record->id,
                            'user_id'     => auth()->id(),
                            'started_at'  => now('Europe/London'),
                            'ended_at'    => null,
                            'status'      => DeliveryStatus::IN_PROGRESS,
                            'location_id' => Location::whereDistrictId($record->client->district_id)
                                ->where('name', 'like', 'In progress%')
                                ->firstOrFail('id')->id,
                        ]);
                        Notification::make()
                            ->title('Standard delivery order taken')
                            ->success()
                            ->send();
                    }),
                Action::make('Stash delivery')
                    ->requiresConfirmation()
                    ->button()
                    ->color('warning')
                    ->visible(
                        static fn (Order $record) => Delivery::query()
                            ->where(
                                'status',
                                DeliveryStatus::IN_PROGRESS->value
                            )
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->form([
                        Select::make('location_id')
                            ->searchable()
                            ->relationship('client', 'name')
                            ->loadingMessage('Loading locations...')
                            ->required(),
                        Textarea::make('comment')
                            ->maxLength(65_535)
                            ->columnSpanFull(),
                    ])
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
                            ->title('requested cargo delivered')
                            ->success()
                            ->send();
                    }),
                Action::make('Continue delivery')
                    ->requiresConfirmation()
                    ->button()
                    ->color('info')
                    ->visible(
                        static fn (Order $record) => Delivery::query()
                            ->where(
                                'status',
                                DeliveryStatus::STASHED->value
                            )
                            ->whereUserId(auth()->id())
                            ->whereOrderId($record->id)
                            ->exists()
                    )
                    ->form([
                        Textarea::make('comment')
                            ->maxLength(65_535)
                            ->columnSpanFull(),
                    ])
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
                            ->title('requested cargo delivered')
                            ->success()
                            ->send();
                    }),
                Action::make('Make delivery')
                    ->requiresConfirmation()
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
                    ->action(static function (Order $record): void {
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
                            ]);
                        Notification::make()
                            ->title('requested cargo delivered')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                AcceptOrderBulkAction::make(),
                CompleteOrderBulkAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
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
            'index'  => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view'   => ViewOrder::route('/{record}'),
            'edit'   => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrdersOverview::class,
        ];
    }
}
