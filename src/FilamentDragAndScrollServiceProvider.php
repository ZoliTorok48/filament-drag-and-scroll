<?php

namespace ZoltanTorok\FilamentDragAndScroll;

use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\ServiceProvider;

class FilamentDragAndScrollServiceProvider extends ServiceProvider
{
    public static string $name = 'filament-drag-and-scroll';

    public function register(): void
    {
        // Add dragAndScroll() method to Panel class via macro
        Panel::macro('dragAndScroll', function (bool $enabled = true): Panel {
            /** @var Panel $this */
            if ($enabled) {
                $this->renderHook(
                    'panels::body.start',
                    fn (): string => '<div data-drag-scroll-enabled="true" style="display: none;"></div>'
                );

                // Register assets for this specific panel
                FilamentAsset::register([
                    Css::make('filament-drag-and-scroll', FilamentDragAndScrollServiceProvider::getDragScrollCssPath()),
                    Js::make('filament-drag-and-scroll', FilamentDragAndScrollServiceProvider::getDragScrollJsPath()),
                ], package: 'filament-drag-and-scroll');
            } else {
                // Explicitly disable drag and scroll functionality
                $this->renderHook(
                    'panels::body.start',
                    fn (): string => '<div data-drag-scroll-enabled="false" style="display: none;"></div>'
                );
            }

            return $this;
        });

        // Add translations for the drag scroll functionality
        FilamentView::registerRenderHook(
            'panels::body.end',
            static fn () => '<script>
                window.dragScrollTranslations = {
                    "dragToScrollHorizontally": "' . __('filament-drag-and-scroll::messages.dragToScrollHorizontally') . '",
                    "releaseShiftToExit": "' . __('filament-drag-and-scroll::messages.releaseShiftToExit') . '"
                };
            </script>',
        );
    }

    public function boot(): void
    {
        // Only publish assets, don't auto-register them
        // Assets are registered per-panel when ->dragAndScroll() is called

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

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-drag-and-scroll');

        // Publish views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-drag-and-scroll'),
            ], 'filament-drag-and-scroll-views');
        }
    }

    public static function getDragScrollCssPath(): string
    {
        // Check if minified version exists (production)
        $minifiedPath = public_path('css/filament-drag-and-scroll/filament-drag-and-scroll.css');
        if (file_exists($minifiedPath)) {
            return $minifiedPath;
        }

        // Fallback to package resources
        $packagePath = __DIR__ . '/../resources/';
        $distCss = $packagePath . 'dist/css/filament-drag-and-scroll.min.css';
        
        if (file_exists($distCss)) {
            return $distCss;
        }

        // Final fallback to source
        return $packagePath . 'css/filament-drag-and-scroll.css';
    }

    public static function getDragScrollJsPath(): string
    {
        // Check if minified version exists (production)
        $minifiedPath = public_path('js/filament-drag-and-scroll/filament-drag-and-scroll.js');
        if (file_exists($minifiedPath)) {
            return $minifiedPath;
        }

        // Fallback to package resources
        $packagePath = __DIR__ . '/../resources/';
        $distJs = $packagePath . 'dist/js/filament-drag-and-scroll.min.js';
        
        if (file_exists($distJs)) {
            return $distJs;
        }

        // Final fallback to source
        return $packagePath . 'js/filament-drag-and-scroll.js';
    }
}