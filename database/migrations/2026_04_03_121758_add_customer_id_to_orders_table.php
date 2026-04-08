<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add customer_id column (nullable, since admin orders use user_id)
            $table->foreignId('customer_id')
                ->nullable()
                ->after('user_id')
                ->constrained('customers')
                ->onDelete('cascade');
            
            // Add index for faster queries
            $table->index(['customer_id', 'delivery_status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};