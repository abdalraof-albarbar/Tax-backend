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
            $table->string('name');
            $table->string('email')->unique();
          $table->enum('role', ['admin', 'supervisor', 'inspector']);
            $table->string('password');
            
    
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
