<?php

namespace App\Filament\Resources\Secretariats\Pages;

use App\Filament\Resources\Secretariats\SecretariatResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateSecretariat extends CreateRecord
{
    protected static string $resource = SecretariatResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->icon(Heroicon::AdjustmentsVertical)->action('create'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
