<?php

namespace App\Filament\Resources\Collections\Pages;

use App\Enums\CollectionType;
use App\Filament\Resources\Collections\CollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ManageCollections extends ManageRecords
{
    protected static string $resource = CollectionResource::class;

    public function getTabs(): array
    {
        $tabs = [];
        foreach (CollectionType::cases() as $type) {
            $tabs[$type->getLabel()] = Tab::make()->query(fn($query) => $query->where('type', $type->value));
        }
        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label('Create'),
        ];
    }
}
