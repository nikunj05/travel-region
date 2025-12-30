<?php

namespace App\Filament\Resources\MarkupHotels\Pages;

use App\Filament\Resources\MarkupHotels\MarkupHotelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarkupHotels extends ListRecords
{
    protected static string $resource = MarkupHotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
