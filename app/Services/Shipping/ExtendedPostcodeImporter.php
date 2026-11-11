<?php

namespace App\Services\Shipping;

use App\Models\Shipping\ShippingExtendedPostcode;
use Illuminate\Support\Facades\DB;

/**
 * Importa la lista de C.P. de zona extendida desde el texto de un archivo
 * (CSV/TXT de la paquetería). Toma cualquier número de exactamente 5 dígitos.
 */
class ExtendedPostcodeImporter
{
    /**
     * @return array{found: int, added: int, total: int}
     */
    public function import(string $contents, bool $replace = false): array
    {
        preg_match_all('/(?<!\d)\d{5}(?!\d)/', $contents, $matches);
        $postcodes = array_values(array_unique($matches[0]));

        return DB::transaction(function () use ($postcodes, $replace) {
            if ($replace) {
                ShippingExtendedPostcode::query()->delete();
            }

            $before = ShippingExtendedPostcode::query()->count();
            $now = now();

            foreach (array_chunk($postcodes, 1000) as $chunk) {
                ShippingExtendedPostcode::query()->insertOrIgnore(
                    array_map(fn ($postcode) => ['postcode' => $postcode, 'created_at' => $now, 'updated_at' => $now], $chunk)
                );
            }

            $total = ShippingExtendedPostcode::query()->count();

            return ['found' => count($postcodes), 'added' => $total - $before, 'total' => $total];
        });
    }
}
