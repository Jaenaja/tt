<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_results', function (Blueprint $table) {
            $table->id();
            $table->date('draw_date');
            $table->string('three_digit', 3);
            $table->string('two_digit', 2);
            $table->timestamps();
            
            $table->unique('draw_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_results');
    }
};