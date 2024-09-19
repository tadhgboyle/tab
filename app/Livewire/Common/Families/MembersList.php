<?php

namespace App\Livewire\Common\Families;

use App\Helpers\Permission;
use App\Models\Family;
use App\Models\FamilyMember;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class MembersList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Family $family;
    public string $context;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Users')
            ->query($this->family->members()->with(hasPermission(Permission::USERS_VIEW) || auth()->user()->isFamilyAdmin($this->family) ? ['user.orders', 'user.activityRegistrations'] : [])->getQuery())
            ->columns([
                TextColumn::make('user.full_name')->sortable()->searchable(),
                TextColumn::make('role')->badge()->color('gray')->state(function (FamilyMember $familyMember) {
                    return ucfirst($familyMember->role->value);
                }),
                TextColumn::make('total_spent')->sortable()->state(function (FamilyMember $familyMember) {
                    return $familyMember->user->findSpent();
                })->visible(function () {
                    if ($this->context === 'family') {
                        return auth()->user()->isFamilyAdmin($this->family);
                    }

                    return hasPermission(Permission::USERS_VIEW);
                }),
                TextColumn::make('total_owing')->sortable()->state(function (FamilyMember $familyMember) {
                    return $familyMember->user->findOwing();
                })->visible(function () {
                    if ($this->context === 'family') {
                        return auth()->user()->isFamilyAdmin($this->family);
                    }

                    return hasPermission(Permission::USERS_VIEW);
                }),
            ])
            ->recordUrl(function (FamilyMember $familyMember) {
                if ($this->context === 'family' && (auth()->user()->isFamilyAdmin($this->family) || auth()->user()->id === $familyMember->user_id)) {
                    return route('families_member_view', [$this->family, $familyMember]);
                } else if ($this->context === 'admin' && hasPermission(Permission::USERS_VIEW)) {
                    return route('users_view', $familyMember->user);
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
                    ->button()
                    ->alpineClickHandler('openSearchUsersModal')
                    ->visible($this->context === 'admin' && hasPermission(Permission::FAMILIES_MANAGE)),
                Action::make('pdf')
                    ->label('PDF')
                    ->button()
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn () => route('family_pdf', $this->family))
                    ->openUrlInNewTab()
                    ->visible($this->context === 'family' && auth()->user()->isFamilyAdmin($this->family)),
            ])
            ->actions([
                Action::make('remove')
                    ->alpineClickHandler(function (FamilyMember $familyMember) {
                        return "openRemoveUserModal('{$familyMember->id}', '{$familyMember->user->full_name}');";
                    })
                    ->visible($this->context === 'admin' && hasPermission(Permission::FAMILIES_MANAGE)),
                Action::make('pdf')
                    ->label('PDF')
                    ->button()
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (FamilyMember $familyMember) => route('family_member_pdf', [$this->family, $familyMember]))
                    ->openUrlInNewTab()
                    ->visible(function (FamilyMember $familyMember) {
                        return $this->context === 'family' && (auth()->user()->isFamilyAdmin($this->family) || auth()->user()->id === $familyMember->user_id);
                    }),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}
