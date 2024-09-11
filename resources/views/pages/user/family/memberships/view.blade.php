@extends('layouts.default', ['page' => 'family'])
@section('content')
<h2 class="title has-text-weight-bold">Family Member</h2>
<h4 class="subtitle">
    {{ $user->full_name }}
    <p><strong>Role:</strong> {{ ucfirst($familyMembership->role->value) }}</p>
</h4>

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
                <a class="title" title="View PDF" style="text-decoration: underline;" href="{{ route('family_membership_pdf', $familyMembership) }}" target="_blank">{{ $user->findOwing() }}</a>
            </div>
        </div>
    </nav>
</div>

<div class="columns">
    <div class="column">
        <livewire:user.family.members.orders-list :user="$user" />
        <div class="mt-5"></div>
        <livewire:user.family.members.activity-registrations-list :user="$user" />
    </div>
    <div class="column">
        <livewire:user.family.members.category-limits-list :user="$user" />
        <div class="mt-5"></div>
        <livewire:user.family.members.payouts-list :user="$user" />
    </div>
</div>
@endsection
