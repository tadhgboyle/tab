@extends('layouts.default', ['page' => 'orders'])
@section('content')
<h2 class="title has-text-weight-bold">Order List</h2>
<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column" id="order_container" style="visibility: hidden;">
        @include('includes.messages')
        <table id="order_list">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Purchaser</th>
                    <th>Cashier</th>
                    <th>Total Price</th>
                    <th>Products</th>
                    <th>Status</th>
                    @permission(\App\Helpers\Permission::ORDERS_VIEW)
                    <th></th>
                    @endpermission
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>
                        <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
                    </td>
                    <td>
                        <div>
                            @permission(\App\Helpers\Permission::USERS_VIEW)
                                <a href="{{ route('users_view', $transaction->purchaser) }}">{{ $transaction->purchaser->full_name }}</a>
                            @else
                                {{ $transaction->purchaser->full_name }}
                            @endpermission
                        </div>
                    </td>
                    <td>
                        <div>{{ $transaction->cashier->full_name }}</div>
                    </td>
                    <td>
                        <div>{{ $transaction->total_price }}</div>
                    </td>
                    <td>
                        <div class="tag is-medium is-clickable" id="products-tooltip-{{ $transaction->id }}" onclick="openTransactionProductsModal({{ $transaction->id }})">
                            {{ $transaction->products_sum_quantity }}
                        </div>
                    </td>
                    <td>
                        <div>{!! $transaction->getStatusHtml() !!}</div>
                    </td>
                    @permission(\App\Helpers\Permission::ORDERS_VIEW)
                    <td>
                        <div><a href="{{ route('orders_view', $transaction->id) }}">View</a></div>
                    </td>
                    @endpermission
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal modal-transaction-products">
    <div class="modal-background" onclick="closeTransactionProductsModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Transaction Products</p>
        </header>
        <section class="modal-card-body">
            <table id="transaction-products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody id="transaction-products-results"></tbody>
            </table>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeTransactionProductsModal();">Close</button>
        </footer>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#order_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "order": [],
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    5,
                    @permission(\App\Helpers\Permission::ORDERS_VIEW)
                    6
                    @endpermission
                ]
            }]
        });
        $('#loading').hide();
        $('#order_container').css('visibility', 'visible');
    });

    const modal_transaction_products = document.querySelector('.modal-transaction-products');

    async function openTransactionProductsModal(id) {
        await fetch(`/orders/${id}/products`)
            .then(response => response.text())
            .then(data => document.getElementById('transaction-products-results').innerHTML = data);
            modal_transaction_products.classList.add('is-active');
    }

    function closeTransactionProductsModal() {
        modal_transaction_products.classList.remove('is-active');
    }

    $('#transaction-products-table').DataTable({
        "paging": false,
        "searching": false,
        "bInfo": false,
        "ordering": false,
    });
</script>
@endsection
