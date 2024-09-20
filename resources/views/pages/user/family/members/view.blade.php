@extends('layouts.default', ['page' => 'family'])
@section('content')
<h2 class="title has-text-weight-bold">Family Member</h2>
<h4 class="subtitle">
    {{ $user->full_name }}
    <p>
        <strong>Role:</strong> {{ ucfirst($user->familyRole()->value) }}
        @if(auth()->user()->isFamilyAdmin())
            <a href="{{ route('families_member_edit', [$familyMember->family, $familyMember]) }}">(Edit)</a>
        @endif
    </p>
</h4>

<div class="columns">
    <div class="column">
        <div class="columns is-multiline">
            <div class="column is-full">
                <livewire:common.users.orders-list :user="$user" context="family" />
            </div>
            <div class="column is-full">
                <livewire:common.users.activity-registrations-list :user="$user" context="family" />
            </div>
            <div class="column">
                <livewire:common.users.payouts-list :user="$user" context="family" />
            </div>
        </div>
    </div>

    <div class="column is-two-fifths">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Name" :value="$user->full_name" />
                    <x-detail-card-item label="Balance" :value="$user->balance" />
                    <x-detail-card-item label="Total spent" :value="$user->findSpent()" />
                    <x-detail-card-item label="Total returned" :value="$user->findReturned()" />
                    <x-detail-card-item label="Total paid out" :value="$user->findPaidOut()" />
                    <x-detail-card-item label="Total owing" :value="$user->findOwing()" />
                </x-detail-card-item-list>
            </x-detail-card>

            <livewire:common.users.category-limits-list :user="$user" context="family"/>
        </x-detail-card-stack>
    </div>
</div>
@endsection
