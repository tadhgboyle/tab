<?php

namespace App\Http\Controllers;

use Validator;
use App\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{

    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:products,name',
            'price' => 'required|numeric',
            'category' => 'required',
            'box_size' => 'not_in:0'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // Box size of -1 means they cannot receive stock via box. Instead must use normal stock
        $box_size = -1;
        if (!empty($request->box_size)) $box_size = $request->box_size;

        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if ($request->stock == null) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $product = new Products();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->category = $request->category;
        $product->stock = $stock;
        $product->box_size = $box_size;
        $product->unlimited_stock = $unlimited_stock;
        $product->stock_override = $request->has('stock_override');
        $product->pst = $request->has('pst');
        $product->creator_id = $request->id;
        $product->save();
        return redirect()->route('products_list')->with('success', 'Successfully created ' . $request->name . '.');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'min:2',
                Rule::unique('products')->ignore($request->product_id, 'id')
            ],
            'price' => 'required|numeric',
            'category' => 'required',
            'box_size' => 'not_in:0'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

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
            ->update(['name' => $request->name, 'price' => $request->price, 'category' => $request->category, 'stock' => $stock, 'box_size' => $request->box_size ?? -1, 'unlimited_stock' => $unlimited_stock, 'stock_override' => $stock_override, 'pst' => $pst, 'editor_id' => $request->id]);
        return redirect()->route('products_list')->with('success', 'Successfully edited ' . $request->name . '.');
    }

    public function delete($id)
    {
        Products::where('id', $id)->update(['deleted' => true]);
        return redirect()->route('products_list')->with('success', 'Successfully deleted ' . Products::find($id)->name . '.');
    }

    public function adjustStock(Request $request)
    {
        $product_id = $request->product_id;
        $product = Products::find($product_id);
        // TODO: do we need this check?
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
            } else if ($request->adjust_box == 0) {
                return redirect()->back()->with('error', 'Please specify how many boxes or stock to add to ' . $product->name . '.');
            } 
        }

        $product->addStock($adjust_stock);

        if ($request->has('adjust_box')) {
            $product->addBox($adjust_box);
        }

        return redirect()->back()->with('success', 'Successfully added ' . $adjust_stock . ' stock and ' . $adjust_box . ' boxes to ' . $product->name . '.');
    }

    public function ajaxInit()
    {
        $product = Products::find(\Request::get('id'));
        return view('pages.products.adjust.form', compact('product', $product));
    }
}
