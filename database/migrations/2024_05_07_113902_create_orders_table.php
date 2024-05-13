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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_amount');
            $table->decimal('additional_amount');
            $table->decimal('shipping_amount');
            $table->decimal('pay_amount');
            $table->decimal('discount_amount')->nullable();
            $table->string('pay_status');
            $table->string('shipping_status');
            $table->string('shipping_class');
            $table->string('name');
            $table->string('phone_number');
            $table->string('address');
            $table->json('gateway_response')->nullable();
            $table->string('shipping_id')->nullable();
            $table->integer('shipping_provider_id');
            $table->string('payment_id')->nullable();
            $table->integer('payment_provider_id');
            $table->string('invoice_id')->unique();
            $table->json('note')->nullable();
            $table->json('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
