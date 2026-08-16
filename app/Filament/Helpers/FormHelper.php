<?php

namespace App\Filament\Helpers;

use Filament\Forms\Components\RichEditor;


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
}
