<?php

namespace App\Livewire\Admin;

use App\Models\Family;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class FamiliesList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(Family::query())
            ->heading('Families')
            ->headerActions(
                hasPermission(Permission::FAMILIES_MANAGE) ? [
                    Action::make('create')->url(route('families_create'))
                ] : []
            )
            ->columns([
                TextColumn::make('created_at')->label('Created')->dateTime('M jS Y h:ia')->sortable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('members_count')->label('Members')->counts('members')->sortable(),
            ])
            ->recordUrl(function (Family $family) {
                if (hasPermission(Permission::FAMILIES_VIEW)) {
                    return route('families_view', $family);
                }
            })
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('name');
    }
}
