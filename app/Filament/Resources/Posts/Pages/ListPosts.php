<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostType;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->badge(fn() => Post::count()),
        ];

        foreach (PostType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->icon($type->getIcon())
                ->badge(fn() => Post::where('type', $type->value)->count())
                ->badgeColor($type->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', $type->value));
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
