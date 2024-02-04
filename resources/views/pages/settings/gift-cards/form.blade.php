@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($giftCard) ? 'Edit' : 'Create' }} Gift Card</h2>
@isset($giftCard)
    <h4 class="subtitle"><strong>Gift Card:</strong> {{ $giftCard->code() }}</h4>
@endisset
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')

        <form action="{{ isset($giftCard) ? route('settings_gift-cards_update', $giftCard->id) : route('settings_gift-cards_store') }}" method="POST">
            @csrf
            @isset($giftCard)
                @method('PUT')
                <input type="hidden" name="gift_card_id" value="{{ $giftCard->id }}">
                <input type="hidden" name="remaining_balance" value="{{ $giftCard->remaining_balance->getAmount() / 100 }}">
            @endisset

            <div class="field">
                <label class="label">Balance<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" name="balance" id="balance" class="input money-input" placeholder="0" step="0.01" min="0" value="{{ (isset($giftCard) ? $giftCard->remaining_balance->formatForInput() : null) ?? number_format(old('balance'), 2) }}">
                </div>
            </div>

            <label class="label">Code<sup style="color: red">*</sup></label>
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input type="text" id="code" name="code" class="input" placeholder="Code" value="{{ $giftCard->code ?? old('code') }}" required>
                </div>
                <div class="control">
                    <button class="button is-info" id="generateCode">
                        Generate
                    </button>
                </div>
            </div>

            <div class="control">
                <button class="button is-success" type="submit">
                    <span class="icon is-small">
                        <i class="fas fa-save"></i>
                    </span>
                    <span>Save</span>
                </button>
                <a class="button is-outlined" href="{{ route('settings') }}">
                    <span>Cancel</span>
                </a>
                @isset($giftCard)
                    <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openModal();">
                        <span>Delete</span>
                        <span class="icon is-small">
                            <i class="fas fa-times"></i>
                        </span>
                    </button>
                @endisset
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>

@isset($giftCard)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete this gift card?</p>
            <form action="{{ route('settings_gift-cards_delete', $giftCard) }}" id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="deleteForm">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endisset

<script>
    document.getElementById('generateCode').addEventListener('click', event => {
        event.preventDefault();
        document.getElementById('code').value = Math.random().toString(36).substring(2, 12).toUpperCase();
    }, false);

    @isset($giftCard)
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }
    @endisset
</script>
@stop
