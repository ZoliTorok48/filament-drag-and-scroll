<?php

namespace Torok\FilamentDragAndScroll\Components;

use Illuminate\View\Component;

class DemoComponent extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $content = null,
        public bool $showButton = true,
        public ?string $buttonText = null
    ) {
        //
    }

    public function render()
    {
        return view('filament-drag-and-scroll::demo-component');
    }
}