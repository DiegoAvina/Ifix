<?php

namespace Tests\Feature;

use App\Data\BrandData;
use App\Data\InventoryData;
use App\Data\ProductData;
use App\Data\PriceData;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    public function test_product_page_renders_lunar_product_details_and_purchase_controls(): void
    {
        View::share('cspNonce', 'test-nonce');

        $product = new ProductData(
            id: 1,
            name: 'Producto de prueba IFIX',
            slug: '1',
            brand: new BrandData(id: 1, name: 'IFIX'),
            price: new PriceData(raw: 643, formatted: '$643.00', currency: 'MXN'),
            inventory: new InventoryData(
                sku: 'IFX-001',
                stock: 5,
                available: true,
                backorder: false,
                purchasable: 'in_stock',
                minQuantity: 1,
                quantityIncrement: 1,
                maxQuantity: 5,
            ),
            variantId: 42,
            image: null,
            gallery: [],
            badges: [],
            description: 'Descripción del producto de prueba.',
            specifications: [],
            relatedProducts: [],
            url: route('catalog.index'),
            addToCartUrl: route('cart.items.store'),
        );

        $html = view('products.show', ['product' => $product])->render();

        $this->assertStringContainsString('Producto de prueba IFIX', $html);
        $this->assertStringContainsString('$643.00', $html);
        $this->assertStringContainsString('Envíos a todo México', $html);
        $this->assertStringContainsString('Agregar al carrito', $html);
        $this->assertStringContainsString('/checkout', $html);
    }
}
