@extends('layouts.default', ['page' => 'families'])
@section('content')
<x-page-header title="Families" :actions="[
    [
        'label' => 'Create',
        'href' => route('families_create'),
        'can' => hasPermission(\App\Helpers\Permission::FAMILIES_MANAGE)
    ],
]" />
<livewire:admin.families-list />
@endsection
