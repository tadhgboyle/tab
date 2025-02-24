<?php

namespace App\Livewire\Admin\Settings;

use App\Helpers\Permission;
use Livewire\Component;
use App\Models\GiftCard;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class GiftCardsList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Gift Cards')
            ->query(GiftCard::query())
            ->headerActions([
                Action::make('create')
                    ->url(route('settings_gift-cards_create')),
            ])
            ->columns([
                TextColumn::make('code')->fontFamily(FontFamily::Mono)->searchable()->copyable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('original_balance')->sortable(),
                TextColumn::make('remaining_balance')->sortable(),
                TextColumn::make('assignments_count')->label('Users')->counts('assignments')->numeric()->sortable(),
                TextColumn::make('uses_count')->label('Uses')->counts('uses')->numeric()->sortable(),
                TextColumn::make('issuer.full_name')->searchable()->sortable()->url(function (GiftCard $giftCard) {
                    if (hasPermission(Permission::USERS_VIEW)) {
                        return route('users_view', $giftCard->issuer);
                    }
                }),
                TextColumn::make('created_at')->label('Created')->dateTime('M jS Y h:ia')->sortable(),
            ])
            ->recordUrl(fn (GiftCard $giftCard) => route('settings_gift-cards_view', $giftCard))
            ->filters([
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'expired' => 'Expired',
                            ])->default('active'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['status'] === 'active', function (Builder $query) {
                            return $query->where('expires_at', '>', now())->orWhere('expires_at', null);
                        })->when($data['status'] === 'expired', function (Builder $query) {
                            return $query->where('expires_at', '<=', now());
                        });
                    }),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc');
    }
}
