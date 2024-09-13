@extends('layouts.default', ['page' => 'family'])
@section('content')
<h2 class="title has-text-weight-bold">Family</h2>

<div class="columns">
    <div class="column is-two-thirds">
        <livewire:common.families.members-list :family="$family" context="family" />
    </div>
    <div class="column">
        <x-detail-card title="Details">
            <p><strong>Name:</strong> {{ $family->name }}</p>

            @if(auth()->user()->isFamilyAdmin($family))
                <p><strong>Total Spent:</strong> {{ $family->totalSpent() }}</p>
                <p><strong>Total Owing:</strong> {{ $family->totalOwing() }}</p>
            @endif
        </x-detail-card>

        <x-entity-timeline :timeline="$family->timeline()" />
    </div>
</div>
@endsection
