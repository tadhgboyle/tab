<div align="center">
    <h4 class="title has-text-weight-bold is-4">Ledger</h4>
    <p><strong>Product:</strong> {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)<a href="{{ route('products_edit', $product->id) }}">(Edit)</a>@endpermission</p>
    @isset($productVariant)
        <p><strong>Variant:</strong> {{ $productVariant->sku }} @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)<a href="{{ route('products_variants_edit', [$product, $productVariant]) }}">(Edit)</a>@endpermission</p>
    @endisset
    <hr>
    @if($product->unlimited_stock)
        <i>No available options</i>
    @else
        <form action="{{ isset($productVariant) ? route('products_ledger_form', [$product, $productVariant]) : route('products_ledger_form', $product) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="field">
                <label class="label">Add/Subtract Stock</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="number" step="1" name="adjust_stock" class="input" value="0">
                </div>
            </div>

            @if(isset($productVariant) ? $productVariant->box_size !== null : $product->box_size !== -1)
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
                <button class="button is-light" type="submit">
                    💾 Save
                </button>
            </div>
        </form>
    @endif
</div>
