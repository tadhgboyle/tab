@php

use App\Activity;
$activity = Activity::find(request()->route('id'));
if ($activity == null) return redirect()->route('activities_list')->with('error', 'Invalid activity.')->send();
@endphp
@extends('layouts.default', ['page' => 'activities'])
@section('content')
<h2 class="title has-text-weight-bold">View Activity</h2>
<div class="columns box">
    <div class="column">
        @include('includes.messages')
        <p><strong>Name:</strong> {{ $activity->name }}</p>
        <p><strong>Start time:</strong> {{ $activity->start->format('M jS Y h:ia') }}</p>
        <p><strong>End time:</strong> {{ $activity->end->format('M jS Y h:ia') }}</p>
        @if(!is_null($activity->description))<p><strong>Description:</strong> {{ $activity->description }}</p>@endif
        @if(!is_null($activity->location))<p><strong>Location:</strong> {{ $activity->location }}</p>@endif
        <p><strong>Slots:</strong> {{ $activity->slots }} (Available: {{ $activity->slotsAvailable() }})</p>
        <p><strong>Price:</strong> {!! $activity->price > 0 ? '$' . number_format($activity->price, 2) : '<i>Free</i>' !!}</p>
        <p><strong>Status:</strong> {!! $activity->getStatus() !!}</p>
        <br>
        <button class="button is-success" type="button" onclick="openModal();">
            <span class="icon is-small">
                <i class="fas fa-plus"></i>
            </span>
            <span>Add Attendee</span>
        </button>
    </div>
    <div class="column">
        <h4 class="title has-text-weight-bold is-4">Attendees</h4>
        <div id="loading" align="center">
            <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
        </div>
        <div id="table_container" style="visibility: hidden;">
            <table id="attendee_list">
                <thead>
                    <th>Name</th>
                </thead>
                <tbody>
                    @foreach($activity->getAttendees() as $user)
                    <tr>
                        <td>
                            <div><a href="{{ route('users_view', $user->id) }}">{{ $user->full_name }}</a></div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Add Attendee</p>
        </header>
        <section class="modal-card-body">
            <input type="text" class="input" name="search" id="search" placeholder="Search for user">
            <table id="search_table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="results"></tbody>
            </table>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#attendee_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "language": {
                "emptyTable": "No attendees"
            }
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');
        $('#search_table').DataTable({
            "paging": false,
            "searching": false,
            "bInfo": false,
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [0, 1, 2]
                }
            ]
        });
    });

    $('#search').on('keyup', function() {
        if (this.value == undefined || this.value == '') return;
        $.ajax({
            type : "POST",
            url : "{{ route('activities_user_search') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "search": this.value,
                "activity": "{{ $activity->id }}"
            },
            beforeSend : function() {
                $('#results').show().html("<center><img src='{{ url('loader.gif') }}' class='loading-spinner'></img></center>");
            },
            success : function(response) {
                $('#results').html(response);
            },
            error: function(xhr, status, error) {
                $('#results').show().html("<p style='color: red;'><b>ERROR: </b><br>" + xhr.responseText + "</p>");
            }
        });
    });

    const modal = document.querySelector('.modal');
    
    function openModal() {
        modal.classList.add('is-active');
    }
    
    function closeModal() {
        modal.classList.remove('is-active');
    }
</script>
@endsection