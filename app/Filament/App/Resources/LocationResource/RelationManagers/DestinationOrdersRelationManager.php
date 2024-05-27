<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\LocationResource\RelationManagers;

use App\Filament\App\Resources\OrderResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DestinationOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'destinationOrders';

    protected static ?string $title = 'Orders to this client';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return OrderResource::table($table);
    }
}
