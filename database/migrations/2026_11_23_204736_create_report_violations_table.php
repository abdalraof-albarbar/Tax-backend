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
        Schema::create('report_violations', function (Blueprint $table) {
            $table->foreignId('report_id')->constrained('reports', 'report_id')->cascadeOnDelete();
            $table->foreignId('violation_type_id')->constrained('violation_types', 'violation_type_id');
            $table->primary(['report_id', 'violation_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_violations');
    }
};
