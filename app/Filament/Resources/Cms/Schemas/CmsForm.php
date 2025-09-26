<?php

namespace App\Filament\Resources\Cms\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CmsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6)
                            ->live(onBlur: true) // generates slug when user leaves the field
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state))
                            ),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('content')
                            ->required()
                            ->translatable()
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
