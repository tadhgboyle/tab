@extends('layouts.default', ['page' => 'purchase_orders'])
@section('content')
<x-page-header :title="$purchaseOrder?->reference ?? 'Create Purchase Order'" />

@endsection