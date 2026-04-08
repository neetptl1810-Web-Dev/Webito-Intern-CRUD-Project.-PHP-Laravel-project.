<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Make user_id nullable (for customer orders)
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // ✅ Only add index if it doesn't already exist
            $indexName = 'orders_customer_id_delivery_status_index';
            $indexes = DB::select("SHOW INDEX FROM orders WHERE Key_name = ?", [$indexName]);
            
            if (empty($indexes)) {
                $table->index(['customer_id', 'delivery_status'], $indexName);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert to NOT NULL if needed
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            // Drop index if it exists
            $table->dropIndex(['customer_id', 'delivery_status']);
        });
    }
};