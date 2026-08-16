<?php

namespace App\Filament\Resources\ParticipationPathways\Pages;

use App\Filament\Resources\ParticipationPathways\ParticipationPathwayResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditParticipationPathway extends EditRecord
{
    protected static string $resource = ParticipationPathwayResource::class;

    /**
     * @return array|Action[]|ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->submit(null)->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }

    /**
     * @return array|Action[]|ActionGroup[]
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->submit(null)->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
