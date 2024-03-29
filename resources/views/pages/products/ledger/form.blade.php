<div align="center">
    <h4 class="title has-text-weight-bold is-4">Ledger</h4>
    <p><strong>Product:</strong> {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)<a href="{{ route('products_edit', $product->id) }}">(Edit)</a>@endpermission</p>
    <hr>
    @if($product->unlimited_stock)
        <i>No available options</i>
    @else
        <form action="{{ route('products_ledger_form', $product->id) }}" method="POST">
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

            @if($product->box_size !== -1)
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
