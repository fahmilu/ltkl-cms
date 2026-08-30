<?php

namespace App\Filament\Resources\JoinFormSubmissions\Tables;

use App\Models\ParticipationPathway;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JoinFormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organization')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('region')
                    ->label('Kabupaten / region')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('participationPathway.title')
                    ->label('Participation pathway')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('participation_pathway_id')
                    ->label('Participation pathway')
                    ->options(fn (): array => ParticipationPathway::orderBy('sorted_at')->pluck('title', 'id')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
