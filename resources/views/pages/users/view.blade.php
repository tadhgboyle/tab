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

@include('includes.messages')

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
                <p class="title" style="text-decoration: underline; cursor: help;"  onclick="openOwingModal();">{{ $owing }}</p>
            </div>
        </div>
    </nav>
</div>

<div class="columns">
    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <div class="box">
                    <livewire:users.orders-list :user="$user" />
                </div>
            </div>
            <div class="column is-full">
                <div class="box">
                    <livewire:users.activity-registrations-list :user="$user" />
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <h4 class="title has-text-weight-bold is-4">Timeline</h4>
                    <x-entity-timeline :timeline="$user->timeline()" />
                </div>
            </div>
        </div>
    </div>

    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <div class="box">
                    <livewire:users.category-limits-list :user="$user" />
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <livewire:users.rotations-list :user="$user" />
                </div>
            </div>
            <div class="column is-full">
                <div class="box">
                    <livewire:users.payouts-list :user="$user" />
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="owing_modal">
    <div class="modal-background" onclick="closeOwingModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Owing</p>
        </header>
        <section class="modal-card-body">
            <table class="table is-fullwidth">
                <tbody>
                <tr>
                    <td colspan="2">
                        <strong>Orders</strong>
                    </td>
                </tr>
                @forelse($user->orders->sortByDesc('created_at') as $order)
                    <tr>
                        <td>
                            <div>Order (#{{ $order->id }})</div>
                        </td>
                        <td>
                            <div>+{{ $order->purchaser_amount }}</div>
                        </td>
                    </tr>
                    @switch($order->status)
                    @case(\App\Enums\OrderStatus::FullyReturned)
                            <tr>
                                <td>
                                    <div>Return (#{{ $order->id }})</div>
                                </td>
                                <td>
                                    <div>-{{ $order->purchaser_amount }}</div>
                                </td>
                            </tr>
                            @break
                        @case(\App\Enums\OrderStatus::PartiallyReturned)
                                @if($order->getReturnedTotalToCash()->isPositive())
                                    <tr>
                                        <td>
                                            <div>Partial Return (#{{ $order->id }})</div>
                                        </td>
                                        <td>
                                            <div>-{{ $order->getReturnedTotalToCash() }}</div>
                                        </td>
                                    </tr>
                                @endif
                            @break
                    @endswitch
                @empty
                    <tr>
                        <td>
                            <div><i>No Orders</i></div>
                        </td>
                        <td>
                            <div></div>
                        </td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2">
                        <strong>Activities</strong>
                    </td>
                </tr>
                @forelse($user->activityRegistrations as $registration)
                    <tr>
                        <td>
                            <div>Activity ({{ $registration->activity->name }})</div>
                        </td>
                        <td>
                            <div>+{{ $registration->total_price }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>
                            <div><i>No Activities</i></div>
                        </td>
                        <td>
                            <div></div>
                        </td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2">
                        <strong>Payouts</strong>
                    </td>
                </tr>
                @forelse($user->payouts->sortByDesc('created_at') as $payout)
                    <tr>
                        <td>
                            <div>Payout ({{ $payout->identifier }})</div>
                        </td>
                        <td>
                            <div>-{{ $payout->amount }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>
                            <div><i>No Payouts</i></div>
                        </td>
                        <td>
                            <div></div>
                        </td>
                    </tr>
                @endforelse
                <tr>
                    <td></td>
                    <td>
                        <div><strong>&nbsp;&nbsp;{{ $owing }}</strong></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeOwingModal();">Close</button>
            <a class="button is-success" target="_blank" href="{{ route('users_pdf', $user) }}">
                <span class="icon is-small">
                    <i class="fas fa-file"></i>
                </span>
                <span>PDF</span>
            </a>
        </footer>
    </div>
</div>

<script>
    const owingModal = document.getElementById('owing_modal');

    const openOwingModal = () => {
        owingModal.classList.add('is-active');
    }

    const closeOwingModal = () => {
        owingModal.classList.remove('is-active');
    }
</script>
@endsection
