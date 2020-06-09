@extends('layouts.default')
@section('content')

<h2>Cashier</h2>
@php

use App\Products;
use App\User;
use App\Http\Controllers\SettingsController;

$user = User::find(request()->route('id'));
if ($user == null) return redirect('/')->with('error', 'Invalid user.')->send();
@endphp
<p>User: {{ $user->full_name }} <a href="/users/info/{{ request()->route('id') }}">(Info)</a></p>
<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-7">
        @include('includes.messages')
        <form method="post" id="order" action="/orders/{{ request()->route('id') }}/submit">
            @csrf
            <input type="hidden" name="purchaser_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="cashier_id" value="{{ Auth::user()->id }}">
            <!-- This is all used by item-sidebar.js to calculate things -->
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
                    @foreach(Products::all()->where('deleted', false) as $product)
                    <tr>
                        <td class="table-text">
                            <center>
                                <input type="checkbox" name="product[]" value="{{ $product->id }}"
                                    id="{{ $product->name . ' $' . $product->price }}" class="clickable" />
                            </center>
                            <input type="hidden" id="pst[{{ $product->id }}]" name="pst[{{ $product->id }}]"
                                value="{{ $product->pst }}" />
                        </td>
                        <td class="table-text">
                            <center>
                                <input type="number" name="quantity[{{ $product->id }}]"
                                    id="quantity[{{ $product->id }}]" value="1" min="1"/>
                            </center>
                        </td>
                        <td class="table-text">
                            <div>{{ $product->name }}</div>
                        </td>
                        <td class="table-text">
                            <div>{{ ucfirst($product->category) }}</div>
                        </td>
                        <td class="table-text">
                            <div>{{ Products::getStock($product->id) }}</div>
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
    <div class="col-md-4" align="center">
        <h3>Items</h3>
        <div id="items"></div>
        <hr>
        <div id="gst"></div>
        <div id="pst"></div>
        <div id="total_price"></div>
        <div id="remaining_balance"></div>
        <p></p>
        <input type="submit" form="order" value="Submit" class="disableable btn btn-xs btn-success">
        <span>&nbsp;&nbsp;</span>
        <input type="submit" onclick="window.location='/';" value="Cancel" class="btn btn-xs btn-danger">
    </div>
</div>
<script>
    $(document).ready(function() {
        let table = $('#product_list').DataTable({
            "paging": false,
            "scrollY": "23vw",
            "scrollCollapse": true,
        });
    });
</script>
<script src="{{ url('item-sidebar.js') }}"></script>
@stop