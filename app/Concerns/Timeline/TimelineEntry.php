<?php

namespace App\Concerns\Timeline;

use Carbon\Carbon;
use App\Models\User;

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
