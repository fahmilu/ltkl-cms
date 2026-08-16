<?php

namespace App\Filament\Resources\MasterFiles\Pages;

use App\Filament\Resources\MasterFiles\MasterFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMasterFiles extends ListRecords
{
    protected static string $resource = MasterFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label('Create'),
        ];
    }
}
