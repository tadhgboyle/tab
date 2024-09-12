<?php

namespace App\Livewire\User\Family\Members;

use App\Models\ActivityRegistration;
use App\Models\User;
use Filament\Tables\Columns\BooleanColumn;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

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
                TextColumn::make('activity.name'),
                TextColumn::make('total_price')->sortable(),
                TextColumn::make('status')->badge()->state(function (ActivityRegistration $activityRegistration) {
                    return match(true) {
                        $activityRegistration->activity->ended() => 'Ended',
                        $activityRegistration->activity->inProgress() => 'In Progress',
                        default => 'Upcoming',
                    };
                })->color(function (ActivityRegistration $activityRegistration) {
                    return match(true) {
                        $activityRegistration->activity->ended() => 'danger',
                        $activityRegistration->activity->inProgress() => 'success',
                        default => 'gray',
                    };
                }),
                BooleanColumn::make('returned')->trueColor('danger')->falseColor('success'),
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
