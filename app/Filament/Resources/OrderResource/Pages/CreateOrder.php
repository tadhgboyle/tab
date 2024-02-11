<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\GiftCard;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)->schema([
                    \Filament\Forms\Components\Section::make()
                        ->columnSpan(2)
                        ->heading('Customer')
                        ->schema([
                            Select::make('customer_id')
                                ->options(
                                   User::query()
                                        ->select('id', 'name')
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->required(),
                            TextInput::make('customer_name')
                                ->disabled()
                                ->label('Name'),
                            TextInput::make('customer_email')
                                ->disabled()
                                ->label('Email'),
                            TextInput::make('customer_phone')
                                ->disabled()
                                ->label('Phone'),
                        ]),
                    \Filament\Forms\Components\Section::make()
                        ->heading('Pricing')
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('gift_card_code')
                            ->placeholder('Optional')
                            ->exists('gift_cards', 'code')
                            ->nullable(),
                        ]),
                    \Filament\Forms\Components\Section::make()
                        ->heading('Products')
                        ->schema([
                            Repeater::make('products')
                            ->orderable(false)
                            ->label('')
                            ->schema([
                                Select::make('product')
                                    ->options(
                                        \App\Models\Product::query()
                                            ->select('id', 'name')
                                            ->where('stock', '>', 0)
                                            ->orWhere('stock_override', true)
                                            ->orWhere('unlimited_stock', true)
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(2),
                        ]),
                ]),
            ]);
        }
}
