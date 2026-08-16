<?php

namespace App\Filament\Resources\ParticipationPathways\Pages;

use App\Filament\Resources\ParticipationPathways\ParticipationPathwayResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateParticipationPathway extends CreateRecord
{
    protected static string $resource = ParticipationPathwayResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->icon(Heroicon::AdjustmentsVertical)->action('create'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
