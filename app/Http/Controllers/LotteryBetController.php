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
            // แปลงวันที่จากรูปแบบไทยเป็น Y-m-d
            $drawDate = $this->convertThaiDateToYmd($validated['draw_date']);

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
        $query = LotteryBet::with(['creator', 'deleter'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if ($request->has('draw_date')) {
            $drawDate = $this->convertThaiDateToYmd($request->draw_date);
            $query->where('draw_date', $drawDate);
        }

        if ($request->has('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }

        $bets = $query->paginate(50);

        return view('bets.history', compact('bets'));
    }

    public function destroy($id)
    {
        try {
            $bet = LotteryBet::findOrFail($id);

            // ตรวจสอบว่างวดนั้นออกผลแล้วหรือยัง
            $draw = LotteryDraw::where('draw_date', $bet->draw_date)->first();
            if ($draw && $draw->is_announced) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เนื่องจากงวดนี้ออกผลแล้ว'
                ], 400);
            }

            $bet->update([
                'deleted_by' => Auth::id(),
                'deleted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ลบรายการสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 400);
        }
    }

    private function convertThaiDateToYmd($thaiDate)
    {
        // รูปแบบ: 1/2/69 -> 2026-02-01
        $parts = explode('/', $thaiDate);
        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        $year = 2500 + intval($parts[2]); // แปลง พ.ศ. 2 หลัก เป็น 4 หลัก
        
        return "$year-$month-$day";
    }
}