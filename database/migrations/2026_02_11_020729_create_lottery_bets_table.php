<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lottery_bets', function (Blueprint $table) {
            $table->id();
            $table->date('draw_date');
            $table->string('customer_name');
            $table->string('number');
            $table->decimal('amount_top', 10, 2)->default(0);
            $table->decimal('amount_bottom', 10, 2)->default(0);
            $table->decimal('amount_toad', 10, 2)->default(0);
            $table->decimal('payout_top', 10, 2)->default(0);
            $table->decimal('payout_bottom', 10, 2)->default(0);
            $table->decimal('payout_toad', 10, 2)->default(0);
            $table->boolean('is_win_top')->default(false);
            $table->boolean('is_win_bottom')->default(false);
            $table->boolean('is_win_toad')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['draw_date', 'customer_name']);
            $table->index('created_by');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lottery_bets');
    }
};