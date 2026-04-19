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

    /**
     * Register services.
     *
     * @return void
     */
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

    /**
     * Bootstrap services.
     *
     * @return void
     */
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

        // Auto-publish assets if they haven't been published yet
        $this->ensureAssetsArePublished();

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

    /**
     * Get the path to the CSS file, checking for published assets first, then falling back to package resources.
     * This allows the package to work seamlessly in both development and production environments, regardless of whether assets have been published or not.
     *
     * @return string
     */
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

    /**
     * Get the path to the JS file, checking for published assets first, then falling back to package resources.
     * This allows the package to work seamlessly in both development and production environments, regardless of whether assets have been published or not.
     *
     * @return string
     */
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

    /**
     * Ensure that the necessary assets are published to the public directory.
     * This method checks if the assets already exist in the public directory, and if not, it copies them from the package resources.
     * This allows the package to function correctly even if the user hasn't run the publish command, while still allowing for customization if they do.
     *
     * @return void
     */
    protected function ensureAssetsArePublished(): void
    {
        $cssTarget = public_path('css/filament-drag-and-scroll/filament-drag-and-scroll.css');
        $jsTarget = public_path('js/filament-drag-and-scroll/filament-drag-and-scroll.js');

        if (file_exists($cssTarget) && file_exists($jsTarget)) {
            return;
        }

        // Determine source files
        $packagePath = __DIR__ . '/../resources/';
        $cssDist = $packagePath . 'dist/css/filament-drag-and-scroll.min.css';
        $jsDist = $packagePath . 'dist/js/filament-drag-and-scroll.min.js';
        $cssSrc = file_exists($cssDist) ? $cssDist : $packagePath . 'css/filament-drag-and-scroll.css';
        $jsSrc = file_exists($jsDist) ? $jsDist : $packagePath . 'js/filament-drag-and-scroll.js';

        // Create target directories
        if (! is_dir(dirname($cssTarget))) {
            mkdir(dirname($cssTarget), 0755, true);
        }
        if (! is_dir(dirname($jsTarget))) {
            mkdir(dirname($jsTarget), 0755, true);
        }

        // Copy assets
        if (file_exists($cssSrc) && ! file_exists($cssTarget)) {
            copy($cssSrc, $cssTarget);
        }
        if (file_exists($jsSrc) && ! file_exists($jsTarget)) {
            copy($jsSrc, $jsTarget);
        }
    }
}
