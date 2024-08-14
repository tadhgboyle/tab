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

        <form action="{{ route('settings_gift-cards_store') }}" method="POST">
            @csrf

            <div class="field">
                <label class="label">Balance<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" name="balance" id="balance" class="input money-input" placeholder="0" step="0.01" min="0" value="{{ number_format(old('balance'), 2) }}">
                </div>
            </div>

            <label class="label">Code<sup style="color: red">*</sup></label>
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input type="text" id="code" name="code" class="input" placeholder="Code" value="{{ old('code') }}" required>
                </div>
                <div class="control">
                    <button class="button is-info" id="generateCode">
                        Generate
                    </button>
                </div>
            </div>

            <label class="label">Expiry</label>
            <div class="field">
                <div class="control">
                    <input type="datetime-local" id="expires_at" name="expires_at" class="input" value="{{ old('expires_at') }}" min={{ now()->toDateTimeLocalString() }}>
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

<script>
    document.getElementById('generateCode').addEventListener('click', event => {
        event.preventDefault();
        document.getElementById('code').value = Math.random().toString(36).substring(2, 12).toUpperCase();
    }, false);
</script>
@stop
