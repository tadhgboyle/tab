@extends('layouts.default', ['page' => 'cashier'])
@section('content')
<x-page-header title="Cashier" />
<livewire:admin.cashier-list />
<script>
    @if (session()->has('last_purchaser_id'))
        localStorage.clear("items-{{ session()->get('last_purchaser_id') }}")
    @endif
</script>
@stop
