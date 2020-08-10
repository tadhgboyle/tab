@php

use App\Products;
use App\User;
use App\Http\Controllers\SettingsController;
$user = User::find(request()->route('id'));
if ($user == null) return redirect('/')->with('error', 'Invalid user.')->send();
@endphp
@extends('layouts.default', ['page' => 'cashier'])
@section('content')
<h2 class="title has-text-weight-bold">Cashier</h2>
<p><strong>User:</strong> {{ $user->full_name }} <a href="/users/info/{{ request()->route('id') }}">(Info)</a></p>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column is-two-thirds" id="order_container" style="visibility: hidden;">
        @include('includes.messages')
        <form method="post" id="order" action="/orders/submit">
            @csrf
            <input type="hidden" name="purchaser_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="cashier_id" value="{{ Auth::user()->id }}">
            <input type="hidden" id="current_gst" value="{{ SettingsController::getGst() }}">
            <input type="hidden" id="current_pst" value="{{ SettingsController::getPst() }}">
            <input type="hidden" id="purchaser_balance" value="{{ $user->balance }}">

            <table id="product_list">
                <thead>
                    <th></th>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Price</th>
                </thead>
                <tbody>
                    @foreach(Products::orderBy('name', 'ASC')->where('deleted', false)->get() as $product)
                    <tr>
                        <td class="table-text">
                            <center>
                                <input type="checkbox" name="product[{{ $product->id }}]" value="{{ $product->id }}"
                                    id="{{ $product->name . ' $' . $product->price }}" class="clickable"
                                    @if(session('product[' . $product->id . ']')) checked @endif />
                            </center>
                            <input type="hidden" id="pst[{{ $product->id }}]" name="pst[{{ $product->id }}]"
                                value="{{ $product->pst }}" />
                        </td>
                        <td class="table-text">
                            <center>
                                <input type="number" name="quantity[{{ $product->id }}]"
                                    id="quantity[{{ $product->id }}]"
                                    value="{{ session('quantity[' . $product->id . ']', 1) }}" min="1" />
                            </center>
                        </td>
                        <td class="table-text">
                            <div>{{ $product->name }}</div>
                        </td>
                        <td class="table-text">
                            <div>{{ ucfirst($product->category) }}</div>
                        </td>
                        <td class="table-text">
                            <div>{!! Products::getStock($product->id) !!}</div>
                        </td>
                        <td class="table-text">
                            <div>${{ number_format($product->price, 2) }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
    <div class="column" align="center">
        <h3 class="title">Items</h3>
        <div id="items"></div>
        <hr>
        <div id="gst"></div>
        <div id="pst"></div>
        <div id="total_price"></div>
        <div id="remaining_balance"></div>
        <br>
        <input type="submit" form="order" value="Submit" class="disableable button is-success" disabled>
        <a class="button is-danger is-outlined" href="/">
            <span>Cancel</span>
            <span class="icon is-small">
                <i class="fas fa-times"></i>
            </span>
        </a>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#product_list').DataTable({
            "paging": false,
            "scrollY": "27vw",
            "scrollCollapse": true,
            "order": [],
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [0, 1]
                }
            ]
        });
        $('#loading').hide();
        $('#order_container').css('visibility', 'visible');
    });
</script>
<script src="{{ url('item-sidebar.js') }}"></script>
@stop