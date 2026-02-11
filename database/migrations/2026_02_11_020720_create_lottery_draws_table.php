<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lottery_draws', function (Blueprint $table) {
            $table->id();
            $table->date('draw_date')->unique();
            $table->string('result_3_top', 3)->nullable();
            $table->string('result_2_top', 2)->nullable();
            $table->string('result_2_bottom', 2)->nullable();
            $table->boolean('is_announced')->default(false);
            $table->timestamp('announced_at')->nullable();
            $table->unsignedBigInteger('announced_by')->nullable();

            $table->foreign('announced_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('draw_date');
            $table->index('is_announced');

        });
    }

    public function down()
    {
        Schema::dropIfExists('lottery_draws');
    }
};