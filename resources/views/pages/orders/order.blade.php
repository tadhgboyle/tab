@extends('layouts.default', ['page' => 'cashier'])
@section('content')
<h2 class="title has-text-weight-bold">Cashier</h2>
<h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @permission(\App\Helpers\Permission::USERS_VIEW)<a href="{{ route('users_view', $user) }}">(View)</a>@endpermission</h4>
<div class="columns box">
    <div class="column is-two-thirds">
        @include('includes.messages')
        <div id="loading" align="center">
            <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
        </div>
        <div id="order_container" style="visibility: hidden;">
            <form method="post" id="order" name="order" action="{{ route('orders_store', $user->id) }}">
                @csrf
                <input type="hidden" id="purchaser_id" name="purchaser_id" value="{{ $user->id }}">
                <input type="hidden" id="current_pst" value="{{ $pst }}">
                <input type="hidden" id="current_gst" value="{{ $gst }}">
                <input type="hidden" id="purchaser_balance" value="{{ $user->balance }}">
                <input type="hidden" id="products" name="products" value="{}">

                <table id="product_list">
                    <thead>
                        <th></th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                @if($product->getStock() != 0)
                                    <span class="button is-small" onclick="addProduct({{ $product->id }})">
                                        <span class="icon is-small">
                                            <i class="fas fa-plus"></i>
                                        </span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $product->name }}</div>
                            </td>
                            <td>
                                <div>{{ $product->category->name }}</div>
                            </td>
                            <td>
                                <div>{!! $product->getStock() !!}</div>
                            </td>
                            <td>
                                <div>${{ number_format($product->price, 2) }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    <div class="column" align="center">
        <h3 class="title">Items</h3>
        <table class="table is-fullwidth">
            <thead>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
            </thead>
            <tbody id="items-table">
                <tr id="no-items" style="display: none;">
                    <td colspan="3">
                        <i>No items selected</i>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="table is-fullwidth">
            <tbody>
                <tr>
                    <td colspan="2">Subtotal</td>
                    <td id="subtotal-total"></td>
                </tr>
                <tr>
                    <td colspan="2">PST</td>
                    <td id="pst-total"></td>
                </tr>
                <tr>
                    <td colspan="2">GST</td>
                    <td id="gst-total"></td>
                </tr>
                <tr>
                    <td colspan="2">Total Price</td>
                    <td id="total-price"></td>
                </tr>
                <tr>
                    <td colspan="2">Remaining Balance</td>
                    <td id="remaining-balance"></td>
                </tr>
            </tbody>
        </table>
        <br>
        <input type="submit" onclick="handleSubmit()" id="submit-button" value="Submit" class="button is-success">
        <a class="button is-outlined" href="{{ route('index') }}">Cancel</a>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#product_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "order": [],
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [0]
            }]
        });
        $('#loading').hide();
        $('#order_container').css('visibility', 'visible');
    });
</script>
<script src="{{ url('js/item-sidebar.js') }}"></script>
@stop
