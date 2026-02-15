<?php

namespace App\Http\Controllers;

use App\Models\LotteryDraw;
use App\Models\LotteryBet;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // หน้าแสดงรายการงวดทั้งหมด
    public function index()
    {
        $draws = LotteryDraw::with('announcedBy')
            ->orderBy('draw_date', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact('draws'));
    }

    // หน้าสรุปผลแต่ละงวด พร้อม Heatmap และการวิเคราะห์ความเสี่ยง
    public function summary($drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => function ($query) {
                $query->whereNull('deleted_at')
                    ->with('creator');
            }
        ])->findOrFail($drawId);

        // ดึง Config อัตราจ่าย
        $rates = [
            '2_top' => Config::get('rate_2_top', 70),
            '2_bottom' => Config::get('rate_2_bottom', 70),
            '3_top' => Config::get('rate_3_top', 500),
            '3_toad' => Config::get('rate_3_toad', 100),
        ];

        // คำนวณ Liability สำหรับ 2 ตัว (00-99)
        $twoDigitLiability = $this->calculateTwoDigitLiability($draw->bets, $rates);

        // คำนวณ Liability สำหรับ 3 ตัว (000-999) รวมโต๊ด
        $threeDigitLiability = $this->calculateThreeDigitLiability($draw->bets, $rates);

        // หา Max Liability สำหรับ Dynamic Scaling
        $maxTwoDigit = max(array_values($twoDigitLiability));
        $maxThreeDigit = max(array_values($threeDigitLiability));

        // เตรียมข้อมูลสำหรับ ECharts Heatmap
        $twoDigitHeatmapData = $this->prepareHeatmapData($twoDigitLiability, 2);
        $threeDigitHeatmapData = $this->prepareHeatmapData($threeDigitLiability, 3);

        // Top 10 Exposure
        $topTwoDigitExposure = $this->getTopExposure($twoDigitLiability, 10);
        $topThreeDigitExposure = $this->getTopExposure($threeDigitLiability, 10);

        // ข้อมูลสถิติพื้นฐาน
        $totalTransactions = $draw->bets->count();

        // คำนวณยอดขายแยกตามประเภท
        $sales = [
            'two_top' => [
                'total' => 0,
                'count' => 0,
            ],
            'two_bottom' => [
                'total' => 0,
                'count' => 0,
            ],
            'three_top' => [
                'total' => 0,
                'count' => 0,
            ],
            'three_toad' => [
                'total' => 0,
                'count' => 0,
            ]
        ];

        foreach ($draw->bets as $bet) {
            $numberLength = strlen($bet->number);

            if ($bet->amount_top > 0 && $numberLength === 2) {
                $sales['two_top']['total'] += $bet->amount_top;
                $sales['two_top']['count']++;
            }

            if ($bet->amount_bottom > 0 && $numberLength === 2) {
                $sales['two_bottom']['total'] += $bet->amount_bottom;
                $sales['two_bottom']['count']++;
            }

            if ($bet->amount_top > 0 && $numberLength === 3) {
                $sales['three_top']['total'] += $bet->amount_top;
                $sales['three_top']['count']++;
            }

            if ($bet->amount_toad > 0 && $numberLength === 3) {
                $sales['three_toad']['total'] += $bet->amount_toad;
                $sales['three_toad']['count']++;
            }
        }

        $totalSales = $sales['two_top']['total'] +
            $sales['two_bottom']['total'] +
            $sales['three_top']['total'] +
            $sales['three_toad']['total'];

        // ข้อมูลสำหรับงวดที่ประกาศผลแล้ว
        $result = null;
        if ($draw->is_announced) {
            $totalPayout = $draw->bets->sum(function ($bet) {
                return $bet->payout_top + $bet->payout_bottom + $bet->payout_toad;
            });

            $winnersCount = $draw->bets->filter(function ($bet) {
                return $bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad;
            })->count();

            $profitLoss = $totalSales - $totalPayout;

            $result = [
                'total_bet' => $totalSales,
                'total_payout' => $totalPayout,
                'profit_loss' => $profitLoss,
                'winners_count' => $winnersCount,
            ];
        }

        // ดึงประวัติการแทงของงวดนี้
        $betsHistory = $draw->bets()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.reports.summary', compact(
            'draw',
            'totalTransactions',
            'sales',
            'totalSales',
            'result',
            'twoDigitHeatmapData',
            'threeDigitHeatmapData',
            'maxTwoDigit',
            'maxThreeDigit',
            'topTwoDigitExposure',
            'topThreeDigitExposure',
            'betsHistory',
            'rates'
        ));
    }

    /**
     * คำนวณ Liability สำหรับเลข 2 ตัว (00-99)
     */
    private function calculateTwoDigitLiability($bets, $rates)
    {
        $liability = [];

        // Initialize ทุกเลข 00-99
        for ($i = 0; $i <= 99; $i++) {
            $number = str_pad($i, 2, '0', STR_PAD_LEFT);
            $liability[$number] = 0;
        }

        // คำนวณยอดจ่ายสูงสุดถ้าเลขออก
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 2) {
                // 2 ตัวบน
                if ($bet->amount_top > 0) {
                    $liability[$bet->number] += $bet->amount_top * $rates['2_top'];
                }

                // 2 ตัวล่าง
                if ($bet->amount_bottom > 0) {
                    $liability[$bet->number] += $bet->amount_bottom * $rates['2_bottom'];
                }
            }
        }

        return $liability;
    }

    /**
     * คำนวณ Liability สำหรับเลข 3 ตัว (000-999) รวมโต๊ด
     */
    private function calculateThreeDigitLiability($bets, $rates)
    {
        $liability = [];

        // Initialize ทุกเลข 000-999
        for ($i = 0; $i <= 999; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $liability[$number] = 0;
        }

        // คำนวณยอดจ่ายสูงสุดถ้าเลขออก
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 3) {
                // 3 ตัวตรง
                if ($bet->amount_top > 0) {
                    $liability[$bet->number] += $bet->amount_top * $rates['3_top'];
                }

                // 3 ตัวโต๊ด - ต้องคำนวณกับทุกเลขที่เป็นโต๊ดของเลขนี้
                if ($bet->amount_toad > 0) {
                    $toadNumbers = $this->getToadNumbers($bet->number);
                    foreach ($toadNumbers as $toadNum) {
                        $liability[$toadNum] += $bet->amount_toad * $rates['3_toad'];
                    }
                }
            }
        }

        return $liability;
    }

    /**
     * หาเลขโต๊ดทั้งหมดของเลข 3 ตัว
     * เช่น 123 จะได้ 123, 132, 213, 231, 312, 321
     */
    private function getToadNumbers($number)
    {
        $digits = str_split($number);
        $permutations = [];

        // สร้างการเรียงสับเปลี่ยนทั้งหมด
        $this->generatePermutations($digits, 0, count($digits) - 1, $permutations);

        // แปลงเป็น string และ unique
        $toadNumbers = array_unique(array_map(function ($perm) {
            return implode('', $perm);
        }, $permutations));

        return $toadNumbers;
    }

    /**
     * สร้างการเรียงสับเปลี่ยน (Permutation)
     */
    private function generatePermutations(&$arr, $left, $right, &$result)
    {
        if ($left == $right) {
            $result[] = $arr;
        } else {
            for ($i = $left; $i <= $right; $i++) {
                // Swap
                $temp = $arr[$left];
                $arr[$left] = $arr[$i];
                $arr[$i] = $temp;

                $this->generatePermutations($arr, $left + 1, $right, $result);

                // Swap back
                $temp = $arr[$left];
                $arr[$left] = $arr[$i];
                $arr[$i] = $temp;
            }
        }
    }

    /**
     * เตรียมข้อมูลสำหรับ ECharts Heatmap
     */
    private function prepareHeatmapData($liability, $digits)
    {
        $data = [];

        if ($digits === 2) {
            // Grid 10x10 สำหรับ 00-99
            for ($y = 0; $y < 10; $y++) {
                for ($x = 0; $x < 10; $x++) {
                    $number = str_pad(($y * 10 + $x), 2, '0', STR_PAD_LEFT);
                    $data[] = [$x, $y, round($liability[$number], 2)];
                }
            }
        } else {
            // Grid 25x40 สำหรับ 000-999
            for ($y = 0; $y < 25; $y++) {
                for ($x = 0; $x < 40; $x++) {
                    $index = $y * 40 + $x;
                    if ($index <= 999) {
                        $number = str_pad($index, 3, '0', STR_PAD_LEFT);
                        $data[] = [$x, $y, round($liability[$number], 2)];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * หา Top N เลขที่มี Liability สูงสุด
     */
    private function getTopExposure($liability, $limit = 10)
    {
        arsort($liability);
        $top = array_slice($liability, 0, $limit, true);

        $result = [];
        foreach ($top as $number => $amount) {
            $result[] = [
                'number' => $number,
                'liability' => $amount,
                'status' => $this->getExposureStatus($amount)
            ];
        }

        return $result;
    }

    /**
     * กำหนดสถานะความเสี่ยง
     */
    private function getExposureStatus($liability)
    {
        if ($liability >= 10000) {
            return 'อั้น'; // อันตราย
        } elseif ($liability >= 5000) {
            return 'จ่ายครึ่ง'; // เฝ้าระวัง
        } else {
            return 'ปกติ'; // ปลอดภัย
        }
    }

    // Export PDF
    public function exportPDF($drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => function ($query) {
                $query->whereNull('deleted_at');
            }
        ])->findOrFail($drawId);

        if (!$draw->is_announced) {
            return redirect()->back()->with('error', 'ยังไม่ประกาศผล ไม่สามารถ Export ได้');
        }

        $pdf = Pdf::loadView('admin.reports.pdf', compact('draw'));
        return $pdf->download('lottery-report-' . $draw->draw_date . '.pdf');
    }

    // Statistics สำหรับดูภาพรวม
    public function statistics(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $draws = LotteryDraw::whereBetween('draw_date', [$startDate, $endDate])
            ->where('is_announced', true)
            ->with('bets')
            ->get();

        $stats = [
            'total_draws' => $draws->count(),
            'total_sales' => 0,
            'total_payout' => 0,
            'total_profit' => 0,
            'avg_profit' => 0,
        ];

        foreach ($draws as $draw) {
            $sales = $draw->bets->sum(function ($bet) {
                return $bet->amount_top + $bet->amount_bottom + $bet->amount_toad;
            });
            $payout = $draw->bets->sum(function ($bet) {
                return $bet->payout_top + $bet->payout_bottom + $bet->payout_toad;
            });

            $stats['total_sales'] += $sales;
            $stats['total_payout'] += $payout;
        }

        $stats['total_profit'] = $stats['total_sales'] - $stats['total_payout'];
        $stats['avg_profit'] = $stats['total_draws'] > 0 ?
            $stats['total_profit'] / $stats['total_draws'] : 0;

        return view('admin.reports.statistics', compact('stats', 'draws', 'startDate', 'endDate'));
    }
}