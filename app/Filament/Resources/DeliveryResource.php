<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\Resources\DeliveryResource\Pages\CreateDelivery;
use App\Filament\Resources\DeliveryResource\Pages\EditDelivery;
use App\Filament\Resources\DeliveryResource\Pages\ListDeliveries;
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
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

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
                                ->options(static fn (): array => Order::limit(10)->get()
                                    ->map(static fn (Order $order): array => [
                                        'id'          => $order->id,
                                        'number-name' => sprintf('%s %s', $order->number, $order->name),
                                    ])
                                    ->pluck('number-name', 'id')
                                    ->toArray())
                                ->getSearchResultsUsing(static fn (string $search): array => Order::query()
                                    ->where('name', 'like', sprintf('%%%s%%', $search))
                                    ->orWhere('number', $search)
                                    ->limit(10)
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
                            Select::make('user_id')
                                ->relationship('user', 'name')
                                ->loadingMessage('Loading users...')
                                ->required(),
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
                                ->content(static fn (?Delivery $record): ?string => $record?->created_at?->diffForHumans()),
                            Placeholder::make('updated_at')
                                ->label(__('Last modified at'))
                                ->content(static fn (?Delivery $record): ?string => $record?->updated_at?->diffForHumans()),
                        ])
                        ->columnSpan([
                            'lg' => 1,
                        ])
                        ->hidden(static fn (?Delivery $record): bool => ! $record instanceof Delivery),
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.name')
                    ->wrap()
                    ->description(fn (Delivery $record): string => 'Premium: ' . $record->order->premium)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->wrap()
                    ->sortable(),
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
                    ->badge()
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

            ])
            ->actions([
                EditAction::make(),
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
