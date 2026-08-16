<?php

namespace App\Filament\Resources\Pillars\Schemas;

use App\Models\Kabupaten;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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

class PillarForm
{
    public static function configure(Schema $schema): Schema
    {
        $module = 'pillars';

        return $schema->schema([

            Grid::make()
                ->schema([
                    Tabs::make('Tabs')
                        ->tabs([
                            self::getTabs('id'),
                            self::getTabs('en'),
                        ])
                        ->columnSpanFull(),

                    self::getPractices($module),
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
                        ])->columnSpanFull(),

                    Section::make('Numbering')
                        ->description('The "Pilar 01" number is the position in the list. Drag rows on the index page to change it.')
                        ->icon(Heroicon::OutlinedHashtag)
                        ->schema([
                            TextInput::make('sorted_at')
                                ->label('Order')
                                ->helperText('Lower numbers come first.')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
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
                    ->placeholder($isEnglish ? 'Shared governance' : 'Tata kelola bersama')
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

                TextInput::make('technical_term' . $suffix)
                    ->label('Technical term')
                    ->placeholder('Multi-stakeholder governance')
                    ->helperText('Shown as "Istilah teknis" under the title.')
                    ->nullable()
                    ->columnSpanFull(),

                Textarea::make('description' . $suffix)
                    ->label('Description')
                    ->placeholder($isEnglish
                        ? 'Government, citizens and business decide at one table.'
                        : 'Pemerintah, warga, dan swasta mengambil keputusan di satu meja.')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull(),

                self::getStatistics($suffix, $isEnglish),
                self::getResults($suffix, $isEnglish),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * Header statistics. The kabupaten count is deliberately absent: it is
     * counted live from the kabupatens table so it can never go stale.
     */
    private static function getStatistics($suffix, bool $isEnglish): Section
    {
        return Section::make('Statistics')
            ->description('Header figures. The kabupaten count is added automatically and must not be entered here.')
            ->icon(Heroicon::OutlinedChartBarSquare)
            ->collapsible()
            ->schema([
                Repeater::make('statistics' . $suffix)
                    ->hiddenLabel()
                    ->addActionLabel('Add statistic')
                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->collapsed()
                    ->cloneable()
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->placeholder($isEnglish ? '38' : '38')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('label')
                            ->label('Label')
                            ->placeholder($isEnglish ? 'Institutions in the forum' : 'Lembaga di forum')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    /**
     * "Hasil pilar ini". Every row carries a source so the number is traceable.
     */
    private static function getResults($suffix, bool $isEnglish): Section
    {
        return Section::make('Hasil Pilar Ini')
            ->description('Measured results for this pillar. Each row needs a source.')
            ->icon(Heroicon::OutlinedChartBar)
            ->collapsible()
            ->schema([
                Repeater::make('results' . $suffix)
                    ->hiddenLabel()
                    ->addActionLabel('Add result')
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
                                ? 'Area protected through forum decisions'
                                : 'Kawasan terlindungi lewat keputusan forum')
                            ->required()
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder($isEnglish
                                ? 'Agreed with communities and rights holders.'
                                : 'Ditetapkan setelah batasnya disepakati bersama masyarakat dan pemegang izin.')
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

    /**
     * "Bagaimana ini terlihat di lapangan".
     *
     * Unlike the statistics and results lists, a practice row carries both
     * languages at once. Each row points at a real Kabupaten, and splitting the
     * list per language would let the two languages reference different ones.
     */
    private static function getPractices($module): Section
    {
        return Section::make('Bagaimana Ini Terlihat di Lapangan')
            ->description('Worked examples, each anchored to a kabupaten. Both languages live on the same row so they cannot point at different kabupatens.')
            ->icon(Heroicon::OutlinedMapPin)
            ->collapsible()
            ->schema([
                Repeater::make('practices')
                    ->hiddenLabel()
                    ->relationship()
                    ->addActionLabel('Add example')
                    ->itemLabel(fn(array $state): ?string => $state['title_id'] ?? $state['title'] ?? null)
                    ->orderColumn('sorted_at')
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->collapsed()
                    ->defaultItems(0)
                    ->schema([
                        Select::make('kabupaten_id')
                            ->label('Kabupaten')
                            ->options(fn(): array => Kabupaten::orderBy('sorted_at')
                                ->pluck('title_id', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->nullable()
                            ->columnSpan(1),

                        TextInput::make('since_year')
                            ->label('Since year')
                            ->placeholder('2019')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue((int) date('Y') + 1)
                            ->nullable()
                            ->columnSpan(1),

                        TextInput::make('title_id')
                            ->label('Title (ID)')
                            ->placeholder('Forum kabupaten yang bertemu tiap kuartal')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->label('Title (EN)')
                            ->placeholder('A district forum that meets every quarter')
                            ->required()
                            ->columnSpan(1),

                        Textarea::make('description_id')
                            ->label('Description (ID)')
                            ->placeholder('Delapan lembaga daerah, dua koperasi, dan perwakilan desa duduk bersama.')
                            ->rows(3)
                            ->nullable()
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Description (EN)')
                            ->placeholder('Eight agencies, two cooperatives and village representatives sit together.')
                            ->rows(3)
                            ->nullable()
                            ->columnSpan(1),

                        FileUpload::make('image')
                            ->label('Image')
                            ->placeholder('Input image...')
                            ->openable()
                            ->image()
                            ->maxSize(5240000)->disk('public')
                            ->removeUploadedFileButtonPosition('bottom')
                            ->directory($module)->preserveFilenames()
                            ->acceptedFileTypes(config('filesystems.image_mimes'))
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }
}
