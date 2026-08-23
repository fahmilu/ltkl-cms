<?php

namespace App\Filament\Resources\JobOpportunities\Pages;

use App\Filament\Resources\JobOpportunities\JobOpportunityResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateJobOpportunity extends CreateRecord
{
    protected static string $resource = JobOpportunityResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->icon(Heroicon::AdjustmentsVertical)->action('create'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
