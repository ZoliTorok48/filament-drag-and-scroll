# Filament Drag and Scroll

A Filament package that adds configurable drag and scroll functionality to admin panels with automatic CSS and JS asset optimization.

## Features

- 🎯 **Per-panel configuration** - Enable drag and scroll only for specific Filament panels
- 🖱️ **Intuitive interaction** - Hold Shift + drag to scroll tables horizontally  
- 🚀 Automatic asset minification during composer install/update
- ⚡ Production-ready minified CSS and JS files
- 🔄 Development mode with file watching
- 📦 Smart fallback minification using PHP when npm is not available
- 🎯 Optimized for both development and production environments
- 💡 Visual feedback with helpful tooltips

## Installation

```bash
composer require zoltantorok/filament-drag-and-scroll
```

## Asset Management

### Automatic Build Process

Assets are automatically built and minified when you:
- Run `composer install`
- Run `composer update`
- Manually execute `php scripts/build-assets.php`

### Development Setup

For development with automatic rebuilding:

```bash
# Install dependencies
npm install

# Start development mode with file watching
npm run dev
```

### Manual Building

```bash
# Build all assets
npm run build

# Build only CSS
npm run build:css

# Build only JS  
npm run build:js

# Clean build artifacts
npm run clean
```

## Asset Structure

```
resources/
├── css/                     # Source CSS files
│   └── filament-drag-and-scroll.css
├── js/                      # Source JavaScript files  
│   └── filament-drag-and-scroll.js
└── dist/                    # Generated minified files (auto-created)
    ├── css/
    │   └── filament-drag-and-scroll.min.css
    └── js/
        └── filament-drag-and-scroll.min.js
```

## Publishing Assets

### Publish All Assets

```bash
php artisan vendor:publish --tag="filament-drag-and-scroll-assets" --force
```

### Specific Asset Types

```bash
# CSS only
php artisan vendor:publish --tag="filament-drag-and-scroll-css" --force

# JS only
php artisan vendor:publish --tag="filament-drag-and-scroll-js" --force

# Core files only
php artisan vendor:publish --tag="filament-drag-and-scroll-core" --force

# Views
php artisan vendor:publish --tag="filament-drag-and-scroll-views"
```

## How It Works

### Production Mode (APP_DEBUG=false)
- Assets are served from `resources/dist/` (minified versions)
- Files are published as `.min.css` and `.min.js`
- Optimized for performance

### Development Mode (APP_DEBUG=true)
- Assets are served from `resources/css/` and `resources/js/` (source versions)
- No minification applied for easier debugging
- Full source maps and readable code

### Build Process

1. **NPM Available**: Uses professional minifiers (clean-css and terser)
2. **NPM Unavailable**: Falls back to PHP-based minification
3. **No Build Tools**: Serves source files directly

### Composer Integration

The package includes composer post-install and post-update hooks that automatically:
1. Detect the environment (development vs production)
2. Run the appropriate build process
3. Generate minified assets
4. Ensure assets are ready for publishing

## Usage

After installation, you can enable drag and scroll functionality for specific Filament admin panels by calling the `dragAndScroll()` method on your panel configuration.

### Enable for a Panel

In your **project's** panel provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`):

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            // ... your existing configuration ...
            ->dragAndScroll(); // Add this method call to enable drag and scroll
    }
}
```

**Note**: This code goes in your Laravel application that has installed this package, not in the package itself.

### Multiple Panels

If you have multiple Filament panels in your application, you can choose which ones should have drag and scroll functionality:

```php
// Enable for admin panel
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ... configuration ...
            ->dragAndScroll(); // Enabled
    }
}

// Don't enable for user panel
class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ... configuration ...
            // No ->dragAndScroll() call means this panel won't have the functionality
    }
}
```

### How it Works

Once enabled for a panel:
- Hold **Shift** and click on any table to activate drag mode
- Drag horizontally to scroll through table columns
- Release **Shift** to exit drag mode
- A helpful tooltip will appear when drag mode is active

The package automatically:
- Registers CSS and JS assets only for enabled panels
- Provides drag and scroll functionality for Filament tables
- Handles asset optimization and minification
- Shows visual feedback during drag operations

## Configuration

The package automatically detects your environment and chooses the best asset version:

- **Production**: Serves minified `.min.css` and `.min.js` files
- **Development**: Serves source `.css` and `.js` files for easier debugging

## Published Files Location

When assets are published, they are copied to:

```
public/
├── css/
│   └── filament-drag-and-scroll.css
└── js/
    └── filament-drag-and-scroll.js
```

## Requirements

### Runtime Requirements
- PHP ^8.1
- Filament ^5.0

### Development Requirements (Optional)
- Node.js (for advanced minification)
- npm

## Assets

- **CSS**: Contains custom styles for drag and scroll functionality
- **JavaScript**: Provides interactive drag and scroll functionality for Filament tables
- **filament-drag-and-scroll.css**: Core CSS file for Filament integration (minified, published without .min suffix)
- **filament-drag-and-scroll.js**: Core JavaScript file for Filament integration (minified, published without .min suffix)

## Features

- 🎨 Custom CSS with modern design
- ✨ Interactive JavaScript components
- 📦 Easy asset publishing
- 🔧 Blade component included
- 🚀 Filament 5.x compatible

## License

MIT License