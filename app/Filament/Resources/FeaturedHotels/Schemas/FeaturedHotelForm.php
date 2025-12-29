<?php

namespace App\Filament\Resources\FeaturedHotels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeaturedHotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hotel_code')
                    ->label('Hotel Code')
                    ->numeric()
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(6),
            ]);
    }
}
