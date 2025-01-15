@extends('layouts.default', ['page' => 'users'])
@section('content')
<x-page-header :entity="'User'" :title="$user->full_name" :actions="[
    [
        'label' => 'Impersonate',
        'href' => route('impersonate', $user),
        'can' => auth()->user()->canImpersonate() && $user->canBeImpersonated(),
    ],
    [
        'label' => 'Edit',
        'href' => route('users_edit', $user->id),
        'can' => hasPermission(\App\Helpers\Permission::USERS_MANAGE) && auth()->user()->role->canInteract($user->role),
    ],
]" />

<div class="grid lg:grid-cols-5 grid-cols-1 gap-5">
    <div class="lg:col-span-3">
        <x-detail-card-stack>
            <livewire:common.users.orders-list :user="$user" context="admin" />
            <livewire:common.users.activity-registrations-list :user="$user" context="admin" />
            <livewire:common.users.payouts-list :user="$user" context="admin" />
        </x-detail-card-stack>
    </div>

    <div class="lg:col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Role">
                        <x-badge :value="$user->role->name" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Balance" :value="$user->balance" />
                    <x-detail-card-item label="Total spent" :value="$user->findSpent()" />
                    <x-detail-card-item label="Total returned" :value="$user->findReturned()" />
                    <x-detail-card-item label="Total paid out" :value="$user->findPaidOut()" />
                    <x-detail-card-item label="Total owing">
                        <a href="{{ route('users_pdf', $user->id) }}">{{ $user->findOwing() }}</a>
                    </x-detail-card-item>
                </x-detail-card-item-list>
            </x-detail-card>

            @if($user->family)
                <x-detail-card title="Family">
                    <x-detail-card-item-list>
                        <x-detail-card-item label="Name">
                            @permission(\App\Helpers\Permission::FAMILIES_VIEW)
                                <a href="{{ route('families_view', $user->family->id) }}">{{ $user->family->name }}</a>
                            @else
                                {{ $user->family->name }}
                            @endpermission
                        </x-detail-card-item>
                        <x-detail-card-item label="Role">
                            <x-badge :value="ucfirst($user->familyRole()->value)" />
                        </x-detail-card-item>
                    </x-detail-card-item-list>
                </x-detail-card>
            @endif

            <livewire:common.users.category-limits-list :user="$user" />

            <livewire:admin.users.rotations-list :user="$user" />

            <x-entity-timeline :timeline="$user->timeline()" />
        </x-detail-card-stack>
    </div>
</div>
@endsection
