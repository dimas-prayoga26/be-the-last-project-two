<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    /** Manages everything in the admin panel. */
    case Admin = 'admin';

    /** Edits only their own designer profile and works. */
    case Designer = 'designer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Designer => 'Designer',
        };
    }
}
