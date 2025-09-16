<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TestimonialForm
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
                            ->columnSpan(6),

                        TextInput::make('location')
                            ->required()
                            ->columnSpan(6),

                        // next rows, full-width
                        FileUpload::make('photo')
                            ->required()
                            ->directory('testimonials') // stored inside storage/app/public/testimonials
                            ->maxSize(2048) // 2 MB
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable(true)
                            ->openable()
                            ->columnSpan(12),

                        Textarea::make('message')
                            ->required()
                            ->columnSpan(12),

                        TextInput::make('rating')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->columnSpan(6),

                        TextInput::make('hotel')
                            ->required()
                            ->columnSpan(6),

                        DatePicker::make('stay_date')
                            ->required()
                            ->maxDate(now())
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
