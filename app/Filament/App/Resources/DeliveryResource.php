<?php

declare(strict_types=1);

namespace App\Filament\App\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\App\Resources\DeliveryResource\Pages\CreateDelivery;
use App\Filament\App\Resources\DeliveryResource\Pages\EditDelivery;
use App\Filament\App\Resources\DeliveryResource\Pages\ListDeliveries;
use App\Filament\Resources\OrderResource;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    /**
     * @return Builder<Delivery>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereUserId(auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Section::make()
                        ->schema([
                            Select::make('order_id')
                                ->label('Order')
                                ->searchable()
                                ->options(static fn (?Delivery $delivery): array => Order::all()
                                    ->map(static fn (Order $order): array => [
                                        'id'          => $order->id,
                                        'number-name' => sprintf('%s %s', $order->number, $order->name),
                                    ])
                                    ->pluck('number-name', 'id')
                                    ->toArray())
                                ->getSearchResultsUsing(static fn (string $search): array => Order::query()
                                    ->where('name', 'like', sprintf('%%%s%%', $search))
                                    ->orWhere('number', $search)
                                    ->limit(50)
                                    ->get()
                                    ->map(static fn (Order $order): array => [
                                        'id'          => $order->id,
                                        'number-name' => sprintf('%s %s', $order->number, $order->name),
                                    ])
                                    ->pluck('number-name', 'id')
                                    ->toArray())
                                ->getOptionLabelUsing(
                                    static function ($value): string {
                                        /** @var ?Order $order */
                                        $order = Order::find($value);
                                        return sprintf('%s %s', $order?->number, $order?->name);
                                    }
                                )
                                ->loadingMessage('Loading orders...')
                                ->required(),
                            //                            Select::make('user_id')
                            //                                ->relationship('user', 'name')
                            //                                ->loadingMessage('Loading users...')
                            //                                ->required(),
                            Select::make('location_id')
                                ->searchable()
                                ->relationship('location', 'name')
                                ->loadingMessage('Loading locations...')
                                ->required(),
                            DateTimePicker::make('started_at')
                                ->required(),
                            DateTimePicker::make('ended_at'),
                            Radio::make('status')
                                ->options(DeliveryStatus::toArrayString())
                                ->required(),
                            Textarea::make('comment')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'lg' => 2,
                        ]),
                    Section::make()
                        ->schema([
                            Placeholder::make('created_at')
                                ->label(__('Created at'))
                                ->content(static fn (?Delivery $delivery): ?string => $delivery?->created_at?->diffForHumans()),
                            Placeholder::make('updated_at')
                                ->label(__('Last modified at'))
                                ->content(static fn (?Delivery $delivery): ?string => $delivery?->updated_at?->diffForHumans()),
                        ])
                        ->columnSpan([
                            'lg' => 1,
                        ])
                        ->hidden(static fn (?Delivery $delivery): bool => ! $delivery instanceof Delivery),
                ]
            )
            ->columns([
                'sm' => 3,
                'lg' => null,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.number')
                    ->url(static fn (Delivery $delivery): string => OrderResource::getUrl('view', [
                        'record' => $delivery->order->id,
                    ]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.name')
                    ->wrap()
                    ->url(static fn (Delivery $delivery): string => OrderResource::getUrl('view', [
                        'record' => $delivery->order->id,
                    ]))
                    ->searchable()
                    ->sortable(),
                //                TextColumn::make('user.name')
                //                    ->wrap()
                //                    ->sortable(),
                TextColumn::make('location.name')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->wrap()
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->wrap()
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->wrap()
                    ->badge(),
                TextColumn::make('comment')
                    ->words(50)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('started_at', 'DESC')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        DeliveryStatus::class
                    ),
            ])
            ->actions([
                EditAction::make(),
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
                            'ended_at'    => now('Europe/London'),
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
                    ->form([
                        Textarea::make('comment')
                            ->maxLength(65_535)
                            ->columnSpanFull(),
                    ])
                    ->action(static fn (array $data, Delivery $record) => $record
                        ->update([
                            'ended_at'    => now('Europe/London'),
                            'status'      => DeliveryStatus::FAILED,
                            'location_id' => $record->order->destination_id,
                            'comment'     => $data['comment'],
                        ])),
                Action::make('Lost')
                    ->requiresConfirmation()
                    ->button()
                    ->color('warning')
                    ->visible(
                        static fn (Delivery $record): bool => $record->status === DeliveryStatus::IN_PROGRESS
                            || $record->status                                === DeliveryStatus::STASHED
                    )
                    ->form([
                        Textarea::make('comment')
                            ->maxLength(65_535)
                            ->columnSpanFull(),
                    ])
                    ->action(static fn (array $data, Delivery $record) => $record
                        ->update([
                            'ended_at'    => now(),
                            'status'      => DeliveryStatus::LOST,
                            'location_id' => $record->order->client_id,
                            'comment'     => $data['comment'],
                        ])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index'  => ListDeliveries::route('/'),
            'create' => CreateDelivery::route('/create'),
            'edit'   => EditDelivery::route('/{record}/edit'),
        ];
    }
}
