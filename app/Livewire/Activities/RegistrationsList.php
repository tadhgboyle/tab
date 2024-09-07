<?php

namespace App\Livewire\Activities;

use Livewire\Component;
use App\Models\Activity;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class RegistrationsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Activity $activity;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Registrations')
            ->headerActions([
                Action::make('register')
                    ->label('Register')
                    ->alpineClickHandler('openModal')
                    ->disabled(function () {
                        return $this->activity->end->isPast() || !$this->activity->hasSlotsAvailable() || !hasPermission(Permission::ACTIVITIES_REGISTER_USER);
                    }),
            ])
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
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
