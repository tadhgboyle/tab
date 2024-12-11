<?php

namespace App\Livewire\Common\Users;

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
            ->query($this->user->userLimits()->withAggregate('category', 'name')->orderBy('category_name')->getQuery())
            ->columns([
                TextColumn::make('category.name')->label('Category')->badge()->color('gray'),
                TextColumn::make('limit')->label('Limit')->state(function (UserLimit $userLimit) {
                    if ($userLimit->isUnlimited()) {
                        return '<i>Unlimited</i>';
                    } else {
                        return $userLimit->limit;
                    }
                })->html(),
                TextColumn::make('duration')->badge()->color('gray'),
                TextColumn::make('spent')->state(fn (UserLimit $userLimit) => $userLimit->findSpent()),
                TextColumn::make('remaining')->state(function (UserLimit $userLimit) {
                    if ($userLimit->isUnlimited()) {
                        return '<i>Unlimited</i>';
                    } else {
                        return $userLimit->remaining();
                    }
                })->html(),
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
