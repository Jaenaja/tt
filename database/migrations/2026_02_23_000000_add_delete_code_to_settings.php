<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // เพิ่ม delete_code setting
        DB::table('settings')->insert([
            'key' => 'delete_code',
            'value' => '',
            'type' => 'string',
            'description' => 'รหัสลบรายการแทงหวย (6 หลัก)',
            'group' => 'security',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'delete_code')->delete();
    }
};
