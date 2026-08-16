<?php

namespace App\Filament\Resources\MasterFiles\Pages;

use App\Filament\Resources\MasterFiles\MasterFileResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateMasterFile extends CreateRecord
{
    protected static string $resource = MasterFileResource::class;
    
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->action('create')->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
