<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\DeliveryStatus;
use App\Filament\Resources\DeliveryCategoryResource\Pages;
use App\Filament\Resources\DeliveryCategoryResource\Pages\CreateDeliveryCategory;
use App\Filament\Resources\DeliveryCategoryResource\Pages\EditDeliveryCategory;
use App\Filament\Resources\DeliveryCategoryResource\Pages\ListDeliveryCategories;
use App\Filament\Resources\DeliveryCategoryResource\Pages\ViewDeliveryCategory;
use App\Filament\Resources\DeliveryCategoryResource\RelationManagers;
use App\Filament\Resources\DeliveryCategoryResource\RelationManagers\OrdersRelationManager;
use App\Models\Delivery;
use App\Models\DeliveryCategory;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryCategoryResource extends Resource
{
    protected static ?string $model = DeliveryCategory::class;

    // php artisan make:filament-relation-manager DeliveryCategoryResource orders number
    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Section::make()
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ])
                        ->columnSpan([
                            'lg' => 2,
                        ]),
                    Section::make()
                        ->schema([
                            Placeholder::make('created_at')
                                ->label(__('Created at'))
                                ->content(static fn (?DeliveryCategory $delivery): ?string => $delivery?->created_at?->diffForHumans()),
                            Placeholder::make('updated_at')
                                ->label(__('Last modified at'))
                                ->content(static fn (?DeliveryCategory $delivery): ?string => $delivery?->updated_at?->diffForHumans()),
                        ])
                        ->columnSpan([
                            'lg' => 1,
                        ])
                        ->hidden(static fn (?DeliveryCategory $delivery): bool => ! $delivery instanceof DeliveryCategory),
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
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->badge(),
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
                //
            ])
            ->actions([
                ViewAction::make(),
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
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDeliveryCategories::route('/'),
            'create' => CreateDeliveryCategory::route('/create'),
            'view'   => ViewDeliveryCategory::route('/{record}'),
            'edit'   => EditDeliveryCategory::route('/{record}/edit'),
        ];
    }
}
