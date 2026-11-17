<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

/**
 * The single row holding the Studio pages (About, Contact, Follow) content.
 */
#[Fillable([
    'intro', 'about_heading', 'about_body', 'email', 'phone', 'whatsapp',
    'address', 'map_url', 'office_hours', 'socials',
])]
class StudioProfile extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['intro', 'about_heading', 'about_body', 'office_hours'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'socials' => 'array',
        ];
    }

    /**
     * The studio profile row, created empty on first use.
     */
    public static function current(): self
    {
        return static::query()->oldest('id')->first() ?? static::query()->create();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('about_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')->fit(Fit::Max, 2000, 2000)->format('webp')->quality(85);
    }
}
