<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->enum('bet_type', ['three_digit', 'two_digit']);
            $table->string('number');
            $table->decimal('amount', 10, 2);
            $table->date('bet_date');
            $table->enum('status', ['pending', 'won', 'lost'])->default('pending');
            $table->decimal('payout', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['bet_date', 'bet_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};