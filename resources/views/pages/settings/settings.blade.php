@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">Settings</h2>
@include('includes.messages')
<div class="columns">
    @permission(\App\Helpers\Permission::SETTINGS_GENERAL)
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
                    <button class="button is-light" type="submit">
                        💾 Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endpermission

    @permission(\App\Helpers\Permission::SETTINGS_CATEGORIES_MANAGE)
    <div class="column">
        <div class="box">
            <div class="columns">
                <div class="column">
                    <h4 class="title has-text-weight-bold is-4">Categories</h4>
                </div>
                <div class="column">
                    <a class="button is-light  is-pulled-right is-small" href="{{ route('settings_categories_create') }}">
                        ➕ Create
                    </a>
                </div>
            </div>
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
        </div>
    </div>
    @endpermission
</div>

<div class="columns">
    @permission(\App\Helpers\Permission::SETTINGS_ROLES_MANAGE)
    <div class="column is-5">
        <div class="box">
            <div class="columns">
                <div class="column">
                    <h4 class="title has-text-weight-bold is-4">Roles</h4>
                </div>
                <div class="column">
                    <a class="button is-light  is-pulled-right is-small" href="{{ route('settings_roles_create') }}">
                        ➕ Create
                    </a>
                </div>
            </div>
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
                                <div>{{ $role->staff ? "✅" : "❌" }}</div>
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
        </div>
    </div>
    @endpermission

    @permission(\App\Helpers\Permission::SETTINGS_ROTATIONS_MANAGE)
    <div class="column">
        <div class="box">
            <div class="columns">
                <div class="column">
                    <h4 class="title has-text-weight-bold is-4">Rotations</h4>
                    <h6 class="subtitle"><strong>Current Rotation:</strong> {!! $currentRotation ?? '<i>None</i>' !!}</h6>
                </div>
                <div class="column">
                    <a class="button is-light  is-pulled-right is-small" href="{{ route('settings_rotations_create') }}">
                        ➕ Create
                    </a>
                </div>
            </div>
            <div id="rotation_loading" align="center">
                <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
            </div>
            <div id="rotation_container" style="visibility: hidden;">
                <table id="rotation_list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Users</th>
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
                                <div>{{ $rotation->users_count }}</div>
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
        </div>
    </div>
    @endpermission
</div>

<div class="columns">
    @permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
    <div class="column is-12">
        <div class="box">
            <div class="columns">
                <div class="column">
                    <h4 class="title has-text-weight-bold is-4">Gift Cards</h4>
                </div>
                <div class="column">
                    <a class="button is-light  is-pulled-right is-small" href="{{ route('settings_gift-cards_create') }}">
                        ➕ Create
                    </a>
                </div>
            </div>
            <div id="gift_cards_loading" align="center">
                <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
            </div>
            <div id="gift_cards_container" style="visibility: hidden;">
                <table id="gift_cards_list">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Original Balance</th>
                            <th>Remaining Balance</th>
                            <th>Issuer</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                    @foreach($giftCards as $giftCard)
                        <tr data-id="{{ $giftCard->id }}">
                            <td>
                                <code>{{ $giftCard->code() }}</code>
                                <i class="fas fa-copy copy" id="gift-card-copy-{{ $giftCard->id }}" onclick="copyCode({{ $giftCard->id }}, '{{ $giftCard->code }}')"></i>
                            </td>
                            <td>
                                <div>{{ $giftCard->original_balance }}</div>
                            </td>
                            <td>
                                <div>{{ $giftCard->remaining_balance }}</div>
                            </td>
                            <td>
                                <div>{{ $giftCard->issuer->full_name }}</div>
                            </td>
                            <td>
                                <div>{{ $giftCard->created_at->format('M jS Y h:ia') }}</div>
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('settings_gift-cards_view', $giftCard->id) }}">View</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endpermission
</div>

<script type="text/javascript">
    $(document).ready(function() {
        @permission(\App\Helpers\Permission::SETTINGS_CATEGORIES_MANAGE)
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

            $('#category_loading').hide();
            $('#category_container').css('visibility', 'visible');
        @endpermission

        @permission(\App\Helpers\Permission::SETTINGS_ROLES_MANAGE)
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

            $('#role_loading').hide();
            $('#role_container').css('visibility', 'visible');
        @endpermission

        @permission(\App\Helpers\Permission::SETTINGS_ROTATIONS_MANAGE)
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
                    "targets": [5]
                }]
            });

            $('#rotation_loading').hide();
            $('#rotation_container').css('visibility', 'visible');
        @endpermission

        @permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
            $('#gift_cards_list').DataTable({
                "order": [],
                "paging": false,
                "searching": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "bInfo": false,
                "columnDefs": [{
                    "orderable": false,
                    "searchable": false,
                    "targets": [5]
                }]
            });

            $('#gift_cards_loading').hide();
            $('#gift_cards_container').css('visibility', 'visible');
        @endpermission
    });

    @permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
        const copyCode = (id, code) => {
            const el = document.createElement('textarea');
            el.value = code;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);

            const e = document.getElementById(`gift-card-copy-${id}`);

            e.classList.remove('fa-copy');
            e.classList.add('fa-check');
            e.style.color = 'green';

            setTimeout(() => {
                const e = document.getElementById(`gift-card-copy-${id}`);
                e.classList.remove('fa-check');
                e.classList.add('fa-copy');
                e.style.color = 'black';
            }, 1000);
        }
    @endpermission
</script>

@permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
    <style>
        .copy {
            cursor: pointer;
        }
    </style>
@endpermission

@stop
