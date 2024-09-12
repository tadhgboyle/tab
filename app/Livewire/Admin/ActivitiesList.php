<?php

namespace App\Livewire\Admin;

use App\Models\Activity;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ActivitiesList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->withCount('registrations'))
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('start')->sortable()->dateTime('M jS Y h:ia'),
                TextColumn::make('end')->sortable()->dateTime('M jS Y h:ia'),
                TextColumn::make('status')->badge()->state(function (Activity $activity) {
                    return match(true) {
                        $activity->ended() => 'Ended',
                        $activity->inProgress() => 'In Progress',
                        default => 'Upcoming',
                    };
                })->color(function (Activity $activity) {
                    return match(true) {
                        $activity->ended() => 'danger',
                        $activity->inProgress() => 'success',
                        default => 'gray',
                    };
                }),
                TextColumn::make('duration')->state(function (Activity $activity) {
                    return $activity->duration();
                }),
                TextColumn::make('slots')->sortable()->numeric(),
                BooleanColumn::make('unlimited_slots'),
                TextColumn::make('slots_available')->state(function (Activity $activity) {
                    // slotsAvailable() causes an N+1 query issue
                    return $activity->slots - $activity->registrations_count;
                })->numeric(),
            ])
            ->recordUrl(fn (Activity $activity) => route('activities_view', $activity))
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('start'),
                        DateConstraint::make('end'),
                    ]),
            ])
            ->filtersFormColumns(2)
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('start', 'desc');
    }
}
