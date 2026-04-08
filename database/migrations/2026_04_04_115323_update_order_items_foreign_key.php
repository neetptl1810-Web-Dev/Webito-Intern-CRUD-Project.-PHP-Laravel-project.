<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['order_id']);
            
            // Re-add with cascade delete
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');  // ✅ Auto-delete order_items when order is deleted
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('restrict');  // Prevent deletion if items exist
        });
    }
};