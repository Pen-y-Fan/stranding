<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers\CompleteOrdersRelationManager;
use App\Filament\Resources\LocationResource\RelationManagers\DeliveriesRelationManager;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    // php artisan make:filament-relation-manager LocationResource deliveries name
    // php artisan make:filament-relation-manager LocationResource completeClientOrders number

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->autofocus()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Forms\Components\Select::make('district_id')
                                ->relationship('district', 'name')
                                ->required(),
                            Forms\Components\Toggle::make('is_physical')
                                ->required(),
                        ])
                        ->columnSpan([
                            'lg' => 2,
                        ]),
                    Section::make()
                        ->schema([
                            Placeholder::make('created_at')
                                ->label(__('Created at'))
                                ->content(static fn (?Location $location): ?string => $location?->created_at?->diffForHumans()),
                            Placeholder::make('updated_at')
                                ->label(__('Last modified at'))
                                ->content(static fn (?Location $location): ?string => $location?->updated_at?->diffForHumans()),
                        ])
                        ->columnSpan([
                            'lg' => 1,
                        ])
                        ->hidden(static fn (?Location $location): bool => ! $location instanceof Location),
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
                Tables\Columns\TextColumn::make('name')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_physical')
                    ->alignCenter()
                    ->boolean(),
                TextColumn::make('client_orders_count')
                    ->counts('clientOrders')
                    ->alignCenter()
                    ->badge(),
                TextColumn::make('destination_orders_count')
                    ->counts('destinationOrders')
                    ->alignCenter()
                    ->badge(),
                TextColumn::make('deliveries_count')
                    ->counts('deliveries')
                    ->alignCenter()
                    ->badge(),
                TextColumn::make('complete_orders_count')
                    ->summarize(Tables\Columns\Summarizers\Sum::make())
                    ->label('Orders completed')
                    ->counts('completeOrders')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
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
                SelectFilter::make('district')
                    ->relationship('district', 'name', static fn (Builder $query) => $query->whereHas('locations'))
                    ->label('District'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            DeliveriesRelationManager::class,
            CompleteOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view'   => Pages\ViewLocation::route('/{record}'),
            'edit'   => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
