@extends('layouts.default', ['page' => 'activities'])
@section('content')
<h2 class="title has-text-weight-bold">View Activity</h2>
<h4 class="subtitle"><strong>Activity:</strong> {{ $activity->name }} @permission(\App\Helpers\Permission::ACTIVITIES_MANAGE)<a href="{{ route('activities_edit', $activity->id) }}">(Edit)</a>@endpermission</h4>
<div class="columns">
    <div class="column is-two-thirds">
        <livewire:admin.activities.registrations-list :activity="$activity" />
    </div>
    <div class="column">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Category" :value="$activity->category->name" />
                    @if($activity->description)
                        <x-detail-card-item label="Description" :value="$activity->description" />
                    @endif
                    @if($activity->location)
                        <x-detail-card-item label="Location" :value="$activity->location" />
                    @endif
                    <x-detail-card-item label="Slots" :value="$activity->unlimited_slots ? '<i>Unlimited</i>' : $activity->slots . ' - ' . $activity->slotsAvailable() . ' available'" />
                    <x-detail-card-item label="Status" :value="$activity->getStatusHtml()" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Timing">
                <x-detail-card-item-list>
                    @unless($activity->ended())
                        <x-detail-card-item label="Starts in" :value="$activity->countdown()" />
                    @endunless
                    <x-detail-card-item label="Starts at" :value="$activity->start->format('M jS Y h:ia')" />
                    <x-detail-card-item label="Ends at" :value="$activity->end->format('M jS Y h:ia')" />
                    <x-detail-card-item label="Duration" :value="$activity->duration()" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Pricing">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Price" :value="$activity->price->isZero() ? 'Free' : $activity->price" />
                    <x-detail-card-item label="PST" :value="$activity->pst ? '✅' : '❌'" />
                </x-detail-card-item-list>
            </x-detail-card>
            <x-entity-timeline :timeline="$activity->timeline()" />
        </x-detail-card-stack>
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
            document.getElementById('remove-user-form').action = `/admin/activities/${activityId}/remove/${activityRegistrationId}`;
            removeUserModal.classList.add('is-active');
        }

        function closeRemoveUserModal() {
            removeUserModal.classList.remove('is-active');
        }
    @endif
</script>
@endsection
