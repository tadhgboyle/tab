<?php

namespace App\Filament\Resources\ActivityResource\Widgets;

use App\Filament\Resources\ActivityResource;
use App\Models\Activity;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ActivityCalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        return Activity::all()
            ->map(
                fn (Activity $event) => [
                    'title' => $event->name,
                    'start' => $event->start,
                    'end' => $event->end,
                    'url' => ActivityResource::getUrl('view', ['record' => $event]),
                ]
            )
            ->all();
    }
}
