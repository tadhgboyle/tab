@extends('layouts.default', ['page' => 'family'])
@section('content')
<h2 class="title has-text-weight-bold">Family</h2>

<div class="columns">
    <div class="column is-two-thirds">
        <livewire:user.family.memberships-list :family="$family" />
    </div>
    <div class="column">
        <x-detail-card title="Details">
            <p><strong>Name:</strong> {{ $family->name }}</p>
            <p><strong>Total Spent:</strong> {{ $family->totalSpent() }}</p>
            <p><strong>Total Owing:</strong> {{ $family->totalOwing() }}</p>
        </x-detail-card>

        <x-entity-timeline :timeline="$family->timeline()" />
    </div>
</div>
@endsection
