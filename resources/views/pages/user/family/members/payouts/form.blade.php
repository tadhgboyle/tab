@extends('layouts.default', ['page' => 'family'])
@section('content')
<h2 class="title has-text-weight-bold">Create Payout</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        <form action="{{ route('family_member_payout_store', [$family, $familyMember]) }}" method="POST">
            @csrf
            <label class="label">Amount<sup style="color: red">*</sup></label>
            <div class="field @if($owing > 0.00) has-addons @endif">
                <div class="control has-icons-left is-expanded">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="amount" id="amount" class="input money-input" required min="0.01" max="{{ $owing }}" value="{{ number_format(old('amount'), 2) }}">
                    <p class="help" id="remaining-owing">Owing: ${{ $owing }}</p>
                </div>
                <div class="control">
                    <button class="button is-info" id="setMax">
                        Full
                    </button>
                </div>
            </div>
            <div class="control pt-4">
                <button class="button is-light" type="submit">
                    💾 Save
                </button>
                <a class="button is-outlined" href="{{ route('families_member_view', [$family, $familyMember]) }}">
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>
<script>
    const updateHelpText = () => {
        const amount = document.getElementById('amount').value;
        const remainingOwing = {{ $owing }};
        const remaining = remainingOwing - amount;
        document.getElementById('remaining-owing').innerText = `Owing: $${(remaining < 0 ? 0 : remaining).toFixed(2)}`;
    }

    document.getElementById('setMax').addEventListener("click", event => {
        event.preventDefault();
        document.getElementById('amount').value = {{ $owing }}
        updateHelpText();
    }, false);

    document.getElementById('amount').addEventListener("change", updateHelpText, false);
</script>
@stop
