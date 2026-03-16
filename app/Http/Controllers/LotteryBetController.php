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

            // ✅ ตรวจสอบว่างวดนี้ปิดรับแทงแล้วหรือยัง
            $draw = LotteryDraw::where('draw_date', $drawDate)->first();
            
            if (!$draw) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบงวดหวยที่เลือก'
                ], 400);
            }
            
            // ตรวจสอบว่างวดปิดรับแทงแล้วหรือยัง
            if ($draw->isClosed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'งวดนี้ปิดรับแทงแล้ว ไม่สามารถแทงได้'
                ], 400);
            }
            
            // ตรวจสอบว่าประกาศผลแล้วหรือยัง
            if ($draw->is_announced) {
                return response()->json([
                    'success' => false,
                    'message' => 'งวดนี้ประกาศผลแล้ว ไม่สามารถแทงได้'
                ], 400);
            }

            DB::transaction(function () use ($validated, $drawDate) {
                foreach ($validated['bets'] as $bet) {
                    // ตรวจสอบกติกา
                    $numberLength = strlen($bet['number']);

                    if ($numberLength === 2) {
                        // 2 ตัว ห้ามมีโต๊ด
                        if ($bet['toad'] > 0) {
                            throw new \Exception("ERROR: เลข 2 ตัว ไม่มีโต๊ด");
                        }
                    } elseif ($numberLength === 3) {
                        // 3 ตัว ห้ามมีล่าง
                        if ($bet['bottom'] > 0) {
                            throw new \Exception("ERROR: เลข 3 ตัว ไม่มีล่าง");
                        }
                    } else {
                        throw new \Exception("ERROR: เลขต้องเป็น 2 หรือ 3 หลักเท่านั้น");
                    }

                    LotteryBet::create([
                        'draw_date' => $drawDate,
                        'customer_name' => $validated['customer_name'],
                        'number' => $bet['number'],
                        'amount_top' => $bet['top'],
                        'amount_bottom' => $bet['bottom'],
                        'amount_toad' => $bet['toad'],
                        'created_by' => Auth::id(),
                    ]);
                }

                // หมายเหตุ: ไม่ต้องสร้างงวดที่นี่อีกแล้ว เพราะเราเช็คว่ามีงวดอยู่แล้วข้างบน
                // ถ้างวดยังไม่มีจะ error ไปตั้งแต่ก่อน transaction
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
}