@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">User List</h2>
<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns">
    <div class="column" id="user_container" style="visibility: hidden;">
        @livewire('user-list-table')
    </div>
</div>
<script>
    window.onload = function() {
        $('#user_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    @if(hasPermission('users_view') && hasPermission('users_manage'))
                    4,
                    5
                    @elseif(hasPermission('users_view') && !hasPermission('users_manage'))
                    4
                    @elseif(!hasPermission('users_view') && hasPermission('users_manage'))
                    4
                    @endif
                ]
            }]
        });
        $('#loading').hide();
        $('#user_container').css('visibility', 'visible');
    };
</script>
@endsection