<?php

namespace App\Filament\Resources\Secretariats\Tables;

use App\Enums\SecretariatLevel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SecretariatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('role_id')
                    ->label('Role (ID)')
                    ->placeholder('-')
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Role (EN)')
                    ->placeholder('-')
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('level')
                    ->label('Level')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Published')
                    ->boolean(),
            ])
            ->striped()
            ->defaultSort('sorted_at', 'asc')
            ->reorderable('sorted_at', 'asc')
            ->filters([
                SelectFilter::make('level')
                    ->label('Level')
                    ->options(SecretariatLevel::class),
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
