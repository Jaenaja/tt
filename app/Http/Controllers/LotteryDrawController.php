<?php
// app/Http/Controllers/LotteryDrawController.php
namespace App\Http\Controllers;

use App\Models\LotteryDraw;
use App\Models\LotteryBet;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LotteryDrawController extends Controller
{
    public function index()
    {
        $draws = LotteryDraw::with('announcedBy')
            ->orderBy('draw_date', 'desc')
            ->paginate(20);

        return view('admin.draws', compact('draws'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'draw_date' => 'required|string',
            'result_3_top' => 'required|string|size:3',
            'result_2_top' => 'required|string|size:2',
            'result_2_bottom' => 'required|string|size:2',
        ]);

        try {
            $drawDate = $this->convertThaiDateToYmd($validated['draw_date']);

            $draw = LotteryDraw::where('draw_date', $drawDate)->first();

            if ($draw && $draw->is_announced) {
                return response()->json([
                    'success' => false,
                    'message' => 'งวดนี้ประกาศผลแล้ว ไม่สามารถแก้ไขได้'
                ], 400);
            }

            DB::transaction(function () use ($validated, $drawDate) {
                // บันทึกผลหวย
                $draw = LotteryDraw::updateOrCreate(
                    ['draw_date' => $drawDate],
                    [
                        'result_3_top' => $validated['result_3_top'],
                        'result_2_top' => $validated['result_2_top'],
                        'result_2_bottom' => $validated['result_2_bottom'],
                        'is_announced' => true,
                        'announced_at' => now(),
                        'announced_by' => Auth::id(),
                    ]
                );

                // คำนวณผลรางวัล
                $this->calculateWinnings($drawDate, $validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'บันทึกผลหวยและคำนวณรางวัลเรียบร้อย'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 400);
        }
    }

    private function calculateWinnings($drawDate, $results)
    {
        $bets = LotteryBet::where('draw_date', $drawDate)
            ->whereNull('deleted_at')
            ->get();

        $rates = [
            '2_top' => Config::get('rate_2_top', 90),
            '2_bottom' => Config::get('rate_2_bottom', 90),
            '3_top' => Config::get('rate_3_top', 900),
            '3_toad' => Config::get('rate_3_toad', 120),
        ];

        foreach ($bets as $bet) {
            $payoutTop = 0;
            $payoutBottom = 0;
            $payoutToad = 0;
            $isWinTop = false;
            $isWinBottom = false;
            $isWinToad = false;

            // ตรวจ 2 ตัวบน
            if ($bet->amount_top > 0 && strlen($bet->number) === 2) {
                if ($bet->number === $results['result_2_top']) {
                    $payoutTop = $bet->amount_top * $rates['2_top'];
                    $isWinTop = true;
                }
            }

            // ตรวจ 2 ตัวล่าง
            if ($bet->amount_bottom > 0 && strlen($bet->number) === 2) {
                if ($bet->number === $results['result_2_bottom']) {
                    $payoutBottom = $bet->amount_bottom * $rates['2_bottom'];
                    $isWinBottom = true;
                }
            }

            // ตรวจ 3 ตัวบน
            if ($bet->amount_top > 0 && strlen($bet->number) === 3) {
                if ($bet->number === $results['result_3_top']) {
                    $payoutTop = $bet->amount_top * $rates['3_top'];
                    $isWinTop = true;
                }
            }

            // ตรวจ 3 ตัวโต๊ด
            if ($bet->amount_toad > 0 && strlen($bet->number) === 3) {
                if ($this->isToadWin($bet->number, $results['result_3_top'])) {
                    $payoutToad = $bet->amount_toad * $rates['3_toad'];
                    $isWinToad = true;
                }
            }

            $bet->update([
                'payout_top' => $payoutTop,
                'payout_bottom' => $payoutBottom,
                'payout_toad' => $payoutToad,
                'is_win_top' => $isWinTop,
                'is_win_bottom' => $isWinBottom,
                'is_win_toad' => $isWinToad,
            ]);
        }
    }

    private function isToadWin($betNumber, $resultNumber)
    {
        // โต๊ด = เลขเรียงไม่ตรงแต่ตัวเลขเหมือนกัน
        $betDigits = str_split($betNumber);
        $resultDigits = str_split($resultNumber);

        sort($betDigits);
        sort($resultDigits);

        return $betDigits === $resultDigits && $betNumber !== $resultNumber;
    }

    public function results($id)
    {
        $draw = LotteryDraw::with([
            'bets' => function ($query) {
                $query->whereNull('deleted_at')
                    ->with('creator')
                    ->orderBy('customer_name');
            }
        ])->findOrFail($id);

        $summary = [
            'total_bets' => $draw->bets->count(),
            'total_amount' => $draw->bets->sum('total_amount'),
            'total_payout' => $draw->bets->sum('total_payout'),
            'total_profit' => $draw->bets->sum('total_amount') - $draw->bets->sum('total_payout'),
            'winners_count' => $draw->bets->filter(function ($bet) {
                return $bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad;
            })->count(),
        ];

        return view('admin.draw-results', compact('draw', 'summary'));
    }

    private function convertThaiDateToYmd($thaiDate)
    {
        $parts = explode('/', $thaiDate);
        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        $year = 2500 + intval($parts[2]);

        return "$year-$month-$day";
    }
}