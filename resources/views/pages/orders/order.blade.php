@extends('layouts.default')
@section('content')
<h2>Cashier</h2>
<?php

use App\Products;
?>
<div class="row">
    <div class="col-md-2"></div>
    <div class="panel-body col-md-6">
        @if (\Session::has('error'))
        <div class="alert alert-danger">
            <p>{!! \Session::get('error') !!}</p>
        </div>
        @endif
        <form method="post" action="/orders/{{ request()->route('id') }}/submit">
            {{ csrf_field() }}
            <input type="hidden" name="purchaser_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="cashier_id" value="{{ Auth::user()->id }}">
            <table id="product_list">
                <thead>
                    <th></th>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Price</th>
                </thead>
                <tbody>
                    @foreach(Products::all() as $product)
                    <tr>
                        <td class="table-text">
                            <center><input type="checkbox" name="product[]" value="{{ $product->id }}" onclick="updateItems('<?php echo $product->name ?>' , <?php echo $product->price ?>)" /></center>
                        </td>
                        <td class="table-text">
                            <center><input type="number" name="quantity[{{ $product->id }}]" value="1"/></center>
                        </td>
                        <td class="table-text">
                            <div>{{ $product->name }}</div>
                        </td>
                        <td class="table-text">
                            <div>${{ $product->price }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button>Submit</button>
        </form>
    </div>
    <div class="col-md-4" align="center">
        <h3>Items</h3>
        <div id="items"></div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#product_list').DataTable({
            paging: false
        });
    });
</script>
@stop