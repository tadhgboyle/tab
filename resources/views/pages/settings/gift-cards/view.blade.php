@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">View Gift Card</h2>
<h4 class="subtitle">
    <code>{{ $giftCard->code() }}</code>
</h4>
<div class="box">
    <div class="columns">
        <div class="column">
            @include('includes.messages')
            <nav class="level">
                <div class="level-item has-text-centered">
                    <div>
                    <p class="heading">Remaining Balance</p>
                    <p class="title">{{ $giftCard->remaining_balance }}</p>
                    </div>
                </div>
                <div class="level-item has-text-centered">
                    <div>
                    <p class="heading">Original Balance</p>
                    <p class="title">{{ $giftCard->original_balance }}</p>
                    </div>
                </div>
            </nav>

            <x-entity-timeline :timeline="$giftCard->timeline()" />
        </div>
    </div>
</div>
@endsection