<?php

namespace App\Filament\Resources\FeaturedHotels\Pages;

use App\Filament\Resources\FeaturedHotels\FeaturedHotelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedHotels extends ListRecords
{
    protected static string $resource = FeaturedHotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
