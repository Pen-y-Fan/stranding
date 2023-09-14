<?php

declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\RelationManagers;

use App\Filament\Resources\DeliveryResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    public function form(Form $form): Form
    {
        return DeliveryResource::form($form);
    }

    public function table(Table $table): Table
    {
        return DeliveryResource::table($table);
    }
}
