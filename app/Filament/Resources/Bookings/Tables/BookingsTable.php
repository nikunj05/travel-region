<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Booking::latest())
            ->columns([
                TextColumn::make('user.first_name')->searchable()->label('First Name'),
                TextColumn::make('user.last_name')->searchable()->label('Last Name'),
                TextColumn::make('hotel_code')->searchable()->label('Hotel Code'),
                TextColumn::make('rooms')->searchable()->label('Rooms'),
                TextColumn::make('adults')->searchable()->label('Adults'),
                TextColumn::make('children')->searchable()->label('Children'),
                TextColumn::make('check_in')->searchable()->label('Check-in')->date(),
                TextColumn::make('check_out')->searchable()->label('Check-out')->date(),
                TextColumn::make('status')->searchable()->label('Status')->formatStateUsing(function ($state) {
                    return ucfirst($state);
                })->colors([
                    'warning' => 'pending',
                    'success' => 'confirmed',
                    'danger' => 'cancelled',
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
