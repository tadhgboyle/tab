<?php

namespace App\Livewire;

use App\Models\Category;
use Filament\Tables\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class CategoriesList extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Categories')
            ->query(Category::query())
            ->headerActions([
                Action::make('create')
                    ->url(route('settings_categories_create')),
            ])
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('type')->badge()->state(function (Category $category) {
                    return $category->type->getName();
                })->color('gray'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn (Category $category) => route('settings_categories_edit', $category)),
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}