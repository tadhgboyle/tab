<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Helpers\CategoryHelper;
use App\Http\Requests\ProductRequest;
use App\Services\Products\ProductEditService;
use App\Services\Products\ProductDeleteService;
use App\Services\Products\ProductCreationService;
use App\Http\Requests\ProductStockAdjustmentRequest;
use App\Services\Products\ProductStockAdjustmentService;

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

    public function adjustStock(ProductStockAdjustmentRequest $request)
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
