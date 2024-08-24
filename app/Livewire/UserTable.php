<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\User;

class UserTable extends DataTableComponent
{
    protected $model = User::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make('Full name', 'full_name')
                ->sortable(),
            Column::make('Balance', 'balance')
                ->format(fn($value) => $value->format())
                ->sortable(),
            Column::make('Role', 'role.name'),
            Column::make('Created at', 'created_at')
                ->sortable(),
        ];
    }
}
