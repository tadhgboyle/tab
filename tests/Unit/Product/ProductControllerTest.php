<?php

namespace Tests\Unit\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    private Product $_product;

    public function setUp(): void
    {
        parent::setUp();

        $superadmin_role = Role::factory()->create([
            'name' => 'Superadmin',
            'order' => 1,
            'superuser' => true,
        ]);

        $superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => $superadmin_role->id,
        ]);

        $this->actingAs($superuser);

        $this->_product = Product::factory()->create([
            'pst' => true,
            'category_id' => Category::factory()->create(),
        ]);
    }

    public function testCanViewIndexPage(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_LIST,
        ]);

        $this
            ->get(route('products_list'))
            ->assertOk()
            ->assertViewIs('pages.products.list')
            ->assertViewHas('products');
    }

    public function testCanViewShowPage(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_VIEW,
        ]);

        $this
            ->get(route('products_view', $this->_product))
            ->assertOk()
            ->assertViewIs('pages.products.view')
            ->assertViewHas('product');
    }

    public function testCanViewCreatePage(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_MANAGE,
        ]);

        $this
            ->get(route('products_create'))
            ->assertOk()
            ->assertViewIs('pages.products.form')
            ->assertViewHas('categories');
    }

    public function testCanViewEditPage(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_MANAGE,
        ]);

        $this
            ->get(route('products_edit', $this->_product))
            ->assertOk()
            ->assertViewIs('pages.products.form')
            ->assertViewHas('product')
            ->assertViewHas('categories');
    }

    public function testCanViewAdjustListPage(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_LEDGER,
        ]);

        $this
            ->get(route('products_ledger'))
            ->assertOk()
            ->assertViewIs('pages.products.ledger.list')
            ->assertViewHas('products');
    }

    public function testAjaxGetInfoForNonVariantProduct(): void
    {
        $this->expectPermissionChecks([
            Permission::CASHIER_CREATE,
        ]);

        $this
            ->get(route('products_show', $this->_product))
            ->assertJson([
                'id' => $this->_product->id,
                'categoryId' => $this->_product->category->id,
                'name' => $this->_product->name,
                'variantDescription' => null,
                'price' => $this->_product->price->getAmount() / 100,
                'pst' => true,
                'gst' => true,
            ]);
    }

    public function testAjaxGetInfoForVariantProduct(): void
    {
        $this->expectPermissionChecks([
            Permission::CASHIER_CREATE,
        ]);

        $variant = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 25_00,
            'stock' => 10,
        ]);

        $this
            ->get(route('products_show', $this->_product) . '?variantId=' . $variant->id)
            ->assertJson([
                'id' => $this->_product->id,
                'categoryId' => $this->_product->category->id,
                'name' => $this->_product->name,
                'variantDescription' => $variant->description(),
                'price' => $variant->price->getAmount() / 100,
                'pst' => true,
                'gst' => true,
            ]);
    }

    public function testCanViewStockAdjustmentListPage(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_LEDGER,
        ]);

        $this
            ->get(route('products_ledger'))
            ->assertOk()
            ->assertViewIs('pages.products.ledger.list')
            ->assertViewHas('products');
    }

    public function testCanViewAdjustStockForm(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_LEDGER,
        ]);

        $this
            ->get(route('products_ledger_ajax', $this->_product))
            ->assertOk()
            ->assertViewIs('pages.products.ledger.form')
            ->assertViewHas('product')
            ->assertViewMissing('productVariant');
    }

    public function testCanViewAdjustStockFormForVariant(): void
    {
        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_LEDGER,
        ]);

        $this
            ->get(route('products_ledger_ajax', ['product' => $this->_product, 'variantId' => $this->_product->variants()->create([
                'sku' => 'SKU-1',
                'price' => 25_00,
                'stock' => 10,
            ])]))
            ->assertOk()
            ->assertViewIs('pages.products.ledger.form')
            ->assertViewHas('product')
            ->assertViewHas('productVariant');
    }
}
