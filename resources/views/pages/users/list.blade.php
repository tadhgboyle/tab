@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">User List</h2>
<div class="columns">
    <div class="column">
        @include('includes.messages')
        <livewire:user-table />
    </div>
</div>
@endsection
