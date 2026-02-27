<?php

namespace App\Http\Controllers;

use App\Models\LotteryDraw;
use App\Models\LotteryBet;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $draws = LotteryDraw::with('announcedBy')
            ->orderBy('draw_date', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact('draws'));
    }

    public function summary(Request $request, $drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => function ($query) {
                $query->whereNull('deleted_at')
                    ->with('creator');
            }
        ])->findOrFail($drawId);

        // Settings
        $settings = [
            'max_payout_2_digit' => Setting::get('max_payout_2_digit', 50000),
            'max_payout_3_digit' => Setting::get('max_payout_3_digit', 100000),
            'rate_2_top' => Setting::get('rate_2_top', 90),
            'rate_2_bottom' => Setting::get('rate_2_bottom', 90),
            'rate_3_top' => Setting::get('rate_3_top', 900),
            'rate_3_toad' => Setting::get('rate_3_toad', 120),
            'commission_rate' => Setting::get('commission_rate', 10),
        ];

        // คำนวณ Liability แยกตามประเภท
        $twoTopLiability = $this->calculateTwoTopLiability($draw->bets, $settings);
        $twoBottomLiability = $this->calculateTwoBottomLiability($draw->bets, $settings);
        $threeTopLiability = $this->calculateThreeTopLiability($draw->bets, $settings);
        $threeToadLiability = $this->calculateThreeToadLiability($draw->bets, $settings);

        // รวมเพื่อใช้ใน heatmap เดิม (backward compat)
        $twoDigitLiability = $this->mergeTwoLiability($twoTopLiability, $twoBottomLiability);
        $threeDigitLiability = $this->mergeThreeLiability($threeTopLiability, $threeToadLiability);

        $maxTwoDigit = max(array_column($twoDigitLiability, 'liability'));
        $maxThreeDigit = max(array_column($threeDigitLiability, 'liability'));

        // เตรียมข้อมูล Heatmap แยกตามประเภท
        $twoTopHeatmapData = $this->prepareHeatmapData($twoTopLiability, 2);
        $twoBottomHeatmapData = $this->prepareHeatmapData($twoBottomLiability, 2);
        $threeTopHeatmapData = $this->prepareHeatmapData($threeTopLiability, 3);
        $threeToadHeatmapData = $this->prepareHeatmapData($threeToadLiability, 3);

        // ค่า max แยกตามประเภท (สำหรับ visualMap)
        $maxTwoTop = max(array_column($twoTopLiability, 'liability'));
        $maxTwoBottom = max(array_column($twoBottomLiability, 'liability'));
        $maxThreeTop = max(array_column($threeTopLiability, 'liability'));
        $maxThreeToad = max(array_column($threeToadLiability, 'liability'));

        // (ยังคงไว้เพื่อ backward compat)
        $twoDigitHeatmapData = $twoTopHeatmapData;
        $threeDigitHeatmapData = $threeTopHeatmapData;

        // Top 10 Exposure แยกตามประเภท
        $topTwoTopExposure = $this->getTopExposure($twoTopLiability, 10, $settings['max_payout_2_digit']);
        $topTwoBottomExposure = $this->getTopExposure($twoBottomLiability, 10, $settings['max_payout_2_digit']);
        $topThreeTopExposure = $this->getTopExposure($threeTopLiability, 10, $settings['max_payout_3_digit']);
        $topThreeToadExposure = $this->getTopExposure($threeToadLiability, 10, $settings['max_payout_3_digit']);

        // backward compat
        $topTwoDigitExposure = $topTwoTopExposure;
        $topThreeDigitExposure = $topThreeTopExposure;

        // สรุปเลขเกิน 100% สำหรับแสดงผลในหน้า (preview)
        $filterOverLimit = function ($liabilityArr, $maxPayout) {
            $result = [];
            foreach ($liabilityArr as $number => $data) {
                if ($data['liability'] <= 0)
                    continue;
                $pct = ($data['liability'] / $maxPayout) * 100;
                if ($pct >= 100) {
                    $result[] = [
                        'number' => $number,
                        'bet_count' => $data['bet_count'],
                        'total_amount' => $data['total_amount'],
                        'liability' => round($data['liability']),
                        'percentage' => round($pct, 1),
                    ];
                }
            }
            usort($result, fn($a, $b) => $b['liability'] - $a['liability']);
            return $result;
        };

        $overLimit2Top = $filterOverLimit($twoTopLiability, $settings['max_payout_2_digit']);
        $overLimit2Bottom = $filterOverLimit($twoBottomLiability, $settings['max_payout_2_digit']);
        $overLimit3Top = $filterOverLimit($threeTopLiability, $settings['max_payout_3_digit']);
        $overLimit3Toad = $filterOverLimit($threeToadLiability, $settings['max_payout_3_digit']);

        // สถิติพื้นฐาน
        $totalTransactions = $draw->bets->count();

        $sales = [
            'two_top' => ['total' => 0, 'count' => 0],
            'two_bottom' => ['total' => 0, 'count' => 0],
            'three_top' => ['total' => 0, 'count' => 0],
            'three_toad' => ['total' => 0, 'count' => 0]
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

        $totalSales = $sales['two_top']['total'] + $sales['two_bottom']['total'] +
            $sales['three_top']['total'] + $sales['three_toad']['total'];

        // Commission
        $commission = $totalSales * ($settings['commission_rate'] / 100);

        // ผลรางวัล
        $result = null;
        if ($draw->is_announced) {
            $totalPayout = $draw->bets->sum(function ($bet) {
                return $bet->payout_top + $bet->payout_bottom + $bet->payout_toad;
            });

            $winnersCount = $draw->bets->filter(function ($bet) {
                return $bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad;
            })->count();

            $netProfit = $totalSales - $totalPayout - $commission;
            $payoutRatio = $totalSales > 0 ? ($totalPayout / $totalSales) * 100 : 0;

            $result = [
                'total_bet' => $totalSales,
                'total_payout' => $totalPayout,
                'commission' => $commission,
                'net_profit' => $netProfit,
                'payout_ratio' => $payoutRatio,
                'winners_count' => $winnersCount,
            ];
        }

        // สรุปผลรายบุคคล
        $customerSummary = $this->getCustomerSummary($draw->bets, $settings['commission_rate']);

        // ดึงรายชื่อลูกค้าทั้งหมดในงวดนี้ (สำหรับ Multi-select)
        $customerNames = $draw->bets()
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('customer_name')
            ->sort()
            ->values();

        // ประวัติการแทง
        $betsQuery = $draw->bets()->with('creator')->whereNull('deleted_at');

        // Filter ชื่อลูกค้า (รองรับ Multi-select)
        if ($request->filled('customer_names')) {
            $betsQuery->whereIn('customer_name', $request->customer_names);
        }

        // Filter เลข
        if ($request->filled('search_number')) {
            $betsQuery->where('number', $request->search_number);
        }

        // Filter สถานะรางวัล
        if ($request->filled('win_status') && $draw->is_announced) {
            if ($request->win_status === 'won') {
                $betsQuery->where(function ($q) {
                    $q->where('is_win_top', true)
                        ->orWhere('is_win_bottom', true)
                        ->orWhere('is_win_toad', true);
                });
            } elseif ($request->win_status === 'lost') {
                $betsQuery->where('is_win_top', false)
                    ->where('is_win_bottom', false)
                    ->where('is_win_toad', false);
            }
        }

        // Filter ประเภทเลข
        if ($request->filled('number_type')) {
            if ($request->number_type === '2_top') {
                $betsQuery->whereRaw('LENGTH(number) = 2')->where('amount_top', '>', 0);
            } elseif ($request->number_type === '2_bottom') {
                $betsQuery->whereRaw('LENGTH(number) = 2')->where('amount_bottom', '>', 0);
            } elseif ($request->number_type === '3_top') {
                $betsQuery->whereRaw('LENGTH(number) = 3')->where('amount_top', '>', 0);
            } elseif ($request->number_type === '3_toad') {
                $betsQuery->whereRaw('LENGTH(number) = 3')->where('amount_toad', '>', 0);
            } elseif ($request->number_type === '2_digit') {
                $betsQuery->whereRaw('LENGTH(number) = 2');
            } elseif ($request->number_type === '3_digit') {
                $betsQuery->whereRaw('LENGTH(number) = 3');
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'customer_name', 'number', 'amount_top', 'amount_bottom', 'amount_toad'];
        if (in_array($sortBy, $allowedSorts)) {
            $betsQuery->orderBy($sortBy, $sortDirection);
        }

        $betsHistory = $betsQuery->paginate(50);

        return view('admin.reports.summary', compact(
            'draw',
            'totalTransactions',
            'sales',
            'totalSales',
            'commission',
            'result',
            'twoTopHeatmapData',
            'twoBottomHeatmapData',
            'threeTopHeatmapData',
            'threeToadHeatmapData',
            'maxTwoTop',
            'maxTwoBottom',
            'maxThreeTop',
            'maxThreeToad',
            'twoDigitHeatmapData',
            'threeDigitHeatmapData',
            'maxTwoDigit',
            'maxThreeDigit',
            'topTwoTopExposure',
            'topTwoBottomExposure',
            'topThreeTopExposure',
            'topThreeToadExposure',
            'topTwoDigitExposure',
            'topThreeDigitExposure',
            'overLimit2Top',
            'overLimit2Bottom',
            'overLimit3Top',
            'overLimit3Toad',
            'betsHistory',
            'customerSummary',
            'customerNames',
            'settings'
        ));
    }

    private function calculateTwoTopLiability($bets, $settings)
    {
        $liability = [];
        for ($i = 0; $i <= 99; $i++) {
            $number = str_pad($i, 2, '0', STR_PAD_LEFT);
            $liability[$number] = ['liability' => 0, 'bet_count' => 0, 'total_amount' => 0];
        }
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 2 && $bet->amount_top > 0) {
                $num = $bet->number;
                $liability[$num]['liability'] += $bet->amount_top * $settings['rate_2_top'];
                $liability[$num]['total_amount'] += $bet->amount_top;
                $liability[$num]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateTwoBottomLiability($bets, $settings)
    {
        $liability = [];
        for ($i = 0; $i <= 99; $i++) {
            $number = str_pad($i, 2, '0', STR_PAD_LEFT);
            $liability[$number] = ['liability' => 0, 'bet_count' => 0, 'total_amount' => 0];
        }
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 2 && $bet->amount_bottom > 0) {
                $num = $bet->number;
                $liability[$num]['liability'] += $bet->amount_bottom * $settings['rate_2_bottom'];
                $liability[$num]['total_amount'] += $bet->amount_bottom;
                $liability[$num]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateThreeTopLiability($bets, $settings)
    {
        $liability = [];
        for ($i = 0; $i <= 999; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $liability[$number] = ['liability' => 0, 'bet_count' => 0, 'total_amount' => 0];
        }
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 3 && $bet->amount_top > 0) {
                $num = $bet->number;
                $liability[$num]['liability'] += $bet->amount_top * $settings['rate_3_top'];
                $liability[$num]['total_amount'] += $bet->amount_top;
                $liability[$num]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateThreeToadLiability($bets, $settings)
    {
        $liability = [];
        for ($i = 0; $i <= 999; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $liability[$number] = ['liability' => 0, 'bet_count' => 0, 'total_amount' => 0];
        }
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 3 && $bet->amount_toad > 0) {
                $toadNumbers = $this->getToadNumbers($bet->number);
                foreach ($toadNumbers as $toadNum) {
                    $liability[$toadNum]['liability'] += $bet->amount_toad * $settings['rate_3_toad'];
                    $liability[$toadNum]['total_amount'] += $bet->amount_toad;
                    $liability[$toadNum]['bet_count']++;
                }
            }
        }
        return $liability;
    }

    private function mergeTwoLiability($top, $bottom)
    {
        $merged = $top;
        foreach ($bottom as $num => $data) {
            $merged[$num]['liability'] += $data['liability'];
            $merged[$num]['total_amount'] += $data['total_amount'];
            $merged[$num]['bet_count'] += $data['bet_count'];
        }
        return $merged;
    }

    private function mergeThreeLiability($top, $toad)
    {
        $merged = $top;
        foreach ($toad as $num => $data) {
            $merged[$num]['liability'] += $data['liability'];
            $merged[$num]['total_amount'] += $data['total_amount'];
            $merged[$num]['bet_count'] += $data['bet_count'];
        }
        return $merged;
    }

    /**
     * ลบรายการแทง (เฉพาะงวดที่ยังไม่ประกาศผล)
     */
    public function deleteBet(Request $request, $betId)
    {
        // ตรวจสอบรหัสลบ
        $deleteCode = Setting::get('delete_code', '');

        if (empty($deleteCode)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาตั้งรหัสลบในหน้าตั้งค่าความเสี่ยงก่อนใช้งาน'
            ], 400);
        }

        $request->validate([
            'delete_code' => 'required|digits:6'
        ], [
            'delete_code.required' => 'กรุณากรอกรหัสลบ',
            'delete_code.digits' => 'รหัสลบต้องเป็นตัวเลข 6 หลัก'
        ]);

        if ($request->delete_code !== $deleteCode) {
            return response()->json([
                'success' => false,
                'message' => 'รหัสลบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง'
            ], 403);
        }

        $bet = LotteryBet::findOrFail($betId);

        // ตรวจสอบว่างวดยังไม่ประกาศผล
        $draw = LotteryDraw::where('draw_date', $bet->draw_date)->first();

        if ($draw && $draw->is_announced) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบได้ เนื่องจากงวดนี้ประกาศผลแล้ว'
            ], 403);
        }

        // Soft delete พร้อมบันทึกผู้ลบและเวลา
        $bet->deleted_at = now();
        $bet->deleted_by = Auth::id();
        $bet->save();

        return response()->json([
            'success' => true,
            'message' => 'ลบรายการเรียบร้อยแล้ว'
        ]);
    }

    private function getCustomerSummary($bets, $commissionRate)
    {
        $summary = [];

        foreach ($bets as $bet) {
            $customer = $bet->customer_name;

            if (!isset($summary[$customer])) {
                $summary[$customer] = [
                    'customer_name' => $customer,
                    'total_bet_before_discount' => 0,
                    'discount' => 0,
                    'total_bet_after_discount' => 0,
                    'total_payout' => 0,
                    'net_amount' => 0,
                    'winning_numbers' => [],
                    'created_by' => $bet->creator ? $bet->creator->name : 'ระบบ',
                    'created_at' => $bet->created_at,
                ];
            }

            $betAmount = $bet->amount_top + $bet->amount_bottom + $bet->amount_toad;
            $summary[$customer]['total_bet_before_discount'] += $betAmount;

            $payout = $bet->payout_top + $bet->payout_bottom + $bet->payout_toad;
            $summary[$customer]['total_payout'] += $payout;

            if ($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad) {
                // คำนวณยอดแทงและยอดจ่ายเฉพาะประเภทที่ถูกรางวัลจริง
                $winBetAmount = 0;
                $winPayout = 0;
                if ($bet->is_win_top) {
                    $winBetAmount += $bet->amount_top;
                    $winPayout += $bet->payout_top;
                }
                if ($bet->is_win_bottom) {
                    $winBetAmount += $bet->amount_bottom;
                    $winPayout += $bet->payout_bottom;
                }
                if ($bet->is_win_toad) {
                    $winBetAmount += $bet->amount_toad;
                    $winPayout += $bet->payout_toad;
                }

                $summary[$customer]['winning_numbers'][] = [
                    'number' => $bet->number,
                    'win_type' => $this->getWinType($bet),
                    'bet_amount' => $winBetAmount,
                    'payout' => $winPayout,
                ];
            }
        }

        foreach ($summary as $customer => &$data) {
            $data['discount'] = $data['total_bet_before_discount'] * ($commissionRate / 100);
            $data['total_bet_after_discount'] = $data['total_bet_before_discount'] - $data['discount'];
            $data['net_amount'] = $data['total_bet_after_discount'] - $data['total_payout'];
        }

        uasort($summary, function ($a, $b) {
            return $b['total_payout'] - $a['total_payout'];
        });

        return $summary;
    }

    private function getWinType($bet)
    {
        $types = [];
        if ($bet->is_win_top) {
            $types[] = strlen($bet->number) === 2 ? '2 ตัวบน' : '3 ตัวตรง';
        }
        if ($bet->is_win_bottom) {
            $types[] = '2 ตัวล่าง';
        }
        if ($bet->is_win_toad) {
            $types[] = '3 ตัวโต๊ด';
        }
        return implode(', ', $types);
    }

    /**
     * คำนวณ Liability 2 ตัว - แก้ไขให้นับจำนวนใบถูกต้อง
     */
    private function calculateTwoDigitLiability($bets, $settings)
    {
        $liability = [];

        for ($i = 0; $i <= 99; $i++) {
            $number = str_pad($i, 2, '0', STR_PAD_LEFT);
            $liability[$number] = [
                'liability' => 0,
                'bet_count' => 0,
                'total_amount' => 0
            ];
        }

        // นับจำนวนใบจริง (แยกนับ top และ bottom)
        $betCounts = [];
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 2) {
                $num = $bet->number;

                if (!isset($betCounts[$num])) {
                    $betCounts[$num] = 0;
                }

                // นับ 1 ใบต่อ 1 bet record (ไม่ว่าจะแทงบน ล่าง หรือทั้งคู่)
                $betCounts[$num]++;

                if ($bet->amount_top > 0) {
                    $liability[$num]['liability'] += $bet->amount_top * $settings['rate_2_top'];
                    $liability[$num]['total_amount'] += $bet->amount_top;
                }

                if ($bet->amount_bottom > 0) {
                    $liability[$num]['liability'] += $bet->amount_bottom * $settings['rate_2_bottom'];
                    $liability[$num]['total_amount'] += $bet->amount_bottom;
                }
            }
        }

        // อัพเดทจำนวนใบ
        foreach ($betCounts as $num => $count) {
            $liability[$num]['bet_count'] = $count;
            // echo $count;
        }

        return $liability;
    }

    /**
     * คำนวณ Liability 3 ตัว - แก้ไขให้นับจำนวนใบถูกต้อง
     */
    private function calculateThreeDigitLiability($bets, $settings)
    {
        $liability = [];

        for ($i = 0; $i <= 999; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $liability[$number] = [
                'liability' => 0,
                'bet_count' => 0,
                'total_amount' => 0
            ];
        }

        // นับจำนวนใบจริง
        $betCounts = [];
        foreach ($bets as $bet) {
            if (strlen($bet->number) === 3) {
                $num = $bet->number;

                // นับใบสำหรับเลขหลัก
                if (!isset($betCounts[$num])) {
                    $betCounts[$num] = 0;
                }
                $betCounts[$num]++;

                // 3 ตัวตรง
                if ($bet->amount_top > 0) {
                    $liability[$num]['liability'] += $bet->amount_top * $settings['rate_3_top'];
                    $liability[$num]['total_amount'] += $bet->amount_top;
                }

                // 3 ตัวโต๊ด - กระจายไปยังเลขโต๊ด
                if ($bet->amount_toad > 0) {
                    $toadNumbers = $this->getToadNumbers($num);
                    foreach ($toadNumbers as $toadNum) {
                        $liability[$toadNum]['liability'] += $bet->amount_toad * $settings['rate_3_toad'];
                        $liability[$toadNum]['total_amount'] += $bet->amount_toad;

                        // นับใบสำหรับโต๊ด
                        if (!isset($betCounts[$toadNum])) {
                            $betCounts[$toadNum] = 0;
                        }
                        $betCounts[$toadNum]++;
                    }
                }
            }
        }

        // อัพเดทจำนวนใบ
        foreach ($betCounts as $num => $count) {
            $liability[$num]['bet_count'] = $count;
        }

        return $liability;
    }

    private function getToadNumbers($number)
    {
        $digits = str_split($number);
        $permutations = [];
        $this->generatePermutations($digits, 0, count($digits) - 1, $permutations);

        return array_unique(array_map(function ($perm) {
            return implode('', $perm);
        }, $permutations));
    }

    private function generatePermutations(&$arr, $left, $right, &$result)
    {
        if ($left == $right) {
            $result[] = $arr;
        } else {
            for ($i = $left; $i <= $right; $i++) {
                $temp = $arr[$left];
                $arr[$left] = $arr[$i];
                $arr[$i] = $temp;

                $this->generatePermutations($arr, $left + 1, $right, $result);

                $temp = $arr[$left];
                $arr[$left] = $arr[$i];
                $arr[$i] = $temp;
            }
        }
    }

    private function prepareHeatmapData($liability, $digits)
    {
        $data = [];

        if ($digits === 2) {
            for ($y = 0; $y < 10; $y++) {
                for ($x = 0; $x < 10; $x++) {
                    $number = str_pad(($y * 10 + $x), 2, '0', STR_PAD_LEFT);
                    $data[] = [
                        $x,
                        $y,
                        round($liability[$number]['liability'], 2),
                        $liability[$number]['bet_count'],
                        $liability[$number]['total_amount'],  // index 4: ยอดแทงจริง
                    ];
                }
            }
        } else {
            for ($y = 0; $y < 25; $y++) {
                for ($x = 0; $x < 40; $x++) {
                    $index = $y * 40 + $x;
                    if ($index <= 999) {
                        $number = str_pad($index, 3, '0', STR_PAD_LEFT);
                        $data[] = [
                            $x,
                            $y,
                            round($liability[$number]['liability'], 2),
                            $liability[$number]['bet_count'],
                            $liability[$number]['total_amount'],
                        ];
                    }
                }
            }
        }

        return $data;
    }

    private function getTopExposure($liability, $limit, $maxPayout)
    {
        uasort($liability, function ($a, $b) {
            return $b['liability'] - $a['liability'];
        });

        $top = array_slice($liability, 0, $limit, true);

        $result = [];
        foreach ($top as $number => $data) {
            $percentage = ($data['liability'] / $maxPayout) * 100;

            $result[] = [
                'number' => $number,
                'liability' => $data['liability'],
                'bet_count' => $data['bet_count'],
                'total_amount' => $data['total_amount'],
                'percentage' => $percentage,
                'status' => $this->getExposureStatus($percentage),
                'should_transfer' => $percentage >= 100
            ];
        }

        return $result;
    }

    private function getExposureStatus($percentage)
    {
        if ($percentage >= 100) {
            return 'critical';
        } elseif ($percentage >= 50) {
            return 'warning';
        } else {
            return 'safe';
        }
    }
    public function exportExcel(Request $request, $drawId)
    {
        $draw = \App\Models\LotteryDraw::findOrFail($drawId);

        $query = \App\Models\LotteryBet::with(['creator'])
            ->whereNull('deleted_at')
            ->where('draw_date', $draw->draw_date);

        // Filter เหมือนกับ summary method
        if ($request->has('customer_names') && count($request->customer_names)) {
            $query->whereIn('customer_name', $request->customer_names);
        }
        if ($request->search_number) {
            $query->where('number', 'like', '%' . $request->search_number . '%');
        }
        if ($request->win_status === 'won') {
            $query->where(function ($q) {
                $q->where('is_win_top', true)->orWhere('is_win_bottom', true)->orWhere('is_win_toad', true);
            });
        } elseif ($request->win_status === 'lost') {
            $query->where('is_win_top', false)->where('is_win_bottom', false)->where('is_win_toad', false);
        }
        if ($request->number_type === '2_digit') {
            $query->whereRaw('LENGTH(number) = 2');
        } elseif ($request->number_type === '3_digit') {
            $query->whereRaw('LENGTH(number) = 3');
        }

        $bets = $query->orderBy('customer_name')->orderBy('created_at')->get();

        $drawDateLabel = \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y');
        $rows = [];
        $rows[] = ['ชื่อลูกค้า', 'เลข', 'บน', 'ล่าง', 'โต๊ด', 'รวม (฿)', 'บันทึกโดย', 'วันที่บันทึก', 'ผล'];

        foreach ($bets as $bet) {
            $total = $bet->amount_top + $bet->amount_bottom + $bet->amount_toad;
            $createdAt = \Carbon\Carbon::parse($bet->created_at)->format('d/m/y H:i');
            $createdBy = $bet->creator ? $bet->creator->name : '-';

            if ($draw->is_announced) {
                if ($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad) {
                    $payout = $bet->payout_top + $bet->payout_bottom + $bet->payout_toad;
                    $result = 'ถูก ' . number_format($payout, 0) . ' ฿';
                } else {
                    $result = 'ไม่ถูก';
                }
            } else {
                $result = 'รอประกาศ';
            }

            $rows[] = [
                $bet->customer_name,
                $bet->number,
                $bet->amount_top > 0 ? $bet->amount_top : '',
                $bet->amount_bottom > 0 ? $bet->amount_bottom : '',
                $bet->amount_toad > 0 ? $bet->amount_toad : '',
                $total,
                $createdBy,
                $createdAt,
                $result,
            ];
        }

        $filename = 'บัญชีแทงหวย_' . \Carbon\Carbon::parse($draw->draw_date)->format('Y-m-d') . '.csv';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "ï»¿");
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
    public function exportCustomerSummary(Request $request, $drawId)
    {
        $draw = \App\Models\LotteryDraw::findOrFail($drawId);
        $settings = [
            'commission_rate' => \App\Models\Setting::get('commission_rate', 10),
        ];

        $bets = \App\Models\LotteryBet::with(['creator'])
            ->whereNull('deleted_at')
            ->where('draw_date', $draw->draw_date)
            ->get();

        $customerSummary = $this->getCustomerSummary($bets, $settings['commission_rate']);
        $drawDateLabel = \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y');

        $rows = [];
        // Header
        $rows[] = ['ชื่อลูกค้า', 'บันทึกโดย', 'ยอดซื้อ (฿)', 'ส่วนลด (฿)', 'หลังหัก (฿)', 'รางวัล (฿)', 'สุทธิ', 'เลขที่ถูก'];

        foreach ($customerSummary as $s) {
            $winningStr = '';
            if (!empty($s['winning_numbers'])) {
                $parts = [];
                foreach ($s['winning_numbers'] as $w) {
                    $parts[] = $w['number'] . '(' . $w['win_type'] . ')=' . number_format($w['payout'], 0);
                }
                $winningStr = implode(', ', $parts);
            }

            $netLabel = $s['net_amount'] < 0
                ? 'จ่าย ' . number_format(abs($s['net_amount']), 0)
                : 'รับ ' . number_format($s['net_amount'], 0);

            $rows[] = [
                $s['customer_name'],
                $s['created_by'],
                number_format($s['total_bet_before_discount'], 0),
                number_format($s['discount'], 0),
                number_format($s['total_bet_after_discount'], 0),
                number_format($s['total_payout'], 0),
                $netLabel,
                $winningStr,
            ];
        }

        $filename = 'สรุปรายบุคคล_' . \Carbon\Carbon::parse($draw->draw_date)->format('Y-m-d') . '.csv';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "ï»¿");
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

    /**
     * Export เลขที่เกิน 100% ทั้ง 4 ประเภท เป็น CSV
     */
    public function exportOverLimit(Request $request, $drawId)
    {
        $draw = \App\Models\LotteryDraw::findOrFail($drawId);

        $settings = [
            'max_payout_2_digit' => \App\Models\Setting::get('max_payout_2_digit', 50000),
            'max_payout_3_digit' => \App\Models\Setting::get('max_payout_3_digit', 100000),
            'rate_2_top' => \App\Models\Setting::get('rate_2_top', 90),
            'rate_2_bottom' => \App\Models\Setting::get('rate_2_bottom', 90),
            'rate_3_top' => \App\Models\Setting::get('rate_3_top', 900),
            'rate_3_toad' => \App\Models\Setting::get('rate_3_toad', 120),
            'commission_rate' => \App\Models\Setting::get('commission_rate', 10),
        ];

        $bets = \App\Models\LotteryBet::whereNull('deleted_at')
            ->where('draw_date', $draw->draw_date)
            ->get();

        // คำนวณ liability แยก 4 ประเภท
        $twoTopLiab = $this->calculateTwoTopLiability($bets, $settings);
        $twoBottomLiab = $this->calculateTwoBottomLiability($bets, $settings);
        $threeTopLiab = $this->calculateThreeTopLiability($bets, $settings);
        $threeToadLiab = $this->calculateThreeToadLiability($bets, $settings);

        $maxPay2 = $settings['max_payout_2_digit'];
        $maxPay3 = $settings['max_payout_3_digit'];

        // กรองเฉพาะที่เกิน 100%
        $filterOver = function ($liabilityArr, $maxPayout) {
            $result = [];
            foreach ($liabilityArr as $number => $data) {
                if ($data['liability'] <= 0)
                    continue;
                $pct = ($data['liability'] / $maxPayout) * 100;
                if ($pct >= 100) {
                    $result[] = [
                        'number' => $number,
                        'bet_count' => $data['bet_count'],
                        'total_amount' => $data['total_amount'],
                        'liability' => round($data['liability']),
                        'percentage' => round($pct, 2),
                    ];
                }
            }
            usort($result, fn($a, $b) => $b['liability'] - $a['liability']);
            return $result;
        };

        $over2Top = $filterOver($twoTopLiab, $maxPay2);
        $over2Bottom = $filterOver($twoBottomLiab, $maxPay2);
        $over3Top = $filterOver($threeTopLiab, $maxPay3);
        $over3Toad = $filterOver($threeToadLiab, $maxPay3);

        $drawDateLabel = \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y');
        $rows = [];

        // Section headers + data helper
        $addSection = function ($title, $data, $maxPayout) use (&$rows, $drawDateLabel) {
            $rows[] = [];
            $rows[] = ["=== $title ===", "งวด $drawDateLabel", "เพดาน: " . number_format($maxPayout, 0) . " บาท"];
            $rows[] = ['เลข', 'จำนวนใบ', 'ยอดซื้อ (฿)', 'ยอดจ่าย (฿)', '% ของเพดาน'];
            if (empty($data)) {
                $rows[] = ['-', '-', '-', '-', 'ไม่มีเลขเกินเพดาน'];
            } else {
                foreach ($data as $item) {
                    $rows[] = [
                        $item['number'],
                        $item['bet_count'],
                        number_format($item['total_amount'], 0),
                        number_format($item['liability'], 0),
                        number_format($item['percentage'], 2) . '%',
                    ];
                }
            }
        };

        $addSection('2 ตัวบน (เกิน 100%)', $over2Top, $maxPay2);
        $addSection('2 ตัวล่าง (เกิน 100%)', $over2Bottom, $maxPay2);
        $addSection('3 ตัวบน (เกิน 100%)', $over3Top, $maxPay3);
        $addSection('3 ตัวโต๊ด (เกิน 100%)', $over3Toad, $maxPay3);

        $filename = 'เลขเกินเพดาน_' . \Carbon\Carbon::parse($draw->draw_date)->format('Y-m-d') . '.csv';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
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