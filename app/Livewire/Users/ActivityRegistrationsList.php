<?php

namespace App\Livewire\Users;

use App\Models\User;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\TernaryFilter;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use App\Models\ActivityRegistration;
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
                TextColumn::make('cashier.full_name'),
                TextColumn::make('total_price')->sortable(),
                BooleanColumn::make('returned')->trueColor('danger')->falseColor('success'),
            ])
            ->recordUrl(function (ActivityRegistration $activityRegistration) {
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
