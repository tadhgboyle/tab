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
            'name' => 'required|min:3',
            'price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        $product = new Products();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->creator_id = $request->id;
        $product->save();
        return redirect('/products');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        DB::table('products')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'price' => $request->price, 'editor_id' => $request->id]);
        return redirect('/products');
    }

    public function delete($id)
    {
        Products::where('id', $id)->delete();
        return redirect('/products');
    }
}
