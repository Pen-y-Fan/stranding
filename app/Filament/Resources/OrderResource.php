<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\DeliveryCategory;
use App\Models\District;
use App\Models\Location;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                TextColumn::make('destination.district.name')
                    ->sortable(),
                TextColumn::make('client.name')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('destination.name')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('deliveryCategory.name')
                    ->wrap()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('max_likes')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('deliveries.status')
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
                    ->query(static fn (Builder $query) => $query->orWhereHas(
                        'deliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::IN_PROGRESS->getLabel())
                    )),
                //     case FAILED      = 'Failed';
                Filter::make('Failed')
                    ->label(DeliveryStatus::FAILED->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->orWhereHas(
                        'deliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::FAILED->getLabel())
                    )),
                //    case COMPLETE    = 'Complete';
                Filter::make('Complete')
                    ->label(DeliveryStatus::COMPLETE->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->orWhereHas(
                        'deliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::COMPLETE->getLabel())
                    )),

                //    case STASHED     = 'Stashed';
                Filter::make('Stashed')
                    ->label(DeliveryStatus::STASHED->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->orWhereHas(
                        'deliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::STASHED->getLabel())
                    )),

                //    case LOST        = 'Lost';
                Filter::make('Lost')
                    ->label(DeliveryStatus::LOST->getLabel())
                    ->checkbox()
                    ->query(static fn (Builder $query) => $query->orWhereHas(
                        'deliveries',
                        static fn (Builder $query) => $query->where('status', DeliveryStatus::LOST->getLabel())
                    )),
            ])
            ->actions([
                // start new delivery
                //                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // start new delivery
                    //                    Tables\Actions\DeleteBulkAction::make(),
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
            'index'  => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit'   => EditOrder::route('/{record}/edit'),
        ];
    }
}
