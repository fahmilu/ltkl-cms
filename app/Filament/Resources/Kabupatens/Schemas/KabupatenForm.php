<?php

namespace App\Filament\Resources\Kabupatens\Schemas;

use App\Models\Kabupaten;
use App\Models\Pillar;
use App\Services\LocationSearch;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class KabupatenForm
{
    /**
     * Bounding box of Indonesia, used to constrain both the map and the inputs.
     */
    private const MIN_LATITUDE = -11.5;

    private const MAX_LATITUDE = 6.5;

    private const MIN_LONGITUDE = 94.5;

    private const MAX_LONGITUDE = 141.5;

    public static function configure(Schema $schema): Schema
    {
        $module = 'kabupatens';

        return $schema->schema([

            Grid::make()
                ->schema([
                    Tabs::make('Tabs')
                        ->tabs([
                            self::getTabs('id'),
                            self::getTabs('en'),
                        ])
                        ->columnSpanFull(),

                    self::getMapLocation(),
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
                            
                            FileUpload::make('image')
                                ->label('Image')
                                ->placeholder('Input image...')
                                ->helperText('Ideal max size are ' . config('filehelper.single-image.max-size') . ' and dimensions are ' . config('filehelper.single-image.dimensions') . ' pixels.')
                                ->openable()
                                ->image()
                                ->maxSize(5240000)->disk('public')
                                ->removeUploadedFileButtonPosition('bottom')
                                ->directory($module)->preserveFilenames()
                                ->acceptedFileTypes(config('filesystems.image_mimes'))
                                ->nullable()
                                ->columnSpanFull(),

                            Toggle::make('is_founding_member')
                                ->label('Founding Member')
                                ->helperText('Founding member. Shows the "Anggota Pendiri" badge.')
                                ->onColor('primary')
                                ->offColor(null)
                                ->onIcon(Heroicon::Check)
                                ->offIcon(Heroicon::XMark)
                                ->default(false)
                                ->columnSpanFull(),

                            TextInput::make('joined_year')
                                ->label('Join year')
                                ->placeholder('2017')
                                ->helperText('Shown as "Anggota sejak {year}".')
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue((int) date('Y') + 1)
                                ->nullable()
                                ->columnSpanFull(),

                            // Pillars are records, not copy, so this stays outside
                            // the language tabs.
                            Select::make('pillars')
                                ->label('Pillars')
                                ->helperText('Shown as "Pilar Terkait" on the kabupaten page.')
                                ->relationship(
                                    name: 'pillars',
                                    titleAttribute: 'title_id',
                                )
                                ->getOptionLabelFromRecordUsing(
                                    fn(Pillar $record): string => $record->title_id ?: $record->title
                                )
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->columnSpanFull(),
                        ])->columnSpanFull(),

                    self::getLandscape(),
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

        return Tabs\Tab::make($lang_code === 'en' ? 'English' : 'Indonesian')
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

                TextInput::make('role' . $suffix)
                    ->label('Role')
                    ->placeholder($lang_code === 'en' ? 'Founding member' : 'Anggota pendiri')
                    ->nullable()
                    ->columnSpanFull(),

                Textarea::make('content' . $suffix)
                    ->label('Content')
                    ->placeholder('Input content...')
                    ->rows(6)
                    ->nullable()
                    ->columnSpanFull(),

                self::getCommodities($suffix),
                self::getAchievements($suffix),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * Pin that drives the member map on the frontend. The map field itself is not
     * a database column — it is a picker whose state is written into the
     * latitude and longitude columns, and rehydrated from them when editing.
     */
    private static function getMapLocation(): Section
    {
        return Section::make('Map Location')
            ->description('Click the map to drop the pin, or type the coordinates directly.')
            ->icon(Heroicon::OutlinedMapPin)
            ->collapsible()
            ->schema([
                Select::make('location_search')
                    ->label('Search location')
                    ->placeholder('Search a place, e.g. Kabupaten Siak')
                    ->helperText('Powered by OpenStreetMap. Picking a result moves the pin — adjust it on the map afterwards if needed.')
                    ->prefixIcon(Heroicon::OutlinedMagnifyingGlass)
                    ->searchable()
                    ->searchPrompt('Type at least 3 characters...')
                    ->searchingMessage('Searching places...')
                    ->noSearchResultsMessage('No place found. Try a different spelling, or place the pin manually.')
                    ->getSearchResultsUsing(fn(string $search): array => self::searchPlaces($search))
                    // The selected option is a coordinate pair; Filament needs a label
                    // resolver for it, since the place name is not kept anywhere.
                    ->getOptionLabelUsing(fn(?string $value): ?string => blank($value)
                        ? null
                        : str_replace(',', ', ', $value))
                    // Transient helper, never stored on the record.
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state, $livewire): void {
                        if (blank($state)) {
                            return;
                        }

                        [$latitude, $longitude] = array_pad(explode(',', $state, 2), 2, null);

                        if ($latitude === null || $longitude === null) {
                            return;
                        }

                        $set('latitude', (float) $latitude);
                        $set('longitude', (float) $longitude);
                        $set('location', ['lat' => (float) $latitude, 'lng' => (float) $longitude]);
                        $livewire->dispatch('refreshMap');
                    })
                    ->columnSpanFull(),

                Map::make('location')
                    ->hiddenLabel()
                    ->dehydrated(false)
                    // Roughly centred on Indonesia, so a new record opens on the archipelago.
                    ->defaultLocation(latitude: -2.5, longitude: 118.0)
                    ->zoom(5)
                    ->minZoom(4)
                    ->maxZoom(18)
                    ->draggable()
                    ->clickable(true)
                    ->showMarker()
                    ->markerColor('#0d9488')
                    ->showZoomControl()
                    ->showFullscreenControl()
                    ->detectRetina()
                    ->boundaries(true, self::MIN_LATITUDE, self::MIN_LONGITUDE, self::MAX_LATITUDE, self::MAX_LONGITUDE)
                    ->extraStyles(['min-height: 420px', 'border-radius: 12px'])
                    ->afterStateHydrated(function (Set $set, ?Kabupaten $record): void {
                        if ($record?->latitude === null || $record?->longitude === null) {
                            return;
                        }

                        $set('location', [
                            'lat' => (float) $record->latitude,
                            'lng' => (float) $record->longitude,
                        ]);
                    })
                    ->afterStateUpdated(function (Set $set, ?array $state): void {
                        $set('latitude', $state['lat'] ?? null);
                        $set('longitude', $state['lng'] ?? null);
                    })
                    ->columnSpanFull(),

                TextInput::make('latitude')
                    ->label('Latitude')
                    ->placeholder('0.8118')
                    ->numeric()
                    ->minValue(self::MIN_LATITUDE)
                    ->maxValue(self::MAX_LATITUDE)
                    ->helperText('Between ' . self::MIN_LATITUDE . ' and ' . self::MAX_LATITUDE . '.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(self::syncPinFromInputs())
                    ->requiredWith('longitude')
                    ->nullable()
                    ->columnSpan(1),

                TextInput::make('longitude')
                    ->label('Longitude')
                    ->placeholder('101.8')
                    ->numeric()
                    ->minValue(self::MIN_LONGITUDE)
                    ->maxValue(self::MAX_LONGITUDE)
                    ->helperText('Between ' . self::MIN_LONGITUDE . ' and ' . self::MAX_LONGITUDE . '.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(self::syncPinFromInputs())
                    ->requiredWith('latitude')
                    ->nullable()
                    ->columnSpan(1),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * Geocode a place name into selectable options. The option key carries the
     * coordinates so no second lookup is needed once a result is chosen.
     *
     * @return array<string, string>
     */
    private static function searchPlaces(string $search): array
    {
        $options = [];

        foreach (app(LocationSearch::class)->search($search) as $place) {
            $options[$place['latitude'] . ',' . $place['longitude']] = $place['label'];
        }

        return $options;
    }

    /**
     * Typing a coordinate moves the pin, so the two inputs and the map never
     * disagree about where the kabupaten sits.
     */
    private static function syncPinFromInputs(): \Closure
    {
        return function (Get $get, Set $set, $livewire): void {
            $latitude = $get('latitude');
            $longitude = $get('longitude');

            if ($latitude === null || $latitude === '' || $longitude === null || $longitude === '') {
                return;
            }

            $set('location', ['lat' => (float) $latitude, 'lng' => (float) $longitude]);
            $livewire->dispatch('refreshMap');
        };
    }

    /**
     * Baseline landscape figures. These are numbers and place names, so they are
     * stored once rather than per language — only their labels get translated,
     * and those live on the frontend.
     */
    private static function getLandscape(): Section
    {
        return Section::make('Landscape')
            ->description('Baseline landscape figures for this kabupaten.')
            ->icon(Heroicon::OutlinedGlobeAsiaAustralia)
            ->collapsible()
            ->schema([
                TextInput::make('city')
                    ->label('City')
                    ->placeholder('Siak')
                    ->nullable()
                    ->columnSpanFull(),

                TextInput::make('province')
                    ->label('Province')
                    ->placeholder('Riau')
                    ->nullable()
                    ->columnSpanFull(),
                TextInput::make('forest_cover_ha')
                    ->label('Forest Cover')
                    ->placeholder('312000')
                    ->suffix('ha')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->columnSpanFull(),

                TextInput::make('peatland_ha')
                    ->label('Peatland')
                    ->placeholder('57000')
                    ->suffix('ha')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->columnSpanFull(),

                TextInput::make('area_km2')
                    ->label('Area')
                    ->placeholder('8556')
                    ->suffix('km²')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    /**
     * Commodities belong to the language tab they are entered in, so each language
     * keeps its own list and the two are edited independently.
     */
    private static function getCommodities($suffix): Section
    {
        $isEnglish = $suffix === '';

        return Section::make('Komoditas Potensial')
            ->description('Potential commodity cards for this language. Drag to reorder — the card numbering follows this order.')
            ->icon(Heroicon::OutlinedSparkles)
            ->collapsible()
            ->schema([
                Repeater::make('commodities' . $suffix)
                    ->hiddenLabel()
                    ->addActionLabel('Add commodity')
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->collapsed()
                    ->cloneable()
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->placeholder($isEnglish ? 'Peat pineapple' : 'Nanas gambut')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder($isEnglish
                                ? 'Grown without clearing new land; absorbed by local processing industry.'
                                : 'Ditanam tanpa membuka lahan baru; diserap industri olahan lokal.')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    /**
     * Local impact rows, also per language. The value is part of the translated
     * content because number formatting differs, for example "12rb ha" against "12k ha".
     */
    private static function getAchievements($suffix): Section
    {
        $isEnglish = $suffix === '';

        return Section::make('Capaian yang Bisa Dihitung')
            ->description('Measurable local impact for this language. Each row needs a source so the number is traceable.')
            ->icon(Heroicon::OutlinedChartBar)
            ->collapsible()
            ->schema([
                Repeater::make('achievements' . $suffix)
                    ->hiddenLabel()
                    ->addActionLabel('Add achievement')
                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->collapsed()
                    ->cloneable()
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->placeholder($isEnglish ? '12k ha' : '12rb ha')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder($isEnglish
                                ? 'High conservation value areas designated'
                                : 'Kawasan bernilai konservasi tinggi ditetapkan')
                            ->required()
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder($isEnglish
                                ? 'Designated by decree, with boundaries mapped together with villages.'
                                : 'Ditetapkan lewat SK Bupati, dengan batas yang dipetakan bersama masyarakat desa.')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('source')
                            ->label('Source')
                            ->placeholder($isEnglish
                                ? 'Source: Regent Decree 2024 · updated Mar 2026'
                                : 'Sumber: SK Bupati 2024 · diperbarui Mar 2026')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }
}
