<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEMO_SKUS = [
        'IFX-ARM-9H',
        'IFX-CERA-SIL',
        'IFX-BR1-500',
        'IFX-HD-POL',
    ];

    public function up(): void
    {
        $prefix = config('lunar.database.table_prefix');
        $productIds = DB::table($prefix.'product_variants')
            ->whereIn('sku', self::DEMO_SKUS)
            ->pluck('product_id')
            ->unique();

        if ($productIds->isNotEmpty()) {
            DB::table($prefix.'products')
                ->whereIn('id', $productIds)
                ->where('status', 'published')
                ->update(['status' => 'draft']);
        }
    }

    public function down(): void
    {
        // Deliberately do not republish unverified demo products on rollback.
    }
};
