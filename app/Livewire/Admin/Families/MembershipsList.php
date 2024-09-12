<?php

namespace App\Livewire\Admin\Families;

use App\Helpers\Permission;
use App\Models\Family;
use App\Models\FamilyMembership;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class MembershipsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Family $family;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Users')
            ->query($this->family->memberships()->with('user.orders', 'user.activityRegistrations')->getQuery())
            ->columns([
                TextColumn::make('user.full_name')->sortable()->searchable(),
                TextColumn::make('role')->badge()->color('gray')->state(function (FamilyMembership $familyMembership) {
                    return ucfirst($familyMembership->role->value);
                }),
                TextColumn::make('total_spent')->sortable()->state(function (FamilyMembership $familyMembership) {
                    return $familyMembership->user->findSpent();
                }),
                TextColumn::make('total_owing')->sortable()->state(function (FamilyMembership $familyMembership) {
                    return $familyMembership->user->findOwing();
                }),
            ])
            ->recordUrl(function (FamilyMembership $familyMembership) {
                if (hasPermission(Permission::USERS_VIEW)) {
                    return route('users_view', $familyMembership->user);
                }
            })
            ->filters([
                SelectFilter::make('role')->options([
                    'admin' => 'Admin',
                    'member' => 'Member',
                ]),
            ])
            ->headerActions([
                Action::make('add_user')
                    ->label('Add User')
                    ->alpineClickHandler('openSearchUsersModal')
                    ->visible(hasPermission(Permission::FAMILIES_MANAGE)),
                Action::make('pdf')
                    ->label('PDF')
                    ->button()
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn () => route('family_pdf'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Action::make('pdf')
                    ->label('PDF')
                    ->button()
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (FamilyMembership $familyMembership) => route('family_membership_pdf', $familyMembership))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}
