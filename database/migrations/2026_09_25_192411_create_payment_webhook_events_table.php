<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();

            // 'mercadopago', y en el futuro cualquier otro proveedor.
            $table->string('provider');

            // El id del recurso de pago del proveedor (data.id en MP).
            $table->string('payment_id');

            // El status del proveedor en el momento de este evento
            // (ej. 'approved', 'rejected', 'pending'). Forma parte de
            // la clave de idempotencia: el MISMO payment_id con el
            // MISMO status es un webhook duplicado; el mismo
            // payment_id con un status DISTINTO es un evento nuevo
            // legítimo (ej. pending -> approved) que sí debe procesarse.
            $table->string('status');

            // Referencia a nuestra Order, cuando se pudo resolver.
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('lunar_orders')
                ->nullOnDelete();

            // Payload crudo recibido, para auditoría/depuración.
            $table->json('payload')->nullable();

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'payment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
    }
};
