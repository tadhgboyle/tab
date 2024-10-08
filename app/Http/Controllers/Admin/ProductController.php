<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Helpers\Permission;
use App\Models\ProductVariant;
use App\Helpers\CategoryHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Products\ProductEditService;
use App\Services\Products\ProductCreateService;
use App\Services\Products\ProductDeleteService;
use App\Http\Requests\ProductStockAdjustmentRequest;
use App\Services\Products\ProductStockAdjustmentService;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.admin.products.list');
    }

    public function show(Product $product)
    {
        if (!$product->isActive() && !hasPermission(Permission::PRODUCTS_VIEW_DRAFT)) {
            return redirect()->route('products_list')->with('error', 'You cannot view draft products.');
        }

        return view('pages.admin.products.view', [
            'product' => $product,
        ]);
    }

    public function create()
    {
        return view('pages.admin.products.form', [
            'categories' => resolve(CategoryHelper::class)->getProductCategories(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        return (new ProductCreateService($request))->redirect();
    }

    public function edit(Product $product)
    {
        return view('pages.admin.products.form', [
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
        return view('pages.admin.products.ledger.list', [
            'products' => Product::with('category', 'variants', 'variants.product')->get(),
        ]);
    }

    public function adjustStock(ProductStockAdjustmentRequest $request, Product $product, ?ProductVariant $productVariant): RedirectResponse
    {
        return (new ProductStockAdjustmentService($request, $product, $productVariant))->redirect();
    }

    public function ajaxGetInfo(Product $product): JsonResponse
    {
        if (request()->query('variantId')) {
            $variant = $product->variants()->find(request()->query('variantId'));
            $variantDescription = $variant->description();
            $price = $variant->price;
        } else {
            $price = $product->price;
        }

        return response()->json([
            'id' => $product->id,
            'categoryId' => $product->category_id,
            'name' => $product->name,
            'variantDescription' => $variantDescription ?? null,
            'price' => (int) $price->getAmount() / 100,
            'pst' => $product->pst,
            'gst' => true, // taxes suck
        ]);
    }

    public function ajaxGetPage(Product $product)
    {
        $data = [
            'product' => $product,
        ];

        if (request()->query('variantId')) {
            $data['productVariant'] = $product->variants()->find(request()->query('variantId'));
        }

        // TODO: Load same product back when adjust page is reloaded
        return view('pages.admin.products.ledger.form', $data);
    }
}
