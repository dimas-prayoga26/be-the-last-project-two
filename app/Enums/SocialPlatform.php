<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasLabel
{
    case Instagram = 'instagram';
    case LinkedIn = 'linkedin';
    case Behance = 'behance';
    case Pinterest = 'pinterest';
    case Facebook = 'facebook';
    case TikTok = 'tiktok';
    case YouTube = 'youtube';
    case X = 'x';
    case Website = 'website';

    public function getLabel(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::LinkedIn => 'LinkedIn',
            self::Behance => 'Behance',
            self::Pinterest => 'Pinterest',
            self::Facebook => 'Facebook',
            self::TikTok => 'TikTok',
            self::YouTube => 'YouTube',
            self::X => 'X',
            self::Website => 'Website',
        };
    }
}
