<?php

namespace App\Filament\Resources\JoinFormSubmissions;

use App\Filament\Resources\JoinFormSubmissions\Pages\ListJoinFormSubmissions;
use App\Filament\Resources\JoinFormSubmissions\Pages\ViewJoinFormSubmission;
use App\Filament\Resources\JoinFormSubmissions\Schemas\JoinFormSubmissionInfolist;
use App\Filament\Resources\JoinFormSubmissions\Tables\JoinFormSubmissionsTable;
use App\Models\JoinFormSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JoinFormSubmissionResource extends Resource
{
    protected static ?string $model = JoinFormSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Contents';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Join Form Submission';

    protected static ?string $pluralModelLabel = 'Join Form Submissions';

    protected static ?string $slug = 'join-form-submissions';

    public static function getLabel(): ?string
    {
        return 'Join Form Submission';
    }

    public static function getHeading(): string
    {
        return 'Join Form Submissions';
    }

    public static function infolist(Schema $schema): Schema
    {
        return JoinFormSubmissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JoinFormSubmissionsTable::configure($table);
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
            'index' => ListJoinFormSubmissions::route('/'),
            'view' => ViewJoinFormSubmission::route('/{record}'),
        ];
    }
}
