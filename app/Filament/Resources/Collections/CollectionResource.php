<?php

namespace App\Filament\Resources\Collections;

use App\Enums\CollectionType;
use App\Filament\Resources\Collections\Pages\ManageCollections;
use App\Models\Collection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title_id';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $module = 'collections';

        return $schema
            ->components([
                Grid::make()->columns([
                    'sm' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Grid::make()
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 4,
                            ])
                            ->schema([
                                FileUpload::make('image')
                                    ->openable()
                                    ->maxSize(5240000)->disk('public')
                                    ->imageCropAspectRatio('square')
                                    ->imageEditorViewportWidth('300')
                                    ->imageEditorViewportHeight('300')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300')
                                    ->directory($module)->preserveFilenames()
                                    ->helperText('Ideal max size are ' . config('filehelper.single-image.max-size') . ' and dimensions are ' . config('filehelper.single-image.dimensions') . ' pixels.')
                                    ->acceptedFileTypes(config('filesystems.image_mimes'))
                                    ->nullable()->columnSpanFull(),
                                Select::make('type')
                                    ->options(CollectionType::class)
                                    ->native(false)->required()->columnSpanFull(),

                            ]),
                        Grid::make()
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 8,
                            ])
                            ->columns(2)
                            ->schema([
                                TextInput::make('title_id')
                                    ->placeholder('Input Indonesian title...')
                                    ->required()->live(onBlur: true)
                                    ->label('Title (Indonesian)')
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug_id', Str::slug($state));
                                    })->columnSpan(1),
                                TextInput::make('title')
                                    ->placeholder('Input title...')
                                    ->required()->live(onBlur: true)
                                    ->label('Title (English)')
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
                                    })->columnSpan(1),
                                TextInput::make('slug_id')->placeholder('Input Indonesian slug...')->required()->unique(ignoreRecord: true)->label('Slug (Indonesian)')->columnSpan(1),
                                TextInput::make('slug')->placeholder('Input slug...')->required()->unique(ignoreRecord: true)->label('Slug (English)')->columnSpan(1),
                                Textarea::make('short_description_id')
                                    ->label('Short description (Indonesian)')
                                    ->placeholder('Input Indonesian short description...')
                                    ->rows(3)
                                    ->nullable()
                                    ->columnSpan(1),
                                Textarea::make('short_description')
                                    ->label('Short description (English)')
                                    ->placeholder('Input short description...')
                                    ->rows(3)
                                    ->nullable()
                                    ->columnSpan(1),
                                Textarea::make('content_id')
                                    ->label('Content (Indonesian)')
                                    ->placeholder('Input Indonesian content...')
                                    ->rows(6)
                                    ->nullable()
                                    ->columnSpan(1),
                                Textarea::make('content')
                                    ->label('Content (English)')
                                    ->placeholder('Input content...')
                                    ->rows(6)
                                    ->nullable()
                                    ->columnSpan(1),
                            ]),
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
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
                ImageColumn::make('image')->disk('public')->circular()->imageHeight(50),
            ])
            ->striped()
            ->defaultSort('sorted_at', 'asc')
            ->reorderable('sorted_at', 'asc')
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

    public static function getPages(): array
    {
        return [
            'index' => ManageCollections::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }


}
