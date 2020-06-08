<div align="center">
    <h3>Adjust</h3>
    <p>Product: {{ $product->name }} <a href="/products/edit/{{ $product->id }}">(Edit)</a></p>
    <hr>
    @if($product->unlimited_stock)
    <i>No available options</i>
    @else
    @if($product->box_size != -1)
    <span>Add/Subtract Box</span><br>
    <input type="number" step="1" name="adjust_box" value="0"><br><br>
    @endif
    <span>Add/Subtract Stock</span><br>
    <input type="number" step="1" name="adjust_stock" value="0"><br><br>
    <button class="btn btn-success">Update</button>
    @endif
</div>