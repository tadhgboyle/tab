@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">Product List</h2>
<div class="columns box">
    <div class="column">
        @include('includes.messages')
        <livewire:products-list />
    </div>
</div>
@endsection
