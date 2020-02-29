<?php

use App\Products;
use App\Transactions;

$transaction = Transactions::where('id', '=', request()->route('id'))->get();
$transaction_items = explode(", ", $transaction['0']['products']);
?>
@extends('layouts.default')
@section('content')
<h2>View Order</h2>

<h4>Order ID: {{request()->route('id') }}</h4>
<h4>Time: {{ $transaction['0']['created_at'] }}</h4>
<h4>Purchaser: {{ DB::table('users')->where('id', $transaction['0']['purchaser_id'])->pluck('full_name')->first() }}</h4>
<h4>Cashier: {{ DB::table('users')->where('id', $transaction['0']['cashier_id'])->pluck('full_name')->first() }}</h4>
<h4>Total Price: ${{ $transaction['0']['total_price'] }}</h4>
<h4>Items: </h4>
<ul>
    <?php 
    foreach ($transaction_items as $items) {
        $item_info = Products::select('name', 'price')->where('id', '=', strtok($items, "*"))->get();
        $quantity = substr($items, strpos($items, "*") + 1); 
        echo "<li>" . $item_info['0']['name'] . " @ $" . $item_info['0']['price'] . " (x" . $quantity . ")</li>";
    }
    ?>
</ul>
@endsection