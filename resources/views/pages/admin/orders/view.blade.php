@extends('layouts.default', ['page' => 'orders'])
@section('content')
<x-page-header title="Order {{ $order->identifier }}" :actions="[
    [
        'label' => 'Return',
        'onclick' => 'openModal()',
        'color' => 'danger',
        'can' => $order->status !== \App\Enums\OrderStatus::FullyReturned && hasPermission(\App\Helpers\Permission::ORDERS_RETURN)
    ],
]" />

<div class="grid grid-cols-6 gap-5">
    <div class="col-span-4">
        <div class="border border-gray-200 rounded-lg shadow-sm">
            @foreach($order->products()->with('product.category')->get() as $orderProduct)
                <div class="bg-gray-50 px-4 py-3 @if($loop->first) rounded-t-lg border-b @else border-y @endif font-semibold text-gray-950">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm">
                            @permission(\App\Helpers\Permission::PRODUCTS_VIEW)
                                <a href="{{ route('products_view', $orderProduct->product) }}" class="hover:underline">
                                    {{ $orderProduct->product->name }}
                                </a>
                            @else
                                {{ $orderProduct->product->name }}
                            @endpermission

                            @if($orderProduct->productVariant)
                                <div class="inline-flex items-center ml-2 space-x-1">
                                    @foreach($orderProduct->productVariant->descriptions(false) as $description)
                                        <x-badge :value="$description"  />
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @permission(\App\Helpers\Permission::ORDERS_RETURN)
                            @if(!$order->isReturned() && $orderProduct->returned < $orderProduct->quantity)
                                <x-filament::button onclick="openProductModal({{ $orderProduct->id }});" color="danger" size="xs">
                                    Return ({{ $orderProduct->quantity - $orderProduct->returned }})
                                </x-filament::button>  
                            @endif
                        @endpermission
                    </div>
                </div>
                <div class="bg-white px-4 rounded-lg py-3">
                    <div class="grid grid-cols-3 gap-4 items-start">
                        <div class="col-span-2">
                            @if($orderProduct->productVariant || $orderProduct->product->sku)
                                <div class="text-xs text-gray-950">
                                    SKU: <span class="font-mono">{{ $orderProduct->productVariant ? $orderProduct->productVariant->sku : $orderProduct->product->sku }}</span>
                                </div>
                            @endif
                            <div class="text-xs inline-flex items-center mt-1 space-x-1 text-gray-950">
                                <p>Category:</p> <x-badge :value="$orderProduct->product->category->name" />
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-xs">
                                {{ $orderProduct->price }} x 
                                @if($orderProduct->returned > 0)
                                    <del>{{ $orderProduct->quantity }}</del> {{ $orderProduct->quantity - $orderProduct->returned }}
                                @else
                                    {{ $orderProduct->quantity }}
                                @endif
                            </p>
                            <p class="text-sm font-semibold text-gray-950 mt-1">
                                {{ $orderProduct->price->multiply($orderProduct->quantity) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm mt-5">
            <div class="bg-white px-3 text-gray-950 rounded-lg">
                <div class="pt-3 pb-1 flex justify-between items-center text-sm text-gray-950">
                    <dt>
                        @php $products_count = $order->products()->sum('quantity'); @endphp
                        Subtotal - {{ $products_count }} {{ \Str::plural('item', $products_count) }}
                    </dt>
                    <dd class="text-right">{{ $order->subtotal() }}</dd>
                </div>
                <div class="pb-3 flex justify-between items-center text-sm text-gray-950">
                    <dt>Taxes</dt>
                    <dd class="text-right text-gray-800">{{ $order->totalTax() }}</dd>
                </div>
                <div class="pb-3">
                    <div class="pt-3 flex justify-between items-center text-sm text-gray-950 border-t border-gray-200">
                        <dt><strong>Total</strong></dt>
                        <dd class="text-right text-gray-800"><strong>{{ $order->total_price }}</strong></dd>
                    </div>
                    <div class="pl-3">
                        @if($order->purchaser_amount->isPositive())
                        <div class="flex justify-between items-center text-sm text-gray-950">
                            <dt>Paid with cash</dt>
                            <dd class="text-right text-gray-800">{{ $order->purchaser_amount }}</dd>
                        </div>
                        @endif
                        @if($order->gift_card_amount->isPositive())
                        <div class="flex justify-between items-center text-sm text-gray-950">
                            <dt>Paid with gift card</dt>
                            <dd class="text-right text-gray-800">{{ $order->gift_card_amount }}</dd>
                        </div>
                        @endif
                    </div>
                </div>

                @if($order->status !== \App\Enums\OrderStatus::NotReturned)
                    <div class="pb-3">
                        <div class="pt-3 flex justify-between items-center text-sm text-gray-950 border-t border-gray-200">
                            <dt>Returned</dt>
                            <dd class="text-right text-gray-800">{{ $order->getReturnedTotal() }}</dd>
                        </div>
                        <div class="pl-3">
                            @if($order->purchaser_amount->isPositive())
                            <div class="flex justify-between items-center text-sm text-gray-950">
                                <dt>Returned to cash</dt>
                                <dd class="text-right text-gray-800">{{ $order->getReturnedTotalToCash() }}</dd>
                            </div>
                            @endif
                            @if($order->gift_card_amount->isPositive())
                                <div class="flex justify-between items-center text-sm text-gray-950">
                                    <dt>Returned to gift card</dt>
                                    <dd class="text-right text-gray-800">{{ $order->getReturnedTotalToGiftCard() }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="py-3 flex justify-between items-center text-sm text-gray-950 border-t border-gray-200">
                        <dt><strong>Balance</strong></dt>
                        <dd class="text-right text-gray-800"><strong>{{ $order->getOwingTotal() }}</strong></dd>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
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
