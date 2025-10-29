<?php

namespace App\Filament\Resources\PopularDestinations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PopularDestinationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),

                        // next rows, full-width
                        FileUpload::make('image')
                            ->required()
                            ->image()
                            ->imageEditor()
                            ->directory('popular-destinations') // stored inside storage/app/public/popular-destinations
                            ->maxSize(4048) // 4 MB
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
