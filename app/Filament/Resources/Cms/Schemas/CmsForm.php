<?php

namespace App\Filament\Resources\Cms\Schemas;

use App\Models\CmsPage;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

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
                        Select::make('type')
                            ->label('Page')
                            ->options(function ($get, $record) {
                                $allTypes = [
                                    'about' => 'About Us',
                                    'privacy' => 'Privacy Policy',
                                    'terms' => 'Terms & Conditions',
                                ];

                                // Get types already used in DB
                                $existingTypes = \App\Models\CmsPage::pluck('type')->toArray();

                                // If editing, remove current record type from $existingTypes
                                if ($record?->type) {
                                    $existingTypes = array_diff($existingTypes, [$record->type]);
                                }

                                return collect($allTypes)
                                    ->reject(fn($label, $key) => in_array($key, $existingTypes))
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('title')
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
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
