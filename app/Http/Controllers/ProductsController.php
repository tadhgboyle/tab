<?php

namespace App\Http\Controllers;

use Validator;
use App\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{

    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:products,name',
            'price' => 'required|numeric',
            'category' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $pst = 0;
        if ($request->has('pst')) $pst = 1;

        $stock = 0;
        if ($request->stock != null || $request->stock != "") $stock = $request->stock;
        // Box size of -1 means they cannot receive stock via box. Instead must use normal stock
        $box_size = -1;
        if ($request->box_size != null || $request->box_size != "") $box_size = $request->box_size;

        $unlimited_stock = false;
        if ($request->has('unlimited_stock')) $unlimited_stock = true;

        $stock_override = false;
        if ($request->has('stock_override')) $stock_override = true;

        $product = new Products();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->category = $request->category;
        $product->stock = $stock;
        $product->box_size = $box_size;
        $product->unlimited_stock = $unlimited_stock;
        $product->stock_override = $stock_override;
        $product->pst = $pst;
        $product->creator_id = $request->id;
        $product->save();
        return redirect('/products')->with('success', 'Successfully created ' . $request->name . '.');
    }

    public function edit(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'category' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        $pst = 0;
        if ($request->has('pst')) $pst = 1;

        DB::table('products')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'price' => $request->price, 'category' => $request->category, 'stock' => $request->stock, 'box_size' => $request->box_size, 'pst' => $pst, 'editor_id' => $request->editor_id]);
        return redirect('/products')->with('success', 'Successfully edited ' . $request->name . '.');
    }

    public function delete($id)
    {
        $name = Products::find($id)->name;
        Products::where('id', $id)->delete();
        return redirect('/products')->with('success', 'Successfully deleted ' . $name . '.');
    }
}
