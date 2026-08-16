<?php

namespace App\Filament\Resources\Pillars\Pages;

use App\Filament\Resources\Pillars\PillarResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreatePillar extends CreateRecord
{
    protected static string $resource = PillarResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->icon(Heroicon::AdjustmentsVertical)->action('create'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
