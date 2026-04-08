<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique(); // Custom order ID like ORD-001
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->default('cod'); // cod, online
            $table->string('payment_status')->default('pending'); // pending, paid, failed
            $table->string('delivery_status')->default('pending'); // pending, processing, shipped, delivered, cancelled, no_response
            $table->text('shipping_address');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};