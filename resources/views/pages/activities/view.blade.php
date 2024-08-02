@extends('layouts.default', ['page' => 'activities'])
@section('content')
<h2 class="title has-text-weight-bold">View Activity</h2>
<h4 class="subtitle"><strong>Activity:</strong> {{ $activity->name }} @if($activities_manage && !$activity->trashed())<a href="{{ route('activities_edit', $activity->id) }}">(Edit)</a>@endif</h4>
<div class="columns box">
    <div class="column">
        @include('includes.messages')
        <p><strong>Category:</strong> {{ $activity->category->name }}</p>
        <p><strong>Start time:</strong> {{ $activity->start->format('M jS Y h:ia') }}</p>
        <p><strong>End time:</strong> {{ $activity->end->format('M jS Y h:ia') }}</p>
        @if(!is_null($activity->description))<p><strong>Description:</strong> {{ $activity->description }}</p>@endif
        @if(!is_null($activity->location))<p><strong>Location:</strong> {{ $activity->location }}</p>@endif
        <p><strong>Slots:</strong> @if($activity->unlimited_slots) <i>Unlimited</i> @else {{ $activity->slots }} (Available: {{ $activity->slotsAvailable() }})@endif</p>
        <p><strong>Price:</strong> {!! $activity->price->isZero() ? '<i>Free</i>' : $activity->price . " (After tax: {$activity->getPriceAfterTax()})" !!}</p>
        <p><strong>Status:</strong> {!! $activity->getStatusHtml() !!}</p>
    </div>
    <div class="column">
        <div class="columns">
            <div class="column">
                <h4 class="title has-text-weight-bold is-4">Attendees</h4>
            </div>
            <div class="column">
                @if($can_register)
                    <button class="button is-light is-pulled-right is-small" onclick="openModal();">
                        ➕ Add Attendee
                    </button>
                @endif
            </div>
        </div>
        <div id="loading" align="center">
            <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
        </div>
        <div id="table_container" style="visibility: hidden;">
            <table id="attendee_list">
                <thead>
                    <tr>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activity->attendants as $user)
                    <tr>
                        <td>
                            <div>
                                @permission(\App\Helpers\Permission::USERS_VIEW)
                                <a href="{{ route('users_view', $user) }}">{{ $user->full_name }}</a>
                                @else
                                {{ $user->full_name }}
                                @endpermission
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($can_register)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Add Attendee</p>
        </header>
        <section class="modal-card-body">
            <input type="text" class="input" name="search" id="search" placeholder="Search for user">
            <div id="search-div"></div>
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
@endif
<script type="text/javascript">
    $(document).ready(function() {
        $('#attendee_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "language": {
                "emptyTable": "No attendees"
            },
            "searching": false,
            "bInfo": false,
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');

        @if($can_register)
            $('#search_table').DataTable({
                "paging": false,
                "searching": false,
                "bInfo": false,
                "columnDefs": [
                    {
                        "orderable": false,
                        "targets": [0, 1, 2]
                    }
                ],
                "language": {
                    "emptyTable": "No applicable users"
                },
            });
        @endif
    });

    @if($can_register)
        // TODO: on page reload, check if text exists in search box and if so, search automatically
        $('#search').on('keyup', function() {
            if (this.value === undefined || this.value === '') {
                return;
            }
            $.ajax({
                type : "GET",
                url : "{{ route('activities_user_search', $activity->id) }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "search": this.value,
                    "activity": "{{ $activity->id }}"
                },
                beforeSend : function() {
                    $('#search-div').html("<center><img src='{{ url('img/loader.gif') }}' class='loading-spinner'></img></center>");
                },
                success : function(response) {
                    $('#results').html(response);
                    $('#search-div').fadeOut(200);
                },
                error: function(xhr) {
                    $('#results').html("<p style='color: red;'><b>ERROR: </b><br>" + xhr.responseText + "</p>");
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
    @endif
</script>
@endsection
