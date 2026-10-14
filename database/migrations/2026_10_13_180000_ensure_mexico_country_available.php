<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table(config('lunar.database.table_prefix').'countries')->insertOrIgnore([[
                'name' => 'México',
                'iso3' => 'MEX',
                'iso2' => 'MX',
                'phonecode' => '52',
                'capital' => 'Ciudad de México',
                'currency' => 'MXN',
                'native' => 'México',
                'emoji' => '🇲🇽',
                'emoji_u' => 'U+1F1F2 U+1F1FD',
                'updated_at' => now(),
                'created_at' => now(),
            ]],
        );
    }

    public function down(): void
    {
    }
};
