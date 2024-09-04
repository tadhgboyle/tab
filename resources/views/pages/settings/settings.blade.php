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
            <livewire:categories-list />
        </div>
    </div>
    @endpermission
</div>

<div class="columns">
    @permission(\App\Helpers\Permission::SETTINGS_ROLES_MANAGE)
    <div class="column is-5">
        <div class="box">
            <livewire:roles-list />
        </div>
    </div>
    @endpermission

    @permission(\App\Helpers\Permission::SETTINGS_ROTATIONS_MANAGE)
    <div class="column">
        <div class="box">
            <livewire:rotations-list />
        </div>
    </div>
    @endpermission
</div>

<div class="columns">
    @permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
    <div class="column is-12">
        <div class="box">
            <livewire:gift-cards-list />
        </div>
    </div>
    @endpermission
</div>
@stop
