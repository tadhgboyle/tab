@extends('layouts.default', ['page' => 'products'])
@section('content')
<x-page-header title="Products" :actions="[
    [
        'label' => 'Create',
        'href' => route('products_create'),
        'can' => hasPermission(\App\Helpers\Permission::PRODUCTS_MANAGE)
    ],
]" />
<livewire:admin.products-list />
@endsection
