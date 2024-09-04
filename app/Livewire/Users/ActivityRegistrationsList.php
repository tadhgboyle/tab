<?php

namespace App\Livewire\Users;

use App\Helpers\Permission;
use App\Models\ActivityRegistration;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ActivityRegistrationsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Activity Registrations')
            ->query($this->user->activityRegistrations()->getQuery())
            ->columns([
                TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
                TextColumn::make('activity.name')
                    ->url(function (ActivityRegistration $activityRegistration) {
                        if (!hasPermission(Permission::ACTIVITIES_VIEW)) {
                            return null;
                        }

                        return route('activities_view', $activityRegistration->activity);
                    }),
                TextColumn::make('cashier.full_name')
                    ->url(function (ActivityRegistration $activityRegistration) {
                        if (!hasPermission(Permission::USERS_VIEW)) {
                            return null;
                        }

                        return route('users_view', $activityRegistration->cashier);
                    }),
                TextColumn::make('total_price')->sortable(),
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