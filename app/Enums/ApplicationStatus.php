<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Where a "Join the collective" application stands.
 */
enum ApplicationStatus: string implements HasColor, HasLabel
{
    /** Waiting for an admin to review it. */
    case Pending = 'pending';

    /** The applicant is invited to join. */
    case Accepted = 'accepted';

    /** The applicant is not invited this time. */
    case Declined = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Declined => 'gray',
        };
    }
}
