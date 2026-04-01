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
        Schema::create('dtr_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('full_name');
            $table->integer('total_hours')->default(720); // Tracks your 720-hour internship
            $table->string('company')->default('M Lhuillier'); // Sets M Lhuillier as default
            $table->string('department')->nullable();
            $table->string('position')->nullable(); // Added this to store "IT Intern"
            $table->time('am_in')->nullable();
            $table->time('am_out')->nullable();
            $table->time('pm_in')->nullable();
            $table->time('pm_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtr_settings');
    }
};
