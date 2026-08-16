<?php

namespace App\Filament\Resources\MasterLinks\Pages;

use App\Filament\Resources\MasterLinks\MasterLinkResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateMasterLink extends CreateRecord
{
    protected static string $resource = MasterLinkResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->action('create')->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
