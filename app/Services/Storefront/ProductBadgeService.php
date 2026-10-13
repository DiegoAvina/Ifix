<?php

namespace App\Services\Storefront;

use App\Data\BadgeData;

class ProductBadgeService
{
    public function build($product, $variant): array
    {
        $badges = [];

        if ($badge = $this->stockBadge($variant)) {
            $badges[] = $badge;
        }

        if ($badge = $this->newProductBadge($product)) {
            $badges[] = $badge;
        }

        return $badges;
    }

    /**
     * Badge por inventario.
     */
    protected function stockBadge($variant): ?BadgeData
    {
        $stock = $variant?->stock ?? 0;

        if ($stock <= 0) {

            return new BadgeData(

                type: 'danger',

                label: 'Agotado',

                icon: 'block',

            );
        }

        if ($stock <= 5) {

            return new BadgeData(

                type: 'warning',

                label: 'Últimas piezas',

                icon: 'warning',

            );
        }

        return new BadgeData(

            type: 'success',

            label: 'Disponible',

            icon: 'check_circle',

        );
    }

    /**
     * Badge de producto nuevo.
     */
    protected function newProductBadge($product): ?BadgeData
    {
        if ($product->created_at?->gt(now()->subDays(30))) {

            return new BadgeData(

                type: 'info',

                label: 'Nuevo',

                icon: 'new_releases',

            );
        }

        return null;
    }
}