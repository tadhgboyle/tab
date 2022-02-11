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
        height: 650,
        events: {!! $activities !!},
        dateClick: function(day) {
            @permission('activities_manage')
                if (day.dateStr < new Date().toISOString().split('T')[0]) return;
                let url = '{{ route('activities_new', ':id') }}';
                url = url.replace(':id', day.dateStr);
                location.href = url;
            @endpermission
        },
        allDaySlot: false,
    });

    calendar.render();

});
</script>
@endsection
