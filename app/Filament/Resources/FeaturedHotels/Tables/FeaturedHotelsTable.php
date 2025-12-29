<?php

namespace App\Filament\Resources\FeaturedHotels\Tables;

use App\Models\FeaturedHotel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeaturedHotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(FeaturedHotel::latest())
            ->columns([
                TextColumn::make('hotel_code')->searchable()->label('Hotel Code'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
