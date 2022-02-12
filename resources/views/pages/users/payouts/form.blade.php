@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">Create Payout</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')
        <form action="{{ route('users_payout_form', $user) }}" method="POST">
            @csrf
            <div class="field">
                <label class="label">Identifier</label>
                <div class="control">
                    <input type="text" name="identifier" class="input" placeholder="Identifier" value="{{ old('identifier') }}">
                </div>
            </div>
            <label class="label">Amount<sup style="color: red">*</sup></label>
            <div class="field @if($owing > 0.00) has-addons @endif">
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="amount" id="amount" class="input" required min="0.01" value="{{ number_format(old('amount'), 2) }}">
                </div>
                @if($owing > 0.00)
                    <div class="control">
                        <button class="button is-outlined is-info" id="setMax">
                            Full
                        </button>
                    </div>
                @endif
            </div>
            <div class="control pt-4">
                <button class="button is-success" type="submit">
                    <span class="icon is-small">
                        <i class="fas fa-save"></i>
                    </span>
                    <span>Save</span>
                </button>
                <a class="button is-outlined" href="{{ route('users_view', $user) }}">
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>
<script>
    @if($owing > 0.00)
        document.getElementById('setMax').addEventListener("click", event => {
            event.preventDefault();
            document.getElementById('amount').value = {{ $owing }}
        }, false);
    @endif
</script>
@stop
