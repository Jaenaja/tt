<?php
// [FIX #5] Migration แก้ขนาด column result_3_bottom จาก varchar(60) → varchar(200)
// เดิม migration_add_3bottom.php กำหนด string('result_3_bottom', 60)
// แต่ validation ใน LotteryDrawController อนุญาต max:200
// เลข 3 ตัวล่าง 20 ตัว = "000,111,222,...,888,999" = ~80 ตัวอักษร → เกิน 60 ได้ง่าย

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lottery_draws', function (Blueprint $table) {
            $table->string('result_3_bottom', 200)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lottery_draws', function (Blueprint $table) {
            $table->string('result_3_bottom', 60)->nullable()->change();
        });
    }
};
