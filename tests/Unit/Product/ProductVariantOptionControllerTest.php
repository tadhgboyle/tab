<?php

namespace Tests\Unit\Product;

use App\Helpers\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantOptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private Product $_product;

    public function setUp(): void
    {
        parent::setUp();

        $superuser = User::factory()->create([
            'role_id' => Role::factory()->create([
                'superuser' => true,
            ])
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

    public function testCanViewCreatePage(): void
    {
        $this->get(route('products_variant-options_create', $this->_product))
            ->assertOk()
            ->assertViewIs('pages.products.variant-options.form')
            ->assertViewHas('product', $this->_product);
    }

    public function testCannotUpdateWithoutName(): void
    {
        $this
            ->post(route('products_variant-options_store', $this->_product), [])
            //->assertRedirect(route('products_variant-options_create', $this->_product))
            ->assertSessionHasErrors('name', 'The name field is required.');
    }

    public function testCannotCreateWithNotStringName(): void
    {
        $this
            ->post(route('products_variant-options_store', $this->_product), [
                'name' => 123,
            ])
            //->assertRedirect(route('products_variant-options_create', $this->_product))
            ->assertSessionHasErrors('name', 'The name must be a string.');
    }

    public function testCannotCreateWithExistingName(): void
    {
        $this->_product->variantOptions()->create([
            'name' => 'Test',
        ]);

        $this
            ->post(route('products_variant-options_store', $this->_product), [
                'name' => 'Test',
            ])
            //->assertRedirect(route('products_variant-options_create', $this->_product))
            //->assertViewIs('pages.products.variant-options.form')
            ->assertSessionHasInput('name')
            ->assertSessionHasErrors('name', 'The name has already been taken.');
    }

    public function testCanCreateWithTrashedName(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Test',
        ]);

        $option->delete();

        $this
            ->post(route('products_variant-options_store', $this->_product), [
                'name' => 'Test',
            ])
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $this->_product->variantOptions()->first()]))
            ->assertSessionHas('success', 'Product variant option created, you can now add values.');
    }

    public function testCanCreateWithUniqueName(): void
    {
        $this
            ->post(route('products_variant-options_store', $this->_product), [
                'name' => 'Test',
            ])
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $this->_product->variantOptions()->first()]))
            ->assertSessionHas('success', 'Product variant option created, you can now add values.');
    }

    public function testCanViewEditPage(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Test',
        ]);

        $this->get(route('products_variant-options_edit', [$this->_product, $option]))
            ->assertOk()
            ->assertViewIs('pages.products.variant-options.form')
            ->assertViewHas('product', $this->_product)
            ->assertViewHas('productVariantOption', $option);
    }

    public function testCanDelete(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Test',
        ]);

        $this
            ->delete(route('products_variant-options_delete', [$this->_product, $option]))
            ->assertRedirect(route('products_view', $this->_product))
            ->assertSessionHas('success', 'Product variant option deleted.');
    }

    public function testCannotDeleteIfWouldResultInNonUniqueVariants(): void
    {
        $option = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $values = $option->values()->createMany([
            ['value' => 'S'],
            ['value' => 'M'],
        ]);

        $variant_one = $this->_product->variants()->create([
            'sku' => '123',
            'price' => 1_00,
            'stock' => 0,
        ]);

        $variant_one->optionValueAssignments()->create([
            'product_variant_option_id' => $option->id,
            'product_variant_option_value_id' => $values->first()->id,
        ]);

        $variant_two = $this->_product->variants()->create([
            'sku' => '456',
            'price' => 2_00,
            'stock' => 0,
        ]);

        $variant_two->optionValueAssignments()->create([
            'product_variant_option_id' => $option->id,
            'product_variant_option_value_id' => $values->last()->id,
        ]);

        $this
            ->delete(route('products_variant-options_delete', [$this->_product, $option]))
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $option]))
            ->assertSessionHas('error', 'Product variant option cannot be deleted because it would result in non-unique variants.');
    }
}