<?php

namespace App\Http\Controllers;

use App\Services\Products\ProductCreationService;
use App\Services\Products\ProductDeleteService;
use App\Services\Products\ProductEditService;
use Validator;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\CategoryHelper;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    public function new(ProductRequest $request)
    {
        return (new ProductCreationService($request))->redirect();
    }

    public function edit(ProductRequest $request)
    {
        return (new ProductEditService($request))->redirect();
    }

    public function delete(Product $product)
    {
        return (new ProductDeleteService($product->id))->redirect();
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

        $validator = Validator::make($request->all(), [
            'adjust_stock' => 'numeric',
            'adjust_box' => 'numeric',
        ]);

        if ($validator->fails()) {
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
