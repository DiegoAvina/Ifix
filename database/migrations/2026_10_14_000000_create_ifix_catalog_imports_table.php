<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ifix_catalog_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 40)->default('mercadolibre_mx');
            $table->string('source_id', 80);
            $table->text('source_url');
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->text('category_path')->nullable();
            $table->string('presentation')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('observed_price_minor')->nullable();
            $table->unsignedBigInteger('observed_compare_price_minor')->nullable();
            $table->timestamp('price_observed_at')->nullable();
            $table->text('image_url')->nullable();
            $table->boolean('image_rights_verified')->default(false);
            $table->unsignedBigInteger('store_price_minor')->nullable();
            $table->unsignedBigInteger('store_compare_price_minor')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('data_verified')->default(false);
            $table->boolean('stock_verified')->default(false);
            $table->unsignedBigInteger('lunar_product_id')->nullable()->index();
            $table->string('import_status', 32)->default('pending_review');
            $table->text('last_import_message')->nullable();
            $table->timestamps();

            $table->unique(['source', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ifix_catalog_imports');
    }
};
