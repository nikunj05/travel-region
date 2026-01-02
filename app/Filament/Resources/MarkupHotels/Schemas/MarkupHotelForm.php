<?php

namespace App\Filament\Resources\MarkupHotels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MarkupHotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hotel_code')
                    ->label('Hotel Code')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->columnSpan(6),

                TextInput::make('markup_percentage')
                    ->label('Markup %')
                    ->integer()
                    ->minValue(-100)
                    ->maxValue(100)
                    ->required()
                    ->columnSpan(6),
            ]);
    }
}
