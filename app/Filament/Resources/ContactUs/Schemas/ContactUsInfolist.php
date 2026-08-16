<?php

namespace App\Filament\Resources\ContactUs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactUsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable()
                            ->copyMessage('Email address copied')
                            ->copyMessageDuration(1500),
                        TextEntry::make('affiliation')
                            ->label('Affiliation')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2),
                Section::make('Message')
                    ->schema([
                        TextEntry::make('subject')
                            ->label('Subject'),
                        TextEntry::make('message')
                            ->label('Message')
                            ->columnSpanFull()
                            ->wrap(),
                    ]),
                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Submitted At')
                            ->dateTime()
                            ->since(),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->since(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
