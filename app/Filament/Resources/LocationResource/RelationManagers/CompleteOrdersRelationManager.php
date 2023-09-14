<?php

declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CompleteOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'completeOrders';

    public function form(Form $form): Form
    {
        return OrderResource::form($form);
    }

    public function table(Table $table): Table
    {
        return OrderResource::table($table);
    }
}
