<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use App\Models\UserLimit;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class CategoryLimitsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Category Limits')
            ->query($this->user->userLimits()->getQuery())
            ->columns([
                TextColumn::make('category.name')->label('Category')->badge()->color('gray'),
                TextColumn::make('limit')->label('Limit')->sortable()->state(function (UserLimit $userLimit) {
                    if ($userLimit->isUnlimited()) {
                        return '<i>Unlimited</i>';
                    } else {
                        return $userLimit->limit;
                    }
                })->html(),
                TextColumn::make('duration')->badge()->color('gray')
                    ->state(fn (UserLimit $userLimit) => ucfirst($userLimit->duration->label())),
                TextColumn::make('spent')->state(fn (UserLimit $userLimit) => $userLimit->findSpent())
                    ->sortable(),
                TextColumn::make('remaining')->state(fn (UserLimit $userLimit) => $userLimit->remaining())
                    ->sortable(),
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
