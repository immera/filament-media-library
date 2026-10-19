<?php

namespace Immera\FilamentMediaLibrary\Support;

use Illuminate\Support\Collection;

class ZipThumbnailGenerator extends PlaceholderThumbnailGenerator
{
    /**
     * @return Collection<int, string>
     */
    public function supportedExtensions(): Collection
    {
        return collect(['zip']);
    }

    /**
     * @return Collection<int, string>
     */
    public function supportedMimeTypes(): Collection
    {
        return collect(['application/zip', 'application/x-zip-compressed']);
    }

    protected function placeholderName(): string
    {
        return 'zip-default.png';
    }
}
