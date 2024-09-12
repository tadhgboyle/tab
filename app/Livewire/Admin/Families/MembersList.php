<?php

namespace App\Livewire\Admin\Families;

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

    public function table(Table $table): Table
    {
        return $table
            ->heading('Users')
            ->query($this->family->members()->with('user.orders', 'user.activityRegistrations')->getQuery())
            ->columns([
                TextColumn::make('user.full_name')->sortable()->searchable(),
                TextColumn::make('role')->badge()->color('gray')->state(function (FamilyMember $familyMember) {
                    return ucfirst($familyMember->role->value);
                }),
                TextColumn::make('total_spent')->sortable()->state(function (FamilyMember $familyMember) {
                    return $familyMember->user->findSpent();
                }),
                TextColumn::make('total_owing')->sortable()->state(function (FamilyMember $familyMember) {
                    return $familyMember->user->findOwing();
                }),
            ])
            ->recordUrl(function (FamilyMember $familyMember) {
                if (hasPermission(Permission::USERS_VIEW)) {
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
                    ->url(fn (FamilyMember $familyMember) => route('family_member_pdf', $familyMember))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated(false);
    }
}
