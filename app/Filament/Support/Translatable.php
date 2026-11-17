<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Builds EN / ID tabs for a spatie/laravel-translatable attribute.
 *
 * The attribute's translations are stored as one JSON column ({"en": "...", "id": "..."}),
 * so each tab edits one key. An empty Indonesian value falls back to English on the site.
 */
final class Translatable
{
    /**
     * @param  Closure(string): Field  $makeField  Builds the input for a state path such as "title.en".
     */
    public static function tabs(string $attribute, string $label, Closure $makeField, bool $required = false): Tabs
    {
        return Tabs::make($label)
            ->tabs([
                Tab::make('English')->schema([
                    $makeField("{$attribute}.en")->label("{$label} (EN)")->required($required),
                ]),
                Tab::make('Indonesia')->schema([
                    $makeField("{$attribute}.id")
                        ->label("{$label} (ID)")
                        ->helperText('Leave empty to show the English text on the Indonesian site.'),
                ]),
            ])
            ->columnSpanFull();
    }
}
