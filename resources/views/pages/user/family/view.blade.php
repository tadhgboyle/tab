@extends('layouts.default', ['page' => 'family'])
@section('content')
<x-page-header title="Family" />

<div class="columns">
    <div class="column is-two-thirds">
        <livewire:common.families.members-list :family="$family" context="family" />
    </div>
    <div class="column">
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
