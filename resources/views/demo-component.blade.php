<div class="filament-package-container">
    <h2 class="filament-package-title">{{ $title ?? 'Filament Package Demo' }}</h2>
    
    <div class="filament-package-card">
        <p class="filament-package-text">
            {{ $content ?? 'This is a demonstration of the custom Filament package with CSS and JavaScript functionality.' }}
        </p>
        
        @if(isset($showButton) && $showButton)
            <button class="filament-package-button">
                {{ $buttonText ?? 'Click Me!' }}
            </button>
        @endif
    </div>
</div>