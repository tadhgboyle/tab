<?php

namespace Tests\Unit\Admin\Product;

use App\Models\Product;
use App\Models\Category;
use Tests\FormRequestTestCase;
use App\Http\Requests\ProductVariantRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVariantRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    private Category $_category;

    public function setUp(): void
    {
        parent::setUp();

        $this->_category = Category::factory()->create();
    }

    public function testSkuIsRequiredAndIsStringAndIsUnique(): void
    {
        $this->assertHasErrors('sku', new ProductVariantRequest([
            'sku' => null,
        ]));

        $this->assertHasErrors('sku', new ProductVariantRequest([
            'sku' => [],
        ]));

        $this->assertNotHaveErrors('sku', new ProductVariantRequest([
            'sku' => 'SKU123',
        ]));

        $product = Product::factory()->create([
            'category_id' => $this->_category->id,
        ]);
        $variant = $product->variants()->create([
            'sku' => 'SKU123',
            'price' => 100,
            'stock' => 10,
        ]);

        $this->assertHasErrors('sku', new ProductVariantRequest([
            'sku' => 'SKU123',
            'product_id' => Product::factory()->create([
                'category_id' => $this->_category->id,
            ])->id,
        ]));

        $this->assertNotHaveErrors('sku', new ProductVariantRequest([
            'sku' => 'SKU123',
            'product_variant_id' => $variant->id,
        ]));
    }

    public function testPriceIsRequiredAndIsNumeric(): void
    {
        $this->assertHasErrors('price', new ProductVariantRequest([
            'price' => null,
        ]));

        $this->assertHasErrors('price', new ProductVariantRequest([
            'price' => 'price',
        ]));

        $this->assertNotHaveErrors('price', new ProductVariantRequest([
            'price' => 100,
        ]));
    }

    public function testStockIsRequiredAndIsIntegerAndMinZero(): void
    {
        $this->assertHasErrors('stock', new ProductVariantRequest([
            'stock' => null,
        ]));

        $this->assertHasErrors('stock', new ProductVariantRequest([
            'stock' => 'stock',
        ]));

        $this->assertHasErrors('stock', new ProductVariantRequest([
            'stock' => -1,
        ]));

        $this->assertNotHaveErrors('stock', new ProductVariantRequest([
            'stock' => 0,
        ]));

        $this->assertNotHaveErrors('stock', new ProductVariantRequest([
            'stock' => 1,
        ]));
    }

    public function testOptionValuesIsRequiredAndIsArrayAndExistsInProductVariantOptionValues(): void
    {
        $this->assertHasErrors('option_values', new ProductVariantRequest([
            'option_values' => null,
        ]));

        $this->assertHasErrors('option_values', new ProductVariantRequest([
            'option_values' => 'option_values',
        ]));

        $this->assertHasErrors('option_values', new ProductVariantRequest([
            'option_values' => [],
        ]));

        $product = Product::factory()->create([
            'category_id' => $this->_category->id,
        ]);

        $product->variants()->create([
            'sku' => 'SKU123',
            'price' => 100,
            'stock' => 10,
        ]);

        $option = $product->variantOptions()->create([
            'name' => 'Option 1',
        ]);

        $optionValue = $option->values()->create([
            'value' => 'Value 1',
        ]);

        // TODO: fix
        // $this->assertHasErrors('option_values', new ProductVariantRequest([
        //     'option_values' => [
        //         $optionValue->id + 1,
        //     ],
        //     'product_id' => $product->id,
        // ]));

        $this->assertNotHaveErrors('option_values', new ProductVariantRequest([
            'option_values' => [$optionValue->id],
            'product_id' => $product->id,
        ]));
    }
}
