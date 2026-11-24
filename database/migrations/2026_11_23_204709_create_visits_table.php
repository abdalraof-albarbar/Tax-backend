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
        Schema::create('visits', function (Blueprint $table) {
            $table->id('visit_id');
            $table->date('visit_date');
            $table->string('status');
            $table->string('risk_level')->nullable();
            $table->string('tax_number');
            $table->foreign('tax_number')->references('tax_number')->on('businesses');
            $table->string('employee_no');
            $table->foreign('employee_no')->references('employee_no')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
