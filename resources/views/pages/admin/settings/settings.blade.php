@extends('layouts.default', ['page' => 'settings'])
@section('content')
<x-page-header title="Settings" />

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 grid-flow-row">
    @permission(\App\Helpers\Permission::SETTINGS_GENERAL)
    <div class="rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between p-4">
            <h3 class="text-base font-semibold leading-6 text-gray-950">
                General
            </h3>
            <x-filament::button type="submit" onclick="document.getElementById('settings').submit()">
                Save
            </x-filament::button>
        </div>
    
        <form action="{{ route('settings_edit') }}" id="settings" method="POST">
            @csrf
    
            <div class="bg-gray-50 border-y">
                <h4 class="text-sm font-semibold leading-6 text-gray-950 px-5 py-3">
                    Tax Rates
                </h4>
            </div>
            <div class="grid grid-cols-2 gap-4 p-4">
                <div>
                    <span class="text-sm font-medium text-gray-950">GST</span>
                    <x-filament::input.wrapper prefix-icon="heroicon-m-percent-badge">
                        <x-filament::input name="gst" type="number" step="0.01" value="{{ $gst }}" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-950">PST</span>
                    <x-filament::input.wrapper prefix-icon="heroicon-m-percent-badge">
                        <x-filament::input name="pst" type="number" step="0.01" value="{{ $pst }}" />
                    </x-filament::input.wrapper>    
                </div>
            </div>
    
            <div class="bg-gray-50 border-y">
                <h4 class="text-sm font-semibold leading-6 text-gray-950 px-5 py-3">
                    Order Identifiers
                </h4>
            </div>
    
            <div class="grid grid-cols-2 gap-4 p-4">
                <div>
                    <span class="text-sm font-medium text-gray-950">Prefix</span>
                    <x-filament::input.wrapper>
                        <x-filament::input name="order_prefix" placeholder="#" type="text" value="{{ $orderPrefix }}" />
                    </x-filament::input.wrapper>
                    <span class="text-xs text-gray-500" id="orderIdentifierPreview"></span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-950">Suffix</span>
                    <x-filament::input.wrapper>
                        <x-filament::input name="order_suffix" type="text" value="{{ $orderSuffix }}" />
                    </x-filament::input.wrapper>  
                </div>
            </div>
        </form>
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
    <div class="lg:col-span-2">
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
