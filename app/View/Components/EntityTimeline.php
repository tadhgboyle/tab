<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Concerns\Timeline\TimelineEntry;

class EntityTimeline extends Component
{
    /**
     * Create a new component instance.
     *
     * @param TimelineEntry[] $timeline
     *
     * @return void
     */
    public function __construct(
        public array $timeline
    ) {
    }

    public function timeline(): array
    {
        return array_reverse($this->timeline);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.entity-timeline');
    }
}
