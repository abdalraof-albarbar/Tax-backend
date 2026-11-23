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
        Schema::create('tax_declarations', function (Blueprint $table) {
            $table->id('declaration_id');
            $table->date('declaration_date');
            $table->string('file_url');
            $table->string('tax_number');
            $table->foreign('tax_number')->references('tax_number')->on('businesses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_declarations');
    }
};
