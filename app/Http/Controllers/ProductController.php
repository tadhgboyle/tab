<?php

namespace App\Http\Controllers;

use App\Services\Products\ProductCreationService;
use App\Services\Products\ProductDeleteService;
use App\Services\Products\ProductEditService;
use App\Services\Products\ProductStockAdjustmentService;
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
        return (new ProductStockAdjustmentService($request))->redirect();
    }

    public function ajaxGetPage()
    {
        return view('pages.products.adjust.form', [
            'product' => Product::find(request('id')),
        ]);
    }
}
