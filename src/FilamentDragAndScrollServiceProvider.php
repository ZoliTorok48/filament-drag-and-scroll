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

        // Publish source assets (will be minified automatically on boot)
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/css/filament-drag-and-scroll.css' => public_path('css/filament-drag-and-scroll/filament-drag-and-scroll.css'),
                __DIR__ . '/../resources/js/filament-drag-and-scroll.js' => public_path('js/filament-drag-and-scroll/filament-drag-and-scroll.js'),
            ], 'filament-drag-and-scroll-assets');
        }

        // Auto-minify and publish assets if they haven't been published yet
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
        // Check if published (minified) version exists
        $publishedPath = public_path('css/filament-drag-and-scroll/filament-drag-and-scroll.css');
        if (file_exists($publishedPath)) {
            return $publishedPath;
        }

        // Fallback to source
        return __DIR__ . '/../resources/css/filament-drag-and-scroll.css';
    }

    /**
     * Get the path to the JS file, checking for published assets first, then falling back to package resources.
     * This allows the package to work seamlessly in both development and production environments, regardless of whether assets have been published or not.
     *
     * @return string
     */
    public static function getDragScrollJsPath(): string
    {
        // Check if published (minified) version exists
        $publishedPath = public_path('js/filament-drag-and-scroll/filament-drag-and-scroll.js');
        if (file_exists($publishedPath)) {
            return $publishedPath;
        }

        // Fallback to source
        return __DIR__ . '/../resources/js/filament-drag-and-scroll.js';
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

        $packagePath = __DIR__ . '/../resources/';

        // Create target directories
        if (! is_dir(dirname($cssTarget))) {
            mkdir(dirname($cssTarget), 0755, true);
        }
        if (! is_dir(dirname($jsTarget))) {
            mkdir(dirname($jsTarget), 0755, true);
        }

        // Minify and publish CSS
        if (! file_exists($cssTarget)) {
            $cssSrc = $packagePath . 'css/filament-drag-and-scroll.css';
            if (file_exists($cssSrc)) {
                $css = file_get_contents($cssSrc);
                file_put_contents($cssTarget, self::minifyCss($css));
            }
        }

        // Minify and publish JS
        if (! file_exists($jsTarget)) {
            $jsSrc = $packagePath . 'js/filament-drag-and-scroll.js';
            if (file_exists($jsSrc)) {
                $js = file_get_contents($jsSrc);
                file_put_contents($jsTarget, self::minifyJs($js));
            }
        }
    }

    /**
     * Simple CSS minification function to reduce file size for production use.
     * This is a basic implementation and may not cover all edge cases, but it should work well for typical CSS files.
     *
     * @param string $css The original CSS content
     * @return string The minified CSS content
     */
    protected static function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        // Remove whitespace around certain characters
        $css = preg_replace('/\s*([{}|:;,>+~])\s*/', '$1', $css);
        // Remove trailing semicolon before closing brace
        $css = preg_replace('/;(\s*})/', '$1', $css);

        return trim($css);
    }

    /**
     * Simple JS minification function to reduce file size for production use.
     * This is a basic implementation and may not cover all edge cases, but it should work well for typical JS files.
     *
     * @param string $js The original JS content
     * @return string The minified JS content
     */
    protected static function minifyJs(string $js): string
    {
        // Remove single-line comments (but not URLs with //)
        $js = preg_replace('#(?<!:)//(?!/).*$#m', '', $js);
        // Remove multi-line comments
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        // Collapse whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        // Remove whitespace around operators and punctuation
        $js = preg_replace('/\s*([{}:;,()=+\-*\/<>!&|?])\s*/', '$1', $js);

        return trim($js);
    }
}
