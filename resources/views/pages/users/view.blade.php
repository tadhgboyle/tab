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

@php $owing = $user->findOwing(); @endphp

<div class="box">
    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Balance</p>
                <p class="title">{{ $user->balance }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total spent</p>
                <p class="title">{{ $user->findSpent() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total returned</p>
                <p class="title">{{ $user->findReturned() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total paid out</p>
                <p class="title">{{ $user->findPaidOut() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total owing</p>
                <a class="title" title="View PDF" style="text-decoration: underline;" href="{{ route('users_pdf', $user) }}" target="_blank">{{ $owing }}</a>
            </div>
        </div>
    </nav>
</div>

<div class="columns">
    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <livewire:users.orders-list :user="$user" />
            </div>
            <div class="column is-full">
                <livewire:users.activity-registrations-list :user="$user" />
            </div>
            <div class="column">
                <x-entity-timeline :timeline="$user->timeline()" />
            </div>
        </div>
    </div>

    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <livewire:users.category-limits-list :user="$user" />
            </div>
            <div class="column">
                <livewire:users.rotations-list :user="$user" />
            </div>
            <div class="column is-full">
                <livewire:users.payouts-list :user="$user" />
            </div>
        </div>
    </div>
</div>
@endsection
