<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tarifas de envío editables desde el panel (Tienda en línea):
 * zonas por código postal, ajustes generales (envío gratis, peso, zona
 * extendida) y la lista de C.P. de zona extendida.
 *
 * Los valores iniciales son de referencia (cotizaciones de noviembre de
 * 2026 desde Guadalajara, C.P. 44720); se ajustan en el panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Primeros 2 dígitos del C.P. que pertenecen a la zona (ej. "44", "45").
            $table->json('postcode_prefixes');
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('min_days');
            $table->unsignedSmallInteger('max_days');
            // Zona que se usa para cualquier C.P. que no esté en otra (y sin C.P.).
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->string('origin_postcode', 5);
            $table->decimal('free_shipping_from', 10, 2)->nullable();
            $table->boolean('free_shipping_covers_extended')->default(false);
            $table->decimal('extended_surcharge', 10, 2)->default(0);
            $table->unsignedSmallInteger('extended_extra_days')->default(0);
            $table->unsignedSmallInteger('processing_days')->default(1);
            // Peso por pieza cuando el producto no tiene peso capturado.
            $table->decimal('default_item_weight_kg', 8, 3)->default(1);
            // [{"max_kg": 3, "surcharge": 0}, ...] en orden ascendente.
            $table->json('weight_brackets');
            // Cargo por cada kg arriba del último rango.
            $table->decimal('extra_per_kg', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('shipping_extended_postcodes', function (Blueprint $table) {
            $table->id();
            $table->string('postcode', 5)->unique();
            $table->timestamps();
        });

        $now = now();

        DB::table('shipping_settings')->insert([
            'origin_postcode' => '44720',
            'free_shipping_from' => 499,
            'free_shipping_covers_extended' => false,
            'extended_surcharge' => 199,
            'extended_extra_days' => 3,
            'processing_days' => 1,
            'default_item_weight_kg' => 1,
            'weight_brackets' => json_encode([
                ['max_kg' => 3, 'surcharge' => 0],
                ['max_kg' => 5, 'surcharge' => 40],
                ['max_kg' => 10, 'surcharge' => 90],
            ]),
            'extra_per_kg' => 15,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $zones = [
            // Zona metropolitana de Guadalajara (Guadalajara, Zapopan, Tlaquepaque, Tonalá, Tlajomulco).
            ['Local (ZM Guadalajara)', ['44', '45'], 79, 1, 2, false],
            // Resto de Jalisco y estados vecinos.
            ['Regional', ['46', '47', '48', '49', '20', '28', '36', '37', '38', '58', '59', '60', '61', '63', '98', '99'], 129, 2, 3, false],
            // Estados lejanos al origen.
            ['Lejana', ['21', '22', '23', '24', '29', '30', '77', '97'], 189, 4, 7, false],
            // Todo lo demás.
            ['Nacional', [], 159, 3, 5, true],
        ];

        foreach ($zones as $position => [$name, $prefixes, $price, $min, $max, $default]) {
            DB::table('shipping_zones')->insert([
                'name' => $name,
                'postcode_prefixes' => json_encode($prefixes),
                'price' => $price,
                'min_days' => $min,
                'max_days' => $max,
                'is_default' => $default,
                'position' => $position + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_extended_postcodes');
        Schema::dropIfExists('shipping_settings');
        Schema::dropIfExists('shipping_zones');
    }
};
