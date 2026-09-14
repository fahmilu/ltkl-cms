<?php

namespace App\Filament\Resources\Secretariats;

use App\Filament\Resources\Secretariats\Pages\CreateSecretariat;
use App\Filament\Resources\Secretariats\Pages\EditSecretariat;
use App\Filament\Resources\Secretariats\Pages\ListSecretariats;
use App\Filament\Resources\Secretariats\Schemas\SecretariatForm;
use App\Filament\Resources\Secretariats\Tables\SecretariatsTable;
use App\Models\Secretariat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SecretariatResource extends Resource
{
    protected static ?string $model = Secretariat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Secretariat';

    protected static ?string $slug = 'secretariats';

    public static function getLabel(): ?string
    {
        return 'Secretariat';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Secretariat';
    }

    public static function form(Schema $schema): Schema
    {
        return SecretariatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecretariatsTable::configure($table);
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
            'index' => ListSecretariats::route('/'),
            'create' => CreateSecretariat::route('/create'),
            'edit' => EditSecretariat::route('/{record}/edit'),
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
