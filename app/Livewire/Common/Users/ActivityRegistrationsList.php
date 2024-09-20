<?php

namespace App\Livewire\Common\Users;

use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use App\Models\ActivityRegistration;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ActivityRegistrationsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;
    public string $context;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Activity Registrations')
            ->query($this->user->activityRegistrations()->getQuery())
            ->columns([
                TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
                TextColumn::make('activity.name'),
                TextColumn::make('cashier.full_name')
                    ->url(function (ActivityRegistration $activityRegistration) {
                        if (hasPermission(Permission::USERS_VIEW)) {
                            return route('users_view', $activityRegistration->cashier);
                        }
                    })
                    ->hidden(fn () => $this->context === 'family'),
                TextColumn::make('total_price')->sortable(),
                TextColumn::make('status')->badge()->state(function (ActivityRegistration $activityRegistration) {
                    return match (true) {
                        $activityRegistration->activity->ended() => 'Ended',
                        $activityRegistration->activity->inProgress() => 'In Progress',
                        default => 'Upcoming',
                    };
                })->color(function (ActivityRegistration $activityRegistration) {
                    return match (true) {
                        $activityRegistration->activity->ended() => 'danger',
                        $activityRegistration->activity->inProgress() => 'success',
                        default => 'gray',
                    };
                }),
                BooleanColumn::make('returned')->trueColor('danger')->falseColor('success'),
            ])
            ->recordUrl(function (ActivityRegistration $activityRegistration) {
                if ($this->context === 'family') {
                    return null;
                }

                if (hasPermission(Permission::ACTIVITIES_VIEW)) {
                    return route('activities_view', $activityRegistration->activity);
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
            ->paginated(false);
    }
}
