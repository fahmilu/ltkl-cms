<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class UserForm
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
                        TextInput::make('name')->placeholder('Input name...')
                            ->required()->columnSpan([
                                'sm' => 1,
                                'xl' => 6,
                            ]),
                        TextInput::make('email')->placeholder('Input email...')
                            ->unique(ignoreRecord: true)
                            ->email()
                            ->required()->columnSpan([
                                'sm' => 1,
                                'xl' => 6,
                            ]),
                        TextInput::make('password')->placeholder('Input password...')
                            ->password()->revealable()->autocomplete('new-password')
                            ->minLength(8)
                            ->confirmed()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))->columnSpan([
                                'sm' => 1,
                                'xl' => 6,
                            ]),
                        TextInput::make('password_confirmation')->placeholder('Input password confirmation...')
                            ->password()->revealable()->autocomplete('new-password')
                            ->required(fn($get) => filled($get('password')))
                            ->dehydrated(false)->columnSpan([
                                'sm' => 1,
                                'xl' => 6,
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
