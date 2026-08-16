<?php

namespace App\Filament\Resources\MasterLinks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class MasterLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns([
                        'sm' => 1,
                        'xl' => 12,
                    ])
                    ->schema([
                        TextInput::make('title')->placeholder('Input title...')->required()->columnSpan([
                            'sm' => 1,
                            'xl' => 12,
                        ]),
                        TextInput::make('url')->prefixIcon(Heroicon::OutlinedLink)->placeholder('https://...')->required()->columnSpan([
                            'sm' => 1,
                            'xl' => 12,
                        ]),
                    ])->columnSpanFull()
            ]);
    }
}
