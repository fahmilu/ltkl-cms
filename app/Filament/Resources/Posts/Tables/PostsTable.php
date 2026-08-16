<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('is_featured')->label('Featured')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('is_external_url')->label('External Link')->trueIcon(Heroicon::OutlinedCheckCircle)->falseIcon(false)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->defaultSort('published_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->color('gray'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make()->color('gray'),
                ReplicateAction::make()->label('Copy')->color('success')
                    ->modalWidth(Width::ExtraLarge)
                    ->modalHeading('Are you sure to copy & paste this data?')
                    ->modalButton('Paste')
                    ->modalIcon(Heroicon::DocumentDuplicate)
                    ->beforeReplicaSaved(function (Model $replica): void {
                        $replica->title = $replica->title . ' | ' . Str::uuid();
                        $replica->slug = $replica->slug . '-' . Str::uuid();
                        $replica->save();
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Copied')
                            ->body('The data has been copied successfully.'),
                    )
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
