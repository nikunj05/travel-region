<?php

namespace App\Filament\Resources\CommissionByCities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CommissionByCityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('city')
                    ->label('City')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(6),

                TextInput::make('commission_percentage')
                    ->label('Commission %')
                    ->integer()
                    ->minValue(-100)
                    ->maxValue(100)
                    ->required()
                    ->columnSpan(6),
            ]);
    }
}
