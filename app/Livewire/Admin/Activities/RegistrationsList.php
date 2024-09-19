<?php

namespace App\Livewire\Admin\Activities;

use App\Models\ActivityRegistration;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->alpineClickHandler('openSearchUsersModal')
                    ->disabled(function () {
                        return $this->activity->ended() || !$this->activity->hasSlotsAvailable() || !hasPermission(Permission::ACTIVITIES_MANAGE_REGISTRATIONS);
                    }),
            ])
            ->query($this->activity->registrations(false)->getQuery())
            ->columns([
                TextColumn::make('user.full_name')->label('Name')->url(function (ActivityRegistration $registration) {
                    if (hasPermission(Permission::USERS_VIEW)) {
                        return route('users_view', $registration->user);
                    }
                }),
                TextColumn::make('cashier.full_name')->label('Cashier')->url(function (ActivityRegistration $registration) {
                    if (hasPermission(Permission::USERS_VIEW)) {
                        return route('users_view', $registration->cashier);
                    }
                }),
                TextColumn::make('created_at')->label('Registered At')->sortable(),
                BooleanColumn::make('returned')->trueColor('danger')->falseColor('success'),
            ])
            ->filters([
                TernaryFilter::make('returned')
                    ->label('Returned')
                    ->options([
                        true => 'Returned',
                        false => 'Not Returned',
                    ])->default(false),
            ])
            ->actions([
                Action::make('remove')
                    ->alpineClickHandler(function (ActivityRegistration $activityRegistration) {
                        return "openRemoveUserModal({$this->activity->id}, {$activityRegistration->id})";
                    })->visible(function (ActivityRegistration $activityRegistration) {
                        return hasPermission(Permission::ACTIVITIES_MANAGE_REGISTRATIONS) && !$this->activity->started() && !$activityRegistration->returned;
                    }),
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
