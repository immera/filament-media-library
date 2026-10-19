<?php

namespace Immera\FilamentMediaLibrary\Support;

use Illuminate\Support\Collection;

class DocThumbnailGenerator extends PlaceholderThumbnailGenerator
{
    /**
     * @return Collection<int, string>
     */
    public function supportedExtensions(): Collection
    {
        return collect(['doc', 'docx']);
    }

    /**
     * @return Collection<int, string>
     */
    public function supportedMimeTypes(): Collection
    {
        return collect([
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    protected function placeholderName(): string
    {
        return 'doc-default.png';
    }
}
