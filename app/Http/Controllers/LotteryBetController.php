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

                // สร้างงวดหวยถ้ายังไม่มี
                LotteryDraw::firstOrCreate(
                    ['draw_date' => $drawDate],
                    ['is_announced' => false]
                );
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

    public function destroy($id)
    {
        try {
            $bet = LotteryBet::findOrFail($id);

            // ตรวจสอบว่างวดนั้นประกาศผลแล้วหรือยัง
            $draw = LotteryDraw::where('draw_date', $bet->draw_date)->first();
            if ($draw && $draw->is_announced) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เพราะงวดนี้ประกาศผลแล้ว'
                ], 400);
            }

            // Soft delete
            $bet->update([
                'deleted_at' => now(),
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ลบรายการเรียบร้อยแล้ว'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}