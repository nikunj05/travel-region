<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\CmsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCms extends EditRecord
{
    protected static string $resource = CmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
