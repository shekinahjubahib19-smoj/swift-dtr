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
        Schema::table('users', function (Blueprint $table) {
            // Drop the unique index on `phone` so duplicate numbers are allowed.
            // Index name is typically `users_phone_unique`.
            if (Schema::hasColumn('users', 'phone')) {
                try {
                    $table->dropUnique(['phone']);
                } catch (\Throwable $e) {
                    // Some drivers (SQLite) may throw if index doesn't exist; ignore.
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                try {
                    $table->unique('phone');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });
    }
};
