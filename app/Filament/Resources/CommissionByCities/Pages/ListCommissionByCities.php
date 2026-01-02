<?php

namespace App\Filament\Resources\CommissionByCities\Pages;

use App\Filament\Resources\CommissionByCities\CommissionByCityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommissionByCities extends ListRecords
{
    protected static string $resource = CommissionByCityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
