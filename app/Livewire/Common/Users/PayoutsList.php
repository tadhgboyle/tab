<?php

namespace App\Livewire\Common\Users;

use App\Models\User;
use App\Models\Payout;
use Livewire\Component;
use Filament\Tables\Table;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PayoutsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public User $user;
    public string $context;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Payouts')
            ->query($this->user->payouts()->getQuery())
            ->headerActions([
                Action::make('create')
                    ->url(route('users_payout_create', $this->user))
                    ->visible(hasPermission(Permission::USERS_PAYOUTS_CREATE))
                    ->disabled($this->user->findOwing()->isZero())
                    ->hidden($this->context === 'family'), // TODO allow family to create payouts
            ])
            ->columns([
                TextColumn::make('identifier')->searchable()->fontFamily(FontFamily::Mono),
                TextColumn::make('amount')->sortable(),
                TextColumn::make('cashier.full_name')
                    ->url(function (Payout $payout) {
                        if (hasPermission(Permission::USERS_VIEW)) {
                            return route('users_view', $payout->cashier);
                        }
                    })
                    ->hidden($this->context === 'family'),
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
