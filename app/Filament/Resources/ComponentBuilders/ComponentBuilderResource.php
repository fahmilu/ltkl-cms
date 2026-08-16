<?php

namespace App\Filament\Resources\ComponentBuilders;

use App\Filament\Helpers\FormHelper;
use App\Models\ComponentBuilder;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;

class ComponentBuilderResource
{

    public static function defaultComponents($module, $lang_code)
    {
        $suffix = $lang_code === 'en' ? '' : '_id';
        return
            Builder::make('components' . $suffix)
                ->blocks([
                    // Excerpt
                    /* Block::make('excerpt')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->label(function (?array $state): string {
                            if ($state === null) {
                                return 'Excerpt';
                            }

                            return $state['title'] ?? 'Excerpt';
                        })
                        ->schema([
                            Grid::make()->columns([
                                'sm' => 1,
                                'xl' => 12,
                            ])
                                ->schema([
                                    Toggle::make('is_active')->hiddenLabel()->inline(false)->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)->columnSpan([
                                        'sm' => 1,
                                        'xl' => 1,
                                    ]),
                                    TextInput::make('title')->hiddenLabel()->placeholder('Your title...')->columnSpan([
                                        'sm' => 1,
                                        'xl' => 11,
                                    ]),
                                ]),
                            RichEditor::make('content')->placeholder('Input content...')->toolbarButtons(config('editor.advanced'))->nullable(),
                        ]), */
                    // Paragraph
                    Block::make('paragraph')
                        ->icon(Heroicon::OutlinedBars4)
                        ->label(function (?array $state): string {
                            if ($state === null) {
                                return 'Paragraph';
                            }

                            return $state['title'] ?? 'Paragraph';
                        })
                        ->schema([
                            Grid::make()->columns([
                                'sm' => 1,
                                'xl' => 12,
                            ])
                                ->schema([
                                    Toggle::make('is_active')->hiddenLabel()->inline(false)->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)->columnSpan([
                                        'sm' => 1,
                                        'xl' => 1,
                                    ]),
                                    TextInput::make('title')->hiddenLabel()->placeholder('Your title...')->columnSpan([
                                        'sm' => 1,
                                        'xl' => 11,
                                    ]),
                                ]),
                            RichEditor::make('content')->placeholder('Input content...')->toolbarButtons(config('editor.advanced'))->nullable(),
                        ]),
                    
                    
                        
                    /* Lead Text Block
                    * Lead
                    */
                    Builder\Block::make('lead_text')
                        ->label('Lead Text')
                        ->icon(Heroicon::Flag)
                        ->schema([
                            Toggle::make('is_active')->hiddenLabel()->inline(false)->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)->columnSpanFull(),
                            FormHelper::makeRichEditor('lead', 'Lead'),
                        ])
                        ->columnSpanFull(),
                    
                        
                    /* Quote Block
                    * Quote
                    * Author
                    */
                    Block::make('quote')
                        ->icon(Heroicon::ChatBubbleBottomCenterText)
                        ->label('Quote')
                        ->schema([


                            Grid::make()->columns([
                                'sm' => 1,
                                'xl' => 12,
                            ])
                                ->schema([
                                    Toggle::make('is_active')->hiddenLabel()->inline(false)->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)->columnSpan([
                                        'sm' => 1,
                                        'xl' => 1,
                                    ]),
                                    TextInput::make('author')
                                        ->hiddenLabel()
                                        ->placeholder('Input author...')
                                        ->columnSpan([
                                            'sm' => 1,
                                            'xl' => 11,
                                        ]),
                                    FormHelper::makeRichEditor('quote', 'Quote')->columnSpanFull(),
                                ]),
                        ])
                        ->columnSpanFull(),

                    // Single Image
                    Block::make('single_image')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->label(function (?array $state): string {
                            if ($state === null) {
                                return 'Single Image';
                            }

                            return $state['title'] ?? 'Images';
                        })
                        ->schema([

                            Grid::make()->columns([
                                'sm' => 1,
                                'xl' => 12,
                            ])
                                ->schema([
                                    Toggle::make('is_active')->hiddenLabel()->inline(false)->onColor('primary')->offColor(null)->onIcon(Heroicon::Check)->columnSpan([
                                        'sm' => 1,
                                        'xl' => 1,
                                    ]),
                                    TextInput::make('title')->hiddenLabel()->placeholder('Your alt/caption...')->columnSpan([
                                        'sm' => 1,
                                        'xl' => 11,
                                    ]),
                                ]),
                            FileUpload::make('image')
                                ->openable()
                                ->image()
                                ->imageEditor()
                                ->maxSize(5240000)->disk('public')
                                ->directory($module)
                                ->helperText('Ideal max size are ' . config('filehelper.single-image.max-size') . ' and dimensions are ' . config('filehelper.single-image.dimensions') . ' pixels.')
                                ->reorderable(true)->panelLayout('grid')
                                ->preserveFilenames()
                                ->acceptedFileTypes(config('filesystems.image_mimes'))
                                ->nullable()->columnSpanFull(),
                        ]),
                ])->columnSpanFull()
                ->deleteAction(fn(Action $action) => $action->requiresConfirmation())
                ->cloneAction(fn(Action $action) => $action->requiresConfirmation())
                ->addActionAlignment(Alignment::Center)
                ->blockPreviews(areInteractive: true)->collapsible()->collapsed()->cloneable();
    }
}
