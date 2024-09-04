<?php

namespace App\Livewire\Activities;

use App\Models\Activity;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class RegistrationsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Activity $activity;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Registrations')
            ->query($this->activity->registrations()->getQuery())
            ->columns([
                TextColumn::make('user.full_name')->label('Name'),
                TextColumn::make('cashier.full_name')->label('Cashier'),
                TextColumn::make('created_at')->label('Registered At')->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}