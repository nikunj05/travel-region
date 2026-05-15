<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('total_price')->searchable()->label('Total Price'),
                TextColumn::make('status')->searchable()->label('Status')->formatStateUsing(function ($state) {
                    return ucfirst($state);
                })->colors([
                    'warning' => 'pending',
                    'success' => 'confirmed',
                    'danger' => 'cancelled',
                ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ])
            ])
            ->recordActions([
                Action::make('downloadPdf')
                    ->label('PDF')
                    ->url(fn (Booking $record): string => route('booking.download-pdf', [
                        'order' => $record->order,
                        'lang' => 'en',
                    ]))
                    ->openUrlInNewTab()
                    ->visible(fn (Booking $record): bool => $record->status === 'confirmed')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->button(),

                ViewAction::make()
                    ->modalHeading('Booking Details')
                    ->schema([
                        Grid::make()
                            ->columns(12)
                            ->schema([
                                TextEntry::make('hotel_name')
                                    ->label('Hotel Name')
                                    ->icon('heroicon-o-building-office-2')
                                    ->copyable()
                                    ->columnSpan(6),

                                TextEntry::make('hotel_location')
                                    ->label('Hotel Location')
                                    ->icon('heroicon-o-building-office-2')
                                    ->copyable()
                                    ->columnSpan(6),

                                TextEntry::make('rooms')
                                    ->label('Rooms')
                                    ->icon('heroicon-o-home')
                                    ->columnSpan(6),

                                TextEntry::make('adults')
                                    ->label('Adults')
                                    ->icon('heroicon-o-users')
                                    ->columnSpan(6),

                                TextEntry::make('children')
                                    ->label('Children')
                                    ->icon('heroicon-o-user-group')
                                    ->columnSpan(6),

                                TextEntry::make('status')
                                    ->badge()
                                    ->size('lg')
                                    ->colors([
                                        'warning' => 'pending',
                                        'success' => 'confirmed',
                                        'danger' => 'cancelled',
                                    ])
                                    ->columnSpan(6),

                                TextEntry::make('check_in')
                                    ->label('Check-in Date')
                                    ->date('M d, Y')
                                    ->icon('heroicon-o-calendar')
                                    ->columnSpan(6),

                                TextEntry::make('check_out')
                                    ->label('Check-out Date')
                                    ->date('M d, Y')
                                    ->icon('heroicon-o-calendar')
                                    ->columnSpan(6),

                                TextEntry::make('total_price')
                                    ->label('Total Price')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success')
                                    ->columnSpan(6),

                                TextEntry::make('discount_amount')
                                    ->label('Discount Amount')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success')
                                    ->columnSpan(6),

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->icon('heroicon-o-calendar')
                                    ->columnSpan(6),
                            ]),

                    ])
                    ->modalWidth('2xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
