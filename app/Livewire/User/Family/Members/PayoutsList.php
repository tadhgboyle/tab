<?php

namespace App\Livewire\User\Family\Members;

use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PayoutsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Payouts')
            ->query($this->user->payouts()->getQuery())
            ->headerActions([
                // Action::make('create')
                //     ->url(route('users_payout_create', $this->user))
                //     ->visible(hasPermission(Permission::USERS_PAYOUTS_CREATE))
                //     ->disabled($this->user->findOwing()->isZero()),
            ])
            ->columns([
                TextColumn::make('identifier'),
                TextColumn::make('amount')->sortable(),
                TextColumn::make('created_at')->label('Date')->sortable(),
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
