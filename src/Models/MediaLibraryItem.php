<?php

namespace Immera\FilamentMediaLibrary\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

/**
 * @property-read string|null $fileName
 */
class MediaLibraryItem extends Model implements HasMedia
{
    use HasTags;
    use InteractsWithMedia;

    protected $table = 'filament_media_library';
    protected $fillable = [
        'uploaded_by_user_id',
        'caption',
        'alt_text',
        'folder_id',
    ];

    public function getMorphClass(): string
    {
        return 'filament_media_library_item';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('responsive')->withResponsiveImages();
        $this->addMediaConversion('800')->width(800);
        $this->addMediaConversion('400')->width(400);
        $this->addMediaConversion('thumb')->nonQueued()->fit(Fit::Crop, 600, 600);
    }

    /**
     * @return BelongsTo<MediaLibraryFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaLibraryFolder::class, 'folder_id');
    }

    /**
     * @return BelongsTo<\Illuminate\Foundation\Auth\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'uploaded_by_user_id');
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function fileName(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->getFirstMedia('library')?->name);
    }

    public function getUrl(string $conversion = ''): string
    {
        return $this->getFirstMediaUrl('library', $conversion);
    }
}
