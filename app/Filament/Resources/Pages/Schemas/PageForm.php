<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\CollectionComponentSource;
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
                                ])->nullable()->native(false)->searchable()->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),

                        // Seo Settings
                        Section::make('SEO Settings')
                            ->schema([
                                FileUpload::make('meta_image')->label('Meta image')->placeholder('Input meta image...')->image()->removeUploadedFileButtonPosition('bottom')->directory('pages')->disk('public')->visibility('public')->columnSpanFull(),
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
                                    ->placeholder('Input banner image...')
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
                                TextInput::make('banner_title')
                                    ->label('Banner Title')
                                    ->placeholder('Input banner title...')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('banner_description')
                                    ->label('Banner Description')
                                    ->placeholder('Input banner description...')
                                    ->columnSpanFull(),
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
                        * Source (Kabupaten Map, Pillars, Participation Pathways)
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
                                    // Refresh the block header as soon as it changes.
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),
                                FormHelper::makeRichEditor('description', 'Description'),
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
                                Section::make('Post Index')->description('Should be showing the news list')->schema([])->columnSpanFull(),
                            ])->columnSpanFull(),
                        
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
                                    ->directory('pages')
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

                        /* Single Image Block
                        * Image
                        * Caption
                        */
                        Builder\Block::make('single_image')
                            ->label('Single Image')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Image')
                                    ->placeholder('Input image...')
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
                        * Items (Repeater) - Title, Value, Unit
                        * Button Text, Button URL
                        */
                        Builder\Block::make('statistic')
                            ->label('Statistic')
                            ->schema([
                                Repeater::make('items')
                                    ->label('Items')
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
                            ])->columns(2)
                            ->columnSpanFull(),

                        /* Your Role Block
                        * Label
                        * Title
                        * Description
                        * Show Icon
                        * Items (Repeater) - Label, Title, Description, Icon, Link, Link Label
                        */
                        Builder\Block::make('your_role')
                            ->label('Your Role')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label')
                                    ->placeholder('Input label...')
                                    ->columnSpan(1),
                                TextInput::make('title')
                                    ->label('Title')
                                    ->placeholder('Input title...')
                                    ->columnSpan(1),
                                FormHelper::makeRichEditor('description', 'Description'),
                                Toggle::make('show_icon')
                                    ->label('Show Icon')
                                    ->helperText('Hide to render the items without their icons.')
                                    ->onColor('primary')
                                    ->offColor(null)
                                    ->onIcon(Heroicon::Check)
                                    ->offIcon(Heroicon::XMark)
                                    ->default(true)
                                    ->columnSpanFull(),
                                Repeater::make('items')
                                    ->label('Items')
                                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->collapsed()
                                    ->cloneable()
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Label')
                                            ->placeholder('Input label...')
                                            ->columnSpan(1),
                                        TextInput::make('title')
                                            ->label('Title')
                                            ->placeholder('Input title...')
                                            ->columnSpan(1),
                                        Textarea::make('description')
                                            ->label('Description')
                                            ->placeholder('Input description...')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        FileUpload::make('icon')
                                            ->label('Icon')
                                            ->placeholder('Input icon...')
                                            ->helperText('SVG or PNG, shown only while Show Icon is on.')
                                            ->image()
                                            ->removeUploadedFileButtonPosition('bottom')
                                            ->directory('pages')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->acceptedFileTypes(config('filesystems.image_mimes'))
                                            ->columnSpanFull(),
                                        TextInput::make('link')
                                            ->label('Link')
                                            ->placeholder('Input link...')
                                            ->suffixIcon(Heroicon::GlobeAlt)
                                            ->columnSpan(1),
                                        TextInput::make('link_label')
                                            ->label('Link Label')
                                            ->placeholder('Input link label...')
                                            ->columnSpan(1),
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
}
