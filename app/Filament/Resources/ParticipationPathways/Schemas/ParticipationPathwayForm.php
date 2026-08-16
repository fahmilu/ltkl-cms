<?php

namespace App\Filament\Resources\ParticipationPathways\Schemas;

use App\Filament\Helpers\FormHelper;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
        $module = 'participation-pathways';

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

                self::getComponents($suffix),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * Page builder content, one list per language. Only the two blocks the
     * pathway pages need today; more can be added alongside them.
     */
    private static function getComponents($suffix): Builder
    {
        return Builder::make('components' . $suffix)
            ->label('Components')
            ->blocks([

                /* Lead Text Block
                * Lead
                */
                Builder\Block::make('lead_text')
                    ->label('Lead Text')
                    ->schema([
                        FormHelper::makeRichEditor('lead', 'Lead'),
                    ])
                    ->columnSpanFull(),

                /* Text Image Block
                * Image
                * Lead
                * Label
                * Title
                * Subtitle
                * Description
                * Button Text, Button URL
                */
                Builder\Block::make('text_image')
                    ->label('Text Image')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Image')
                            ->placeholder('Input image...')
                            ->helperText('Ideal max size are ' . config('filehelper.side-image.max-size') . ' and dimensions are ' . config('filehelper.side-image.dimensions') . ' pixels.')
                            ->image()
                            ->removeUploadedFileButtonPosition('bottom')
                            ->directory('participation-pathways')
                            ->disk('public')
                            ->visibility('public')
                            ->required()
                            ->columnSpanFull(),
                        FormHelper::makeRichEditor('lead', 'Lead'),
                        TextInput::make('label')
                            ->label('Label')
                            ->placeholder('Input label...')
                            ->columnSpanFull(),
                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Input title...')
                            ->columnSpanFull(),
                        TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->placeholder('Input subtitle...')
                            ->columnSpanFull(),
                        FormHelper::makeRichEditor('description', 'Description'),
                        TextInput::make('button_text')
                            ->label('Button Text')
                            ->placeholder('Input button text...')
                            ->columnSpan(1),
                        TextInput::make('button_url')
                            ->label('Button URL')
                            ->placeholder('Input button url...')
                            ->suffixIcon(Heroicon::GlobeAlt)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /* Stats Block
                * Items (Repeater) - Title, Value, Unit
                * Button Text, Button URL
                */
                Builder\Block::make('stats')
                    ->label('Stats')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->reorderableWithDragAndDrop()
                            ->collapsible()
                            ->cloneable()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Title')
                                    ->placeholder('Input title...')
                                    ->columnSpan(1),
                                TextInput::make('value')
                                    ->label('Value')
                                    ->placeholder('Input value...')
                                    ->columnSpan(1),
                                TextInput::make('unit')
                                    ->label('Unit')
                                    ->placeholder('ha, km², %...')
                                    ->columnSpan(1),
                            ])->columns(3)
                            ->columnSpanFull(),
                        TextInput::make('button_text')
                            ->label('Button Text')
                            ->placeholder('Input button text...')
                            ->columnSpan(1),
                        TextInput::make('button_url')
                            ->label('Button URL')
                            ->placeholder('Input button url...')
                            ->suffixIcon(Heroicon::GlobeAlt)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->blockPickerColumns(2)
            ->collapsible()
            ->collapsed()
            ->columnSpanFull();
    }
}
