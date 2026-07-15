<?php

namespace Immera\FilamentMediaLibrary\Support;

use Illuminate\Support\Collection;
use Imagick;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

class PdfThumbnailGenerator extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        copy($this->placeholderPath(), $imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        if (! class_exists(Imagick::class)) {
            return false;
        }

        if (! class_exists(\Spatie\PdfToImage\Pdf::class)) {
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

    protected function placeholderPath(): string
    {
        $published = storage_path('app/public/vendor/filament-media-library/pdf-default.png');

        return file_exists($published) ? $published : __DIR__.'/../../resources/images/pdf-default.png';
    }
}
