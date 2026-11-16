<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Página "Nosotros" (misión, visión y valores). Una sola fila; se edita
        // en el panel de Lunar (Tienda en línea → Nosotros).
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('headline');
            $table->text('intro')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->json('values')->nullable();
            $table->json('stats')->nullable();
            $table->json('milestones')->nullable();
            $table->string('closing_title')->nullable();
            $table->string('closing_text', 500)->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
