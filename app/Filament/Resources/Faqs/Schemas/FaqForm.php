<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('faq_category_id')
                            ->label('Category')
                            ->relationship('category', 'name') // uses relation
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(6),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('question')
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('answer')
                            ->required()
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
