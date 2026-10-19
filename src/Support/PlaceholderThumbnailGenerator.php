<?php

namespace Immera\FilamentMediaLibrary\Support;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

abstract class PlaceholderThumbnailGenerator extends ImageGenerator
{
    abstract protected function placeholderName(): string;

    public function convert(string $file, ?Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        copy($this->placeholderPath(), $imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return true;
    }

    protected function placeholderPath(): string
    {
        $name = $this->placeholderName();
        $published = storage_path("app/public/vendor/filament-media-library/{$name}");

        return file_exists($published) ? $published : __DIR__."/../../resources/images/{$name}";
    }
}
