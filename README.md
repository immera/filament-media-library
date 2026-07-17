# Filament Media Library

A folder-based media library and picker field for [Filament](https://filamentphp.com) v4 and v5, built on top of [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary).

## Features

- A full-page media manager (folders, search, sort, upload, tagging, PDF page-count preview) registered as a Filament page.
- A `MediaPicker` form field (extends `ModalTableSelect`) for selecting media from within any Filament form/resource.
- Folder tree organization on top of Spatie Media Library's `media` table.
- Automatic PDF thumbnail placeholder generation.

## Installation

```bash
composer require immera/filament-media-library
php artisan migrate
```

Register the plugin on your panel:

```php
use Immera\FilamentMediaLibrary\FilamentMediaLibraryPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentMediaLibraryPlugin::make(),
        ]);
}
```

Optionally publish the default PDF thumbnail placeholder image to customize it:

```bash
php artisan vendor:publish --tag=filament-media-library-assets
```

## Usage

```php
use Immera\FilamentMediaLibrary\Forms\Components\MediaPicker;

MediaPicker::make('featured_image')
    ->showFileName()
    ->downloadable()
    ->acceptedFileTypes(['image/*']);
```

## License

MIT
