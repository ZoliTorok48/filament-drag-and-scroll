<?php

namespace Torok\FilamentDragAndScroll;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Torok\FilamentDragAndScroll\Components\DemoComponent;

class FilamentDragAndScrollServiceProvider extends ServiceProvider
{
    public static string $name = 'filament-drag-and-scroll';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Determine if we should use minified assets
        $useMinified = !config('app.debug', false);
        $cssPath = $useMinified ? 'dist/css' : 'css';
        $jsPath = $useMinified ? 'dist/js' : 'js';
        $cssExt = $useMinified ? '.min.css' : '.css';
        $jsExt = $useMinified ? '.min.js' : '.js';

        // Note: Core assets are not auto-registered with Filament
        // They will only be published manually when needed

        // Publish assets
        if ($this->app->runningInConsole()) {
            // Publish minified assets to public directory without .min suffix
            $this->publishes([
                __DIR__ . '/../resources/dist/css/filament-drag-and-scroll.min.css' => public_path('css/filament-drag-and-scroll/filament-drag-and-scroll.css'),
            ], 'filament-drag-and-scroll-css');

            $this->publishes([
                __DIR__ . '/../resources/dist/js/filament-drag-and-scroll.min.js' => public_path('js/filament-drag-and-scroll/filament-drag-and-scroll.js'),
            ], 'filament-drag-and-scroll-js');

            $this->publishes([
                __DIR__ . '/../resources/dist/css/filament-drag-and-scroll.min.css' => public_path('css/filament-drag-and-scroll/filament-drag-and-scroll.css'),
                __DIR__ . '/../resources/dist/js/filament-drag-and-scroll.min.js' => public_path('js/filament-drag-and-scroll/filament-drag-and-scroll.js'),
            ], 'filament-drag-and-scroll-assets');

            // Publish core filament package files specifically (minified versions without .min suffix)
            $this->publishes([
                __DIR__ . '/../resources/dist/css/filament-drag-and-scroll.min.css' => public_path('css/filament-drag-and-scroll.css'),
                __DIR__ . '/../resources/dist/js/filament-drag-and-scroll.min.js' => public_path('js/filament-drag-and-scroll.js'),
            ], 'filament-drag-and-scroll-core');

            // Fallback to source files if dist doesn't exist (development)
            if (!is_dir(__DIR__ . '/../resources/dist')) {
                $this->publishes([
                    __DIR__ . '/../resources/css/filament-drag-and-scroll.css' => public_path('css/filament-drag-and-scroll.css'),
                    __DIR__ . '/../resources/js/filament-drag-and-scroll.js' => public_path('js/filament-drag-and-scroll.js'),
                ], 'filament-drag-and-scroll-source');
            }
        }

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-drag-and-scroll');

        // Register Blade component
        Blade::component('filament-drag-and-scroll::demo-component', DemoComponent::class);

        // Publish views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-drag-and-scroll'),
            ], 'filament-drag-and-scroll-views');
        }
    }
}