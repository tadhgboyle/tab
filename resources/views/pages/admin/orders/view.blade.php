@extends('layouts.default', ['page' => 'orders'])
@section('content')
<h2 class="title has-text-weight-bold">View Order</h2>
<div class="columns">
    <div class="column">
        <div class="is-pulled-right">
            @if($order->status !== \App\Enums\OrderStatus::FullyReturned && hasPermission(\App\Helpers\Permission::ORDERS_RETURN))
                <button class="button is-danger is-outlined" type="button" onclick="openModal();">
                    <span>Return</span>
                    <span class="icon is-small">
                        <i class="fas fa-undo"></i>
                    </span>
                </button>
            @endif
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-two-thirds">
        <div class="card">
            <div class="card-content">
                @foreach($order->products()->with('product.category')->get() as $orderProduct)
                <div class="content">
                    <div class="columns">
                        <div class="column">
                            <strong>
                                @permission(\App\Helpers\Permission::PRODUCTS_VIEW)
                                    <a href="{{ route('products_view', $orderProduct->product) }}">{{ $orderProduct->product->name }}</a>
                                @else
                                    {{ $orderProduct->product->name }}
                                @endpermission
                            </strong>
                            @if($orderProduct->productVariant)
                                <br>
                                @foreach($orderProduct->productVariant->descriptions(false) as $description)
                                    <span class="tag">{{ $description }}</span>
                                @endforeach
                            @endif
                            @if($orderProduct->productVariant || $orderProduct->product->sku)
                                <br>
                                <span class="is-size-7">SKU: {{ $orderProduct->productVariant ? $orderProduct->productVariant->sku : $orderProduct->product->sku }}</span>
                            @endif
                            <br>
                            <span class="is-size-7">Category: {{ $orderProduct->product->category->name }}</span>
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                <p>
                                    {{ $orderProduct->price }}
                                    x
                                    @if($orderProduct->returned > 0)
                                        <del>{{ $orderProduct->quantity }}</del> {{ $orderProduct->quantity - $orderProduct->returned }}
                                    @else
                                        {{ $orderProduct->quantity }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                {{ $orderProduct->price->multiply($orderProduct->quantity) }}
                            </div>
                        </div>
                        @permission(\App\Helpers\Permission::ORDERS_RETURN)
                            <div class="column">
                                <div class="is-pulled-right">
                                    @if(!$order->isReturned() && $orderProduct->returned < $orderProduct->quantity)
                                        <button class="button is-danger is-small is-outlined"  onclick="openProductModal({{ $orderProduct->id }});">Return ({{ $orderProduct->quantity - $orderProduct->returned }})</button>
                                    @else
                                        <div><i>Returned</i></div>
                                    @endif
                                </div>
                            </div>
                        @endpermission
                    </div>
                </div>
                    @unless($loop->last)
                        <hr />
                    @endif
                @endforeach
            </div>
        </div>
        <br>
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <div class="columns">
                        <div class="column">
                            Subtotal
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                @php
                                    $products_count = $order->products()->sum('quantity');
                                @endphp
                                {{ $products_count }} {{ \Str::plural('item', $products_count) }}
                            </div>
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                {{ $order->subtotal() }}
                            </div>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column is-two-thirds">
                            Taxes
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                {{ $order->totalTax() }}
                            </div>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column is-two-thirds">
                            <strong>Total</strong>
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                <strong>{{ $order->total_price }}</strong>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="columns">
                        <div class="column is-two-thirds">
                            Purchaser amount
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                {{ $order->purchaser_amount }}
                            </div>
                        </div>
                    </div>
                    @if($order->gift_card_amount->isPositive())
                        <div class="columns">
                            <div class="column is-two-thirds">
                                @permission(\App\Helpers\Permission::SETTINGS_GIFT_CARDS_MANAGE)
                                    <a href="{{ route('settings_gift-cards_view', $order->giftCard)}}">Gift card amount</a>
                                @else
                                    Gift card amount
                                @endpermission
                            </div>
                            <div class="column">
                                <div class="is-pulled-right">
                                    {{ $order->gift_card_amount }}
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($order->status !== \App\Enums\OrderStatus::NotReturned)
                        <hr>
                        <div class="columns">
                            <div class="column is-two-thirds">
                                Purchaser returned
                            </div>
                            <div class="column">
                                <div class="is-pulled-right">
                                    {{ $order->getReturnedTotalToCash() }}
                                </div>
                            </div>
                        </div>
                        @if($order->gift_card_amount->isPositive())
                            <div class="columns">
                                <div class="column is-two-thirds">
                                    Gift card returned
                                </div>
                                <div class="column">
                                    <div class="is-pulled-right">
                                        {{ $order->getReturnedTotalToGiftCard() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="columns">
                            <div class="column is-two-thirds">
                                <strong>Returned</strong>
                            </div>
                            <div class="column">
                                <div class="is-pulled-right">
                                    <strong>{{ $order->getReturnedTotal() }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column is-two-thirds">
                                Balance
                            </div>
                            <div class="column">
                                <div class="is-pulled-right">
                                    {{ $order->getOwingTotal() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="column">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Identifier" :value="$order->identifier" />
                    <x-detail-card-item label="Status">
                        <x-order-status-badge :order="$order" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Date" :value="$order->created_at->format('M jS Y h:ia')" />
                    <x-detail-card-item label="Rotation">
                        <x-badge :value="$order->rotation->name" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Purchaser">
                        @permission(\App\Helpers\Permission::USERS_VIEW)
                            <a href="{{ route('users_view', $order->purchaser_id) }}">{{ $order->purchaser->full_name }}</a>
                        @else
                            {{ $order->purchaser->full_name }}
                        @endpermission
                        - {{ $order->purchaser->orders()->count() }} orders
                    </x-detail-card-item>
                    <x-detail-card-item label="Cashier">
                        @permission(\App\Helpers\Permission::USERS_VIEW)
                            <a href="{{ route('users_view', $order->cashier_id) }}">{{ $order->cashier->full_name }}</a>
                        @else
                            {{ $order->cashier->full_name }}
                        @endpermission
                    </x-detail-card-item>
                </x-detail-card-item-list>
            </x-detail-card>
            <x-entity-timeline :timeline="$order->timeline()" />
        </x-detail-card-stack>
    </div>
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
