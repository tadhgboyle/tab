<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityRegistrationRelationManagerResource\RelationManagers\AttendeesRelationManager;
use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Filament\Resources\ActivityResource\Widgets\ActivityCalendarWidget;
use App\Helpers\CategoryHelper;
use App\Models\Activity;
use Carbon\CarbonInterface;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Catalog Management';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('description'),
                TextEntry::make('location'),
                TextEntry::make('start'),
                TextEntry::make('end'),
                TextEntry::make('duration')
                    ->default(fn (Activity $activity) => $activity->start->diffForHumans($activity->end, CarbonInterface::DIFF_ABSOLUTE, false, 3)),
                TextEntry::make('price')->money(),
                IconEntry::make('pst')->label('PST')->boolean(),
                IconEntry::make('unlimited_slots')->boolean(),
                TextEntry::make('slots'),
                TextEntry::make('slots_available')
                    ->default(fn (Activity $activity) => $activity->slotsAvailable()),
                TextEntry::make('category.name'),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('location'),
                \Filament\Forms\Components\DateTimePicker::make('start')
                    ->before('end')
                    ->default(now())
                    ->required(),
                \Filament\Forms\Components\DateTimePicker::make('end')
                    ->after('start')
                    ->default(now()->addHour())
                    ->required(),
                Select::make('category_id')
                    ->name('Category')
                    ->options(app(CategoryHelper::class)->getActivityCategories()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('price')
                    ->prefix('$')
                    ->numeric()
                    ->required(),
                Toggle::make('pst')->label('PST'),
                Toggle::make('unlimited_slots'),
                TextInput::make('slots')
                    ->numeric()
                    ->requiredIf('unlimited_slots', false),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttendeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'view' => Pages\ViewActivity::route('/{record}'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
