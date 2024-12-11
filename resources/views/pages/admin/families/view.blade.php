@extends('layouts.default', ['page' => 'families'])
@section('content')
<x-page-header :title="$family->name" :actions="[
    [
        'label' => 'Edit',
        'href' => route('families_edit', $family),
        'can' => hasPermission(\App\Helpers\Permission::FAMILIES_MANAGE)
    ],
]" />

<div class="columns">
    <div class="column is-two-thirds">
        <livewire:common.families.members-list :family="$family" context="admin" />
    </div>
    <div class="column">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Total spent" :value="$family->totalSpent()" />
                    <x-detail-card-item label="Total paid out" :value="$family->totalPaidOut()" />
                    <x-detail-card-item label="Total owing" :value="$family->totalOwing()" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-entity-timeline :timeline="$family->timeline()" />
        </x-detail-card-stack>
    </div>
</div>

@permission(\App\Helpers\Permission::FAMILIES_MANAGE)
    <div class="modal" id="search-users-modal">
        <div class="modal-background" onclick="closeSearchUsersModal();"></div>
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
                <button class="button" onclick="closeSearchUsersModal();">Cancel</button>
            </footer>
        </div>
    </div>

    <div class="modal" id="edit-user-modal">
        <div class="modal-background" onclick="closeEditUserModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Edit User</p>
            </header>
            <section class="modal-card-body">
                <p>Editing <strong id="edit-user-name"></strong></p>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PATCH')

                    <label for="role">Role</label>
                    <div class="control">
                        <div class="select">
                            <select name="role" id="role" class="input" required>
                                <option value="{{ \App\Enums\FamilyMemberRole::Admin }}">Admin</option>
                                <option value="{{ \App\Enums\FamilyMemberRole::Member }}">Member</option>
                            </select>
                        </div>
                    </div>
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" type="submit" form="editForm">Submit</button>
                <button class="button" onclick="closeEditUserModal();">Cancel</button>
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
                <p>Are you sure you want to remove <strong id="remove-user-name"></strong> from the family?</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" type="submit" form="deleteForm">Confirm</button>
                <button class="button" onclick="closeRemoveUserModal();">Cancel</button>
            </footer>
        </div>
    </div>

    <script>
        $(document).ready(function() {
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
        });

        $('#search').on('keyup', function() {
            if (this.value === undefined || this.value === '') {
                return;
            }
            $.ajax({
                type : "GET",
                url : "{{ route('families_user_search', $family->id) }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "search": this.value,
                    "family": "{{ $family->id }}"
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

        const editUserModal = document.getElementById('edit-user-modal');

        function openEditUserModal(familyMemberId, familyMemberName, familyMemberRole) {
            document.getElementById('editForm').action = `/admin/families/{{ $family->id }}/edit/${familyMemberId}`;
            document.getElementById('edit-user-name').innerText = familyMemberName;
            document.getElementById('role').value = familyMemberRole;

            editUserModal.classList.add('is-active');
        }

        function closeEditUserModal() {
            editUserModal.classList.remove('is-active');
        }

        const removeUserModal = document.getElementById('remove-user-modal');

        function openRemoveUserModal(familyMemberId, familyMemberName) {
            document.getElementById('deleteForm').action = `/admin/families/{{ $family->id }}/remove/${familyMemberId}`;
            document.getElementById('remove-user-name').innerText = familyMemberName;

            removeUserModal.classList.add('is-active');
        }

        function closeRemoveUserModal() {
            removeUserModal.classList.remove('is-active');
        }
    </script>
@endpermission
@endsection
