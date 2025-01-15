@extends('layouts.default', ['page' => 'family'])
@section('content')
<x-page-header title="Family" />

<div class="grid lg:grid-cols-6 grid-cols-1 gap-5">
    <div class="lg:col-span-4">
        <livewire:common.families.members-list :family="$family" context="family" />
    </div>

    <div class="lg:col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Name" :value="$family->name" />

                    @if(auth()->user()->isFamilyAdmin())
                        <x-detail-card-item label="Total spent" :value="$family->totalSpent()" />
                        <x-detail-card-item label="Total paid out" :value="$family->totalPaidOut()" />
                        <x-detail-card-item label="Total owing" :value="$family->totalOwing()" />
                    @endif
                </x-detail-card-item-list>
            </x-detail-card>
            <x-entity-timeline :timeline="$family->timeline()" />
        </x-detail-card-stack>
    </div>
</div>
@endsection
