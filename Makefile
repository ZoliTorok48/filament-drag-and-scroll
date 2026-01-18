# Build and install commands for Filament Drag and Scroll

.PHONY: install build clean dev help

# Install dependencies and build assets
install:
	npm install
	npm run build

# Build minified assets
build:
	npm run build

# Clean build artifacts
clean:
	npm run clean:css
	npm run clean:js

# Development mode with file watching
dev:
	npm run dev

# Show help
help:
	@echo "Available commands:"
	@echo "  make install  - Install dependencies and build assets"
	@echo "  make build    - Build minified assets"
	@echo "  make clean    - Clean build artifacts" 
	@echo "  make dev      - Start development mode with file watching"
	@echo "  make help     - Show this help message"

# Default target
all: install