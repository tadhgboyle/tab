@extends('layouts.default', ['page' => 'activities'])
@section('content')
<x-page-header title="Activities" :actions="[
    [
        'label' => 'Create',
        'href' => route('activities_create'),
        'can' => hasPermission(\App\Helpers\Permission::ACTIVITIES_MANAGE)
    ],
]" />

<div class="columns box">
    <div class="column">
        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new FullCalendar.Calendar(document.getElementById('calendar'), {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {!! $activities !!},
        nowIndicator: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
        },
        initialView: 'timeGridWeek',
    }).render();
});
</script>
@endsection
