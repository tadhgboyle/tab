<?php

namespace Tests\Unit\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVariantOptionValueControllerTest extends TestCase
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

    public function testCanCreateNewValue(): void
    {
        $productVariantOption = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $this
            ->post(route('products_variant-options_values_store', [$this->_product, $productVariantOption]), [
                'value' => 'Test Value',
            ])
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $productVariantOption]))
            ->assertSessionHas('success', 'Product variant option value added.');

        $this->assertEquals(1, $productVariantOption->values()->count());
        $this->assertEquals('Test Value', $productVariantOption->values()->first()->value);
    }

    public function testCannotCreateNonUniqueValue(): void
    {
        $productVariantOption = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $productVariantOption->values()->create([
            'value' => 'Test Value',
        ]);

        $this
            ->post(route('products_variant-options_values_store', [$this->_product, $productVariantOption]), [
                'value' => 'Test Value',
            ])
            ->assertSessionHasErrors('value');

        $this->assertEquals(1, $productVariantOption->values()->count());
    }

    public function testCanCreateValueWithSameNameForDifferentOption(): void
    {
        $productVariantOption1 = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $productVariantOption2 = $this->_product->variantOptions()->create([
            'name' => 'Color',
        ]);

        $productVariantOption1->values()->create([
            'value' => 'Test Value',
        ]);

        $this
            ->post(route('products_variant-options_values_store', [$this->_product, $productVariantOption2]), [
                'value' => 'Test Value',
            ])
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $productVariantOption2]))
            ->assertSessionHas('success', 'Product variant option value added.');

        $this->assertEquals(1, $productVariantOption2->values()->count());
        $this->assertEquals('Test Value', $productVariantOption2->values()->first()->value);
    }

    public function testCanUpdateValue(): void
    {
        $productVariantOption = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $productVariantOptionValue = $productVariantOption->values()->create([
            'value' => 'Test Value',
        ]);

        $this
            ->put(route('products_variant-options_values_update', [$this->_product, $productVariantOption, $productVariantOptionValue]), [
                'value' => 'Updated Value',
            ])
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $productVariantOption]))
            ->assertSessionHas('success', 'Product variant option value updated.');

        $this->assertEquals(1, $productVariantOption->values()->count());
        $this->assertEquals('Updated Value', $productVariantOption->values()->first()->value);
    }

    public function testCannotUpdateValueToNonUniqueValue(): void
    {
        $productVariantOption = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $productVariantOptionValue1 = $productVariantOption->values()->create([
            'value' => 'Test Value 1',
        ]);

        $productVariantOption->values()->create([
            'value' => 'Test Value 2',
        ]);

        $this
            ->put(route('products_variant-options_values_update', [$this->_product, $productVariantOption, $productVariantOptionValue1]), [
                'value' => 'Test Value 2',
            ])
            ->assertSessionHasErrors('value');

        $this->assertEquals(2, $productVariantOption->values()->count());
        $this->assertEquals('Test Value 1', $productVariantOption->values()->first()->value);
    }

    public function testCanDeleteValue(): void
    {
        $productVariantOption = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $productVariantOptionValue = $productVariantOption->values()->create([
            'value' => 'Test Value',
        ]);

        $this
            ->delete(route('products_variant-options_values_delete', [$this->_product, $productVariantOption, $productVariantOptionValue]))
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $productVariantOption]))
            ->assertSessionHas('success', 'Product variant option value deleted.');

        $this->assertEquals(0, $productVariantOption->values()->count());
    }

    public function testCannotDeleteValueIfItWouldResultInNonUniqueVariants(): void
    {
        $variant1 = $this->_product->variants()->create([
            'sku' => 'SKU-1',
            'price' => 100,
            'stock' => 10,
        ]);

        $variant2 = $this->_product->variants()->create([
            'sku' => 'SKU-2',
            'price' => 200,
            'stock' => 20,
        ]);

        $productVariantOption1 = $this->_product->variantOptions()->create([
            'name' => 'Size',
        ]);

        $productVariantOption2 = $this->_product->variantOptions()->create([
            'name' => 'Color',
        ]);

        $productVariantOptionValue1 = $productVariantOption1->values()->create([
            'value' => 'Test Value',
        ]);

        $productVariantOptionValue2 = $productVariantOption2->values()->create([
            'value' => 'Test Value',
        ]);

        $variant1->optionValueAssignments()->create([
            'product_variant_option_id' => $productVariantOption1->id,
            'product_variant_option_value_id' => $productVariantOptionValue1->id,
        ]);

        $variant1->optionValueAssignments()->create([
            'product_variant_option_id' => $productVariantOption2->id,
            'product_variant_option_value_id' => $productVariantOptionValue2->id,
        ]);

        $variant2->optionValueAssignments()->create([
            'product_variant_option_id' => $productVariantOption1->id,
            'product_variant_option_value_id' => $productVariantOptionValue1->id,
        ]);

        $this
            ->delete(route('products_variant-options_values_delete', [$this->_product, $productVariantOption2, $productVariantOptionValue2]))
            ->assertRedirect(route('products_variant-options_edit', [$this->_product, $productVariantOption2]))
            ->assertSessionHas('error', 'Product variant option value cannot be deleted because it would result in non-unique variants.');
    }
}