<?php

namespace App\Filament\Resources\MasterLinks\Pages;

use App\Filament\Resources\MasterLinks\MasterLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMasterLinks extends ListRecords
{
    protected static string $resource = MasterLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label('Create'),
        ];
    }
}
