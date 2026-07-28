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
```

This package builds on [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary) and [Spatie Tags](https://github.com/spatie/laravel-tags), neither of which run their migrations automatically. If your app doesn't already have `media` and `tags`/`taggables` tables, publish and run them:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\Tags\TagsServiceProvider" --tag="tags-migrations"
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

Publish Filament's assets so this package's compiled CSS is copied into your `public/` directory:

```bash
php artisan filament:assets
```

No Vite/Node setup is required for this — the package ships a precompiled, self-contained stylesheet and registers it with Filament directly. Re-run this command whenever you update the package. If your app's `composer.json` has a `post-autoload-dump` script calling `@php artisan filament:upgrade` (added by `filament:install`), this happens automatically on every `composer update`.

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
