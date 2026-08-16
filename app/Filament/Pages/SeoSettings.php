<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Inerba\DbConfig\AbstractPageSettings;

class SeoSettings extends AbstractPageSettings
{
    protected static ?string $title = 'Seo';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;
    protected static ?string $slug = 'seo-settings'; // Uncomment if you want to set a custom navigation icon
    public ?array $data = []; // Uncomment if you want to set a custom slug
    protected string $view = 'filament.pages.seo-settings';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * Provide default values.
     *
     * @return array<string, mixed>
     */
    public function getDefaultData(): array
    {
        return [];
    }

    public function content(Schema $schema): Schema
    {
        $module = 'settings';

        return $schema
            ->components([
                Section::make()
                    ->columns([
                        'sm' => 1,
                        'xl' => 12,
                    ])->schema([
                        Textarea::make('meta_description')->label('Global meta description')
                            ->placeholder('Input global meta description...')
                            ->autosize()->maxLength(160)
                            ->helperText('Every URL in your site should have a unique Meta Description, ideally less than 160 characters long.')
                            ->nullable()->columnSpan([
                                'sm' => 1,
                                'xl' => 6,
                            ]),
                        FileUpload::make('meta_image')
                            ->label('Global meta image')
                            ->openable()
                            ->imageEditor()
                            ->maxSize(5240000)->disk('public')
                            ->imageCropAspectRatio('square')
                            ->imageEditorViewportWidth('300')
                            ->imageEditorViewportHeight('100')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('150')
                            ->directory($module)->preserveFilenames()
                            ->helperText('Ideal max size are ' . config('filehelper.meta-image.max-size') . ' and dimensions are ' . config('filehelper.meta-image.dimensions') . ' pixels.')
                            ->acceptedFileTypes(config('filesystems.image_mimes'))
                            ->nullable()->columnSpan([
                                'sm' => 1,
                                'xl' => 6,
                            ]),
                        TextInput::make('ga_analytic_id')->label('Google tracker')
                            ->prefix('GTM-')
                            ->placeholder('Input google tracker ID...')
                            ->helperText('Connect with your own Google Analytics by tracking ID.')
                            ->columnSpanFull(),

                    ])
            ])
            ->statePath('data');
    }

    protected function settingName(): string
    {
        return 'seo';
    }
}
