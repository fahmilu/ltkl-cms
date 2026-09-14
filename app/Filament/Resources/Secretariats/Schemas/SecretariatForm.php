<?php

namespace App\Filament\Resources\Secretariats\Schemas;

use App\Enums\SecretariatLevel;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SecretariatForm
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

                    Section::make('Details')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Image')
                                ->helperText('Ideal max size are ' . config('filehelper.portrait-image.max-size') . ' and dimensions are ' . config('filehelper.portrait-image.dimensions') . ' pixels.')
                                ->image()
                                ->removeUploadedFileButtonPosition('bottom')
                                ->directory('secretariats')
                                ->disk('public')
                                ->visibility('public')
                                ->nullable()
                                ->columnSpanFull(),
                            TextInput::make('name')
                                ->label('Name')
                                ->placeholder('Input name...')
                                ->required()
                                ->columnSpanFull(),
                            Select::make('level')
                                ->label('Level')
                                ->options(SecretariatLevel::class)
                                ->default(SecretariatLevel::STAFF->value)
                                ->selectablePlaceholder(false)
                                ->native(false)
                                ->required()
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
                TextInput::make('role' . $suffix)
                    ->label('Role')
                    ->placeholder('Input role...')
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }
}
