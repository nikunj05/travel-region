<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CouponForm
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

                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn ($state) => strtoupper(str_replace(' ', '', $state)))
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('code', strtoupper(str_replace(' ', '', $state)))
                            )
                            ->extraInputAttributes([
                                'style' => 'text-transform: uppercase',
                                'oninput' => "this.value = this.value.replace(/\\s+/g, '').toUpperCase();",
                            ])
                            ->maxLength(15)
                            ->columnSpan(6),

                        Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('discount')
                            ->required()
                            ->numeric()
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
