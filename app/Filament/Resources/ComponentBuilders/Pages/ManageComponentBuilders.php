<?php

namespace App\Filament\Resources\ComponentBuilders\Pages;

use App\Filament\Resources\ComponentBuilders\ComponentBuilderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageComponentBuilders extends ManageRecords
{
    protected static string $resource = ComponentBuilderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
