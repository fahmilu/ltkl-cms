<?php

namespace App\Filament\Resources\ParticipationPathways;

use App\Filament\Resources\ParticipationPathways\Pages\CreateParticipationPathway;
use App\Filament\Resources\ParticipationPathways\Pages\EditParticipationPathway;
use App\Filament\Resources\ParticipationPathways\Pages\ListParticipationPathways;
use App\Filament\Resources\ParticipationPathways\Schemas\ParticipationPathwayForm;
use App\Filament\Resources\ParticipationPathways\Tables\ParticipationPathwaysTable;
use App\Models\ParticipationPathway;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ParticipationPathwayResource extends Resource
{
    protected static ?string $model = ParticipationPathway::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Participation Pathways';

    protected static ?string $slug = 'participation-pathways';

    public static function getLabel(): ?string
    {
        return 'Participation Pathway';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Participation Pathways';
    }

    public static function form(Schema $schema): Schema
    {
        return ParticipationPathwayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParticipationPathwaysTable::configure($table);
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
            'index' => ListParticipationPathways::route('/'),
            'create' => CreateParticipationPathway::route('/create'),
            'edit' => EditParticipationPathway::route('/{record}/edit'),
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
