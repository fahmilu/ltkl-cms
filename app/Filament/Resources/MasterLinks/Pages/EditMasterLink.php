<?php

namespace App\Filament\Resources\MasterLinks\Pages;

use App\Filament\Resources\MasterLinks\MasterLinkResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditMasterLink extends EditRecord
{
    protected static string $resource = MasterLinkResource::class;

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
