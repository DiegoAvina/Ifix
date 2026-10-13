<?php

namespace App\Services\Storefront;

use App\Data\InventoryData;
use App\Support\VariantQuantityRules;

class ProductInventoryService
{
    public function build($variant): InventoryData
    {
        return new InventoryData(

            sku: $variant?->sku,

            stock: $variant?->stock ?? 0,

            available: VariantQuantityRules::isAvailable($variant),

            backorder: VariantQuantityRules::allowsBackorder($variant),

            purchasable: $variant?->purchasable ?? 'in_stock',

            minQuantity: VariantQuantityRules::minQuantity($variant),

            quantityIncrement: VariantQuantityRules::quantityIncrement($variant),

            maxQuantity: VariantQuantityRules::maxQuantity($variant),

        );
    }
}
