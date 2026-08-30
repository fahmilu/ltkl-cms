<?php

namespace App\Filament\Pages;

use App\Enums\SocialMedia;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Inerba\DbConfig\AbstractPageSettings;
use Inerba\DbConfig\DbConfig;

class WebsiteSettings extends AbstractPageSettings
{
    protected static ?string $title = 'Website';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;
    protected static ?string $slug = 'website-settings';
    public ?array $data = [];
    protected string $view = 'filament.pages.website-settings';

    /**
     * Meta data fields edited from the "Meta Data" tab but stored in the `seo` group,
     * so they stay the single source of truth for /api/settings?group=seo.
     *
     * @var array<int, string>
     */
    protected const SEO_KEYS = [
        'meta_description',
        'meta_description_id',
        'meta_image',
    ];

    /**
     * Every upload on this page, guarded on save against a storage disk that is
     * out of sync with the database.
     *
     * @var array<int, string>
     */
    protected const UPLOAD_KEYS = [
        'favicon',
        'main_logo',
        'footer_logo',
        'meta_image',
    ];

    /**
     * Provide default values.
     *
     * @return array<string, mixed>
     */
    public function getDefaultData(): array
    {
        return [
            'multi_language' => true,
        ];
    }

    /**
     * Load the `website` group as usual, then pull the meta data fields in from the `seo` group.
     */
    public function mount(): void
    {
        parent::mount();

        $seo = DbConfig::getGroup('seo') ?? [];

        foreach (self::SEO_KEYS as $key) {
            $this->data[$key] = $seo[$key] ?? null;
        }

        $this->form->fill($this->data);
    }

