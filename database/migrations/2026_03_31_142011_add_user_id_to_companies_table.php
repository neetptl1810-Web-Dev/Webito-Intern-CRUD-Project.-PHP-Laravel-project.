<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Only add if column doesn't exist
            if (!Schema::hasColumn('companies', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('cascade')
                      ->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
