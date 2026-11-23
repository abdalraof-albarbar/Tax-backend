<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('name')->nullable();
            $table->string('email')->unique();
          $table->enum('role', ['supervisor', 'inspector'])->nullable();
            $table->string('password')->nullable();
            
    
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
