<?php

namespace App\Filament\Resources\MarkupHotels\Pages;

use App\Filament\Resources\MarkupHotels\MarkupHotelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarkupHotel extends EditRecord
{
    protected static string $resource = MarkupHotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
