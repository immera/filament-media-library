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

This package builds on [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary), which doesn't run its own migration automatically. If your app doesn't already have a `media` table, publish and run it before migrating:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
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

### Styling

This package's views use Tailwind utility classes that aren't part of Filament's default precompiled CSS. If your panel isn't already using a [custom theme](https://filamentphp.com/docs/panels/themes), create one and add this package's views to its `@source` paths, e.g. in `resources/css/filament/admin/theme.css`:

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../vendor/immera/filament-media-library/resources/views/**/*';
```

Then register the theme on your panel with `->viteTheme('resources/css/filament/admin/theme.css')` and rebuild your assets. Without this, elements like the "no media" placeholder icon render unstyled at their native SVG size instead of the intended small icon.

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
