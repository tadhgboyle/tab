<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Helpers\CategoryHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Products\ProductEditService;
use App\Services\Products\ProductDeleteService;
use App\Services\Products\ProductCreateService;
use App\Http\Requests\ProductStockAdjustmentRequest;
use App\Services\Products\ProductStockAdjustmentService;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.products.list', [
            'products' => Product::all(),
        ]);
    }

    public function create()
    {
        return view('pages.products.form', [
            'categories' => resolve(CategoryHelper::class)->getProductCategories(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        return (new ProductCreateService($request))->redirect();
    }

    public function edit(Product $product)
    {
        return view('pages.products.form', [
            'product' => $product,
            'categories' => resolve(CategoryHelper::class)->getProductCategories(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        return (new ProductEditService($request, $product))->redirect();
    }

    public function delete(Product $product): RedirectResponse
    {
        return (new ProductDeleteService($product))->redirect();
    }

    public function adjustList()
    {
        return view('pages.products.adjust.list', [
            'products' => Product::all(),
        ]);
    }

    public function adjustStock(ProductStockAdjustmentRequest $request, Product $product): RedirectResponse
    {
        return (new ProductStockAdjustmentService($request, $product))->redirect();
    }

    public function ajaxGetInfo(Product $product): JsonResponse
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => (int) $product->price->getAmount() / 100,
            'pst' => $product->pst,
            'gst' => true, // taxes suck
        ]);
    }

    public function ajaxGetPage(Product $product)
    {
        // TODO: Load same product back when adjust page is reloaded
        return view('pages.products.adjust.form', [
            'product' => $product,
        ]);
    }
}
