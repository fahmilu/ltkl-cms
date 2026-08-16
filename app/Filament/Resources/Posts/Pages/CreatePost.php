<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    /**
     * @return array|Action[]|ActionGroup[]
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->action('create')->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
