<?php

namespace App\Filament\Resources\JobOpportunities\Tables;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JobOpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_id')
                    ->label('Title (ID)')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Title (EN)')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->label('Type')
                    ->placeholder('-')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('location')
                    ->label('Location')
                    ->placeholder('-')
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('deadline_at')
                    ->label('Deadline')
                    ->placeholder('-')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Published')
                    ->boolean(),
            ])
            ->striped()
            ->defaultSort('sorted_at', 'asc')
            ->reorderable('sorted_at', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(JobStatus::class),
                SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options(EmploymentType::class),
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
