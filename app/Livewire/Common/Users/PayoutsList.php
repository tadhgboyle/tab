<?php

namespace App\Livewire\Common\Users;

use App\Enums\PayoutStatus;
use App\Models\User;
use App\Models\Payout;
use Filament\Tables\Filters\SelectFilter;
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
                    ->visible($this->context === 'family')
                    ->url(fn() => route('family_member_payout', [$this->user->family, $this->user->familyMember]))
                    ->disabled($this->user->findOwing()->isZero())
            ])
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('amount')->sortable(),
                TextColumn::make('status')->badge()->color(function (Payout $payout) {
                    return match ($payout->status) {
                        PayoutStatus::Cancelled => 'danger',
                        PayoutStatus::Pending => 'warning',
                        PayoutStatus::Paid => 'success',
                    };
                })->state(fn (Payout $payout) => ucfirst($payout->status->value)),
                TextColumn::make('creator.full_name')
                    ->url(function (Payout $payout) {
                        if ($this->context === 'admin' && hasPermission(Permission::USERS_VIEW)) {
                            return route('users_view', $payout->creator);
                        } else if ($this->context === 'family' && auth()->user()->isFamilyAdmin($this->user->family) && $payout->creator->family?->is($this->user->family)) {
                            return route('families_member_view', [$this->user->family, $payout->creator->familyMember]);
                        }
                    }),
                TextColumn::make('created_at')->label('Date')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        PayoutStatus::Cancelled->value => 'Cancelled',
                        PayoutStatus::Pending->value => 'Pending',
                        PayoutStatus::Paid->value => 'Paid',
                    ])->default(PayoutStatus::Paid->value),
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
