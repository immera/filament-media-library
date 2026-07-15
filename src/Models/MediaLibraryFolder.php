<?php

namespace Immera\FilamentMediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaLibraryFolder extends Model
{
    protected $table = 'filament_media_library_folders';
    protected $fillable = [
        'parent_id',
        'name',
    ];

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<MediaLibraryItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MediaLibraryItem::class, 'folder_id');
    }
}
