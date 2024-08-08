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
                @foreach($orders as $order)
                <tr>
                    <td>
                        <div>{{ $order->created_at->format('M jS Y h:ia') }}</div>
                    </td>
                    <td>
                        <div>
                            @permission(\App\Helpers\Permission::USERS_VIEW)
                                <a href="{{ route('users_view', $order->purchaser) }}">{{ $order->purchaser->full_name }}</a>
                            @else
                                {{ $order->purchaser->full_name }}
                            @endpermission
                        </div>
                    </td>
                    <td>
                        <div>{{ $order->cashier->full_name }}</div>
                    </td>
                    <td>
                        <div>{{ $order->total_price }}</div>
                    </td>
                    <td>
                        <div class="tag is-medium is-clickable" id="products-tooltip-{{ $order->id }}" onclick="openOrderProductsModal({{ $order->id }})">
                            {{ $order->products_sum_quantity }}
                        </div>
                    </td>
                    <td>
                        <div>{!! $order->getStatusHtml() !!}</div>
                    </td>
                    @permission(\App\Helpers\Permission::ORDERS_VIEW)
                    <td>
                        <div><a href="{{ route('orders_view', $order->id) }}">View</a></div>
                    </td>
                    @endpermission
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal modal-order-products">
    <div class="modal-background" onclick="closeOrderProductsModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Order Products</p>
        </header>
        <section class="modal-card-body">
            <table id="order-products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody id="order-products-results"></tbody>
            </table>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeOrderProductsModal();">Close</button>
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

    const modal_order_products = document.querySelector('.modal-order-products');

    async function openOrderProductsModal(id) {
        await fetch(`/orders/${id}/products`)
            .then(response => response.text())
            .then(data => document.getElementById('order-products-results').innerHTML = data);
            modal_order_products.classList.add('is-active');
    }

    function closeOrderProductsModal() {
        modal_order_products.classList.remove('is-active');
    }

    $('#order-products-table').DataTable({
        "paging": false,
        "searching": false,
        "bInfo": false,
        "ordering": false,
    });
</script>
@endsection
