@extends('layouts.default', ['page' => 'activities'])
@section('content')
<x-page-header title="Activities" :actions="[
    [
        'label' => 'Create',
        'href' => route('activities_create'),
        'can' => hasPermission(\App\Helpers\Permission::ACTIVITIES_MANAGE)
    ],
]" />
<livewire:admin.activities-list />
@endsection
