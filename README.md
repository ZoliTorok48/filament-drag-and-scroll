# Filament Drag and Scroll

Horizontal table scrolling in Filament can be frustrating, especially for users with basic mice that lack a horizontal scroll wheel, trackball users, or anyone working with accessibility needs. This package solves that problem by letting users hold **Shift** and drag to scroll tables horizontally with ease.

## Features

- **Shift + Drag to scroll** - Hold Shift and drag any table to scroll horizontally
- **Per-panel configuration** - Enable for specific Filament panels using a simple method call
- **Visual feedback** - Tooltip and cursor changes guide the user
- **13 languages included** - Automatic translation based on your app locale
- **Zero configuration** - Install, enable, and it just works

## Requirements

- PHP ^8.1
- Laravel ^11.0
- Filament ^5.0

## Installation

```bash
composer require zoltantorok/filament-drag-and-scroll
```

## Usage

Add `->dragAndScroll()` to your panel provider:

```php
use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->dragAndScroll();
    }
}
```

That's it. Your users can now hold **Shift** and drag to scroll any table horizontally.

### Disabling

```php
->dragAndScroll(false)
```

Or simply don't call the method on panels where you don't need it.

## How It Works

1. User holds **Shift** - the cursor changes and a tooltip appears
2. User clicks and drags on a table - the table scrolls horizontally
3. User releases **Shift** - drag mode deactivates

The interaction is designed to stay out of the way. It doesn't interfere with normal table usage, text selection, or other Filament features.

## Translations

The package uses Laravel's translation system and automatically detects your app locale. No configuration required.

### Supported Languages

| Language | Code |
|----------|------|
| English | `en` |
| Spanish | `es` |
| French | `fr` |
| German | `de` |
| Italian | `it` |
| Portuguese | `pt` |
| Dutch | `nl` |
| Russian | `ru` |
| Chinese | `zh` |
| Japanese | `ja` |
| Korean | `ko` |
| Hungarian | `hu` |
| Romanian | `ro` |

### Customizing Translations

Publish the translation files to override them:

```bash
php artisan vendor:publish --tag="filament-drag-and-scroll-views"
```

Or create your own in `resources/lang/vendor/filament-drag-and-scroll/{locale}/messages.php`:

```php
<?php

return [
    'dragToScrollHorizontally' => 'Your custom message',
    'releaseShiftToExit' => 'Your custom exit message',
];
```

## Publishing Assets

Assets are registered automatically. If you need to publish them manually:

```bash
php artisan vendor:publish --tag="filament-drag-and-scroll-assets" --force
```

## Contributing

Contributions are welcome! Feel free to submit a pull request.

## Credits

- [Zoltán Török](https://github.com/ZoliTorok48)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.