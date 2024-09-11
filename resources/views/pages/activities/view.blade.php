@extends('layouts.default', ['page' => 'activities'])
@section('content')
<h2 class="title has-text-weight-bold">View Activity</h2>
<h4 class="subtitle"><strong>Activity:</strong> {{ $activity->name }} @permission(\App\Helpers\Permission::ACTIVITIES_MANAGE)<a href="{{ route('activities_edit', $activity->id) }}">(Edit)</a>@endpermission</h4>
<div class="columns">
    <div class="column is-two-thirds">
        <livewire:activities.registrations-list :activity="$activity" />
    </div>
    <div class="column">
        <x-detail-card title="Details">
            <p><strong>Category:</strong> {{ $activity->category->name }}</p>
            @if(!is_null($activity->description))<p><strong>Description:</strong> {{ $activity->description }}</p>@endif
            @if(!is_null($activity->location))<p><strong>Location:</strong> {{ $activity->location }}</p>@endif
            <p><strong>Slots:</strong> @if($activity->unlimited_slots) <i>Unlimited</i> @else {{ $activity->slots }} - {{ $activity->slotsAvailable() }} available @endif</p>
            <p><strong>Status:</strong> {!! $activity->getStatusHtml() !!}</p>
        </x-detail-card>
        <x-detail-card title="Timing">
            @unless($activity->ended())
                <p><strong>Starts in:</strong> {{ $activity->countdown() }}</p>
            @endunless
            <p><strong>Starts at:</strong> {{ $activity->start->format('M jS Y h:ia') }}</p>
            <p><strong>Ends at:</strong> {{ $activity->end->format('M jS Y h:ia') }}</p>
            <p><strong>Duration:</strong> {{ $activity->duration() }}</p>
        </x-detail-card>
        <x-detail-card title="Pricing">
            <p><strong>Price:</strong> {!! $activity->price->isZero() ? '<i>Free</i>' : $activity->price !!}</p>
            <p><strong>PST:</strong> {{ $activity->pst ? '✅' : '❌' }}</p>
        </x-detail-card>
        <x-entity-timeline :timeline="$activity->timeline()" />
    </div>
</div>

@if($can_register)
<div class="modal" id="search-users-modal">
    <div class="modal-background" onclick="closeSearchUsersModal();"></div>
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
            <button class="button" onclick="closeSearchUsersModal();">Cancel</button>
        </footer>
    </div>
</div>
@endif

@if($can_remove)
<div class="modal" id="remove-user-modal">
    <div class="modal-background" onclick="closeRemoveUserModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Remove Attendee</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to remove this user from the activity?</p>
        </section>
        <footer class="modal-card-foot">
            <form method="POST" id="remove-user-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="user_id" id="user_id" value="">
                <button type="submit" class="button is-danger">Remove</button>
                <button type="button" class="button" onclick="closeRemoveUserModal();">Cancel</button>
            </form>
        </footer>
    </div>
</div>
@endif

<script type="text/javascript">
    $(document).ready(function() {
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

        const searchUsersModal = document.getElementById('search-users-modal');

        function openSearchUsersModal() {
            searchUsersModal.classList.add('is-active');
        }

        function closeSearchUsersModal() {
            searchUsersModal.classList.remove('is-active');
        }
    @endif

    @if($can_remove)
        const removeUserModal = document.getElementById('remove-user-modal');

        function openRemoveUserModal(activityId, activityRegistrationId) {
            document.getElementById('remove-user-form').action = `/activities/${activityId}/remove/${activityRegistrationId}`;
            removeUserModal.classList.add('is-active');
        }

        function closeRemoveUserModal() {
            removeUserModal.classList.remove('is-active');
        }
    @endif
</script>
@endsection
