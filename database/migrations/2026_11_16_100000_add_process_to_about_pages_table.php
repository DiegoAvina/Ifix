<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pasos del proceso de detallado en /nosotros; cada paso puede
        // enlazar a una categoría de la tienda.
        Schema::table('about_pages', function (Blueprint $table) {
            $table->json('process')->nullable()->after('values');
        });
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            $table->dropColumn('process');
        });
    }
};
