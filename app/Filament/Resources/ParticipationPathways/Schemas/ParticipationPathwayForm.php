<?php

namespace App\Filament\Resources\ParticipationPathways\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ParticipationPathwayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            Grid::make()
                ->schema([
                    Tabs::make('Tabs')
                        ->tabs([
                            self::getTabs('id'),
                            self::getTabs('en'),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpan([ // left section
                    'md' => 12,
                    'lg' => 8,
                ]),

            Grid::make()
                ->schema([
                    Section::make()
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Set as published')
                                ->onColor('primary')
                                ->offColor(null)
                                ->onIcon(Heroicon::Check)
                                ->offIcon(Heroicon::XMark)
                                ->default(true)
                                ->columnSpanFull(),
                        ])->columnSpanFull(),
                ])
                ->columnSpan([ // right section
                    'md' => 12,
                    'lg' => 4,
                ]),
        ])->columns(12);
    }

    private static function getTabs($lang_code)
    {
        $suffix = $lang_code === 'en' ? '' : '_id';
        $isEnglish = $lang_code === 'en';

        return Tabs\Tab::make($isEnglish ? 'English' : 'Indonesian')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                TextInput::make('title' . $suffix)
                    ->label('Title')
                    ->placeholder('Input title...')
                    ->required()->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) use ($suffix) {
                        $set('slug' . $suffix, Str::slug($state));
                    })
                    ->columnSpan(1),

                TextInput::make('slug' . $suffix)
                    ->label('Slug')
                    ->placeholder('Input slug...')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),

                Textarea::make('description' . $suffix)
                    ->label('Description')
                    ->placeholder('Input description...')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull(),

            ])
            ->columns(2)
            ->columnSpanFull();
    }
}
