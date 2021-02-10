@php

use App\Http\Controllers\TransactionController;
use App\Product;
use App\Transaction;
use App\User;
use App\Role;

$transaction = Transaction::find(request()->route('id'));
if ($transaction == null) return redirect()->route('orders_list')->with('error', 'Invalid order.')->send();

$transaction_items = explode(", ", $transaction->products);
$transaction_returned = $transaction->checkReturned();
$users_view = Auth::user()->hasPermission('users_view');
$return_order = Auth::user()->hasPermission('orders_return');
@endphp
@extends('layouts.default', ['page' => 'orders'])
@section('content')
<h2 class="title has-text-weight-bold">View Order</h2>
<div class="columns box">
    <div class="column">
        @include('includes.messages')
        <p><strong>Order ID:</strong> {{ request()->route('id') }}</p>
        <p><strong>Date:</strong> {{ $transaction->created_at->format('M jS Y h:ia') }}</p>
        <p><strong>Purchaser:</strong> @if($users_view) <a href="{{ route('users_view', $transaction->purchaser_id) }}">{{ $transaction->purchaser_id->full_name }}</a> @else {{ $transaction->purchaser_id->full_name }} @endif</p>
        <p><strong>Cashier:</strong> @if($users_view) <a href="{{ route('users_view', $transaction->cashier_id) }}">{{ $transaction->cashier_id->full_name }}</a> @else {{ $transaction->cashier_id->full_name }} @endif</p>
        <p><strong>Total Price:</strong> ${{ number_format($transaction->total_price, 2) }}</p>
        <p><strong>Status:</strong> @switch($transaction_returned) @case(0) Not Returned @break @case(1) Returned @break @case(2) Semi Returned @break @endswitch</p>
        <br>
        @if($transaction_returned != 1 && $return_order)
        <button class="button is-danger is-outlined" type="button" onclick="openModal();">
            <span>Return</span>
            <span class="icon is-small">
                <i class="fas fa-undo"></i>
            </span>
        </button>
        @endif
    </div>
    <div class="column">
        <h4 class="title has-text-weight-bold is-4">Items</h4>
        <div id="loading" align="center">
            <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
        </div>
        <div id="table_container" style="visibility: hidden;">
            <table id="product_list">
                <thead>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Item Price</th>
                    @if($return_order)
                    <th></th>
                    @endif
                </thead>
                <tbody>
                    @foreach($transaction_items as $product)
                    @php $item_info = TransactionController::deserializeProduct($product); @endphp
                    <tr>
                        <td>
                            <div>{{ $item_info['name'] }}</div>
                        </td>
                        <td>
                            <div>${{ number_format($item_info['price'], 2) }}</div>
                        </td>
                        <td>
                            <div>{{ $item_info['quantity'] }}</div>
                        </td>
                        <td>
                            <div>${{ number_format($item_info['price'] * $item_info['quantity'], 2) }}</div>
                        </td>
                        @if($return_order)
                        <td>
                            <div>
                                @if($transaction->status == 0 && $item_info['returned'] < $item_info['quantity']) 
                                    <button class="button is-danger is-small" onclick="openProductModal({{ $item_info['id'] }});">Return ({{ $item_info['quantity'] - $item_info['returned'] }})</button>
                                @else
                                    <div>Returned</div>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(!is_null($transaction) && $return_order)
<div class="modal modal-order">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to return this transaction?</p>
            <form action="" id="returnForm" method="GET">
                @csrf
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" onclick="returnData();">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endif

@if($return_order)
<div class="modal modal-product">
    <div class="modal-background" onclick="closeProductModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to return this product?</p>
            <form action="" id="returnItemForm" method="GET">
                @csrf
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" onclick="returnProductData();">Confirm</button>
            <button class="button" onclick="closeProductModal();">Cancel</button>
        </footer>
    </div>
</div>
@endif

<script type="text/javascript">
    $(document).ready(function() {
        $('#product_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            @if($return_order)
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [4]
            }]
            @endif
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');
    });

    @if($return_order)
    const modal_order = document.querySelector('.modal-order');

    function openModal() {
        modal_order.classList.add('is-active');
    }

    function closeModal() {
        modal_order.classList.remove('is-active');
    }

    function returnData() {
        let url = '{{ route("orders_return", ":id") }}';
        url = url.replace(':id', {{ $transaction->id }});
        $("#returnForm").attr('action', url);
        $("#returnForm").submit();
    }

    let product = null;
    const modal_product = document.querySelector('.modal-product');

    function openProductModal(return_product) {
        product = return_product;
        modal_product.classList.add('is-active');
    }

    function closeProductModal() {
        product = null;
        modal_product.classList.remove('is-active');
    }

    function returnProductData() {
        let url = '{{ route("orders_return_item", [":item", ":order"]) }}';
        url = url.replace(':item', product);
        url = url.replace(':order', {{ $transaction->id }});
        $("#returnItemForm").attr('action', url);
        $("#returnItemForm").submit();
    }
    @endif
</script>
@endsection