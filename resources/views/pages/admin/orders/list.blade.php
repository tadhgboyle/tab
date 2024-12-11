@extends('layouts.default', ['page' => 'orders'])
@section('content')
<x-page-header title="Orders" />
<livewire:admin.orders-list />
@endsection
