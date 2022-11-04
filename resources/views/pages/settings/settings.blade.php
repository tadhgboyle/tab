@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">Settings</h2>
@include('includes.messages')
<div class="columns">
    @permission('settings_general')
    <div class="column">
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">Taxes</h4>
            <form action="{{ route('settings_edit') }}" id="settings" method="POST">
                @csrf

                <div class="field">
                    <label class="label">GST<sup style="color: red">*</sup></label>
                    <div class="control has-icons-left">
                        <span class="icon is-small is-left">
                            <i class="fas fa-percent"></i>
                        </span>
                        <input type="number" step="0.01" name="gst" class="input" value="{{ $gst }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">PST<sup style="color: red">*</sup></label>
                    <div class="control has-icons-left">
                        <span class="icon is-small is-left">
                            <i class="fas fa-percent"></i>
                        </span>
                        <input type="number" step="0.01" name="pst" class="input" value="{{ $pst }}">
                    </div>
                </div>

                <div class="control">
                    <button class="button is-success" type="submit">
                        <span class="icon is-small">
                            <i class="fas fa-save"></i>
                        </span>
                        <span>Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endpermission

    @permission('settings_categories_manage')
    <div class="column">
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">Categories</h4>
            <div id="category_loading" align="center">
                <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
            </div>
            <div id="category_container" style="visibility: hidden;">
                <table id="category_list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>
                                <div>{{ $category->name }}</div>
                            </td>
                            <td>
                                <div>{{ $category->type->name }}</div>
                            </td>
                            <td>
                                <a href="{{ route('settings_categories_edit', $category->id) }}">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <br>
            <a class="button is-success" href="{{ route('settings_categories_create') }}">
                <span class="icon is-small">
                    <i class="fas fa-plus"></i>
                </span>
                <span>New</span>
            </a>
        </div>
    </div>
    @endpermission
</div>

<div class="columns">
    @permission('settings_roles_manage')
    <div class="column is-5">
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">Roles</h4>
            <div id="role_loading" align="center">
                <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
            </div>
            <div id="role_container" style="visibility: hidden;">
                <table id="role_list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Staff</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        @foreach($roles as $role)
                        <tr data-id="{{ $role->id }}">
                            <td>
                                <div>{{ $role->name }}</div>
                            </td>
                            <td>
                                <div>{!! $role->staff ? "<span class=\"tag is-success is-medium\">Yes</span>" : "<span class=\"tag is-danger is-medium\">No</span>" !!}</div>
                            </td>
                            <td>
                                <div>
                                    @if (Auth::user()->role->canInteract($role))
                                        <a href="{{ route('settings_roles_edit', $role->id) }}">Edit</a>
                                    @else
                                        <div class="control">
                                            <button class="button is-warning" disabled>
                                                <span class="icon">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <br>
            <a class="button is-success" href="{{ route('settings_roles_create') }}">
                <span class="icon is-small">
                    <i class="fas fa-plus"></i>
                </span>
                <span>New</span>
            </a>
        </div>
    </div>
    @endpermission

    @permission('settings_rotations_manage')
    <div class="column">
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">Rotations</h4>
            <h6 class="subtitle"><strong>Current Rotation:</strong> {!! $currentRotation ?? '<i>None</i>' !!}</h6>
            <div id="rotation_loading" align="center">
                <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
            </div>
            <div id="rotation_container" style="visibility: hidden;">
                <table id="rotation_list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        @foreach($rotations as $rotation)
                        <tr data-id="{{ $rotation->id }}">
                            <td>
                                <div>{{ $rotation->name }}</div>
                            </td>
                            <td>
                                <div>{{ $rotation->start->format('M jS Y h:ia') }}</div>
                            </td>
                            <td>
                                <div>{{ $rotation->end->format('M jS Y h:ia') }}</div>
                            </td>
                            <td>
                                <div>{!! $rotation->getStatusHtml() !!}</div>
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('settings_rotations_edit', $rotation->id) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <br>
            <a class="button is-success" href="{{ route('settings_rotations_create') }}">
                <span class="icon is-small">
                    <i class="fas fa-plus"></i>
                </span>
                <span>New</span>
            </a>
        </div>
    </div>
    @endpermission
</div>

<script type="text/javascript">
    $(document).ready(function() {
        @permission('settings_categories_manage')
            $('#category_list').DataTable({
                "paging": false,
                "searching": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "bInfo": false,
                "columnDefs": [{
                    "orderable": false,
                    "searchable": false,
                    "targets": [1, 2]
                }]
            });
        @endpermission

        @permission('settings_roles_manage')
            $('#role_list').DataTable({
                "order": [],
                "paging": false,
                "searching": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "bInfo": false,
                "columnDefs": [{
                    "orderable": false,
                    "searchable": false,
                    "targets": [1, 2]
                }]
            });

            @if(Auth::user()->role->superuser)
                $("#sortable").sortable({
                    start: function(event, ui) {
                        let start_pos = ui.item.index();
                        ui.item.data('startPos', start_pos);
                    },
                    update: () => {
                        let roles = $("#sortable").children();
                        let toSubmit = [];
                        roles.each(function() {
                            toSubmit.push($(this).data().id);
                        });

                        $.ajax({
                            url: "{{ route('settings_roles_order_ajax') }}",
                            type: "PUT",
                            data: {
                                _token: "{{ csrf_token() }}",
                                roles: JSON.stringify(toSubmit),
                            },
                            error: function(xhr) {
                                console.log(xhr);
                            }
                        });
                    }
                });
            @endif
        @endpermission

        @permission('settings_rotations_manage')
            $('#rotation_list').DataTable({
                "order": [],
                "paging": false,
                "searching": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "bInfo": false,
                "columnDefs": [{
                    "orderable": false,
                    "searchable": false,
                    "targets": [1, 2]
                }]
            });
        @endpermission

        $('#category_loading').hide();
        $('#category_container').css('visibility', 'visible');
        $('#role_loading').hide();
        $('#role_container').css('visibility', 'visible');
        $('#rotation_loading').hide();
        $('#rotation_container').css('visibility', 'visible');
    });
</script>
@stop
