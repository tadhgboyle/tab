@extends('layouts.default', ['page' => 'orders'])
@section('content')
<h2 class="title has-text-weight-bold">View Order</h2>
<div class="box">
    <div class="columns">
        <div class="column">
            @include('includes.messages')
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('M jS Y h:ia') }}</p>
            <p><strong>Rotation:</strong> {{ $order->rotation->name }}</p>
            <p><strong>Purchaser:</strong> @permission(\App\Helpers\Permission::USERS_VIEW) <a href="{{ route('users_view', $order->purchaser_id) }}">{{ $order->purchaser->full_name }}</a> @else {{ $order->purchaser->full_name }} @endpermission</p>
            <p><strong>Cashier:</strong> @permission(\App\Helpers\Permission::USERS_VIEW) <a href="{{ route('users_view', $order->cashier_id) }}">{{ $order->cashier->full_name }}</a> @else {{ $order->cashier->full_name }} @endpermission</p>
            <p><strong>Total Price:</strong> {{ $order->total_price }}</p>
            <p><strong>Purchaser amount:</strong> {{ $order->purchaser_amount }}</p>
            @if($order->gift_card_amount->isPositive())
                <p><strong>Gift card amount:</strong> {{ $order->gift_card_amount }} @if($order->giftCard) <code>{{ $order->giftCard->code() }}</code> @endif</p>
            @endif
            <p><strong>Status:</strong> {!! $order->getStatusHtml() !!}</p>
            @if($order->status !== \App\Models\Order::STATUS_NOT_RETURNED)
                <p><strong>Returned:</strong> {{ $order->getReturnedTotal() }}</p>
                @if($order->gift_card_amount->isPositive())
                    <p><strong>Returned to gift card:</strong> {{ $order->getReturnedTotalToGiftCard() }}</p>
                @endif
                <p><strong>Returned to purchaser balance:</strong> {{ $order->getReturnedTotalInCash() }}</p>
                @if($order->gift_card_amount->isPositive())
                    <p><strong>Amount left to return to gift card:</strong> {{ $order->gift_card_amount->subtract($order->getReturnedTotalToGiftCard()) }}</p>
                @endif
                <p><strong>Amount left to return:</strong> {{ $order->getOwingTotal() }}</p>
            @endif
            <br>
            @if($order->status !== \App\Models\Order::STATUS_FULLY_RETURNED && hasPermission(\App\Helpers\Permission::ORDERS_RETURN))
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
                <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
            </div>
            <div id="table_container" style="visibility: hidden;">
                <table id="product_list">
                    <thead>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Item Subtotal</th>
                        @permission(\App\Helpers\Permission::ORDERS_RETURN)
                        <th></th>
                        @endpermission
                    </thead>
                    <tbody>
                        @foreach($order->products as $product)
                        <tr>
                            <td>
                                <div>{{ $product->product->name }}</div>
                            </td>
                            <td>
                                <div>{{ $product->price }}</div>
                            </td>
                            <td>
                                <div>{{ $product->quantity }}</div>
                            </td>
                            <td>
                                <div>{{ $product->price->multiply($product->quantity) }}</div>
                            </td>
                            @permission(\App\Helpers\Permission::ORDERS_RETURN)
                            <td>
                                <div>
                                    @if(!$order->returned && $product->returned < $product->quantity)
                                        <button class="button is-danger is-small"  onclick="openProductModal({{ $product->id }});">Return ({{ $product->quantity - $product->returned }})</button>
                                    @else
                                        <div><i>Returned</i></div>
                                    @endif
                                </div>
                            </td>
                            @endpermission
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-entity-timeline :timeline="$order->timeline()" />
</div>

@permission(\App\Helpers\Permission::ORDERS_RETURN)
<div class="modal modal-order">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to return this order?</p>
            <form action="{{ route('orders_return', $order->id) }}" id="returnOrderForm" method="POST">
                @csrf
                @method('PUT')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="returnOrderForm">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>

<div class="modal modal-product">
    <div class="modal-background" onclick="closeProductModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to return this product?</p>
            <form action="" id="returnItemForm" method="POST">
                @csrf
                @method('PUT')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" onclick="returnProductData();">Confirm</button>
            <button class="button" onclick="closeProductModal();">Cancel</button>
        </footer>
    </div>
</div>
@endpermission

<script type="text/javascript">
    $(document).ready(function() {
        $('#product_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            @permission(\App\Helpers\Permission::ORDERS_RETURN)
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [4]
            }]
            @endpermission
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');
    });

    @permission(\App\Helpers\Permission::ORDERS_RETURN)
        const modal_order = document.querySelector('.modal-order');

        function openModal() {
            modal_order.classList.add('is-active');
        }

        function closeModal() {
            modal_order.classList.remove('is-active');
        }

        let orderProduct = null;
        const modal_product = document.querySelector('.modal-product');

        function openProductModal(return_product) {
            orderProduct = return_product;
            modal_product.classList.add('is-active');
        }

        function closeProductModal() {
            orderProduct = null;
            modal_product.classList.remove('is-active');
        }

        function returnProductData() {
            let url = '{{ route("order_return_product", [":order", ":orderProduct"]) }}';
            url = url.replace(':order', {{ $order->id }});
            url = url.replace(':orderProduct', orderProduct);
            $("#returnItemForm").attr('action', url);
            $("#returnItemForm").submit();
        }
    @endpermission
</script>
@endsection
