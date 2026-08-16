<?php

namespace App\Filament\Resources\Pillars\Pages;

use App\Filament\Resources\Pillars\PillarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPillars extends ListRecords
{
    protected static string $resource = PillarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label('Create'),
        ];
    }
}
