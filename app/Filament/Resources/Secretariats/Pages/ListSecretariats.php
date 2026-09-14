<?php

namespace App\Filament\Resources\Secretariats\Pages;

use App\Filament\Resources\Secretariats\SecretariatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSecretariats extends ListRecords
{
    protected static string $resource = SecretariatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label('Create'),
        ];
    }
}
