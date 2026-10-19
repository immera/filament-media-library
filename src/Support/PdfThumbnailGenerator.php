<?php

namespace Immera\FilamentMediaLibrary\Support;

use Illuminate\Support\Collection;
use Imagick;
use Spatie\PdfToImage\Pdf;

class PdfThumbnailGenerator extends PlaceholderThumbnailGenerator
{
    public function requirementsAreInstalled(): bool
    {
        if (! class_exists(Imagick::class)) {
            return false;
        }

        if (! class_exists(Pdf::class)) {
            return false;
        }

        return true;
    }

    /**
     * @return Collection<int, string>
     */
    public function supportedExtensions(): Collection
    {
        return collect(['pdf']);
    }

    /**
     * @return Collection<int, string>
     */
    public function supportedMimeTypes(): Collection
    {
        return collect(['application/pdf']);
    }

    protected function placeholderName(): string
    {
        return 'pdf-default.png';
    }
}
