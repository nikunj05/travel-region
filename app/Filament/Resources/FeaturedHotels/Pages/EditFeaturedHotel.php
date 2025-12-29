<?php

namespace App\Filament\Resources\FeaturedHotels\Pages;

use App\Filament\Resources\FeaturedHotels\FeaturedHotelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedHotel extends EditRecord
{
    protected static string $resource = FeaturedHotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
