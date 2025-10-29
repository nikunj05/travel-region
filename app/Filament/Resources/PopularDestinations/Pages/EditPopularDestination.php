<?php

namespace App\Filament\Resources\PopularDestinations\Pages;

use App\Filament\Resources\PopularDestinations\PopularDestinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPopularDestination extends EditRecord
{
    protected static string $resource = PopularDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
