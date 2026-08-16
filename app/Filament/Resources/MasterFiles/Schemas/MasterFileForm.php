<?php

namespace App\Filament\Resources\MasterFiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterFileForm
{
    public static function configure(Schema $schema): Schema
    {
        $module = 'master_files';

        return $schema
            ->components([
                Section::make()
                    ->columns([
                        'sm' => 1,
                        'xl' => 12,
                    ])
                    ->schema([
                        TextInput::make('filename')->placeholder('Input filename...')->required()->columnSpan([
                            'sm' => 1,
                            'xl' => 12,
                        ]),
                        FileUpload::make('file')
                            ->openable()->downloadable()
                            ->maxSize(52428800)->disk('public')
                            ->directory($module)->preserveFilenames()
                            ->helperText('File, ideally max size are 50 MB.')
                            ->acceptedFileTypes(config('filesystems.file_mimes'))
                            ->required()->columnSpan([
                                'sm' => 1,
                                'xl' => 12,
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
