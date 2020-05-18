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
            'category' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        $pst = 0;
        if ($request->has('pst')) {
            $pst = 1;
        }
        $product = new Products();
        $product->name = $request->name;
        $product->category = $request->category;
        $product->price = $request->price;
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
            'category' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        $pst = 0;
        if ($request->has('pst')) {
            $pst = 1;
        }
        DB::table('products')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'category' => $request->category, 'price' => $request->price, 'pst' => $pst, 'editor_id' => $request->editor_id]);
        return redirect('/products')->with('success', 'Successfully edited ' . $request->name . '.');
    }

    public function delete($id)
    {
        Products::where('id', $id)->delete();
        return redirect('/products');
    }
}
