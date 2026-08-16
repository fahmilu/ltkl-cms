<?php

namespace App\Filament\Resources\MasterLinks;

use App\Filament\Resources\MasterLinks\Pages\CreateMasterLink;
use App\Filament\Resources\MasterLinks\Pages\EditMasterLink;
use App\Filament\Resources\MasterLinks\Pages\ListMasterLinks;
use App\Filament\Resources\MasterLinks\Schemas\MasterLinkForm;
use App\Filament\Resources\MasterLinks\Tables\MasterLinksTable;
use App\Models\MasterLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MasterLinkResource extends Resource
{
    protected static ?string $model = MasterLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Links';

    protected static ?string $slug = 'links';

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Links';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterLinksTable::configure($table);
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
            'index' => ListMasterLinks::route('/'),
            'create' => CreateMasterLink::route('/create'),
            'edit' => EditMasterLink::route('/{record}/edit'),
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
