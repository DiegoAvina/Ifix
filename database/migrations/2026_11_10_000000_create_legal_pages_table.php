<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Textos legales editables desde el panel de Lunar (aviso de privacidad,
        // y más adelante términos y condiciones).
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        // Constancia de cuándo aceptó el cliente el aviso de privacidad al registrarse.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('privacy_accepted_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('privacy_accepted_at');
        });

        Schema::dropIfExists('legal_pages');
    }
};
