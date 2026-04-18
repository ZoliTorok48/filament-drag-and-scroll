# Filament Drag and Scroll Assets

This package includes automatic asset minification for CSS and JavaScript files.

## Building Assets

To build and minify assets:

```bash
npm install
npm run build
```

## Development

For development with file watching:

```bash
npm run dev
```

## Publishing Assets

When publishing the package assets, run:

```bash
composer require zoltantorok/filament-drag-and-scroll
php artisan vendor:publish --tag="filament-drag-and-scroll-assets" --force
```

The minified assets will be automatically published to the public directory.

## Asset Structure

- `resources/css/` - Source CSS files
- `resources/js/` - Source JavaScript files  
- `resources/dist/css/` - Minified CSS files (auto-generated)
- `resources/dist/js/` - Minified JavaScript files (auto-generated)