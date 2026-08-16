<?php

namespace App\Filament\Resources\ParticipationPathways\Pages;

use App\Filament\Resources\ParticipationPathways\ParticipationPathwayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListParticipationPathways extends ListRecords
{
    protected static string $resource = ParticipationPathwayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label('Create'),
        ];
    }
}
