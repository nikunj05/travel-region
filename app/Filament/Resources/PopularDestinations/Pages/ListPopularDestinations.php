<?php

namespace App\Filament\Resources\PopularDestinations\Pages;

use App\Filament\Resources\PopularDestinations\PopularDestinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPopularDestinations extends ListRecords
{
    protected static string $resource = PopularDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
