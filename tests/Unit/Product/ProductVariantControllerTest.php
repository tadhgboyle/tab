<?php

namespace Tests\Unit\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVariantControllerTest extends TestCase
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
            'category_id' => Category::factory()->create(),
        ]);

        $this->expectPermissionChecks([
            Permission::PRODUCTS,
            Permission::PRODUCTS_MANAGE,
        ]);
    }

    public function testCannotViewCreatePageIfNoVariantOptionsExist(): void
    {
        $this->_product->variantOptions()->delete();

        $this
            ->get(route('products_variants_create', $this->_product))
            ->assertRedirect(route('products_view', $this->_product))
            ->assertSessionHas('error', 'Product has no variant options.');
    }

    public function testCannotViewCreatePageIfAllVariantCombinationsExist(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $value = $option->values()->create([
            'value' => 'Small',
        ]);

        $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $this->_product->variants()->first()->optionValueAssignments()->create([
            'product_variant_option_id' => $option->id,
            'product_variant_option_value_id' => $value->id,
        ]);

        $this
            ->get(route('products_variants_create', $this->_product))
            ->assertRedirect(route('products_view', $this->_product))
            ->assertSessionHas('error', 'Product has all variant combinations already.');
    }

    public function testCanViewCreatePage(): void
    {
        $this->_product->variantOptions()->create([
            'name' => 'Size',
        ])->values()->create([
            'value' => 'Small',
        ]);

        $this
            ->get(route('products_variants_create', $this->_product))
            ->assertOk()
            ->assertViewIs('pages.admin.products.variants.form')
            ->assertViewHas('product', $this->_product);
    }

    public function testCanCreateProductVariant(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $value = $option->values()->create([
            'value' => 'Small',
        ]);

        $this
            ->post(route('products_variants_store', $this->_product), [
                'sku' => 'SKU-1',
                'price' => 100,
                'stock' => 10,
                'box_size' => 4,
                'option_values' => [
                    $option->id => $value->id,
                ],
            ])
            ->assertRedirect(route('products_view', $this->_product))
            ->assertSessionHas('success', 'Product variant created.');

        $variant = $this->_product->variants()->first();
        $this->assertEquals('SKU-1', $variant->sku);
        $this->assertEquals(Money::parse(1_00), $variant->price);
        $this->assertEquals(10, $variant->stock);
        $this->assertEquals(4, $variant->box_size);

        $variantOptionValueAssignment = $variant->optionValueAssignments()->first();
        $this->assertEquals($option->id, $variantOptionValueAssignment->product_variant_option_id);
    }

    public function testCannotMakeDuplicateVariantOptionValueAssignments(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $value = $option->values()->create([
            'value' => 'Small',
        ]);

        $variant = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $variant->optionValueAssignments()->create([
            'product_variant_option_id' => $option->id,
            'product_variant_option_value_id' => $value->id,
        ]);

        $this
            ->post(route('products_variants_store', $this->_product), [
                'sku' => 'SKU-2',
                'price' => 200,
                'stock' => 20,
                'option_values' => [
                    $option->id => $value->id,
                ],
            ])
            //->assertRedirect(route('products_variants_create', $this->_product))
            ->assertSessionHas('error', 'Product variant already exists for SKUs: SKU-1.')
            ->assertSessionHasInput([
                'sku' => 'SKU-2',
                'price' => 200,
                'stock' => 20,
                'option_values' => [
                    $option->id => $value->id,
                ],
            ]);
    }

    public function testCanViewEditPage(): void
    {
        $variant = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $this
            ->get(route('products_variants_edit', [$this->_product, $variant]))
            ->assertOk()
            ->assertViewIs('pages.admin.products.variants.form')
            ->assertViewHasAll([
                'product' => $this->_product,
                'productVariant' => $variant,
            ]);
    }

    public function testCanUpdateProductVariant(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $value = $option->values()->create([
            'value' => 'Small',
        ]);

        $variant = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $this
            ->put(route('products_variants_update', [$this->_product, $variant]), [
                'sku' => 'SKU-2',
                'price' => 200,
                'stock' => 20,
                'option_values' => [
                    $option->id => $value->id,
                ],
            ])
            ->assertRedirect(route('products_view', $this->_product))
            ->assertSessionHas('success', 'Product variant updated.');

        $variant->refresh();
        $this->assertEquals('SKU-2', $variant->sku);
        $this->assertEquals(Money::parse(2_00), $variant->price);
        $this->assertEquals(20, $variant->stock);

        $variantOptionValueAssignment = $variant->optionValueAssignments()->first();
        $this->assertEquals($option->id, $variantOptionValueAssignment->product_variant_option_id);
    }

    public function testCannotUpdateProductVariantToDuplicateOfExistingVariant(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $value = $option->values()->create([
            'value' => 'Small',
        ]);

        $variant1 = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $variant1->optionValueAssignments()->create([
            'product_variant_option_id' => $option->id,
            'product_variant_option_value_id' => $value->id,
        ]);

        $variant2 = $this->_product->variants()->create([
            'sku' => 'SKU-2',
            'price' => 200,
            'stock' => 20,
        ]);

        $this
            ->put(route('products_variants_update', [$this->_product, $variant2]), [
                'sku' => 'SKU-2',
                'price' => 100,
                'stock' => 10,
                'option_values' => [
                    $option->id => $value->id,
                ],
                'product_variant_id' => $variant2->id,
            ])
            //->assertRedirect(route('products_variants_create', $this->_product))
            ->assertSessionHas('error', 'Product variant already exists for SKUs: SKU-1.')
            ->assertSessionHasInput([
                'sku' => 'SKU-2',
                'price' => 100,
                'stock' => 10,
                'option_values' => [
                    $option->id => $value->id,
                ],
            ]);
    }

    public function testCanDeleteProductVariant(): void
    {
        $variant = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $this
            ->delete(route('products_variants_delete', [$this->_product, $variant]))
            ->assertRedirect(route('products_view', $this->_product))
            ->assertSessionHas('success', 'Product variant deleted.');

        $this->assertTrue($variant->refresh()->trashed());
    }
}
