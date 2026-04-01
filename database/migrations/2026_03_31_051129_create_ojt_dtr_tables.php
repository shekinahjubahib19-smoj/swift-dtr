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
        // Table for the Intern's Profile (Name, Dept, etc.)
        Schema::create('intern_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('department');
            $table->string('position');
            $table->integer('required_hours')->default(600);
            $table->enum('dtr_mode', ['split', 'continuous'])->default('split');
            $table->timestamps();
        });

        // Table for the Daily Logs
        Schema::create('dtr_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('intern_profiles')->onDelete('cascade');
            $table->date('log_date');
            $table->time('am_in')->nullable();
            $table->time('am_out')->nullable();
            $table->time('pm_in')->nullable();
            $table->time('pm_out')->nullable();
            $table->decimal('daily_total_hours', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_dtr_tables');
    }
};
