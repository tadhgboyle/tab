<?php

namespace App\Livewire\Common\Users;

use App\Models\User;
use App\Models\Payout;
use Livewire\Component;
use Filament\Tables\Table;
use App\Enums\PayoutStatus;
use App\Helpers\Permission;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
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
                    ->url(fn () => route('family_member_payout', [$this->user->family, $this->user->familyMember]))
                    ->disabled($this->user->findOwing()->isZero())
            ])
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('amount')->sortable(),
                TextColumn::make('status')->badge(),
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
                    ->options(PayoutStatus::class)->default(PayoutStatus::Paid->value),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Payout $payout) => "https://dashboard.stripe.com/test/payments/{$payout->stripe_payment_intent_id}")
                    ->openUrlInNewTab()
                    ->visible(fn (Payout $payout) => $payout->stripe_payment_intent_id && $this->context === 'admin' && hasPermission(Permission::DASHBOARD_FINANCIAL)), // TODO: Add permission
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
