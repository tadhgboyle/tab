@extends('layouts.default', ['page' => 'users'])
@section('content')
<x-page-header title="Users" :actions="[
    [
        'label' => 'Create',
        'href' => route('users_create'),
        'can' => hasPermission(\App\Helpers\Permission::USERS_MANAGE)
    ],
]" />
<livewire:admin.users-list />
@endsection
