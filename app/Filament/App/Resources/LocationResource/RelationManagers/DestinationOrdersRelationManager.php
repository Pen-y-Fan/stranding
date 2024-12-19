<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\LocationResource\RelationManagers;

use App\Enum\OrderStatus;
use App\Filament\App\Resources\LocationResource;
use App\Filament\App\Resources\OrderResource;
use App\Models\Location;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class DestinationOrdersRelationManager extends RelationManager
{
    #[Url]
    public ?array $tableFilters;

    protected array $queryString = [];

    protected static string $relationship = 'destinationOrders';

    #[On('setStatusToFilter')]
    public function setStatusToFilter(string $filter): void
    {
        if (! OrderStatus::tryFrom($filter) instanceof OrderStatus) {
            Log::warning(sprintf('%s is not a valid OrderStatus Enum value', $filter));
            return;
        }

        // @phpstan-ignore-next-line
        $this->tableFilters['deliveries']['value'] = $filter;
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector|void
     */
    #[On('setStatusFilter')]
    public function setStatusFilter(string $filter)
    {
        if (! OrderStatus::tryFrom($filter) instanceof OrderStatus) {
            Log::warning(sprintf('%s is not a valid OrderStatus Enum value', $filter));
            return;
        }

        return redirect(LocationResource::getUrl(
            'view',
            [
                'record'                          => $this->getOwnerRecord(),
                'tableFilters[deliveries][value]' => $filter,
            ]
        ));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return OrderResource::table($table);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        if (! $ownerRecord instanceof Location) {
            return 'Orders to this client';
        }

        $clientName = $ownerRecord->name;
        return 'Orders to ' . $clientName;
    }
}
