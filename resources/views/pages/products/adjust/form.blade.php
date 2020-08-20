@php
use App\Roles;
@endphp
<div align="center">
    <h4 class="title has-text-weight-bold is-4">Adjust</h4>
    <p><strong>Product:</strong> {{ $product->name }} @if(Roles::hasPermission(Auth::user()->role, 'products_edit'))<a href="{{ route('products_edit' ,$product->id) }}">(Edit)</a>@endif</p>
    <hr>
    @if($product->unlimited_stock)
        <i>No available options</i>
    @else
    <form method="POST" action="/products/adjust">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <div class="field">
            <label class="label">Add/Subtract Stock</label>
            <div class="control has-icons-left">
                <span class="icon is-small is-left">
                    <i class="fas fa-hashtag"></i>
                </span>
                <input type="number" step="1" name="adjust_stock" class="input" value="0">
            </div>
        </div>
        @if($product->box_size != -1)
            <div class="field">
                <label class="label">Add/Subtract Box</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="number" step="1" name="adjust_box" class="input" value="0">
                </div>
            </div>
        @endif
        <div class="control">
            <button class="button is-success" type="submit">
                <span class="icon is-small">
                    <i class="fas fa-save"></i>
                </span>
                <span>Save</span>
            </button>
        </div>
    </form>
    @endif
</div>