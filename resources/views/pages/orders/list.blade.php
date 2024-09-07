@extends('layouts.default', ['page' => 'orders'])
@section('content')
<h2 class="title has-text-weight-bold">Order List</h2>
<div class="columns box">
    <div class="column">
        <livewire:orders-list />
    </div>
</div>

@endsection
