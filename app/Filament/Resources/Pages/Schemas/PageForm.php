<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\BlockBackgroundColor;
use App\Enums\CollectionComponentSource;
use App\Enums\CollectionDisplay;
use App\Enums\ImagePosition;
use App\Filament\Helpers\FormHelper;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Get;

class PageForm
{
    /**
     * @param Schema $schema
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Tabs::make('Tabs')
                            ->tabs([
                                self::getTabs('id'),
                                self::getTabs('en'),
                            ])
                            ->columnSpanFull()
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
                                Toggle::make('is_default')->inline(false)->columnSpan(1)
                                    ->label('Set as default page')
                                    ->onColor('primary')
                                    ->offColor(null)
                                    ->onIcon(Heroicon::Check)
                                    ->offIcon(Heroicon::XMark)
                                    ->default(false),
                                Toggle::make('is_active')->inline(false)->columnSpan(1)
                                    ->label('Set as published')
                                    ->onColor('primary')
                                    ->offColor(null)
                                    ->onIcon(Heroicon::Check)
                                    ->offIcon(Heroicon::XMark)
                                    ->default(true),
                            ])->columns(2)->columnSpanFull(),

                        // Menu Settings
                        Section::make('Menu Settings')
                            ->schema([
                                Toggle::make('menu_is_active')->live()->inline(false)->columnSpan(1)
                                    ->label('Set as active')
                                    ->onColor('primary')
                                    ->offColor(null)
                                    ->onIcon(Heroicon::Check)
                                    ->default(true)
                                    ->columnSpan(1),
                                Toggle::make('menu_is_external')->live()->inline(false)->columnSpan(1)
                                    ->label('Set as external link')
                                    ->onColor('warning')
                                    ->offColor(null)
                                    ->onIcon(Heroicon::Check)
                                    ->offIcon(Heroicon::XMark)
                                    ->default(false)
                                    ->columnSpan(1),
                                // TextInput::make('menu_title')->label('Menu title')->placeholder('Input menu title...'),
                                TextInput::make('menu_url')->label('Menu url')->placeholder('Input menu url...')->suffixIcon(Heroicon::GlobeAlt)->columnSpanFull(),
                                Select::make('menu_parent_id')->label('Menu parent')->options(Page::query()->pluck('title', 'id'))->nullable()->native(false)->searchable()->columnSpanFull(),
                                Select::make('menu_group')->label('Menu group')->options([
                                    'main' => 'Main',
                                    'header' => 'Header',
                                    'footer' => 'Footer',
                                ])->multiple()->nullable()->native(false)->searchable()->helperText('The page shows up in every group picked here.')->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),

                        // Seo Settings
                        Section::make('SEO Settings')
                            ->schema([
                                FileUpload::make('meta_image')->label('Meta image')->image()->removeUploadedFileButtonPosition('bottom')->directory('pages')->disk('public')->visibility('public')->columnSpanFull(),
                            ])->columns(1)->columnSpanFull(),
                    ])
                    // ->columns(1)
                    ->columnSpan([ // right section
                        'md' => 12,
                        'lg' => 4,

                    ])
                ])->columns(12);
    }

    private static function getTabs($lang_code)
    {
        $suffix = $lang_code === 'en' ? '' : '_id';
        return Tabs\Tab::make($lang_code === 'en' ? 'English' : 'Indonesian')
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

                TextInput::make('meta_title' . $suffix)
                    ->label('Meta title')
                    ->placeholder('Input meta title...')
                    ->columnSpanFull(),
                Textarea::make('meta_description' . $suffix)
                    ->label('Meta description')
                    ->placeholder('Input meta description...')
                    ->columnSpanFull(),

                Builder::make('components' . $suffix)
                    ->label('Components')
                    ->visible(fn(Get $get) => $get('menu_is_external') == false)
                    ->blocks([

                        /* Banner Block 
                        * Banner Title
                        * Banner Description
                        * Banner Image
                        * Banner Label
                        * Banner Button 1 Text
                        * Banner Button 1 URL
                        * Banner Button 2 Text
                        * Banner Button 2 URL
                        */
                        Builder\Block::make('banner')
                            ->label('Banner')
                            ->schema([
                                FileUpload::make('banner_image')
                                    ->label('Banner Image')
                                    // ->placeholder('Input banner image...')
                                    ->removeUploadedFileButtonPosition('bottom')
                                    ->image()
                                    ->directory('pages')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->helperText('Ideal max size are ' . config('filehelper.banner-image.max-size') . ' and dimensions are ' . config('filehelper.banner-image.dimensions') . ' pixels.')
                                    ->columnSpanFull(),
                                TextInput::make('banner_label')
                                    ->label('Banner Label')
                                    ->placeholder('Input banner label...')
                                    ->columnSpanFull(),
                                FormHelper::makeRichEditor('banner_title', 'Banner Title')
                                    ->required(),
                                FormHelper::makeRichEditor('banner_description', 'Banner Description'),
                                TextInput::make('banner_button_1_text')
                                    ->label('Banner Button #1 Text')
                                    ->placeholder('Input banner button #1 text...')
                                    ->columnSpan(1),
                                TextInput::make('banner_button_1_url')
                                    ->label('Banner Button #1 URL')
                                    ->placeholder('Input banner button #1 url...')
                                    ->suffixIcon(Heroicon::GlobeAlt)
                                    ->columnSpan(1),
                                TextInput::make('banner_button_2_text')
                                    ->label('Banner Button 2 Text')
                                    ->placeholder('Input banner button #2 text...')
                                    ->columnSpan(1),
                                TextInput::make('banner_button_2_url')
                                    ->label('Banner Button 2 URL')
                                    ->placeholder('Input banner button #2 url...')
                                    ->suffixIcon(Heroicon::GlobeAlt)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        /* Collection Block
                        * Label
                        * Title
                        * Description
                        * Source (Kabupaten Map, Pillars, Participation Pathways, Job Opportunities)
                        * Display, on the Participation Pathways source only
                        */
                        Builder\Block::make('collection')
                            // Null state means the block picker, which needs the
                            // plain name; once a source is chosen the block header
                            // shows it, so collapsed blocks stay tellable apart.
                            ->label(function (?array $state): string {
                                if ($state === null) {
                                    return 'Collection';
                                }

                                $source = CollectionComponentSource::tryFrom($state['source'] ?? '');

                                return $source
                                    ? 'Collection: ' . $source->getLabel()
                                    : 'Collection';
                            })
                            ->schema([
                                ...FormHelper::submenuFields(),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->placeholder('Input label...')
                                    ->columnSpan(1),
                                TextInput::make('title')
                                    ->label('Title')
                                    ->placeholder('Input title...')
                                    ->columnSpan(1),
                                Select::make('source')
                                    ->label('Collection')
                                    ->options(CollectionComponentSource::class)
                                    ->helperText('Which dataset this section renders.')
                                    ->native(false)
                                    // Refresh the block header, and the display
                                    // choice, as soon as it changes.
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),
                                // Only the pathways render more than one way, so
                                // the choice is not put in front of the other sources.
                                Select::make('display')
                                    ->label('Display')
                                    ->options(CollectionDisplay::class)
                                    ->helperText('How the pathways are laid out.')
                                    ->default(CollectionDisplay::SIDE_ACCORDION->value)
                                    // Blocks saved before the choice existed hydrate
                                    // as the side accordion they already render as.
                                    ->formatStateUsing(fn($state): string => CollectionDisplay::fromState($state)->value)
                                    ->selectablePlaceholder(false)
                                    ->native(false)
                                    ->required(fn(Get $get): bool => self::isPathwaysSource($get('source')))
                                    ->visible(fn(Get $get): bool => self::isPathwaysSource($get('source')))
                                    ->columnSpanFull(),
                                FormHelper::makeRichEditor('description', 'Description'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        /* Banner Statistic Block
                        * Banner and statistic in one section, without the image.
                        * Label
                        * Title
                        * Description
                        * Items (Repeater, max 4) - Title, Value, Unit
                        * Button #1 Text, Button #1 URL
                        * Button #2 Text, Button #2 URL
                        */
                        Builder\Block::make('banner_statistic')
                            ->label('Banner Statistic')
                            ->schema([
                                ...FormHelper::submenuFields(),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->placeholder('Input label...')
                                    ->columnSpanFull(),
                                FormHelper::makeRichEditor('title', 'Title')
                                    ->required(),
                                FormHelper::makeRichEditor('description', 'Description'),
                                Repeater::make('items')
                                    ->label('Items')
                                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                    ->reorderableWithDragAndDrop()
                                    ->cloneable()
                                    ->maxItems(4)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Title')
                                            ->placeholder('Input title...')
                                            ->columnSpanFull(),
                                        TextInput::make('value')
                                            ->label('Value')
                                            ->placeholder('Input value...')
                                            ->columnSpan(1),
                                        TextInput::make('unit')
                                            ->label('Unit')
                                            ->placeholder('ha, km², %...')
                                            ->columnSpan(1),
                                    ])->columns(2)
                                    ->columnSpanFull(),
                                TextInput::make('button_1_text')
                                    ->label('Button #1 Text')
                                    ->placeholder('Input button #1 text...')
                                    ->columnSpan(1),
                                TextInput::make('button_1_url')
                                    ->label('Button #1 URL')
                                    ->placeholder('Input button #1 url...')
                                    ->suffixIcon(Heroicon::GlobeAlt)
                                    ->columnSpan(1),
                                TextInput::make('button_2_text')
                                    ->label('Button #2 Text')
                                    ->placeholder('Input button #2 text...')
                                    ->columnSpan(1),
                                TextInput::make('button_2_url')
                                    ->label('Button #2 URL')
                                    ->placeholder('Input button #2 url...')
                                    ->suffixIcon(Heroicon::GlobeAlt)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        /* Latest News Block
                        * Label
                        * Title
                        * Description
                        * Button Text, Button URL
                        */
                        Builder\Block::make('latest_news')
                            ->label('Latest News')
                            ->schema([
                                ...FormHelper::submenuFields(),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->placeholder('Input label...')
                                    ->columnSpan(1),
                                TextInput::make('title')
                                    ->label('Title')
                                    ->placeholder('Input title...')
                                    ->columnSpan(1),
                                FormHelper::makeRichEditor('description', 'Description'),
                                TextInput::make('button_text')
                                    ->label('Button Text')
                                    ->placeholder('Input button text...')
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        /* Lead Text Block
                        * Lead
                        */
                        Builder\Block::make('lead_text')
                            ->label('Lead Text')
                            ->schema([
                                ...FormHelper::submenuFields(null),
                                FormHelper::makeRichEditor('lead', 'Lead'),
                            ])
                            ->columnSpanFull(),
                        
                        /* Paragraph Block
                        * Title
                        * Description
                        */
                        Builder\Block::make('paragraph')
                            ->label('Paragraph')
                            ->schema([
                                ...FormHelper::submenuFields(),
                                TextInput::make('title')
                                    ->label('Title')
                                    ->placeholder('Input title...')
                                    ->columnSpanFull(),
                                FormHelper::makeRichEditor('description', 'Description'),
                            ])->columns(2)
                            ->columnSpanFull(),

                        /* Post Index Block
                        * Should be showing the news list
                        */
                        Builder\Block::make('post_index')
                            ->label('Post Index')
                            ->schema([
                                ...FormHelper::submenuFields(null),
                                Section::make('Post Index')->description('Should be showing the news list')->schema([])->columnSpanFull(),
                            ])->columnSpanFull(),

                        /* Quote Block
                        * Quote
                        */
                        Builder\Block::make('quote')
                            ->label('Quote')
                            ->schema([
                                ...FormHelper::submenuFields(null),
                                Textarea::make('quote')
                                    ->label('Quote')
                                    ->placeholder('Input quote...')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),


                        /* Text Image Block
                        * Is Block, Background Color
                        * Image, Image Position
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
                                ...FormHelper::submenuFields(),
                                Toggle::make('is_block')
                                    ->label('Show as colour block')
                                    ->helperText('Fill the section with a brand colour behind the text.')
                                    ->onColor('primary')
                                    ->offColor(null)
                                    ->onIcon(Heroicon::Check)
                                    ->offIcon(Heroicon::XMark)
                                    ->default(false)
                                    ->live()
                                    ->columnSpanFull(),
                                Select::make('background_color')
                                    ->label('Background Color')
                                    ->options(self::backgroundColorOptions())
                                    ->default(BlockBackgroundColor::CYAN->value)
                                    // A colour saved before it left the palette hydrates as
                                    // empty, so the block reopens without a validation error.
                                    ->formatStateUsing(fn($state): ?string => BlockBackgroundColor::fromState($state))
                                    ->allowHtml()
                                    ->selectablePlaceholder(false)
                                    ->native(false)
                                    ->required(fn(Get $get): bool => (bool) $get('is_block'))
                                    ->visible(fn(Get $get): bool => (bool) $get('is_block'))
                                    ->columnSpanFull(),
                                FileUpload::make('image')
                                    ->label('Image')
                                    // ->placeholder('Input image...')
                                    ->helperText('Ideal max size are ' . config('filehelper.side-image.max-size') . ' and dimensions are ' . config('filehelper.side-image.dimensions') . ' pixels.')
                                    ->image()
                                    ->removeUploadedFileButtonPosition('bottom')
                                    ->directory('pages')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('image_position')
                                    ->label('Image Position')
                                    ->options(ImagePosition::class)
                                    ->default(ImagePosition::RIGHT->value)
                                    // Blocks saved before the option existed hydrate as right.
                                    ->formatStateUsing(fn($state): string => ImagePosition::fromState($state)->value)
                                    ->selectablePlaceholder(false)
                                    ->native(false)
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

                        /* Single Image Block
                        * Image
                        * Caption
                        */
                        Builder\Block::make('single_image')
                            ->label('Single Image')
                            ->schema([
                                ...FormHelper::submenuFields(null),
                                FileUpload::make('image')
                                    ->label('Image')
                                    // ->placeholder('Input image...')
                                    ->helperText('Ideal max size are ' . config('filehelper.single-image.max-size') . ' and dimensions are ' . config('filehelper.single-image.dimensions') . ' pixels.')
                                    ->image()
                                    ->removeUploadedFileButtonPosition('bottom')
                                    ->directory('pages')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('caption')
                                    ->label('Caption')
                                    ->placeholder('Input caption...')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        /* Statistic Block
                        * Items (Repeater, max 4) - Title, Value, Unit
                        * Button Text, Button URL
                        */
                        Builder\Block::make('statistic')
                            ->label('Statistic')
                            ->schema([
                                Repeater::make('items')
                                    ->label('Items')
                                    ->maxItems(4)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Title')
                                            ->placeholder('Input title...')
                                            ->columnSpanFull(),
                                        TextInput::make('value')
                                            ->label('Value')
                                            ->placeholder('Input value...')
                                            ->columnSpan(1),
                                        TextInput::make('unit')
                                            ->label('Unit')
                                            ->placeholder('ha, km², %...')
                                            ->columnSpan(1),
                                    ])->columns(2)
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
                            ])->columns(2)
                            ->columnSpanFull(),

                        /* The Values, The Problems and The Vision Blocks
                        * Label
                        * Title
                        * Description
                        * Items (Repeater) - Title, Description, and an Image on The Vision
                        */
                        self::getListBlock('the_values', 'The Values'),
                        self::getListBlock('the_problems', 'The Problems'),
                        self::getListBlock('the_vision', 'The Vision', withItemImage: true),

                        /* Journey Block
                        * Label
                        * Title
                        * Description
                        * Items (Repeater) - Title, Period, Description
                        * The step number comes from the row order, so reordering renumbers.
                        */
                        Builder\Block::make('journey')
                            ->label('Journey')
                            ->schema([
                                ...FormHelper::submenuFields(),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->placeholder('Perjalanan')
                                    ->columnSpan(1),
                                TextInput::make('title')
                                    ->label('Title')
                                    ->placeholder('Gerakan yang Terus Bertumbuh')
                                    ->columnSpan(1),
                                FormHelper::makeRichEditor('description', 'Description'),
                                Repeater::make('items')
                                    ->label('Steps')
                                    ->addActionLabel('Add step')
                                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->collapsed()
                                    ->cloneable()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Title')
                                            ->placeholder('Mencari cara')
                                            ->required()
                                            ->columnSpan(1),
                                        TextInput::make('period')
                                            ->label('Period')
                                            ->placeholder('2018–2020')
                                            ->columnSpan(1),
                                        Textarea::make('description')
                                            ->label('Description')
                                            ->placeholder('Beberapa kabupaten mulai berkumpul...')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Toggle::make('is_past')
                                            ->label('Is past')
                                            ->helperText('Turn on for a step that already happened.')
                                            ->onColor('primary')
                                            ->offColor(null)
                                            ->onIcon(Heroicon::Check)
                                            ->offIcon(Heroicon::XMark)
                                            ->default(false)
                                            ->columnSpanFull(),
                                    ])->columns(2)
                                    ->columnSpanFull(),
                            ])->columns(2)
                            ->columnSpanFull(),

                    ])
                    ->blockPickerColumns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * A titled section over a list of items: The Values, The Problems and The
     * Vision are the same block, and only The Vision illustrates its items.
     */
    private static function getListBlock(string $name, string $label, bool $withItemImage = false): Builder\Block
    {
        return Builder\Block::make($name)
            ->label($label)
            ->schema([
                ...FormHelper::submenuFields(),
                TextInput::make('label')
                    ->label('Label')
                    ->placeholder('Input label...')
                    ->columnSpan(1),
                TextInput::make('title')
                    ->label('Title')
                    ->placeholder('Input title...')
                    ->columnSpan(1),
                FormHelper::makeRichEditor('description', 'Description'),
                Repeater::make('items')
                    ->label('Items')
                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->collapsed()
                    ->cloneable()
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Input title...')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Input description...')
                            ->rows(3)
                            ->columnSpanFull(),
                        ...($withItemImage ? [
                            FileUpload::make('image')
                                ->label('Image')
                                // ->placeholder('Input image...')
                                ->helperText('Ideal max size are ' . config('filehelper.single-image.max-size') . ' and dimensions are ' . config('filehelper.single-image.dimensions') . ' pixels.')
                                ->image()
                                ->openable()
                                ->removeUploadedFileButtonPosition('bottom')
                                ->directory('pages')
                                ->disk('public')
                                ->visibility('public')
                                ->acceptedFileTypes(config('filesystems.image_mimes'))
                                ->nullable()
                                ->columnSpanFull(),
                        ] : []),
                    ])->columns(1)
                    ->columnSpanFull(),
            ])->columns(2)
            ->columnSpanFull();
    }

