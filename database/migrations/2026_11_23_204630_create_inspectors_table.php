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
        Schema::create('inspectors', function (Blueprint $table) {
            $table->string('employee_no')->primary();
            $table->string('title')->nullable();
            $table->string('photo_url')->nullable();
            $table->foreignId('region_id')->constrained('regions', 'region_id');
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('supervisor_no');
            $table->foreign('supervisor_no')->references('employee_no')->on('supervisors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspectors');
    }
};
