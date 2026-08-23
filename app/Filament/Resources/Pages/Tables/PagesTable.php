<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PagesTable
{
    /**
     * @param Table $table
     * @return Table
     * @throws Exception
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_id')
                    ->label('Title (ID)')
                    ->formatStateUsing(function ($record) {
                        $parent = $record->menu_parent_id ? $record->page_parent?->title_id : null;

                        return filled($parent) ? $parent . ' / ' . $record->title_id : $record->title_id;
                    })
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Title (EN)')
                    ->formatStateUsing(function ($record) {
                        return $record->menu_parent_id ? $record->page_parent->title . ' / ' . $record->title : $record->title;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                ToggleColumn::make('is_default')->label('Default')
                    ->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                    ->beforeStateUpdated(function (Set $set, $record) {
                        // get default on checked, get the record page & global page default count
                        $page = Page::where('id', $record->id)->where('is_default', true)->count();
                        $countPage = Page::where('is_default', true)->count();
                        if ($page == 0 && $countPage != 0) {
                            $validator = Validator::make(
                                ['is_default' => $record->is_default],
                                ['is_default' => 'unique:pages,is_default']
                            );

                            if ($validator->fails()) {
                                Notification::make()
                                    ->title('Default page found!')
                                    ->body($validator->errors()->first())
                                    ->danger()
                                    ->send();
                                throw new ValidationException($validator);
                            }
                        } else {
                            Notification::make()
                                ->title('Saved successfully')
                                ->success()
                                ->send();

                        }
                        return $record;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_active')->label('Active')->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('menu_is_external')->label('External link')->boolean()
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
            ->defaultSort('sorted_at', 'asc')
            ->reorderable('sorted_at')
            ->filters([
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
