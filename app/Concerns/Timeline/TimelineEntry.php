<?php

namespace App\Concerns\Timeline;
use App\Models\User;
use Carbon\Carbon;

class TimelineEntry
{
    public function __construct(
        public ?string $description = null,
        public string $emoji,
        public Carbon $time,
        public ?User $actor = null,
    ) {
        //
    }
}