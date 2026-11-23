<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->geography('location', subtype: 'point', srid: 4326);
            $table->timestamp('report_time');
            $table->string('qr_code')->unique();
            $table->string('status');
            $table->foreignId('visit_id')->unique()->constrained('visits', 'visit_id');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
