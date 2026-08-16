<?php

namespace App\Filament\Resources\Kabupatens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class KabupatensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Kabupaten images are landscape photos, so no circular or square
                // crop here — the full frame is shown at a fixed row height.
                ImageColumn::make('image')->label('Image')->disk('public')->imageHeight(50),
                TextColumn::make('title')
                    ->label('Title (EN)')
                    ->searchable(),
                TextColumn::make('title_id')
                    ->label('Title (ID)')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('pillars_count')
                    ->label('Pillars')
                    ->counts('pillars')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label('Published')
                    ->boolean(),
            ])
            ->striped()
            ->defaultSort('sorted_at', 'asc')
            ->reorderable('sorted_at', 'asc')
            ->filters([
                TernaryFilter::make('is_active')->label('Published'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->color('gray'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make()->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
