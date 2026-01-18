#!/usr/bin/env php
<?php

/**
 * Build script for minifying assets automatically during composer install
 */

class AssetBuilder
{
    private string $basePath;
    private array $cssFiles;
    private array $jsFiles;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__);
        $this->cssFiles = ['filament-drag-and-scroll.css'];
        $this->jsFiles = ['filament-drag-and-scroll.js'];
    }

    public function build(): void
    {
        echo "Building Filament Drag and Scroll assets...\n";

        // Create dist directories
        $this->createDistDirectories();

        // Check if we're in development mode (has package.json and node_modules)
        if ($this->hasNpmEnvironment()) {
            echo "Development environment detected, running npm build...\n";
            $this->buildWithNpm();
        } else {
            echo "Production environment detected, using fallback PHP minification...\n";
            $this->buildWithPhp();
        }

        echo "Asset build process completed.\n";
    }

    private function createDistDirectories(): void
    {
        $cssDir = $this->basePath . '/resources/dist/css';
        $jsDir = $this->basePath . '/resources/dist/js';

        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }

        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0755, true);
        }
    }

    private function hasNpmEnvironment(): bool
    {
        return file_exists($this->basePath . '/package.json') && 
               is_dir($this->basePath . '/node_modules');
    }

    private function buildWithNpm(): void
    {
        $output = [];
        $returnVar = 0;
        
        // Change to package directory
        chdir($this->basePath);
        
        exec('npm run build 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "✓ Assets built successfully with npm\n";
            foreach ($output as $line) {
                echo "  $line\n";
            }
        } else {
            echo "⚠ npm build failed, falling back to PHP minification\n";
            foreach ($output as $line) {
                echo "  $line\n";
            }
            $this->buildWithPhp();
        }
    }

    private function buildWithPhp(): void
    {
        // Minify CSS files
        foreach ($this->cssFiles as $file) {
            $this->minifyCssFile($file);
        }

        // Minify JS files
        foreach ($this->jsFiles as $file) {
            $this->minifyJsFile($file);
        }

        echo "✓ Assets built successfully with PHP fallback\n";
    }

    private function minifyCssFile(string $filename): void
    {
        $sourcePath = $this->basePath . "/resources/css/{$filename}";
        $outputPath = $this->basePath . "/resources/dist/css/" . str_replace('.css', '.min.css', $filename);

        if (!file_exists($sourcePath)) {
            echo "⚠ Warning: CSS file not found: {$sourcePath}\n";
            return;
        }

        $css = file_get_contents($sourcePath);
        $minified = $this->minifyCSS($css);
        file_put_contents($outputPath, $minified);
        
        echo "  ✓ Minified CSS: {$filename}\n";
    }

    private function minifyJsFile(string $filename): void
    {
        $sourcePath = $this->basePath . "/resources/js/{$filename}";
        $outputPath = $this->basePath . "/resources/dist/js/" . str_replace('.js', '.min.js', $filename);

        if (!file_exists($sourcePath)) {
            echo "⚠ Warning: JS file not found: {$sourcePath}\n";
            return;
        }

        $js = file_get_contents($sourcePath);
        $minified = $this->minifyJS($js);
        file_put_contents($outputPath, $minified);
        
        echo "  ✓ Minified JS: {$filename}\n";
    }

    private function minifyCSS(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove whitespace around certain characters
        $css = preg_replace('/\s*([{}|:;,>+~])\s*/', '$1', $css);
        
        // Remove trailing semicolon before closing brace
        $css = preg_replace('/;(\s*})/', '$1', $css);
        
        return trim($css);
    }

    private function minifyJS(string $js): string
    {
        // Remove single line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove whitespace around operators and punctuation
        $js = preg_replace('/\s*([{}|:;,()=+\-*\/])\s*/', '$1', $js);
        
        return trim($js);
    }
}

// Run the build process
$builder = new AssetBuilder();
$builder->build();