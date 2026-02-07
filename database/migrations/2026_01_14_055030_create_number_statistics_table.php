<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->enum('type', ['two_digit', 'three_digit']);
            $table->integer('frequency')->default(0);
            $table->date('last_drawn')->nullable();
            $table->timestamps();
            
            $table->unique(['number', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_statistics');
    }
};