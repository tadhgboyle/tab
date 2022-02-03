<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\CategoryHelper;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    public function new(ProductRequest $request)
    {
        // Box size of -1 means they cannot receive stock via box. Instead must use normal stock
        $box_size = $request->box_size ?: -1;

        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if (!$request->has('stock')) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->stock = $stock;
        $product->box_size = $box_size;
        $product->unlimited_stock = $unlimited_stock;
        $product->stock_override = $request->has('stock_override');
        $product->pst = $request->has('pst');
        $product->save();

        return redirect()->route('products_list')->with('success', 'Successfully created ' . $request->name . '.');
    }

    public function edit(ProductRequest $request)
    {
        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if (!$request->has('stock')) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        Product::where('id', $request->product_id)->update([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'stock' => $stock,
            'box_size' => $request->box_size ?? -1,
            'unlimited_stock' => $unlimited_stock,
            'stock_override' => $request->has('stock_override'),
            'pst' => $request->has('pst'),
        ]);

        return redirect()->route('products_list')->with('success', 'Successfully edited ' . $request->name . '.');
    }

    public function delete(Product $product)
    {
        $product->delete();

        return redirect()->route('products_list')->with('success', 'Successfully deleted ' . $product->name . '.');
    }

    public function list()
    {
        return view('pages.products.list', [
            'products' => Product::all(),
        ]);
    }

    public function form()
    {
        return view('pages.products.form', [
            'product' => Product::find(request()->route('id')),
            'categories' => CategoryHelper::getInstance()->getProductCategories(),
        ]);
    }

    public function adjustList()
    {
        return view('pages.products.adjust.list', [
            'products' => Product::all(),
        ]);
    }

    public function adjustStock(Request $request)
    {
        $product_id = $request->product_id;
        $product = Product::find($product_id);

        if ($product === null) {
            return redirect()->route('products_adjust')->with('error', 'Invalid Product.');
        }

        session()->flash('last_product', $product);

        if ($validator = Validator::make($request->all(), [
            'adjust_stock' => 'numeric',
            'adjust_box' => 'numeric',
        ])->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $adjust_stock = (int) $request->adjust_stock;
        $adjust_box = (int) ($request->adjust_box ?? 0);

        if ($adjust_stock === 0) {
            if (!$request->has('adjust_box')) {
                return redirect()->back()->with('error', 'Please specify how much stock to add to ' . $product->name . '.');
            }

            if ($request->adjust_box === 0) {
                return redirect()->back()->with('error', 'Please specify how many boxes or stock to add to ' . $product->name . '.');
            }
        }

        $product->adjustStock($adjust_stock);

        if ($request->has('adjust_box')) {
            $product->addBox($adjust_box);
        }

        return redirect()->back()->with('success', 'Successfully added ' . $adjust_stock . ' stock and ' . $adjust_box . ' boxes to ' . $product->name . '.');
    }

    public function ajaxGetPage()
    {
        return view('pages.products.adjust.form', [
            'product' => Product::find(request('id')),
        ]);
    }
}
