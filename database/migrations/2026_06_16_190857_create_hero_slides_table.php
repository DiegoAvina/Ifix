<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->string('subtitle')->nullable();

            $table->string('image');

            $table->string('button_text')->nullable();

            $table->string('button_url')->nullable();

            $table->boolean('active')
                ->default(true);

            $table->integer('sort_order')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};