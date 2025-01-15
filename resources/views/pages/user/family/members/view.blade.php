@extends('layouts.default', ['page' => 'family'])
@section('content')
<x-page-header title="{{ $user->full_name }}" :actions="[
    [
        'label' => 'Edit',
        'href' => route('families_member_edit', [$familyMember->family, $familyMember]),
        'can' => auth()->user()->isFamilyAdmin(),
    ],
]" />

<div class="grid lg:grid-cols-6 grid-cols-1 gap-5">
    <div class="lg:col-span-4">
        <x-detail-card-stack>
            <livewire:common.users.orders-list :user="$user" context="family" />
            <livewire:common.users.activity-registrations-list :user="$user" context="family" />
            <livewire:common.users.payouts-list :user="$user" context="family" />
        </x-detail-card-stack>
    </div>

    <div class="lg:col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Name" :value="$user->full_name" />
                    <x-detail-card-item label="Role">
                        <x-badge :value="ucfirst($familyMember->role->value)" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Balance" :value="$user->balance" />
                    <x-detail-card-item label="Total spent" :value="$user->findSpent()" />
                    <x-detail-card-item label="Total returned" :value="$user->findReturned()" />
                    <x-detail-card-item label="Total paid out" :value="$user->findPaidOut()" />
                    <x-detail-card-item label="Total owing">
                        <a href="{{ route('family_member_pdf', [$familyMember->family, $familyMember]) }}">{{ $user->findOwing() }}</a>
                    </x-detail-card-item>
                </x-detail-card-item-list>
            </x-detail-card>

            <livewire:common.users.category-limits-list :user="$user" context="family"/>
        </x-detail-card-stack>
    </div>
</div>
@endsection
