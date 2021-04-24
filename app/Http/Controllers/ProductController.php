<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\CategoryHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    public function new(ProductRequest $request)
    {
        // Box size of -1 means they cannot receive stock via box. Instead must use normal stock
        $box_size = -1;
        if (!empty($request->box_size)) {
            $box_size = $request->box_size;
        }

        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if ($request->stock == null) {
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
        $product->creator_id = auth()->id();
        $product->save();
        return redirect()->route('products_list')->with('success', 'Successfully created ' . $request->name . '.');
    }

    public function edit(ProductRequest $request)
    {
        $pst = $request->has('pst');

        $unlimited_stock = $request->has('unlimited_stock');

        if ($request->stock == null) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $stock_override = $request->has('stock_override');

        DB::table('products')
            ->where('id', $request->product_id)
            ->update(['name' => $request->name, 'price' => $request->price, 'category_id' => $request->category_id, 'stock' => $stock, 'box_size' => $request->box_size ?? -1, 'unlimited_stock' => $unlimited_stock, 'stock_override' => $stock_override, 'pst' => $pst, 'editor_id' => auth()->id()]);
        return redirect()->route('products_list')->with('success', 'Successfully edited ' . $request->name . '.');
    }

    public function delete($id)
    {
        $product = Product::find($id);
        $product->update(['deleted' => true]);
        return redirect()->route('products_list')->with('success', 'Successfully deleted ' . $product->name . '.');
    }

    public function list()
    {
        return view('pages.products.list', [
            'products' => Product::where('deleted', false)->get(),
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
            'products' => Product::where('deleted', false)->get(),
        ]);
    }

    public function adjustStock(Request $request)
    {
        $product_id = $request->product_id;
        $product = Product::find($product_id);

        if ($product == null) {
            return redirect()->route('products_adjust')->with('error', 'Invalid Product.');
        }

        session()->flash('last_product', $product);

        $validator = Validator::make($request->all(), [
            'adjust_stock' => 'numeric',
            'adjust_box' => 'numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $adjust_stock = $request->adjust_stock;
        $adjust_box = $request->adjust_box ?? 0;

        if ($adjust_stock == 0) {
            if (!$request->has('adjust_box')) {
                return redirect()->back()->with('error', 'Please specify how much stock to add to ' . $product->name . '.');
            } else {
                if ($request->adjust_box == 0) {
                    return redirect()->back()->with('error', 'Please specify how many boxes or stock to add to ' . $product->name . '.');
                }
            }
        }

        $product->adjustStock($adjust_stock);

        if ($request->has('adjust_box')) {
            $product->addBox($adjust_box);
        }

        return redirect()->back()->with('success', 'Successfully added ' . $adjust_stock . ' stock and ' . $adjust_box . ' boxes to ' . $product->name . '.');
    }

    public function ajaxInit()
    {
        $product = Product::find(\Request::get('id'));
        return view('pages.products.adjust.form', compact('product', $product));
    }
}
