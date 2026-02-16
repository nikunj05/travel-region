<?php

namespace App\Filament\Resources\PopularDestinations\Schemas;

use App\Forms\Components\MapboxLocation;
use App\Models\Destination;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
                        Select::make('location')
                            ->label('Search Location')
                            ->required()
                            ->options(Destination::all()->pluck('name', 'code'))
                            ->searchable()
                            ->columnSpan(6)
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image')
                            ->required()
                            ->image()
                            ->imageEditor()
                            ->directory('popular-destinations')
                            ->maxSize(10048)
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(6),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('hotel_count')
                            ->columnSpan(6)
                            ->numeric(),

                        TextInput::make('hotel_min_price')
                            ->columnSpan(6)
                            ->numeric(),
                    ]),

            ]);
    }
}
