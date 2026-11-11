<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado de "envío gratis" aplicado desde el panel a un pedido sin pagar:
 * si está activo y los montos originales para poder quitarlo. Va aparte de
 * la columna meta del pedido porque el panel la muestra tal cual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_free_shippings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->boolean('active')->default(true);
            $table->json('state');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_free_shippings');
    }
};
