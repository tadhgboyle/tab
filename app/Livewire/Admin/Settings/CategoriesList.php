<?php

namespace App\Livewire\Admin\Settings;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Livewire\Component;
use App\Models\Category;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class CategoriesList extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
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
                // TODO use enums on CategoryType
                TextColumn::make('type')->badge()->state(function (Category $category) {
                    return $category->type->getName();
                })->color('gray'),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                Action::make('edit')
                    ->url(fn (Category $category) => route('settings_categories_edit', $category)),
            ])
            ->toolbarActions([
                // ...
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}
