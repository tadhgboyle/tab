<?php

namespace App\Concerns\Timeline;

interface HasTimeline
{
    /**
     * Get the timeline of the model.
     *
     * @return TimelineEntry[]
     */
    public function timeline(): array;
}
