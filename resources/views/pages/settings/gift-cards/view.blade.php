@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">View Gift Card</h2>
<h4 class="subtitle">
    <code>{{ $giftCard->code() }}</code> {!! $giftCard->getStatusHtml() !!}
</h4>

@if($giftCard->expires_at)
    <p><strong>{{ $giftCard->expired() ? "Expired" : "Expires" }} at:</strong> {{ $giftCard->expires_at->format('M jS Y') }}</p>
@endif

<div class="columns">
    <div class="column">
        <div class="box">
            @include('includes.messages')
            <nav class="level">
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading">Amount Used</p>
                        <p class="title">{{ $giftCard->amountUsed() }}</p>
                    </div>
                </div>
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
        </div>
    </div>
</div>
<div class="columns">
    <div class="column">
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">Timeline</h4>
            <x-entity-timeline :timeline="$giftCard->timeline()" />
        </div>
    </div>
    <div class="column">
        <div class="box">
            <h4 class="title has-text-weight-bold is-4">Users</h4>
            <table id="user_list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Total use</th>
                        <th>Granted by</th>
                        <th>Given at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($giftCard->assignments as $assignment)
                        <tr>
                            <td>
                                <div>{{ $assignment->user->full_name }}</div>
                            </td>
                            <td>
                                <div>{{ $giftCard->usageBy($assignment->user) }}</div>
                            </td>
                            <td>
                                <div>{{ $assignment->assigner->full_name }}</div>
                            </td>
                            <td>
                                <div>{{ $assignment->created_at->format('M jS Y h:ia') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
$('#user_list').DataTable({
    "paging": false,
    "bInfo": false,
    "searching": false,
    "language": {
        "emptyTable": "No users assigned, anyone can use"
    },
});
</script>
@endsection