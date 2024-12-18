@extends('layouts.default', ['page' => 'settings'])
@section('content')
<x-page-header title="Settings" />

<div class="grid grid-cols-2 gap-5 grid-flow-row">
    @permission(\App\Helpers\Permission::SETTINGS_GENERAL)
    <div>
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">General</h4>
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

                <div class="field">
                    <label class="label">Order Identifiers</label>
                    <div class="field-body">
                        <div class="field has-addons">
                            <p class="control">
                                <a class="button is-static">
                                    Prefix
                                </a>
                            </p>
                            <p class="control is-expanded">
                                <input class="input" type="text" name="order_prefix" placeholder="#" value="{{ $orderPrefix }}">
                            </p>
                        </div>
                        <div class="field has-addons">
                            <p class="control">
                                <a class="button is-static">
                                    Suffix
                                </a>
                            </p>
                            <p class="control is-expanded">
                                <input class="input" type="text" name="order_suffix" value="{{ $orderSuffix }}">
                            </p>
                        </div>
                    </div>
                    <p class="help" id="orderIdentifierPreview"></p>
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
        <livewire:admin.settings.categories-list />
    @endpermission

    @permission(\App\Helpers\Permission::SETTINGS_ROLES_MANAGE)
        <livewire:admin.settings.roles-list />
    @endpermission

    @permission(\App\Helpers\Permission::SETTINGS_ROTATIONS_MANAGE)
        <livewire:admin.settings.rotations-list />
    @endpermission

    @permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
    <div class="col-span-2">
        <livewire:admin.settings.gift-cards-list />
    </div>
    @endpermission
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const orderPrefix = document.querySelector('input[name="order_prefix"]');
        const orderSuffix = document.querySelector('input[name="order_suffix"]');
        const preview = document.getElementById('orderIdentifierPreview');
        const set = () => setPreview(preview, orderPrefix, orderSuffix);

        set();

        orderPrefix.addEventListener('input', set);
        orderSuffix.addEventListener('input', set);
    });

    const formatExampleIds = (prefix, suffix) => {
        return 'Example: ' + [1000, 1001, 1002].map(id => `${prefix}${id}${suffix}`).join(', ');
    }

    const setPreview = (preview, prefix, suffix) => {
        preview.innerText = formatExampleIds(prefix.value, suffix.value);
    }
</script>
@stop
