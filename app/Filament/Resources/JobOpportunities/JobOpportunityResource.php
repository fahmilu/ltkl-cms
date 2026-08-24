<?php

namespace App\Filament\Resources\JobOpportunities;

use App\Filament\Resources\JobOpportunities\Pages\CreateJobOpportunity;
use App\Filament\Resources\JobOpportunities\Pages\EditJobOpportunity;
use App\Filament\Resources\JobOpportunities\Pages\ListJobOpportunities;
use App\Filament\Resources\JobOpportunities\Schemas\JobOpportunityForm;
use App\Filament\Resources\JobOpportunities\Tables\JobOpportunitiesTable;
use App\Models\JobOpportunity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class JobOpportunityResource extends Resource
{
    protected static ?string $model = JobOpportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $recordTitleAttribute = 'title_id';

    protected static string|UnitEnum|null $navigationGroup = 'Masters';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Job Opportunities';

    protected static ?string $slug = 'job-opportunities';

    public static function getLabel(): ?string
    {
        return 'Job Opportunity';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Job Opportunities';
    }

    public static function form(Schema $schema): Schema
    {
        return JobOpportunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobOpportunitiesTable::configure($table);
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
            'index' => ListJobOpportunities::route('/'),
            'create' => CreateJobOpportunity::route('/create'),
            'edit' => EditJobOpportunity::route('/{record}/edit'),
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