    /**
     * Whether a Collection block's source is the pathways.
     *
     * The select hands back an instance of the enum, while a block read from the
     * database holds the plain string, so both shapes are answered here.
     */
    private static function isPathwaysSource($state): bool
    {
        $source = $state instanceof CollectionComponentSource
            ? $state
            : (is_string($state) ? CollectionComponentSource::tryFrom($state) : null);

        return $source === CollectionComponentSource::PARTICIPATION_PATHWAYS;
    }

    /**
     * The Text Image background palette, each option drawn as a swatch next to
     * its label so the colour is picked by eye rather than by hex code.
     *
     * @return array<string, string>
     */
    private static function backgroundColorOptions(): array
    {
        $options = [];

        foreach (BlockBackgroundColor::cases() as $color) {
            // Styled inline rather than with utility classes: the swatch is
            // painted with the stored hex itself, so no theme rebuild is needed
            // when the palette changes.
            $options[$color->value] = '<span style="display: inline-flex; align-items: center; gap: 0.5rem;">'
                . '<span style="display: inline-block; width: 1rem; height: 1rem; border-radius: 9999px;'
                . ' box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.2); background-color: ' . e($color->value) . ';"></span>'
                . e($color->getLabel())
                . '</span>';
        }

        return $options;
    }
}
