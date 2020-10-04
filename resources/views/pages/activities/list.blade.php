@extends('layouts.default', ['page' => 'activities'])
@section('content')
<h2 class="title has-text-weight-bold">Activities</h2>
@include('includes.messages')
<div class="columns box">
    <div class="column">
        <div id="calendar"></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {!! $activities !!},
        eventClick: function(event) {
            if (event.event.url) {
                event.jsEvent.preventDefault();
                window.open(event.event.url, '_blank');
            }
        },
        allDaySlot: false,
    });

    calendar.render();

});
</script>
@endsection