@extends('layouts.default', ['page' => 'cashier'])
@section('content')
<h2 class="title has-text-weight-bold">Cashier</h2>
<livewire:admin.cashier-list />
<script>
    @if (session()->has('last_purchaser_id'))
        localStorage.clear("items-{{ session()->get('last_purchaser_id') }}")
    @endif
</script>
@stop
