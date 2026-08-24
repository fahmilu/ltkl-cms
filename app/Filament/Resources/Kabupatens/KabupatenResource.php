<?php

namespace App\Filament\Resources\Kabupatens;

use App\Filament\Resources\Kabupatens\Pages\CreateKabupaten;
use App\Filament\Resources\Kabupatens\Pages\EditKabupaten;
use App\Filament\Resources\Kabupatens\Pages\ListKabupatens;
use App\Filament\Resources\Kabupatens\Schemas\KabupatenForm;
use App\Filament\Resources\Kabupatens\Tables\KabupatensTable;
use App\Models\Kabupaten;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KabupatenResource extends Resource
{
    protected static ?string $model = Kabupaten::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'title_id';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Kabupaten';

    protected static ?string $slug = 'kabupaten';

    public static function getLabel(): ?string
    {
        return 'Kabupaten';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Kabupaten';
    }

    public static function form(Schema $schema): Schema
    {
        return KabupatenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KabupatensTable::configure($table);
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
            'index' => ListKabupatens::route('/'),
            'create' => CreateKabupaten::route('/create'),
            'edit' => EditKabupaten::route('/{record}/edit'),
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
