@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">View User</h2>
<h4 class="subtitle">
    {{ $user->full_name }} @if(hasPermission(\App\Helpers\Permission::USERS_MANAGE) && auth()->user()->role->canInteract($user->role))<a href="{{ route('users_edit', $user->id) }}">(Edit)</a>@endif
</h4>

@canImpersonate
    @canBeImpersonated($user)
        <a href="{{ route('impersonate', $user) }}" class="button is-light">
            🕵 Impersonate
        </a>
        <br />
        <br />
    @endCanBeImpersonated
@endCanImpersonate

<div class="columns">
    <div class="column">
        <div class="columns is-multiline">
            <div class="column is-full">
                <livewire:common.users.orders-list :user="$user" context="admin" />
            </div>
            <div class="column is-full">
                <livewire:common.users.activity-registrations-list :user="$user" context="admin" />
            </div>
            <div class="column">
                <livewire:common.users.payouts-list :user="$user" context="admin" />
            </div>
        </div>
    </div>

    <div class="column is-two-fifths">
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
