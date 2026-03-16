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
        Schema::table('lottery_draws', function (Blueprint $table) {
            // เพิ่มคอลัมน์ close_time สำหรับกำหนดเวลาปิดรับแทง
            $table->timestamp('close_time')->nullable()->after('draw_date');
        });

        // อัพเดทข้อมูลเดิมให้มี close_time
        // งวดวันที่ 1 ปิดรับ 23:59 วันที่ 30 เดือนก่อนหน้า
        // งวดวันที่ 16 ปิดรับ 23:59 วันที่ 15
        DB::statement("
            UPDATE lottery_draws 
            SET close_time = CASE 
                WHEN DAY(draw_date) = 1 THEN 
                    DATE_SUB(draw_date, INTERVAL 1 DAY) + INTERVAL '23:59:59' HOUR_SECOND
                WHEN DAY(draw_date) = 16 THEN 
                    DATE_SUB(draw_date, INTERVAL 1 DAY) + INTERVAL '23:59:59' HOUR_SECOND
                ELSE 
                    draw_date + INTERVAL '23:59:59' HOUR_SECOND
            END
            WHERE close_time IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lottery_draws', function (Blueprint $table) {
            $table->dropColumn('close_time');
        });
    }
};
