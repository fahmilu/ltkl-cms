<?php

namespace App\Filament\Resources\MasterFiles;

use App\Filament\Resources\MasterFiles\Pages\CreateMasterFile;
use App\Filament\Resources\MasterFiles\Pages\EditMasterFile;
use App\Filament\Resources\MasterFiles\Pages\ListMasterFiles;
use App\Filament\Resources\MasterFiles\Schemas\MasterFileForm;
use App\Filament\Resources\MasterFiles\Tables\MasterFilesTable;
use App\Models\MasterFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MasterFileResource extends Resource
{
    protected static ?string $model = MasterFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $recordTitleAttribute = 'filename';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Files';

    protected static ?string $slug = 'files';
    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Files';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterFileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterFilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterFiles::route('/'),
            'create' => CreateMasterFile::route('/create'),
            'edit' => EditMasterFile::route('/{record}/edit'),
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
