<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('category_id')->nullable();
            $table->decimal('sale_price');
            $table->decimal('production_cost');
            $table->string('description', 5000);
            $table->string('stock_status');
            $table->integer('stock')->nullable();
            $table->string('main_photo');
            $table->json('photos')->nullable();
            $table->string('video_link')->nullable();
            $table->json('meta')->nullable();
            $table->json('production_cost_breakdown')->nullable();
            $table->binary('is_shipping_charge_applicable')
                ->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
