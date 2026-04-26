<?php
namespace App\Http\Controllers;

use App\Models\LotteryDraw;
use App\Models\LotteryBet;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LotteryDrawController extends Controller
{
    public function index()
    {
        $draws = LotteryDraw::with('announcedBy')->orderBy('draw_date', 'desc')->paginate(20);
        return view('admin.draws', compact('draws'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'draw_date'       => 'required|string',
            'result_3_top'    => 'required|string|size:3',
            'result_2_top'    => 'required|string|size:2',
            'result_2_bottom' => 'required|string|size:2',
            // [FIX #5] เปลี่ยน max:100 → max:200 ให้สอดคล้องกับ column ที่จะแก้เป็น 200 chars
            'result_3_bottom' => 'nullable|string|max:200',
        ]);

        try {
            $drawDate = $this->convertThaiDateToYmd($validated['draw_date']);
            $draw     = LotteryDraw::where('draw_date', $drawDate)->first();

            if ($draw && $draw->is_announced) {
                return response()->json(['success' => false, 'message' => 'งวดนี้ประกาศผลแล้ว ไม่สามารถแก้ไขได้'], 400);
            }

            $result3Bottom = $this->parseResult3Bottom($validated['result_3_bottom'] ?? '');

            DB::transaction(function () use ($validated, $drawDate, $result3Bottom) {
                LotteryDraw::updateOrCreate(
                    ['draw_date' => $drawDate],
                    [
                        'result_3_top'    => $validated['result_3_top'],
                        'result_2_top'    => $validated['result_2_top'],
                        'result_2_bottom' => $validated['result_2_bottom'],
                        'result_3_bottom' => $result3Bottom ?: null,
                        'is_announced'    => true,
                        'announced_at'    => now(),
                        'announced_by'    => Auth::id(),
                    ]
                );
                $this->calculateWinnings($drawDate, array_merge($validated, ['result_3_bottom' => $result3Bottom]));
            });

            return response()->json(['success' => true, 'message' => 'บันทึกผลหวยและคำนวณรางวัลเรียบร้อย']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 400);
        }
    }

    private function parseResult3Bottom(?string $raw): ?string
    {
        if (empty(trim($raw ?? ''))) return null;
        $parts = array_map('trim', explode(',', $raw));
        $valid = array_filter($parts, fn($n) => preg_match('/^\d{3}$/', $n));
        return empty($valid) ? null : implode(',', array_values($valid));
    }

    private function calculateWinnings($drawDate, $results)
    {
        $bets  = LotteryBet::where('draw_date', $drawDate)->whereNull('deleted_at')->get();
        $rates = [
            '2_top'    => Setting::get('rate_2_top', 90),
            '2_bottom' => Setting::get('rate_2_bottom', 90),
            '3_top'    => Setting::get('rate_3_top', 900),
            '3_toad'   => Setting::get('rate_3_toad', 120),
            '3_bottom' => Setting::get('rate_3_bottom', 500),
        ];

        $bottom3Nums = [];
        if (!empty($results['result_3_bottom'])) {
            $bottom3Nums = array_filter(
                array_map('trim', explode(',', $results['result_3_bottom'])),
                fn($n) => preg_match('/^\d{3}$/', $n)
            );
        }

        foreach ($bets as $bet) {
            $payoutTop = 0; $payoutBottom = 0; $payoutToad = 0; $payoutBottom3 = 0;
            $isWinTop  = false; $isWinBottom = false; $isWinToad = false; $isWinBottom3 = false;
            $len = strlen($bet->number);

            if ($bet->amount_top > 0 && $len === 2 && $bet->number === $results['result_2_top']) {
                $payoutTop = $bet->amount_top * $rates['2_top']; $isWinTop = true;
            }
            if ($bet->amount_bottom > 0 && $len === 2 && $bet->number === $results['result_2_bottom']) {
                $payoutBottom = $bet->amount_bottom * $rates['2_bottom']; $isWinBottom = true;
            }
            if ($bet->amount_top > 0 && $len === 3 && $bet->number === $results['result_3_top']) {
                $payoutTop = $bet->amount_top * $rates['3_top']; $isWinTop = true;
            }
            if ($bet->amount_toad > 0 && $len === 3 && $this->isToadWin($bet->number, $results['result_3_top'])) {
                $payoutToad = $bet->amount_toad * $rates['3_toad']; $isWinToad = true;
            }
            if (($bet->amount_bottom_3 ?? 0) > 0 && $len === 3 && !empty($bottom3Nums) && in_array($bet->number, $bottom3Nums)) {
                $payoutBottom3 = $bet->amount_bottom_3 * $rates['3_bottom']; $isWinBottom3 = true;
            }

            $bet->update([
                'payout_top'      => $payoutTop,
                'payout_bottom'   => $payoutBottom,
                'payout_toad'     => $payoutToad,
                'payout_bottom_3' => $payoutBottom3,
                'is_win_top'      => $isWinTop,
                'is_win_bottom'   => $isWinBottom,
                'is_win_toad'     => $isWinToad,
                'is_win_bottom_3' => $isWinBottom3,
            ]);
        }
    }

    private function isToadWin($betNumber, $resultNumber)
    {
        $b = str_split($betNumber); $r = str_split($resultNumber);
        sort($b); sort($r);
        return $b === $r;
    }

    public function results($id)
    {
        $draw = LotteryDraw::with(['bets' => fn($q) => $q->whereNull('deleted_at')->with('creator')->orderBy('customer_name')])->findOrFail($id);
        $summary = [
            'total_bets'    => $draw->bets->count(),
            'total_amount'  => $draw->bets->sum('total_amount'),
            'total_payout'  => $draw->bets->sum('total_payout'),
            'total_profit'  => $draw->bets->sum('total_amount') - $draw->bets->sum('total_payout'),
            'winners_count' => $draw->bets->filter(fn($b) => $b->is_win_top || $b->is_win_bottom || $b->is_win_toad || ($b->is_win_bottom_3 ?? false))->count(),
        ];
        return view('admin.draw-results', compact('draw', 'summary'));
    }

    /**
     * [FIX #6] แก้สูตรแปลงปี พ.ศ. → ค.ศ.
     * หลักการ: ปี พ.ศ. = ปี ค.ศ. + 543  ดังนั้น ค.ศ. = พ.ศ. - 543
     * รองรับ 2 format:
     *   - YYYY-MM-DD (Gregorian)  → ส่งผ่านตรง ไม่แปลง
     *   - DD/MM/YYYY (อาจเป็น พ.ศ.)  → ถ้า Year > 2500 ถือว่าเป็น พ.ศ. ให้ลบ 543
     */
    private function convertThaiDateToYmd($date)
    {
        // รูปแบบ YYYY-MM-DD (Gregorian) → ส่งผ่านตรง
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // รูปแบบ DD/MM/YYYY
        if (strpos($date, '/') !== false) {
            $p = explode('/', $date);
            if (count($p) === 3) {
                $year = intval($p[2]);
                // [FIX #6] ถ้าปีมากกว่า 2500 แสดงว่าเป็น พ.ศ. → ลบ 543
                if ($year > 2500) {
                    $year -= 543;
                }
                return $year . '-' . str_pad($p[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($p[0], 2, '0', STR_PAD_LEFT);
            }
        }

        throw new \Exception("รูปแบบวันที่ไม่ถูกต้อง: $date");
    }
}
