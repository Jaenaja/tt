<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RiskSettingsController extends Controller
{
    /**
     * แสดงหน้าตั้งค่าความเสี่ยง
     */
    public function index()
    {
        // ดึงค่า settings ทั้งหมด
        $settings = [
            // Risk Management
            'max_payout_2_digit' => Setting::get('max_payout_2_digit', 50000),
            'max_payout_3_digit' => Setting::get('max_payout_3_digit', 100000),
            'auto_transfer_enabled' => Setting::get('auto_transfer_enabled', false),
            'auto_transfer_threshold' => Setting::get('auto_transfer_threshold', 100),
            
            // Payout Rates
            'rate_2_top' => Setting::get('rate_2_top', 90),
            'rate_2_bottom' => Setting::get('rate_2_bottom', 90),
            'rate_3_top' => Setting::get('rate_3_top', 900),
            'rate_3_toad' => Setting::get('rate_3_toad', 120),
            
            // Commission
            'commission_rate' => Setting::get('commission_rate', 10),
        ];

        return view('admin.risk-settings', compact('settings'));
    }

    /**
     * บันทึกการตั้งค่า
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'max_payout_2_digit' => 'required|numeric|min:0',
            'max_payout_3_digit' => 'required|numeric|min:0',
            'auto_transfer_enabled' => 'boolean',
            'auto_transfer_threshold' => 'required|integer|min:0|max:200',
            'rate_2_top' => 'required|numeric|min:0',
            'rate_2_bottom' => 'required|numeric|min:0',
            'rate_3_top' => 'required|numeric|min:0',
            'rate_3_toad' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // บันทึกค่าทั้งหมด
            Setting::set('max_payout_2_digit', $request->max_payout_2_digit, 'decimal', 'ยอดจ่ายสูงสุดต่อเลข 2 ตัว (บาท)', 'risk');
            Setting::set('max_payout_3_digit', $request->max_payout_3_digit, 'decimal', 'ยอดจ่ายสูงสุดต่อเลข 3 ตัว (บาท)', 'risk');
            Setting::set('auto_transfer_enabled', $request->has('auto_transfer_enabled') ? 'true' : 'false', 'boolean', 'เปิด/ปิด ระบบตัดยอดอัตโนมัติ', 'risk');
            Setting::set('auto_transfer_threshold', $request->auto_transfer_threshold, 'integer', 'เปอร์เซ็นต์ที่เริ่มตัดยอด', 'risk');
            
            Setting::set('rate_2_top', $request->rate_2_top, 'decimal', 'อัตราจ่าย 2 ตัวบน', 'payout');
            Setting::set('rate_2_bottom', $request->rate_2_bottom, 'decimal', 'อัตราจ่าย 2 ตัวล่าง', 'payout');
            Setting::set('rate_3_top', $request->rate_3_top, 'decimal', 'อัตราจ่าย 3 ตัวตรง', 'payout');
            Setting::set('rate_3_toad', $request->rate_3_toad, 'decimal', 'อัตราจ่าย 3 ตัวโต๊ด', 'payout');
            
            Setting::set('commission_rate', $request->commission_rate, 'decimal', 'อัตราค่าคอมมิชชั่น (%)', 'general');

            // Clear cache
            Setting::clearCache();

            return redirect()->back()->with('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
