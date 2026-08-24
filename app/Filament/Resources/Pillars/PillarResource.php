<?php

namespace App\Filament\Resources\Pillars;

use App\Filament\Resources\Pillars\Pages\CreatePillar;
use App\Filament\Resources\Pillars\Pages\EditPillar;
use App\Filament\Resources\Pillars\Pages\ListPillars;
use App\Filament\Resources\Pillars\Schemas\PillarForm;
use App\Filament\Resources\Pillars\Tables\PillarsTable;
use App\Models\Pillar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PillarResource extends Resource
{
    protected static ?string $model = Pillar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'title_id';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pillars';

    protected static ?string $slug = 'pillars';

    public static function getLabel(): ?string
    {
        return 'Pillar';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Pillars';
    }

    public static function form(Schema $schema): Schema
    {
        return PillarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PillarsTable::configure($table);
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
            'index' => ListPillars::route('/'),
            'create' => CreatePillar::route('/create'),
            'edit' => EditPillar::route('/{record}/edit'),
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
