<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The three top-level work menus in the site header.
 */
enum Discipline: string implements HasLabel
{
    case Architecture = 'architecture';
    case Interior = 'interior';
    case Design = 'design';

    public function getLabel(): string
    {
        return match ($this) {
            self::Architecture => 'Architecture',
            self::Interior => 'Interior',
            self::Design => 'Design',
        };
    }
}
