<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop existing order_id column (if it's varchar)
            $table->dropColumn('order_id');
            
            // Add correct foreign key column
            $table->foreignId('order_id')
                ->after('id')
                ->constrained('orders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            
            // Re-add old column if needed for rollback
            $table->string('order_id');
        });
    }
};