<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('order_id')
                                ->label('Order')
                                ->searchable()
                                ->options(static function (?Delivery $delivery): array {
                                    return Order::all()
                                        ->map(fn (Order $order) => [
                                            'id'          => $order->id,
                                            'number-name' => sprintf('%s %s', $order->number, $order->name),
                                        ])
                                        ->pluck('number-name', 'id')
                                        ->toArray();
                                })
                                ->getSearchResultsUsing(fn (string $search): array => Order::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('number', $search)
                                    ->limit(50)
                                    ->get()
                                    ->map(fn (Order $order) => [
                                        'id'          => $order->id,
                                        'number-name' => sprintf('%s %s', $order->number, $order->name),
                                    ])
                                    ->pluck('number-name', 'id')
                                    ->toArray())
                                ->getOptionLabelUsing(
                                    function ($value): string {
                                        /** @var ?Order $order */
                                        $order = Order::find($value);
                                        return sprintf('%s %s', $order?->number, $order?->name);
                                    }
                                )
                                ->loadingMessage('Loading orders...')
                                ->required(),
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->loadingMessage('Loading users...')
                                ->required(),
                            Forms\Components\Select::make('location_id')
                                ->searchable()
                                ->relationship('location', 'name')
                                ->loadingMessage('Loading locations...')
                                ->required(),
                            Forms\Components\DateTimePicker::make('started_at')
                                ->required(),
                            Forms\Components\DateTimePicker::make('ended_at'),
                            Forms\Components\Radio::make('status')
                                ->options(DeliveryStatus::toArrayString())
                                ->required(),
                            Forms\Components\Textarea::make('comment')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'lg' => 2,
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Placeholder::make('created_at')
                                ->label(__('Created at'))
                                ->content(static fn (?Delivery $delivery): ?string => $delivery?->created_at?->diffForHumans()),
                            Forms\Components\Placeholder::make('updated_at')
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
                Tables\Columns\TextColumn::make('order.number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.name')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->wrap()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->wrap()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index'  => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit'   => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }
}
