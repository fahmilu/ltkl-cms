<?php

namespace App\Filament\Helpers;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;


class FormHelper
{
    /**
     * Create a standardized RichEditor component with brand styling.
     */
    public static function makeRichEditor(string $name, string $label): RichEditor
    {
        return RichEditor::make($name)
            ->label($label ?? $name)
            ->toolbarButtons([
                ['bold', 'italic'],
                ['h2', 'h3'],
                ['blockquote', 'bulletList', 'orderedList', 'link'],
                ['table'],
            ])->columnSpanFull();
    }

    /**
     * Submenu fields shared by every page component block.
     *
     * When the toggle is on the block is published as an anchor entry under its
     * page in the navigations endpoint. The label falls back to the block's own
     * title field, so the override input is only there when that is not enough.
     *
     * @param  string|null  $titleKey  Field the label falls back to, if any.
     * @return array<int, \Filament\Forms\Components\Field>
     */
    public static function submenuFields(?string $titleKey = 'title'): array
    {
        return [
            Toggle::make('add_as_submenu')
                ->label('Add as submenu')
                ->helperText('Show this section as an anchor link under the page in the menu.')
                ->onColor('primary')
                ->offColor(null)
                ->onIcon(Heroicon::Check)
                ->offIcon(Heroicon::XMark)
                ->default(false)
                ->live()
                ->columnSpanFull(),
            TextInput::make('submenu_title')
                ->label('Submenu title')
                ->placeholder($titleKey ? 'Defaults to the block title...' : 'Input submenu title...')
                ->helperText('The anchor is derived from this text.')
                ->required(fn(Get $get): bool => $titleKey === null && (bool) $get('add_as_submenu'))
                ->visible(fn(Get $get): bool => (bool) $get('add_as_submenu'))
                ->columnSpanFull(),
        ];
    }
}
