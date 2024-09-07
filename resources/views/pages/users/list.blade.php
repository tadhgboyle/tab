@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">User List</h2>
<div class="columns">
    <div class="column">
        <div class="box">
            <livewire:users-list />
        </div>
    </div>
</div>
@endsection
