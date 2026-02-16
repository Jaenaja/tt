<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, decimal, boolean
            $table->text('description')->nullable();
            $table->string('group')->default('general'); // general, risk, payout, etc.
            $table->timestamps();
        });

        // Insert default risk management settings
        $defaultSettings = [
            // Max Payout Limits
            [
                'key' => 'max_payout_2_digit',
                'value' => '50000',
                'type' => 'decimal',
                'description' => 'ยอดจ่ายสูงสุดต่อเลข 2 ตัว (บาท)',
                'group' => 'risk'
            ],
            [
                'key' => 'max_payout_3_digit',
                'value' => '100000',
                'type' => 'decimal',
                'description' => 'ยอดจ่ายสูงสุดต่อเลข 3 ตัว (บาท)',
                'group' => 'risk'
            ],
            
            // Payout Rates
            [
                'key' => 'rate_2_top',
                'value' => '90',
                'type' => 'decimal',
                'description' => 'อัตราจ่าย 2 ตัวบน',
                'group' => 'payout'
            ],
            [
                'key' => 'rate_2_bottom',
                'value' => '90',
                'type' => 'decimal',
                'description' => 'อัตราจ่าย 2 ตัวล่าง',
                'group' => 'payout'
            ],
            [
                'key' => 'rate_3_top',
                'value' => '900',
                'type' => 'decimal',
                'description' => 'อัตราจ่าย 3 ตัวตรง',
                'group' => 'payout'
            ],
            [
                'key' => 'rate_3_toad',
                'value' => '120',
                'type' => 'decimal',
                'description' => 'อัตราจ่าย 3 ตัวโต๊ด',
                'group' => 'payout'
            ],
            
            // Auto Transfer
            [
                'key' => 'auto_transfer_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'เปิด/ปิด ระบบตัดยอดอัตโนมัติ',
                'group' => 'risk'
            ],
            [
                'key' => 'auto_transfer_threshold',
                'value' => '100',
                'type' => 'integer',
                'description' => 'เปอร์เซ็นต์ที่เริ่มตัดยอด (100 = 100% ของ Max Payout)',
                'group' => 'risk'
            ],
            
            // Commission
            [
                'key' => 'commission_rate',
                'value' => '10',
                'type' => 'decimal',
                'description' => 'อัตราค่าคอมมิชชั่น (%)',
                'group' => 'general'
            ],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
