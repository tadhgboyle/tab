@php

use App\Http\Controllers\SettingsController;
use App\Roles;
$manage_general = Roles::hasPermission(Auth::user()->role, 'settings_general');
$manage_roles = Roles::hasPermission(Auth::user()->role, 'settings_roles_manage');
$manage_categories = Roles::hasPermission(Auth::user()->role, 'settings_categories_manage');
// TODO: Remove self purchases setting and change to per-group permission
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">Settings</h2>
<div class="columns">
    @if($manage_general)
        <div class="column is-3">
            <div class="box">
                <h4 class="title has-text-weight-bold is-4">General</h4>
                @include('includes.messages')
                <form action="{{ route('settings_form') }}" id="settings" method="POST">
                    @csrf

                    <div class="field">
                        <label class="label">GST<sup style="color: red">*</sup></label>
                        <div class="control has-icons-left">
                            <span class="icon is-small is-left">
                                <i class="fas fa-percent"></i>
                            </span>
                            <input type="number" step="0.01" name="gst" class="input" value="{{ SettingsController::getGst() }}">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">PST<sup style="color: red">*</sup></label>
                        <div class="control has-icons-left">
                            <span class="icon is-small is-left">
                                <i class="fas fa-percent"></i>
                            </span>
                            <input type="number" step="0.01" name="pst" class="input" value="{{ SettingsController::getPst() }}">
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
    @endif

    <div class="column"></div>

    @if($manage_categories)
        <div class="column is-3">
            <div class="box">
                <h4 class="title has-text-weight-bold is-4">Categories</h4>
                <div id="category_loading" align="center">
                    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
                </div>
                <div id="category_container" style="visibility: hidden;">
                    <table id="category_list">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(SettingsController::getCategories() as $category)
                            <tr>
                                <td>
                                    <div>{{ ucfirst($category->value) }}</div>
                                </td>
                                <td>
                                    <div>
                                        <form>
                                            <input type="hidden" id="{{ $category->value }}" value="{{ $category->value }}">
                                            <a href="javascript:;">Edit</a>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <br>
                <a class="button is-success" href="{{ route('settings_categories_new') }}">
                    <span class="icon is-small">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span>New</span>
                </a>
            </div>
        </div>
    @endif

    <div class="column"></div>

    @if($manage_roles)
        <div class="column box is-4">
            <h4 class="title has-text-weight-bold is-4">Roles</h4>
            <div id="role_loading" align="center">
                <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
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
                        @foreach(Roles::getRoles('ASC') as $role)
                            <tr data-id="{{ $role->id }}">
                                <td>
                                    <div>{{ $role->name }}</div>
                                </td>
                                <td>
                                    <div>{!! $role->staff ? "<span class=\"tag is-success is-medium\">Yes</span>" : "<span class=\"tag is-danger is-medium\">No</span>" !!}</div>
                                </td>
                                <td>
                                    <div>
                                    @if (Roles::canInteract(Auth::user()->role, $role->id))
                                        <a href="{{ route('settings_roles_edit', $role->id) }}">Edit</a>
                                    @else 
                                        <div class="control">
                                            <button class="button is-warning" disabled>
                                                <span class="icon">z
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
            <a class="button is-success" href="{{ route('settings_roles_new') }}">
                <span class="icon is-small">
                    <i class="fas fa-plus"></i>
                </span>
                <span>New</span>
            </a>
        </div>
    @endif    
</div>

<script type="text/javascript">
    $(document).ready(function() {
        @if($manage_categories)
            $('#category_list').DataTable({
                "paging": false,
                "searching": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "bInfo": false,
                "columnDefs": [
                    { 
                        "orderable": false, 
                        "searchable": false,
                        "targets": 1
                    }
                ]
            });
        @endif

        @if($manage_roles)
            $('#role_list').DataTable({
                "order": [],
                "paging": false,
                "searching": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "bInfo": false,
                "columnDefs": [
                    { 
                        "orderable": false, 
                        "searchable": false,
                        "targets": [1, 2]
                    }
                ]
            });

            @if(Roles::find(Auth::user()->role)->superuser)
                $("#sortable").sortable({
                    start: function(event, ui) {
                        let start_pos = ui.item.index();
                        ui.item.data('startPos', start_pos);
                    },
                    update: function(event, ui){
                        let roles = $("#sortable").children();
                        let toSubmit = [];
                        roles.each(function(){
                            toSubmit.push($(this).data().id);
                        });

                        $.ajax({
                            url: "{{ route('settings_roles_order_ajax') }}",
                            type: "GET",
                            data: {
                                roles: JSON.stringify({"roles": toSubmit})
                            },
                            success: function(response) {
                                // Success
                            },
                            error: function(xhr) {
                                // Error
                                console.log(xhr);
                            }
                        });
                    }
                });
            @endif
        @endif

        $('#category_loading').hide();
        $('#category_container').css('visibility', 'visible');
        $('#role_loading').hide();
        $('#role_container').css('visibility', 'visible');
    });
</script>
@stop