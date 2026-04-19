class FilamentTableDragScroll {
    constructor() {
        this.isMouseDown = false;
        this.startX = null;
        this.scrollLeft = null;
        this.isDragging = false;
        this.scrollableElement = null;
        this.preventClick = false;
        this.translations = {};

        // Store bound function references to properly remove event listeners
        this.boundOnMouseDown = this.onMouseDown.bind(this);
        this.boundOnMouseLeave = this.onMouseLeave.bind(this);
        this.boundOnMouseUp = this.onMouseUp.bind(this);
        this.boundOnMouseMove = this.onMouseMove.bind(this);
        this.boundOnClickDuringDrag = this.onClickDuringDrag.bind(this);

        this.init();
    }

    async init() {
        // Only initialize if drag and scroll is enabled for this panel
        if (!this.isDragScrollEnabled()) {
            return;
        }

        // Load translations first
        await this.loadTranslations();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupEventListeners());
        } else {
            this.setupEventListeners();
        }
    }

    async loadTranslations() {
        try {
            // First check if translations are already provided by the server
            if (window.dragScrollTranslations) {
                this.translations = window.dragScrollTranslations;
                console.log('Drag scroll: Using server-provided translations');
                return;
            }

            // Fallback to file-based translation loading
            let language = 'en'; // Default fallback
            let detectionMethod = 'default';

            // First, try to get Laravel app locale
            if (window.Laravel && window.Laravel.locale) {
                language = window.Laravel.locale;
                detectionMethod = 'window.Laravel.locale';
            } 
            // Check for Filament locale (often used in Filament apps)
            else if (window.Filament && window.Filament.locale) {
                language = window.Filament.locale;
                detectionMethod = 'window.Filament.locale';
            }
            // Check for locale meta tag
            else if (document.querySelector('meta[name="locale"]')) {
                language = document.querySelector('meta[name="locale"]').getAttribute('content');
                detectionMethod = 'meta[name="locale"]';
            }
            // Check for global appLocale variable
            else if (window.appLocale) {
                language = window.appLocale;
                detectionMethod = 'window.appLocale';
            }
            // Check for locale in HTML lang attribute
            else if (document.documentElement.lang) {
                language = document.documentElement.lang;
                detectionMethod = 'html[lang]';
            }
            // Fall back to browser language detection
            else {
                language = navigator.language || navigator.userLanguage || 'en';
                language = language.split('-')[0]; // Get base language (e.g., 'en' from 'en-US')
                detectionMethod = 'browser';
            }

            // Ensure we have just the base language code
            language = language.split('-')[0].toLowerCase();

            // List of supported languages
            const supportedLanguages = ['en', 'es', 'fr', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'ko', 'hu', 'ro'];
            
            // Use supported language or fall back to English
            if (!supportedLanguages.includes(language)) {
                console.log(`Drag scroll: Language '${language}' not supported, falling back to English`);
                language = 'en';
            }

            console.log(`Drag scroll: Detected language '${language}' via ${detectionMethod}`);

            // Try to load the translation file
            const response = await fetch(`/vendor/filament-drag-and-scroll/lang/${language}.json`);
            if (response.ok) {
                this.translations = await response.json();
                console.log(`Drag scroll: Successfully loaded ${language} translations from file`);
            } else {
                console.warn(`Drag scroll: Failed to load ${language} translations (${response.status}), using fallback`);
                // Fallback to default English translations
                this.translations = {
                    "dragToScrollHorizontally": "Drag to scroll horizontally",
                    "releaseShiftToExit": "Release Shift to exit"
                };
            }
        } catch (error) {
            console.error('Drag scroll: Failed to load translations:', error);
            // Fallback to default English translations
            this.translations = {
                "dragToScrollHorizontally": "Drag to scroll horizontally",
                "releaseShiftToExit": "Release Shift to exit"
            };
        }
    }

    isDragScrollEnabled() {
        return document.querySelector('[data-drag-scroll-enabled="true"]') !== null;
    }

    setupEventListeners() {
        // Listen for keydown to activate drag mode
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Shift' && !this.isDragging && !e.repeat) {
                this.activateDragMode();
            }
        });

        // Listen for keyup to deactivate drag mode
        document.addEventListener('keyup', (e) => {
            if (e.key === 'Shift' && this.isDragging) {
                this.deactivateDragMode();
            }
        });

        // Prevent default Shift behavior that might interfere
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Shift' && this.isDragging) {
                e.preventDefault();
            }
        });

        // Also deactivate when window loses focus
        window.addEventListener('blur', () => {
            if (this.isDragging) {
                this.deactivateDragMode();
            }
        });

        // Prevent click events during drag
        document.addEventListener('click', (e) => {
            if (this.preventClick) {
                e.preventDefault();
                e.stopPropagation();
                this.preventClick = false;
            }
        }, true);
    }

    activateDragMode() {
        this.isDragging = true;
        document.body.classList.add('drag-scroll-active');

        // Temporary listener to detect which table is clicked
        this.tempTableClickHandler = (e) => {
            // Find the table container
            const container = e.target.closest('.fi-table-container') || e.target.closest('table')?.parentElement;
            if (!container) return;

            this.scrollableElement = container;

            // Add event listeners to the scrollable element using bound references
            this.scrollableElement.addEventListener('mousedown', this.boundOnMouseDown);
            this.scrollableElement.addEventListener('mouseleave', this.boundOnMouseLeave);
            this.scrollableElement.addEventListener('mouseup', this.boundOnMouseUp);
            this.scrollableElement.addEventListener('mousemove', this.boundOnMouseMove);

            // Prevent any link/button clicks during drag
            this.scrollableElement.addEventListener('click', this.boundOnClickDuringDrag, true);

            this.showTooltip();

            // Remove temporary listener once a table is selected
            document.removeEventListener('mousedown', this.tempTableClickHandler, true);
            this.tempTableClickHandler = null;
        };

        document.addEventListener('mousedown', this.tempTableClickHandler, true);
    }

    deactivateDragMode() {
        this.isDragging = false;
        this.isMouseDown = false;
        document.body.classList.remove('drag-scroll-active', 'grabbing');
        this.preventClick = false;

        if (this.scrollableElement) {
            // Remove event listeners using the same bound references
            this.scrollableElement.removeEventListener('mousedown', this.boundOnMouseDown);
            this.scrollableElement.removeEventListener('mouseleave', this.boundOnMouseLeave);
            this.scrollableElement.removeEventListener('mouseup', this.boundOnMouseUp);
            this.scrollableElement.removeEventListener('mousemove', this.boundOnMouseMove);
            this.scrollableElement.removeEventListener('click', this.boundOnClickDuringDrag, true);
            this.scrollableElement = null;
        }

        // Remove temporary table selection listener if still active
        if (this.tempTableClickHandler) {
            document.removeEventListener('mousedown', this.tempTableClickHandler, true);
            this.tempTableClickHandler = null;
        }

        this.hideTooltip();
    }

    onMouseDown(e) {
        if (!this.isDragging) return;

        this.isMouseDown = true;
        this.startX = e.pageX - this.scrollableElement.offsetLeft;
        this.scrollLeft = this.scrollableElement.scrollLeft;
        document.body.classList.add('grabbing');

        // Prevent text selection
        e.preventDefault();
    }

    onMouseLeave() {
        if (!this.isDragging) return;
        this.isMouseDown = false;
        document.body.classList.remove('grabbing');
    }

    onMouseUp() {
        if (!this.isDragging) return;
        this.isMouseDown = false;
        document.body.classList.remove('grabbing');
        this.preventClick = true;

        // Small delay to ensure click prevention
        setTimeout(() => {
            this.preventClick = false;
        }, 100);
    }

    onMouseMove(e) {
        if (!this.isDragging || !this.isMouseDown) return;

        e.preventDefault();
        const x = e.pageX - this.scrollableElement.offsetLeft;
        const walk = (x - this.startX) * 2;
        this.scrollableElement.scrollLeft = this.scrollLeft - walk;
    }

    onClickDuringDrag(e) {
        if (this.isMouseDown) {
            e.preventDefault();
            e.stopPropagation();
            this.preventClick = true;
        }
    }

    showTooltip() {
        this.hideTooltip();

        // Use loaded translations or fallback to English
        const translations = this.translations || {
            "dragToScrollHorizontally": "Drag to scroll horizontally",
            "releaseShiftToExit": "Release Shift to exit"
        };

        this.tooltip = document.createElement('div');
        this.tooltip.innerHTML = `
            <div style="display: flex; gap: 8px; align-items: flex-start;">
                <span style="font-size: 18px;">🎯</span>
                <div>
                    <div>${translations.dragToScrollHorizontally}</div>
                    <div style="font-weight: 300;">${translations.releaseShiftToExit}</div>
                </div>
            </div>
        `;
        const isDark = document.documentElement.classList.contains('dark');

        this.tooltip.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 15px;
            border-radius: 0.75rem;
            font-size: 14px;
            font-weight: 500;
            z-index: 10000;
            animation: fadeIn 0.2s ease-out;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: ${isDark ? '#111827' : '#ffffff'};
            color: ${isDark ? '#f9fafb' : '#111827'};
            border: 1px solid ${isDark ? '#374151' : '#e5e7eb'};
        `;

        document.body.appendChild(this.tooltip);

        const hideOnRelease = () => {
            this.hideTooltip();
            document.removeEventListener('mouseup', hideOnRelease);
        };

        document.addEventListener('mouseup', hideOnRelease);
    }

    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
        if (this.tooltipTimeout) {
            clearTimeout(this.tooltipTimeout);
        }
    }
}

// Initialize
new FilamentTableDragScroll();
