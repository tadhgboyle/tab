@extends('layouts.default', ['page' => 'settings'])
@section('content')
<x-page-header title="View Gift Card" />

<div class="grid lg:grid-cols-6 grid-cols-1 gap-5">
    <div class="lg:col-span-4">
        <livewire:admin.settings.gift-cards.users-list :gift-card="$giftCard" />
    </div>

    <div class="lg:col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Code" :value="$giftCard->code()" />
                    <x-detail-card-item label="Status">
                        <x-gift-card-status-badge :gift-card="$giftCard" />
                    </x-detail-card-item>
                    <x-detail-card-item :label="$giftCard->expired() ? 'Expired at' : 'Expires at'" :value="$giftCard->expires_at ? $giftCard->expires_at->format('M jS Y') : '<i>Never</i>'" />
                </x-detail-card-item-list>
            </x-detail-card>
            <x-detail-card title="Balance">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Original Balance" :value="$giftCard->original_balance" />
                    <x-detail-card-item label="Remaining Balance" :value="$giftCard->remaining_balance" />
                </x-detail-card-item-list>
            </x-detail-card>
            <x-entity-timeline :timeline="$giftCard->timeline()" />
        </x-detail-card-stack>
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