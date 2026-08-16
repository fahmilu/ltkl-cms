<?php

namespace App\Filament\Resources\ContactUs;

use App\Filament\Resources\ContactUs\Pages\ListContactUs;
use App\Filament\Resources\ContactUs\Pages\ViewContactUs;
use App\Filament\Resources\ContactUs\Schemas\ContactUsInfolist;
use App\Filament\Resources\ContactUs\Tables\ContactUsTable;
use App\Models\ContactUs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContactUsResource extends Resource
{
    protected static ?string $model = ContactUs::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static string|UnitEnum|null $navigationGroup = 'Contents';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Contact Us';

    protected static ?string $pluralModelLabel = 'Contact Us';

    protected static ?string $slug = 'contact-us';

    public static function getLabel(): ?string
    {
        return 'Contact Us';
    }

    public static function getHeading(): string
    {
        return 'Contact Us';
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactUsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactUsTable::configure($table);
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
            'index' => ListContactUs::route('/'),
            'view' => ViewContactUs::route('/{record}'),
        ];
    }
}
