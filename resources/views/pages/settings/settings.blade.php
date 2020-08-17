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
                <form action="/settings" id="settings" method="POST">
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
                <br>
                <a class="button is-success" href="/settings/categories/new">
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
            <table id="role_list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Order</th>
                        <th>Staff</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(Roles::getRoles('ASC') as $role)
                        <tr>
                            <td>
                                <div>{{ $role->name }}</div>
                            </td>
                            <td>
                                <div>{{ $role->order }}</div>
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
                        "targets": [1, 2, 3]
                    }
                ]
            });
        @endif
    });
</script>
@stop