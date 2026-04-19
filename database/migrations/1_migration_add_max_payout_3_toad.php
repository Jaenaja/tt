<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'         => 'max_payout_3_toad',
            'value'       => '50000',
            'type'        => 'decimal',
            'description' => 'ยอดจ่ายสูงสุดต่อเลข 3 ตัวโต๊ด (บาท)',
            'group'       => 'risk',
            'created_at'  => now(),
            'updated_at'  => now()
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'max_payout_3_toad')->delete();
    }
};
