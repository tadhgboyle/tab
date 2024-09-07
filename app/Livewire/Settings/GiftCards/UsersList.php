<?php

namespace App\Livewire\Settings\GiftCards;

use Livewire\Component;
use App\Models\GiftCard;
use Filament\Tables\Table;
use App\Models\GiftCardAssignment;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class UsersList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public GiftCard $giftCard;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Users')
            ->headerActions([
                Action::make('add_user')
                    ->label('Add user')
                    ->alpineClickHandler('openModal')
                    ->disabled($this->giftCard->expired()),
            ])
            ->query($this->giftCard->assignments()->getQuery())
            ->columns([
                TextColumn::make('user.full_name'),
                TextColumn::make('total_use')->state(function (GiftCardAssignment $assignment) {
                    return $this->giftCard->usageBy($assignment->user); // TODO this is doing 3 queries per row, why?
                }),
                TextColumn::make('assigner.full_name')->label('Assigned by'),
                TextColumn::make('created_at')->label('Given at')->dateTime('M jS Y h:ia'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('revoke')
                    ->alpineClickHandler(function (GiftCardAssignment $giftCardAssignment) {
                        return "openRemoveUserModal('{$giftCardAssignment->user->id}', '{$giftCardAssignment->user->full_name}')";
                    })
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
