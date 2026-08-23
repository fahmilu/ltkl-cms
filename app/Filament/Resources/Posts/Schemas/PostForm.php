<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\ExternalType;
use App\Enums\PostType;
use App\Filament\Resources\ComponentBuilders\ComponentBuilderResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        $module = 'posts';

        $typeLabel = [];
        $typeIcon = [];
        $typeColor = [];
        foreach (ExternalType::cases() as $externalType) {
            $typeLabel[$externalType->value] = $externalType->getLabel();
            $typeIcon[$externalType->value] = $externalType->getIcon();
            $typeColor[$externalType->value] = $externalType->getColor();
        }

        return $schema->schema([

            Grid::make()
                ->schema([
                    Tabs::make('Tabs')
                        ->tabs([
                            self::getTabs($module, 'id'),
                            self::getTabs($module, 'en'),
                        ])
                        ->columnSpanFull(),

                    // Type specific details that are shared across languages
                    self::getEventSection(),
                    self::getVideoSection(),
                    self::getLibrarySection($module),
                    self::getMediaCoverageSection($module),
                    self::getArticleSection(),
                ])
                ->columnSpan([ // left section
                    'md' => 12,
                    'lg' => 8,

                ]),
            Grid::make()
                ->schema([

                    // Status
                    Section::make()
                        ->schema([
                            Select::make('type')->label('Type')
                                ->options(PostType::class)
                                ->default(PostType::ARTICLE->value)
                                ->native(false)
                                ->live()
                                ->required()
                                ->columnSpanFull(),

                            FileUpload::make('image')
                                ->label('Image')
                                // ->placeholder('Input image...')
                                ->helperText('Ideal max size are ' . config('filehelper.post-image.max-size') . ' and dimensions are ' . config('filehelper.post-image.dimensions') . ' pixels.')
                                ->openable()
                                ->image()
                                ->maxSize(5240000)->disk('public')
                                ->removeUploadedFileButtonPosition('bottom')
                                ->directory($module)->preserveFilenames()
                                ->columnSpanFull(),

                            Select::make('post_tags')->label('Tags')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->relationship(
                                    name: 'post_tags',
                                    titleAttribute: 'title'
                                )->columnSpanFull(),

                            Select::make('post_topics')->label('Categories')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->relationship(
                                    name: 'post_topics',
                                    titleAttribute: 'title'
                                )->columnSpanFull(),

                            Select::make('post_kabupatens')->label('Kabupaten')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->relationship(
                                    name: 'post_kabupatens',
                                    titleAttribute: 'title'
                                )->columnSpanFull(),

                            DateTimePicker::make('published_at')->placeholder('Set publish date...')->required()->native(false)->prefixIcon(Heroicon::OutlinedCalendar)->columnSpanFull(),

                            Toggle::make('is_active')
                                ->label('Set as published')
                                ->onColor('primary')
                                ->offColor(null)
                                ->onIcon(Heroicon::Check)
                                ->offIcon(Heroicon::XMark)
                                ->default(true)
                                ->columnSpanFull(),

                            Toggle::make('is_featured')->label('Set as featured')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                                ->columnSpanFull(),

                            Toggle::make('is_external_url')->label('Set as external')->onColor('warning')->offColor(null)->onIcon(Heroicon::Check)
                                ->live()
                                ->columnSpanFull(),

                            ToggleButtons::make('external_type')->label('External Type')
                                ->visible(fn(Get $get) => $get('is_external_url') == true)
                                ->inline(true)
                                ->live()
                                ->options($typeLabel)
                                ->icons($typeIcon)
                                ->colors($typeColor)
                                ->columnSpanFull(),

                            TextInput::make('external_url')
                                ->visible(fn(Get $get) => $get('external_type') == '_link' && $get('is_external_url') == true)
                                ->placeholder('https://...')
                                ->nullable()->columnSpanFull(),

                            FileUpload::make('external_file')
                                ->visible(fn(Get $get) => $get('external_type') == '_file' && $get('is_external_url') == true)
                                ->openable()->downloadable()
                                ->maxSize(52428800)->disk('public')
                                ->directory($module)->preserveFilenames()
                                ->helperText('File, ideally max size are 50 MB.')
                                ->acceptedFileTypes(config('filesystems.file_mimes'))
                                ->required()->columnSpanFull(),
                        ])->columns(3)->columnSpanFull(),
                ])
                ->columnSpan([ // right section
                    'md' => 12,
                    'lg' => 4,
                ]),
        ])->columns(12);
    }

    private static function getTabs($module, $lang_code)
    {
        $suffix = $lang_code === 'en' ? '' : '_id';
        return Tabs\Tab::make($lang_code === 'en' ? 'English' : 'Indonesian')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                TextInput::make('title' . $suffix)
                    ->placeholder('Input title...')
                    ->required()->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) use ($suffix) {
                        $set('slug' . $suffix, Str::slug($state));
                    })
                    ->columnSpan(1),
                TextInput::make('slug' . $suffix)
                    ->placeholder('Input slug...')->required()->unique(column: 'slug' . $suffix)->columnSpan(1),

                // Type specific fields that need their own translation
                self::getEventFieldset($suffix),
                self::getLibraryFieldset($module, $suffix),

                ComponentBuilderResource::defaultComponents($module, $lang_code)->label('Contents')
                    ->visible(fn(Get $get) => $get('is_external_url') == false)
                    // ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * The type select is cast to a PostType enum on the model, so the form state
     * can hold either the enum or its raw string value depending on where it came from.
     */
    private static function isType(Get $get, PostType $type): bool
    {
        $state = $get('type');

        return ($state instanceof PostType ? $state->value : $state) === $type->value;
    }

    private static function getEventSection(): Section
    {
        return Section::make('Event Details')
            ->description('Schedule and registration info shown on the event header.')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->visible(fn(Get $get) => self::isType($get, PostType::EVENT))
            ->schema([
                DatePicker::make('type_data.start_date')
                    ->label('Start date')
                    ->placeholder('Set start date...')
                    ->native(false)
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->required(),

                DatePicker::make('type_data.end_date')
                    ->label('End date')
                    ->placeholder('Leave empty for a single day event')
                    ->native(false)
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->afterOrEqual('type_data.start_date')
                    ->nullable(),

                TimePicker::make('type_data.start_time')
                    ->label('Start time')
                    ->seconds(false)
                    ->native(false)
                    ->nullable(),

                TimePicker::make('type_data.end_time')
                    ->label('End time')
                    ->seconds(false)
                    ->native(false)
                    ->nullable(),

                Select::make('type_data.timezone')
                    ->label('Timezone')
                    ->options([
                        'WIB' => 'WIB',
                        'WITA' => 'WITA',
                        'WIT' => 'WIT',
                    ])
                    ->default('WIB')
                    ->native(false)
                    ->nullable(),

                TextInput::make('type_data.register_url')
                    ->label('Registration URL')
                    ->placeholder('https://...')
                    ->url()
                    ->helperText('Target of the "Daftar sekarang" button. Leave empty to hide it.')
                    ->nullable(),

                Toggle::make('type_data.is_public')
                    ->label('Open to public')
                    ->helperText('Shows the "Terbuka untuk umum" badge.')
                    ->onColor('primary')
                    ->offColor(null)
                    ->onIcon(Heroicon::Check)
                    ->default(true),

                Toggle::make('type_data.is_registration_open')
                    ->label('Registration open')
                    ->helperText('Shows the "Pendaftaran dibuka" badge.')
                    ->onColor('primary')
                    ->offColor(null)
                    ->onIcon(Heroicon::Check)
                    ->default(true),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    private static function getEventFieldset($suffix): Fieldset
    {
        return Fieldset::make('Event Details')
            ->visible(fn(Get $get) => self::isType($get, PostType::EVENT))
            ->schema([
                TextInput::make('type_data.location' . $suffix)
                    ->label('Location')
                    ->placeholder('Alun-alun Siak, Riau')
                    ->nullable()
                    ->columnSpan(1),

                TextInput::make('type_data.cost' . $suffix)
                    ->label('Cost')
                    ->placeholder('Gratis · perlu daftar')
                    ->nullable()
                    ->columnSpan(1),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    private static function getVideoSection(): Section
    {
        return Section::make('Video Details')
            ->description('Embed and playback info shown on the video header.')
            ->icon(Heroicon::OutlinedPlayCircle)
            ->visible(fn(Get $get) => self::isType($get, PostType::VIDEO))
            ->schema([
                TextInput::make('type_data.video_url')
                    ->label('Video URL')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText('YouTube or Vimeo URL, embedded as a 16:9 player.')
                    ->url()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('type_data.duration')
                    ->label('Duration')
                    ->placeholder('9:12')
                    ->helperText('Format mm:ss or hh:mm:ss.')
                    ->nullable(),

                Select::make('type_data.subtitles')
                    ->label('Subtitles')
                    ->options([
                        'id' => 'ID',
                        'en' => 'EN',
                    ])
                    ->multiple()
                    ->native(false)
                    ->nullable(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    private static function getLibrarySection($module): Section
    {
        return Section::make('Library Details')
            ->description('Document metadata shown next to the download buttons.')
            ->icon(Heroicon::OutlinedBookOpen)
            ->visible(fn(Get $get) => self::isType($get, PostType::LIBRARY))
            ->schema([
                TextInput::make('type_data.pages')
                    ->label('Page count')
                    ->placeholder('116')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),

                TextInput::make('type_data.license')
                    ->label('License')
                    ->placeholder('CC BY 4.0')
                    ->nullable(),

                FileUpload::make('type_data.cover')
                    ->label('Cover')
                    // ->placeholder('Input cover...')
                    ->helperText('Document cover. Falls back to the post image when empty.')
                    ->openable()
                    ->image()
                    ->maxSize(5240000)->disk('public')
                    ->removeUploadedFileButtonPosition('bottom')
                    ->directory($module)->preserveFilenames()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    private static function getLibraryFieldset($module, $suffix): Fieldset
    {
        return Fieldset::make('Library Details')
            ->visible(fn(Get $get) => self::isType($get, PostType::LIBRARY))
            ->schema([
                FileUpload::make('type_data.file' . $suffix)
                    ->label('Document')
                    ->helperText('File, ideally max size are 50 MB.')
                    ->openable()->downloadable()
                    ->maxSize(52428800)->disk('public')
                    ->directory($module)->preserveFilenames()
                    ->acceptedFileTypes(config('filesystems.file_mimes'))
                    ->nullable()
                    ->columnSpanFull(),

                TextInput::make('type_data.access_note' . $suffix)
                    ->label('Access note')
                    ->placeholder('Gratis · tanpa pendaftaran')
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->columnSpanFull();
    }

    private static function getMediaCoverageSection($module): Section
    {
        return Section::make('Media Coverage Details')
            ->description('Publisher credit shown in the "Baca di sumber" card.')
            ->icon(Heroicon::OutlinedMegaphone)
            ->visible(fn(Get $get) => self::isType($get, PostType::MEDIA_COVERAGE))
            ->schema([
                TextInput::make('type_data.publisher_name')
                    ->label('Publisher name')
                    ->placeholder('Nama Media Penerbit')
                    ->required(),

                TextInput::make('type_data.journalist')
                    ->label('Journalist')
                    ->placeholder('Nama Jurnalis')
                    ->nullable(),

                DatePicker::make('type_data.source_published_at')
                    ->label('Published at source')
                    ->placeholder('Set source publish date...')
                    ->native(false)
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->nullable(),

                TextInput::make('type_data.source_url')
                    ->label('Source URL')
                    ->placeholder('https://...')
                    ->helperText('Target of the "Baca di sumber" button.')
                    ->url()
                    ->required(),

                FileUpload::make('type_data.publisher_logo')
                    ->label('Publisher logo')
                    // ->placeholder('Input logo...')
                    ->openable()
                    ->image()
                    ->maxSize(2048000)->disk('public')
                    ->removeUploadedFileButtonPosition('bottom')
                    ->directory($module)->preserveFilenames()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    private static function getArticleSection(): Section
    {
        return Section::make('Article Details')
            ->description('Byline shown under the article title.')
            ->icon(Heroicon::OutlinedDocumentText)
            ->visible(fn(Get $get) => self::isType($get, PostType::ARTICLE))
            ->schema([
                TextInput::make('type_data.author')
                    ->label('Author')
                    ->placeholder('Tim Narasi LTKL')
                    ->nullable(),

                TextInput::make('type_data.read_time')
                    ->label('Read time (minutes)')
                    ->placeholder('7')
                    ->helperText('Leave empty to calculate it from the content.')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }
}
