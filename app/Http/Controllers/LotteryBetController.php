<?php
// app/Http/Controllers/LotteryBetController.php
namespace App\Http\Controllers;

use App\Models\LotteryBet;
use App\Models\LotteryDraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LotteryBetController extends Controller
{
    public function index()
    {
        return view('bets.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'draw_date' => 'required|string',
            'customer_name' => 'required|string',
            'bets' => 'required|array',
            'bets.*.number' => 'required|string',
            'bets.*.top' => 'required|numeric|min:0',
            'bets.*.bottom' => 'required|numeric|min:0',
            'bets.*.toad' => 'required|numeric|min:0',
        ]);

        try {
            // draw_date มาในรูปแบบ Y-m-d แล้ว (เช่น 2026-03-16)
            $drawDate = $validated['draw_date'];

            // 🔍 Dynamic Draw Logic
            $draw = LotteryDraw::where('draw_date', $drawDate)->first();

            // กรณีที่ 1: มีงวดใน DB และประกาศผลแล้ว → ห้ามแทง
            if ($draw && $draw->is_announced) {
                return response()->json([
                    'success' => false,
                    'message' => 'งวดนี้มีการประกาศผลรางวัลไปแล้ว ไม่สามารถรับแทงเพิ่มได้'
                ], 400);
            }

            // กรณีที่ 2: ไม่มีงวดใน DB → สร้างงวดใหม่อัตโนมัติ
            if (!$draw) {
                $draw = LotteryDraw::create([
                    'draw_date' => $drawDate,
                    'is_announced' => 0,
                ]);
            }

            // กรณีที่ 3: มีงวดใน DB และ is_announced = 0 → ผ่านได้ บันทึกปกติ

            DB::transaction(function () use ($validated, $drawDate) {

                // ── Step 1: ตรวจกติกาทุก bet ก่อน (ไม่แตะ DB) ──────────────────
                foreach ($validated['bets'] as $bet) {
                    $len = strlen($bet['number']);
                    if ($len === 2 && $bet['toad'] > 0) {
                        throw new \Exception("ERROR: เลข 2 ตัว ไม่มีโต๊ด");
                    }
                    if ($len === 3 && $bet['bottom'] > 0) {
                        throw new \Exception("ERROR: เลข 3 ตัว ไม่มีล่าง");
                    }
                    if ($len !== 2 && $len !== 3) {
                        throw new \Exception("ERROR: เลขต้องเป็น 2 หรือ 3 หลักเท่านั้น");
                    }
                }

                // ── Step 2: ดึง existing records ที่บันทึกภายใน 10 วินาทีที่ผ่านมา ──
                // (ป้องกัน network retry / double-submit โดยไม่บล็อกการแทงซ้ำโดยตั้งใจ)
                $recentCutoff = now()->subSeconds(10);

                $existing = LotteryBet::whereNull('deleted_at')
                    ->where('draw_date', $drawDate)
                    ->where('customer_name', $validated['customer_name'])
                    ->where('created_at', '>=', $recentCutoff)
                    ->get(['number', 'amount_top', 'amount_bottom', 'amount_toad']);

                // สร้าง Set ของ key ที่บันทึกไปล่าสุด
                $recentKeys = $existing->map(function ($r) use ($validated) {
                    return "{$validated['customer_name']}|{$r->number}|{$r->amount_top}|{$r->amount_bottom}|{$r->amount_toad}";
                })->flip()->all();

                // ── Step 3: ตรวจซ้ำ + เตรียม bulk insert (ยังไม่แตะ DB) ──────────
                $now = now();
                $rows = [];
                foreach ($validated['bets'] as $bet) {
                    $key = "{$validated['customer_name']}|{$bet['number']}|{$bet['top']}|{$bet['bottom']}|{$bet['toad']}";
                    if (isset($recentKeys[$key])) {
                        throw new \Exception(
                            "พบข้อมูลซ้ำ: เลข {$bet['number']} ถูกบันทึกไปแล้วในช่วงเวลานี้ กรุณารอสักครู่แล้วลองใหม่"
                        );
                    }
                    $rows[] = [
                        'draw_date' => $drawDate,
                        'customer_name' => $validated['customer_name'],
                        'number' => $bet['number'],
                        'amount_top' => $bet['top'],
                        'amount_bottom' => $bet['bottom'],
                        'amount_toad' => $bet['toad'],
                        'created_by' => Auth::id(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // ── Step 4: INSERT ทีเดียวทั้งหมด (1 query) ──────────────────────
                DB::table('lottery_bets')->insert($rows);
            });

            return response()->json([
                'success' => true,
                'message' => 'บันทึกข้อมูลสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function history(Request $request)
    {
        // Query พื้นฐาน
        $query = LotteryBet::with(['creator', 'deleter', 'draw'])
            ->whereNull('deleted_at');

        // ฟิลเตอร์ตามชื่อลูกค้า
        if ($request->has('customer_name') && $request->customer_name) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }

        // ฟิลเตอร์ตามงวดวันที่
        if ($request->has('draw_date') && $request->draw_date) {
            $query->where('draw_date', $request->draw_date);
        }

        // ฟิลเตอร์ตามเลข
        if ($request->has('search_number') && $request->search_number) {
            $query->where('number', 'like', '%' . $request->search_number . '%');
        }

        // เรียงลำดับ (default: งวดล่าสุด, วันที่บันทึกล่าสุด)
        $sortBy = $request->get('sort_by', 'draw_date');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'draw_date') {
            $query->orderBy('draw_date', $sortOrder)
                ->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $bets = $query->paginate(50)->appends($request->all());

        // ดึงรายการงวดที่มีการซื้อ
        $drawDates = LotteryBet::select('draw_date')
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('draw_date', 'desc')
            ->get();

        return view('bets.history', compact('bets', 'drawDates'));
    }

    public function destroy(Request $request, $id)
    {
        try {
            // ตรวจสอบรหัสลบจาก Setting
            $deleteCode = \App\Models\Setting::get('delete_code', '');

            if (empty($deleteCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาตั้งรหัสลบในหน้าตั้งค่าความเสี่ยงก่อนใช้งาน'
                ], 400);
            }

            // Validate รหัสที่กรอกเข้ามา
            $request->validate([
                'delete_code' => 'required|digits:6'
            ], [
                'delete_code.required' => 'กรุณากรอกรหัสลบ',
                'delete_code.digits' => 'รหัสลบต้องเป็นตัวเลข 6 หลัก'
            ]);

            // ตรวจสอบความถูกต้องของรหัส
            if ($request->delete_code !== $deleteCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'รหัสลบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง'
                ], 403);
            }

            $bet = LotteryBet::findOrFail($id);

            // ตรวจสอบว่างวดนั้นประกาศผลแล้วหรือยัง
            $draw = LotteryDraw::where('draw_date', $bet->draw_date)->first();
            if ($draw && $draw->is_announced) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เพราะงวดนี้ประกาศผลแล้ว'
                ], 400);
            }

            // Soft delete พร้อมบันทึกผู้ลบ
            $bet->deleted_by = Auth::id();
            $bet->save();
            $bet->delete(); // ใช้ SoftDeletes trait

            return response()->json([
                'success' => true,
                'message' => 'ลบรายการเรียบร้อยแล้ว'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
    public function exportExcel(Request $request)
    {
        $query = LotteryBet::with(['creator', 'draw'])
            ->whereNull('deleted_at');

        if ($request->customer_name) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->draw_date) {
            $query->where('draw_date', $request->draw_date);
        }
        if ($request->search_number) {
            $query->where('number', 'like', '%' . $request->search_number . '%');
        }

        $sortBy = $request->get('sort_by', 'draw_date');
        $sortOrder = $request->get('sort_order', 'desc');
        if ($sortBy === 'draw_date') {
            $query->orderBy('draw_date', $sortOrder)->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $bets = $query->get();

        // สร้าง CSV (UTF-8 BOM สำหรับ Excel)
        $rows = [];
        $rows[] = ['งวดวันที่', 'ชื่อลูกค้า', 'เลข', 'บน', 'ล่าง', 'โต๊ด', 'รวม (฿)', 'บันทึกโดย', 'วันที่บันทึก', 'สถานะ'];

        foreach ($bets as $bet) {
            $total = $bet->amount_top + $bet->amount_bottom + $bet->amount_toad;
            $drawDate = \Carbon\Carbon::parse($bet->draw_date)->format('d/m/Y');
            $createdAt = \Carbon\Carbon::parse($bet->created_at)->format('d/m/y H:i');
            $createdBy = $bet->creator ? $bet->creator->name : '-';

            if ($bet->draw && $bet->draw->is_announced) {
                $status = ($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad) ? 'ถูกรางวัล' : 'ไม่ถูก';
            } else {
                $status = 'รอประกาศ';
            }

            $rows[] = [
                $drawDate,
                $bet->customer_name,
                $bet->number,
                $bet->amount_top > 0 ? $bet->amount_top : '',
                $bet->amount_bottom > 0 ? $bet->amount_bottom : '',
                $bet->amount_toad > 0 ? $bet->amount_toad : '',
                $total,
                $createdBy,
                $createdAt,
                $status,
            ];
        }

        $filename = 'ประวัติการแทง_' . now()->format('Y-m-d') . '.csv';

        $handle = fopen('php://temp', 'r+');
        // BOM สำหรับ Excel ภาษาไทย
        fwrite($handle, "\xEF\xBB\xBF");
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}