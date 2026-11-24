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
        Schema::create('employees', function (Blueprint $table) {
            $table->string('employee_no')->primary();
             $table->string('supervisor_no')->nullable();
             $table->string('title');
             $table->string('photo_url')->nullable();
             
            $table->timestamps();
         
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->foreignId('region_id')->constrained('regions', 'region_id');
          
  

        });
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('supervisor_no')->references('employee_no')->on('employees')->onDelete('set null');
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
