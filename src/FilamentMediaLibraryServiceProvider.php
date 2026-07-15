<?php

namespace Immera\FilamentMediaLibrary;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Immera\FilamentMediaLibrary\Livewire\MediaPickerBrowser;
use Immera\FilamentMediaLibrary\Models\MediaLibraryItem;
use Immera\FilamentMediaLibrary\Support\PdfThumbnailGenerator;
use Livewire\Livewire;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf as SpatiePdfImageGenerator;

class FilamentMediaLibraryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-media-library');

        Livewire::component('filament-media-library-picker-browser', MediaPickerBrowser::class);

        Relation::morphMap([
            'filament_media_library_item' => MediaLibraryItem::class,
        ]);

        $this->registerPdfThumbnailGenerator();

        $this->publishes([
            __DIR__.'/../resources/images/pdf-default.png' => storage_path('app/public/vendor/filament-media-library/pdf-default.png'),
        ], 'filament-media-library-assets');
    }

    protected function registerPdfThumbnailGenerator(): void
    {
        $generators = collect(config('media-library.image_generators', []))
            ->reject(fn (string $generator): bool => $generator === SpatiePdfImageGenerator::class)
            ->push(PdfThumbnailGenerator::class)
            ->unique()
            ->values()
            ->all();

        config(['media-library.image_generators' => $generators]);
    }
}
