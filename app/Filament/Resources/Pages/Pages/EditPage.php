<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Enums\PageTemplate;
use App\Filament\Resources\ComponentBuilders\ComponentBuilderResource;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /**
     * @param Schema $schema
     * @return Schema
     * @throws Exception
     */
    /* public function form(Schema $schema): Schema
    {
        $module = 'pages';

        $targets = [
            '_self' => 'Open in same tab',
            '_blank' => 'Open in new tab'
        ];

        return $schema->schema([
            // Index
            Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Main')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([
                            TextInput::make('title')
                                ->placeholder('Input title...')
                                ->required()->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    $set('slug', Str::slug($state));
                                }),
                            RichEditor::make('content')
                                ->placeholder('Input content...')
                                ->toolbarButtons(config('editor.simple'))
                                ->fileAttachmentsDirectory('contents')
                        ]),
                    Tabs\Tab::make('Menu')
                        ->icon(Heroicon::OutlinedBars3)
                        ->columns([
                            'sm' => 1,
                            'xl' => 12,
                        ])
                        ->schema([
                            Select::make('menu_parent_id')->label('Parent page')->options(function ($record) {
                                return Page::where('menu_parent_id', null)->where('id', '<>', $record->id)->pluck('title', 'id');
                            })->nullable()->native(false)
                                ->searchable()
                                ->columnSpan([
                                    'sm' => 1,
                                    'xl' => 12,
                                ]),
                            Toggle::make('menu_is_active')->label('Set menu as publish/draft')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                                ->helperText('This menu will be publish/draft from front end.')
                                ->inline(false)
                                ->columnSpan([
                                    'sm' => 1,
                                    'xl' => 6,
                                ]),
                            Toggle::make('menu_is_external')->label('Set as external url')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                                ->helperText('This option define to be page set as external url.')
                                ->inline(false)->reactive()
                                ->columnSpan([
                                    'sm' => 1,
                                    'xl' => 6,
                                ]),

                            Grid::make()->columnSpanFull()->schema([
                                TextInput::make('menu_title')->label('Title')->maxLength(60)
                                    ->placeholder('Input title...')
                                    ->helperText('This should be title on the menu.')
                                    ->nullable()->columnSpanFull(),
                            ]),
                            TextInput::make('menu_url')->label('Url')->nullable()
                                ->placeholder('https://...')
                                ->prefixIcon(Heroicon::Link)
                                ->hidden(fn(Get $get) => $get('menu_is_external') != true)
                                ->columnSpan([
                                    'sm' => 1,
                                    'xl' => 6,
                                ]),
                            Select::make('menu_url_target')->label('Target url')->nullable()
                                ->options($targets)
                                ->native(false)
                                ->hidden(fn(Get $get) => $get('menu_is_external') != true)
                                ->columnSpan([
                                    'sm' => 1,
                                    'xl' => 6,
                                ]),

                        ]),
                    Tabs\Tab::make('SEO')
                        ->icon(Heroicon::OutlinedGlobeAlt)
                        ->hidden(fn(Get $get) => $get('menu_is_external') == true)
                        ->schema([
                            Toggle::make('meta_is_hidden')->label('Set ' . $module . ' noindex/nofollow')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                                ->helperText('Set ' . $module . ' hidden from search engine'),
                            TextInput::make('meta_title')
                                ->placeholder('Input title...')
                                ->maxLength(60)
                                ->helperText('Every URL in your site should have a unique Meta Title, ideally less than 60 characters long.')
                                ->nullable(),
                            Textarea::make('meta_description')
                                ->placeholder('Input description...')
                                ->autosize()->maxLength(160)
                                ->helperText('Every URL in your site should have a unique Meta Description, ideally less than 160 characters long.')
                                ->nullable(),
                            FileUpload::make('meta_image')
                                ->openable()
                                ->imageEditor()
                                ->maxSize(5240000)->disk('public')
                                ->imageCropAspectRatio('square')
                                ->imageEditorViewportWidth('300')
                                ->imageEditorViewportHeight('300')
                                ->imageResizeTargetWidth('300')
                                ->imageResizeTargetHeight('300')
                                ->directory($module)->preserveFilenames()
                                ->helperText('Meta Image, ideally max size are 300 x 300 pixel.')
                                ->acceptedFileTypes(config('filesystems.image_mimes'))
                                ->nullable()->columnSpanFull(),
                        ]),
                ])->columnSpan([
                    'sm' => 2,
                    'xl' => fn(Get $get) => $get('menu_is_external') == true ? 12 : 8,
                ]),
            Section::make()
                ->hidden(fn(Get $get) => $get('menu_is_external') == true)
                ->schema([
                    TextInput::make('slug')->placeholder('Input slug...')->required()->unique(ignoreRecord: true)->columnSpanFull(),
                    FileUpload::make('image')
                        ->openable()
                        ->imageEditor()
                        ->maxSize(5240000)->disk('public')
                        ->directory($module)->preserveFilenames()
                        ->acceptedFileTypes(config('filesystems.image_mimes'))
                        ->nullable()->columnSpanFull(),
                    Select::make('template')->options(PageTemplate::class)
                        ->default(PageTemplate::HOME)
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $set('template', $state);
                        })
                        ->native(false)
                ])->columnSpan([
                    'sm' => 2,
                    'xl' => 4,
                ]),
            // Components
            ComponentBuilderResource::defaultComponents($module)->label('Contents')
                ->hidden(fn(Get $get) => $get('menu_is_external') == true),
            // Status
            Fieldset::make()
                ->columns([
                    'sm' => 1,
                    'xl' => 12,
                ])
                ->schema([
                    Toggle::make('is_default')->label('Set as default page')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                        ->hidden(fn(Get $get) => $get('menu_is_external') == true)
                        ->helperText('Set as default homepage.')
                        ->columnSpan([
                            'sm' => 1,
                            'xl' => 4,
                        ]),
                    Toggle::make('is_active')->label('Set as publish/draft')->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)
                        ->helperText('This data will be set into publish/draft.')
                        ->columnSpan([
                            'sm' => 1,
                            'xl' => 4,
                        ]),
                ])->columnSpan([
                    'sm' => 1,
                    'xl' => 12,
                ]),
        ])->columns([
            'sm' => 1,
            'xl' => 12,
        ]);
    } */

    /**
     * @param array $data
     * @return array|mixed[]
     */
    /* public function mutateFormDataBeforeSave(array $data): array
    {
        $record = static::getRecord();
        $recordId = $record->id;

        // get default on checked, get the record page & global page default count
        $page = Page::where('id', $recordId)->where('is_default', true)->count();
        $countPage = Page::where('is_default', true)->count();
        if ($data['is_default'] == true && $page == 0 && $countPage != 0) {
            $validator = Validator::make(
                ['is_default' => $data['is_default']],
                ['is_default' => 'unique:pages,is_default']
            );

            if ($validator->fails()) {
                Notification::make()
                    ->title('Default page found!')
                    ->body($validator->errors()->first())
                    ->danger()
                    ->send();

                throw new ValidationException($validator);
            }
        }
        return $data;
    } */

    /**
     * @return array|Action[]|ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->submit(null)->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }

    /**
     * @return array|Action[]|ActionGroup[]
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->submit(null)->icon(Heroicon::AdjustmentsVertical)->action('save'),
            $this->getCancelFormAction()->icon(Heroicon::OutlinedNoSymbol),
        ];
    }
}
