<?php

namespace App\Filament\Resources\PopularDestinations\Schemas;

use App\Forms\Components\MapboxLocation;
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
                        MapboxLocation::make('location')
                            ->label('Search Location')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // if (is_array($state)) {
                                //     $set('latitude', $state['latitude'] ?? null);
                                //     $set('longitude', $state['longitude'] ?? null);
                                //     $set('city', $state['city'] ?? null);
                                //     $set('state', $state['state'] ?? null);
                                //     $set('country', $state['country'] ?? null);
                                // }
                            })
                            ->columnSpanFull(),
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
                        TextInput::make('city')
                            ->columnSpan(4)
                            ->readOnly()
                            ->dehydrated(true),
                        TextInput::make('state')
                            ->columnSpan(4)
                            ->readOnly()
                            ->dehydrated(true),
                        TextInput::make('country')
                            ->columnSpan(4)
                            ->readOnly()
                            ->dehydrated(true),
                    ]),

                Grid::make()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('latitude')
                            ->columnSpan(6)
                            ->readOnly()
                            ->dehydrated(true),

                        TextInput::make('longitude')
                            ->columnSpan(6)
                            ->readOnly()
                            ->dehydrated(true),
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