    /**
     * Persist the form, routing the meta data fields back to the `seo` group.
     */
    public function save(): void
    {
        // Support both $this->content and $this->form for the schema instance.
        if (! isset($this->form)) {
            $this->form = $this->content;
        }

        foreach ($this->form->getState() as $key => $value) {
            $group = in_array($key, self::SEO_KEYS, true) ? 'seo' : $this->settingName();

            if (in_array($key, self::UPLOAD_KEYS, true) && $this->uploadIsMissingLocally($group, $key, $value)) {
                continue;
            }

            DbConfig::set($group . '.' . $key, $value);
        }

        Notification::make()
            ->success()
            ->title(__('db-config::db-config.saved_title'))
            ->body(__('db-config::db-config.saved_body'))
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main site
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Main')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                Grid::make()
                                    ->columns([
                                        'sm' => 1,
                                        'xl' => 12,
                                    ])
                                    ->schema([
                                        TextInput::make('site_name')->placeholder('Input site name...')->columnSpan([
                                            'sm' => 1,
                                            'xl' => 6,
                                        ]),
                                        ToggleButtons::make('site_online')->label('Site online?')
                                            ->inline(false)
                                            ->inline(true)
                                            ->options([
                                                'publish' => 'Online',
                                                'draft' => 'Draft',
                                            ])
                                            ->colors([
                                                'publish' => 'success',
                                                'draft' => 'danger',
                                            ])
                                            ->icons([
                                                'publish' => Heroicon::OutlinedCheck,
                                                'draft' => Heroicon::OutlinedMinusCircle,
                                            ])
                                            ->helperText('If you activate this, the website will be on maintenance mode in frontend')
                                            ->columnSpan([
                                                'sm' => 1,
                                                'xl' => 6,
                                            ]),
                                        FileUpload::make('favicon')
                                        ->label('Favicon')
                                        // ->placeholder('Input favicon...')
                                        ->helperText('Ideal max size are ' . config('filehelper.favicon.max-size') . ' and dimensions are ' . config('filehelper.favicon.dimensions') . ' pixels.')
                                        ->image()->directory('favicon')->disk('public')->visibility('public')->columnSpan([
                                            'sm' => 1,
                                            'xl' => 6,
                                        ]),
                                        FileUpload::make('main_logo')
                                            ->label('Main logo')
                                            // ->placeholder('Input main logo...')
                                            ->helperText('SVG, PNG or WEBP. Ideal max size are ' . config('filehelper.main-logo.max-size') . ' and dimensions are ' . config('filehelper.main-logo.dimensions') . ' pixels.')
                                            ->image()
                                            ->acceptedFileTypes(config('filesystems.image_mimes'))
                                            ->openable()
                                            ->directory('logo')->disk('public')->visibility('public')
                                            ->columnSpan([
                                                'sm' => 1,
                                                'xl' => 6,
                                            ]),
                                        Toggle::make('multi_language')
                                            ->label('Multi language')
                                            ->inline(false)
                                            ->onColor('primary')
                                            ->offColor(null)
                                            ->onIcon(Heroicon::Check)
                                            ->offIcon(Heroicon::XMark)
                                            ->default(true)
                                            ->helperText('Turn off to hide the language switcher in frontend.')
                                            ->columnSpan([
                                                'sm' => 1,
                                                'xl' => 6,
                                            ]),
                                    ])->columnSpanFull(),
                                // Footer
                                Section::make('Footer')
                                    ->schema([
                                        FileUpload::make('footer_logo')
                                            ->label('Footer logo')
                                            ->placeholder('Input footer logo...')
                                            ->helperText('SVG, PNG or WEBP. Ideal max size are ' . config('filehelper.footer-logo.max-size') . ' and dimensions are ' . config('filehelper.footer-logo.dimensions') . ' pixels.')
                                            ->image()
                                            ->acceptedFileTypes(config('filesystems.image_mimes'))
                                            ->openable()
                                            ->directory('logo')->disk('public')->visibility('public')
                                            ->columnSpanFull(),
                                        Textarea::make('footer_description_id')
                                            ->label('Footer description (Indonesian)')
                                            ->placeholder('Input Indonesian footer description...')
                                            ->autosize()
                                            ->nullable()
                                            ->columnSpan(1),
                                        Textarea::make('footer_description')
                                            ->label('Footer description (English)')
                                            ->placeholder('Input footer description...')
                                            ->autosize()
                                            ->nullable()
                                            ->columnSpan(1),
                                    ])->columns(2)->columnSpanFull(),
                                // Footer call to action
                                Section::make('Footer CTA')
                                    ->description('Closing call to action above the footer. The link is shared by both languages.')
                                    ->schema([
                                        TextInput::make('footer_cta.title_id')
                                            ->label('Title (Indonesian)')
                                            ->placeholder('Input Indonesian title...')
                                            ->nullable()
                                            ->columnSpan(1),
                                        TextInput::make('footer_cta.title')
                                            ->label('Title (English)')
                                            ->placeholder('Input title...')
                                            ->nullable()
                                            ->columnSpan(1),
                                        Textarea::make('footer_cta.description_id')
                                            ->label('Description (Indonesian)')
                                            ->placeholder('Input Indonesian description...')
                                            ->autosize()
                                            ->nullable()
                                            ->columnSpan(1),
                                        Textarea::make('footer_cta.description')
                                            ->label('Description (English)')
                                            ->placeholder('Input description...')
                                            ->autosize()
                                            ->nullable()
                                            ->columnSpan(1),
                                        TextInput::make('footer_cta.button_text_id')
                                            ->label('Button text (Indonesian)')
                                            ->placeholder('Gabung sekarang')
                                            ->nullable()
                                            ->columnSpan(1),
                                        TextInput::make('footer_cta.button_text')
                                            ->label('Button text (English)')
                                            ->placeholder('Join now')
                                            ->nullable()
                                            ->columnSpan(1),
                                        TextInput::make('footer_cta.button_url')
                                            ->label('Button URL')
                                            ->placeholder('https://...')
                                            ->url()
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ])->columns(2)->columnSpanFull(),
                                // Social media
                                Section::make()
                                    ->schema([
                                        Repeater::make('social_media')
                                            ->schema([
                                                Select::make('site')
                                                    ->native(false)
                                                    ->options(SocialMedia::class)
                                                    ->required(),
                                                TextInput::make('url')->placeholder('https://...')->url()->prefixIcon(Heroicon::OutlinedLink)->required()
                                            ])->columns(2),
                                    ])->columnSpanFull(),
                            ]),
                        Tabs\Tab::make('Meta Data')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema([
                                Grid::make()
                                    ->columns([
                                        'sm' => 1,
                                        'xl' => 12,
                                    ])
                                    ->schema([
                                        Textarea::make('meta_description_id')->label('Global meta description (Indonesian)')
                                            ->placeholder('Input Indonesian global meta description...')
                                            ->autosize()->maxLength(160)
                                            ->helperText('Every URL in your site should have a unique Meta Description, ideally less than 160 characters long.')
                                            ->nullable()->columnSpan([
                                                'sm' => 1,
                                                'xl' => 6,
                                            ]),
                                        Textarea::make('meta_description')->label('Global meta description (English)')
                                            ->placeholder('Input global meta description...')
                                            ->autosize()->maxLength(160)
                                            ->helperText('Every URL in your site should have a unique Meta Description, ideally less than 160 characters long.')
                                            ->nullable()->columnSpan([
                                                'sm' => 1,
                                                'xl' => 6,
                                            ]),
                                        FileUpload::make('meta_image')
                                            ->label('Global meta image')
                                            // ->placeholder('Input meta image...')
                                            ->openable()
                                            ->image()
                                            ->maxSize(5240000)->disk('public')->visibility('public')
                                            ->directory('settings')->preserveFilenames()
                                            ->helperText('Ideal max size are ' . config('filehelper.meta-image.max-size') . ' and dimensions are ' . config('filehelper.meta-image.dimensions') . ' pixels.')
                                            ->acceptedFileTypes(config('filesystems.image_mimes'))
                                            ->nullable()->columnSpan([
                                                'sm' => 1,
                                                'xl' => 6,
                                            ]),
                                    ])->columnSpanFull(),
                            ]),
                        Tabs\Tab::make('Cookie Consent')
                            ->icon(Heroicon::OutlinedArchiveBox)
                            ->schema([
                                TextInput::make('cookie_title')->placeholder('Input cookie title...'),
                                RichEditor::make('cookie_content')
                                    ->placeholder('Input cookie content...')
                                    ->helperText('Recommended max characters: ideally 180 (but you can push to 200 if you must)')
                                    ->toolbarButtons(config('editor.simple'))
                                    ->fileAttachmentsDirectory('contents')
                            ]),
                        Tabs\Tab::make('Mailchimp')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->schema([
                                TextInput::make('api_key')->placeholder('Input api key...'),
                            ]),
                        Tabs\Tab::make('Join Us')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->schema([
                                TextInput::make('join_us_email')
                                    ->label('Recipient email')
                                    ->placeholder('join@example.com')
                                    ->email()
                                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                                    ->helperText('Submissions from the join us form on the frontend are sent to this address.')
                                    ->nullable()
                                    ->columnSpanFull(),
                            ])
                    ])
            ])
            ->statePath('data');
    }

    /**
     * FileUpload drops any file that is missing from the disk, so on an environment whose
     * storage is not in sync (local, staging) an upload would come back empty and a plain
     * save of this page would wipe the stored reference. Keep it instead — the value is
     * only cleared when the file really is on disk and the user removed it.
     */
    protected function uploadIsMissingLocally(string $group, string $key, mixed $value): bool
    {
        if (filled($value)) {
            return false;
        }

        $stored = DbConfig::get($group . '.' . $key);

        return filled($stored) && ! Storage::disk('public')->exists($stored);
    }

    protected function settingName(): string
    {
        return 'website';
    }
}
