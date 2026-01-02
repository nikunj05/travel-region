<?php

namespace App\Filament\Resources\CommissionByCities\Pages;

use App\Filament\Resources\CommissionByCities\CommissionByCityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommissionByCity extends EditRecord
{
    protected static string $resource = CommissionByCityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
