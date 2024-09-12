@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">View Gift Card</h2>
<h4 class="subtitle">
    <code>{{ $giftCard->code() }}</code> {!! $giftCard->getStatusHtml() !!}
</h4>

@if($giftCard->expires_at)
    <p><strong>{{ $giftCard->expired() ? "Expired" : "Expires" }} at:</strong> {{ $giftCard->expires_at->format('M jS Y') }}</p>
@endif

<div class="columns">
    <div class="column">
        <div class="box">
            <nav class="level">
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading">Amount Used</p>
                        <p class="title">{{ $giftCard->amountUsed() }}</p>
                    </div>
                </div>
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading">Remaining Balance</p>
                        <p class="title">{{ $giftCard->remaining_balance }}</p>
                    </div>
                </div>
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading">Original Balance</p>
                        <p class="title">{{ $giftCard->original_balance }}</p>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>
<div class="columns">
    <div class="column">
        <x-entity-timeline :timeline="$giftCard->timeline()" />
    </div>
    <div class="column">
        <livewire:admin.settings.gift-cards.users-list :gift-card="$giftCard" />
    </div>
</div>

<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Add User</p>
        </header>
        <section class="modal-card-body">
            <input type="text" class="input" name="search" id="search" placeholder="Search for user">
            <div id="search-div"></div>
            <table id="search_table">
                <thead>
                    <tr>
                        <th>Full Name</th>
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

<div class="modal" id="remove-user-modal">
    <div class="modal-background" onclick="closeRemoveUserModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to remove the user <strong id="remove-user-name"></strong>?</p>
            <form id="removeUserForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="removeUserForm">Confirm</button>
            <button class="button" onclick="closeRemoveUserModal();">Cancel</button>
        </footer>
    </div>
</div>

<script>
    const removeUserModal = document.getElementById('remove-user-modal');

    function openRemoveUserModal(userId, user) {
        const url = `/admin/settings/gift-cards/{{ $giftCard->id }}/unassign/${userId}`;
        document.getElementById('removeUserForm').action = url;
        document.getElementById('remove-user-name').innerText = user;

        removeUserModal.classList.add('is-active');
    }

    function closeRemoveUserModal() {
        removeUserModal.classList.remove('is-active');
    }

    @unless($giftCard->expired())
        $('#search_table').DataTable({
            "paging": false,
            "searching": false,
            "bInfo": false,
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": [0, 1]
                }
            ],
            "language": {
                "emptyTable": "No applicable users"
            },
        });

        $('#search').on('keyup', function() {
            if (this.value === undefined || this.value === '') {
                return;
            }
            $.ajax({
                type : "GET",
                url : "{{ route('settings_gift-cards_assign_search', $giftCard->id) }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "search": this.value,
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
    @endunless
</script>
@endsection