<?php

namespace App\Filament\Resources\JoinFormSubmissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JoinFormSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Full name'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable()
                            ->copyMessage('Email address copied')
                            ->copyMessageDuration(1500),
                        TextEntry::make('organization')
                            ->label('Organization')
                            ->placeholder('Not provided'),
                        TextEntry::make('phone')
                            ->label('Phone')
                            ->copyable()
                            ->copyMessage('Phone number copied')
                            ->copyMessageDuration(1500)
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2),
                Section::make('Participation')
                    ->schema([
                        TextEntry::make('participationPathway.title')
                            ->label('Participation pathway')
                            ->badge()
                            ->placeholder('Not provided'),
                        TextEntry::make('message')
                            ->label('Interest')
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
