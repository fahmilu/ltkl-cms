<?php

namespace App\Filament\Resources\Kabupatens\Pages;

use App\Filament\Resources\Kabupatens\KabupatenResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateKabupaten extends CreateRecord
{
    protected static string $resource = KabupatenResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->icon(Heroicon::AdjustmentsVertical)->action('create'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
