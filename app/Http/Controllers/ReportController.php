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
        $draws = LotteryDraw::with('announcedBy')->orderBy('draw_date','desc')->paginate(20);
        return view('admin.reports.index', compact('draws'));
    }

    public function summary(Request $request, $drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => fn($q) => $q->whereNull('deleted_at')->with('creator')
        ])->findOrFail($drawId);

        $settings = [
            'max_payout_2_digit'  => Setting::get('max_payout_2_digit', 50000),
            'max_payout_3_digit'  => Setting::get('max_payout_3_digit', 100000),
            'max_payout_3_toad'   => Setting::get('max_payout_3_toad', 50000),
            'max_payout_3_bottom' => Setting::get('max_payout_3_bottom', 50000),
            'rate_2_top'          => Setting::get('rate_2_top', 90),
            'rate_2_bottom'       => Setting::get('rate_2_bottom', 90),
            'rate_3_top'          => Setting::get('rate_3_top', 900),
            'rate_3_toad'         => Setting::get('rate_3_toad', 120),
            'rate_3_bottom'       => Setting::get('rate_3_bottom', 500),
            'commission_rate'     => Setting::get('commission_rate', 10),
        ];

        // ── Liability Calculations ──
        $twoTopLiability    = $this->calculateTwoTopLiability($draw->bets, $settings);
        $twoBottomLiability = $this->calculateTwoBottomLiability($draw->bets, $settings);
        $threeTopLiability  = $this->calculateThreeTopLiability($draw->bets, $settings);
        $threeToadLiability = $this->calculateThreeToadLiability($draw->bets, $settings);
        $threeBottomLiability = $this->calculateThreeBottomLiability($draw->bets, $settings);

        // backward compat merged
        $twoDigitLiability   = $this->mergeTwoLiability($twoTopLiability, $twoBottomLiability);
        $threeDigitLiability = $this->mergeThreeLiability($threeTopLiability, $threeToadLiability);

        $maxTwoDigit   = max(array_column($twoDigitLiability, 'liability'));
        $maxThreeDigit = max(array_column($threeDigitLiability, 'liability'));

        // Heatmap data
        $twoTopHeatmapData    = $this->prepareHeatmapData($twoTopLiability, 2);
        $twoBottomHeatmapData = $this->prepareHeatmapData($twoBottomLiability, 2);
        $threeTopHeatmapData  = $this->prepareHeatmapData($threeTopLiability, 3);
        $threeToadHeatmapData = $this->prepareHeatmapData($threeToadLiability, 3);
        $threeBottomHeatmapData = $this->prepareHeatmapData($threeBottomLiability, 3);

        $maxTwoTop    = max(array_column($twoTopLiability, 'liability'));
        $maxTwoBottom = max(array_column($twoBottomLiability, 'liability'));
        $maxThreeTop  = max(array_column($threeTopLiability, 'liability'));
        $maxThreeToad = max(array_column($threeToadLiability, 'liability'));
        $maxThreeBottom = max(array_column($threeBottomLiability, 'liability'));

        $twoDigitHeatmapData   = $twoTopHeatmapData;
        $threeDigitHeatmapData = $threeTopHeatmapData;

        // Top 10 Exposure
        $topTwoTopExposure    = $this->getTopExposure($twoTopLiability, 10, $settings['max_payout_2_digit']);
        $topTwoBottomExposure = $this->getTopExposure($twoBottomLiability, 10, $settings['max_payout_2_digit']);
        $topThreeTopExposure  = $this->getTopExposure($threeTopLiability, 10, $settings['max_payout_3_digit']);
        // โต๊ด: ใช้เพดาน max_payout_3_digit สำหรับ %
        $topThreeToadExposure = $this->getTopExposure($threeToadLiability, 10, $settings['max_payout_3_digit']);
        $topThreeBottomExposure = $this->getTopExposure($threeBottomLiability, 10, $settings['max_payout_3_bottom']);

        // เพิ่ม individual_payout สำหรับโต๊ด = total_amount × rate (ต่อ perm)
        $rateToad = $settings['rate_3_toad'];
        $topThreeToadExposure = array_map(function($item) use ($rateToad) {
            $item['individual_payout'] = round($item['total_amount'] * $rateToad);
            return $item;
        }, $topThreeToadExposure);

        $topTwoDigitExposure   = $topTwoTopExposure;
        $topThreeDigitExposure = $topThreeTopExposure;

        // Over limit filter
        $filterOverLimit = function ($liabilityArr, $maxPayout) {
            $result = [];
            foreach ($liabilityArr as $number => $data) {
                if ($data['liability'] <= 0) continue;
                $pct = ($data['liability'] / $maxPayout) * 100;
                if ($pct >= 100) {
                    $result[] = [
                        'number'     => $number,
                        'bet_count'  => $data['bet_count'],
                        'total_amount' => $data['total_amount'],
                        'liability'  => round($data['liability']),
                        'percentage' => round($pct, 1),
                    ];
                }
            }
            usort($result, fn($a,$b) => $b['liability'] - $a['liability']);
            return $result;
        };

        $overLimit2Top    = $filterOverLimit($twoTopLiability,    $settings['max_payout_2_digit']);
        $overLimit2Bottom = $filterOverLimit($twoBottomLiability, $settings['max_payout_2_digit']);
        $overLimit3Top    = $filterOverLimit($threeTopLiability,  $settings['max_payout_3_digit']);
        // โต๊ด: ใช้เพดาน max_payout_3_digit
        $overLimit3Toad   = $filterOverLimit($threeToadLiability, $settings['max_payout_3_digit']);
        $overLimit3Bottom = $filterOverLimit($threeBottomLiability, $settings['max_payout_3_bottom']);

        // เพิ่ม individual_payout สำหรับโต๊ด over-limit
        $overLimit3Toad = array_map(function($item) use ($rateToad) {
            $item['individual_payout'] = round($item['total_amount'] * $rateToad);
            return $item;
        }, $overLimit3Toad);

        // Sales stats
        $totalTransactions = $draw->bets->count();
        $sales = [
            'two_top'      => ['total'=>0,'count'=>0],
            'two_bottom'   => ['total'=>0,'count'=>0],
            'three_top'    => ['total'=>0,'count'=>0],
            'three_toad'   => ['total'=>0,'count'=>0],
            'three_bottom' => ['total'=>0,'count'=>0],
        ];
        foreach ($draw->bets as $bet) {
            $l = strlen($bet->number);
            if ($bet->amount_top    > 0 && $l===2) { $sales['two_top']['total']    += $bet->amount_top;    $sales['two_top']['count']++; }
            if ($bet->amount_bottom > 0 && $l===2) { $sales['two_bottom']['total'] += $bet->amount_bottom; $sales['two_bottom']['count']++; }
            if ($bet->amount_top    > 0 && $l===3) { $sales['three_top']['total']  += $bet->amount_top;    $sales['three_top']['count']++; }
            if ($bet->amount_toad   > 0 && $l===3) { $sales['three_toad']['total'] += $bet->amount_toad;   $sales['three_toad']['count']++; }
            if (($bet->amount_bottom_3??0)>0&&$l===3){ $sales['three_bottom']['total'] += $bet->amount_bottom_3; $sales['three_bottom']['count']++; }
        }
        $totalSales = array_sum(array_column($sales, 'total'));
        $commission = $totalSales * ($settings['commission_rate'] / 100);

        // Result
        $result = null;
        if ($draw->is_announced) {
            $totalPayout = $draw->bets->sum(fn($b) =>
                $b->payout_top + $b->payout_bottom + $b->payout_toad + ($b->payout_bottom_3??0)
            );
            $winnersCount = $draw->bets->filter(fn($b) =>
                $b->is_win_top||$b->is_win_bottom||$b->is_win_toad||($b->is_win_bottom_3??false)
            )->count();
            $netProfit   = $totalSales - $totalPayout - $commission;
            $payoutRatio = $totalSales > 0 ? ($totalPayout / $totalSales) * 100 : 0;
            $result = [
                'total_bet'     => $totalSales,
                'total_payout'  => $totalPayout,
                'commission'    => $commission,
                'net_profit'    => $netProfit,
                'payout_ratio'  => $payoutRatio,
                'winners_count' => $winnersCount,
            ];
        }

        $customerSummary = $this->getCustomerSummary($draw->bets, $settings['commission_rate']);

        $customerNames = $draw->bets()->whereNull('deleted_at')->distinct()->pluck('customer_name')->sort()->values();

        // Bet history with filters
        $betsQuery = $draw->bets()->with('creator')->whereNull('deleted_at');

        if ($request->filled('customer_names')) $betsQuery->whereIn('customer_name', $request->customer_names);
        if ($request->filled('search_number'))  $betsQuery->where('number', $request->search_number);

        if ($request->filled('win_status') && $draw->is_announced) {
            if ($request->win_status === 'won') {
                $betsQuery->where(fn($q) => $q->where('is_win_top',true)->orWhere('is_win_bottom',true)->orWhere('is_win_toad',true)->orWhere('is_win_bottom_3',true));
            } elseif ($request->win_status === 'lost') {
                $betsQuery->where('is_win_top',false)->where('is_win_bottom',false)->where('is_win_toad',false)->where('is_win_bottom_3',false);
            }
        }

        if ($request->filled('number_type')) {
            switch ($request->number_type) {
                case '2_top':    $betsQuery->whereRaw('LENGTH(number)=2')->where('amount_top','>',0); break;
                case '2_bottom': $betsQuery->whereRaw('LENGTH(number)=2')->where('amount_bottom','>',0); break;
                case '3_top':    $betsQuery->whereRaw('LENGTH(number)=3')->where('amount_top','>',0); break;
                case '3_toad':   $betsQuery->whereRaw('LENGTH(number)=3')->where('amount_toad','>',0); break;
                case '3_bottom': $betsQuery->whereRaw('LENGTH(number)=3')->where('amount_bottom_3','>',0); break;
                case '2_digit':  $betsQuery->whereRaw('LENGTH(number)=2'); break;
                case '3_digit':  $betsQuery->whereRaw('LENGTH(number)=3'); break;
            }
        }

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_direction'), ['asc','desc']) ? $request->get('sort_direction') : 'desc';
        $allowed = ['created_at','customer_name','number','amount_top','amount_bottom','amount_toad','amount_bottom_3','total_amount'];
        if (in_array($sortBy, $allowed)) {
            if ($sortBy === 'total_amount') {
                $betsQuery->orderByRaw("(amount_top + amount_bottom + amount_toad + COALESCE(amount_bottom_3,0)) $sortDir");
            } else {
                $betsQuery->orderBy($sortBy, $sortDir);
            }
        } else {
            // default: ล่าสุดก่อน
            $betsQuery->orderBy('created_at', 'desc');
        }

        $betsHistory = $betsQuery->paginate(50);

        return view('admin.reports.summary', compact(
            'draw','totalTransactions','sales','totalSales','commission','result',
            'twoTopHeatmapData','twoBottomHeatmapData','threeTopHeatmapData',
            'threeToadHeatmapData','threeBottomHeatmapData',
            'maxTwoTop','maxTwoBottom','maxThreeTop','maxThreeToad','maxThreeBottom',
            'twoDigitHeatmapData','threeDigitHeatmapData','maxTwoDigit','maxThreeDigit',
            'topTwoTopExposure','topTwoBottomExposure','topThreeTopExposure',
            'topThreeToadExposure','topThreeBottomExposure',
            'topTwoDigitExposure','topThreeDigitExposure',
            'overLimit2Top','overLimit2Bottom','overLimit3Top','overLimit3Toad','overLimit3Bottom',
            'betsHistory','customerSummary','customerNames','settings'
        ));
    }

    // ── Liability Calculators ──

    private function calculateTwoTopLiability($bets, $settings)
    {
        $liability = [];
        for ($i=0;$i<=99;$i++) { $n=str_pad($i,2,'0',STR_PAD_LEFT); $liability[$n]=['liability'=>0,'bet_count'=>0,'total_amount'=>0]; }
        foreach ($bets as $bet) {
            if (strlen($bet->number)===2 && $bet->amount_top>0) {
                $n=$bet->number;
                $liability[$n]['liability']    += $bet->amount_top*$settings['rate_2_top'];
                $liability[$n]['total_amount'] += $bet->amount_top;
                $liability[$n]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateTwoBottomLiability($bets, $settings)
    {
        $liability = [];
        for ($i=0;$i<=99;$i++) { $n=str_pad($i,2,'0',STR_PAD_LEFT); $liability[$n]=['liability'=>0,'bet_count'=>0,'total_amount'=>0]; }
        foreach ($bets as $bet) {
            if (strlen($bet->number)===2 && $bet->amount_bottom>0) {
                $n=$bet->number;
                $liability[$n]['liability']    += $bet->amount_bottom*$settings['rate_2_bottom'];
                $liability[$n]['total_amount'] += $bet->amount_bottom;
                $liability[$n]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateThreeTopLiability($bets, $settings)
    {
        $liability = [];
        for ($i=0;$i<=999;$i++) { $n=str_pad($i,3,'0',STR_PAD_LEFT); $liability[$n]=['liability'=>0,'bet_count'=>0,'total_amount'=>0]; }
        foreach ($bets as $bet) {
            if (strlen($bet->number)===3 && $bet->amount_top>0) {
                $n=$bet->number;
                $liability[$n]['liability']    += $bet->amount_top*$settings['rate_3_top'];
                $liability[$n]['total_amount'] += $bet->amount_top;
                $liability[$n]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateThreeToadLiability($bets, $settings)
    {
        $liability = [];
        for ($i=0;$i<=999;$i++) { $n=str_pad($i,3,'0',STR_PAD_LEFT); $liability[$n]=['liability'=>0,'bet_count'=>0,'total_amount'=>0]; }
        foreach ($bets as $bet) {
            if (strlen($bet->number)===3 && $bet->amount_toad>0) {
                $toadNumbers = $this->getToadNumbers($bet->number);
                // LIABILITY spread ไปทุก permutation (โต๊ดจ่ายถ้าผลตรงกับ perm ใดก็ได้)
                foreach ($toadNumbers as $tn) {
                    $liability[$tn]['liability'] += $bet->amount_toad * $settings['rate_3_toad'];
                }
                // bet_count และ total_amount นับเฉพาะเลขที่แทงตรง (ไม่ spread)
                // เพื่อให้แต่ละ perm แสดงว่ามีคนแทงเลขนั้นตรงๆ กี่ใบ/เท่าไหร่
                $liability[$bet->number]['total_amount'] += $bet->amount_toad;
                $liability[$bet->number]['bet_count']++;
            }
        }
        return $liability;
    }

    private function calculateThreeBottomLiability($bets, $settings)
    {
        $liability = [];
        for ($i=0;$i<=999;$i++) { $n=str_pad($i,3,'0',STR_PAD_LEFT); $liability[$n]=['liability'=>0,'bet_count'=>0,'total_amount'=>0]; }
        foreach ($bets as $bet) {
            if (strlen($bet->number)===3 && ($bet->amount_bottom_3??0)>0) {
                $n=$bet->number;
                $liability[$n]['liability']    += $bet->amount_bottom_3 * $settings['rate_3_bottom'];
                $liability[$n]['total_amount'] += $bet->amount_bottom_3;
                $liability[$n]['bet_count']++;
            }
        }
        return $liability;
    }

    private function mergeTwoLiability($top, $bottom)
    {
        $merged = $top;
        foreach ($bottom as $n => $d) {
            $merged[$n]['liability']    += $d['liability'];
            $merged[$n]['total_amount'] += $d['total_amount'];
            $merged[$n]['bet_count']    += $d['bet_count'];
        }
        return $merged;
    }

    private function mergeThreeLiability($top, $toad)
    {
        $merged = $top;
        foreach ($toad as $n => $d) {
            $merged[$n]['liability']    += $d['liability'];
            $merged[$n]['total_amount'] += $d['total_amount'];
            $merged[$n]['bet_count']    += $d['bet_count'];
        }
        return $merged;
    }

    private function prepareHeatmapData($liability, $digits)
    {
        $data = [];
        if ($digits === 2) {
            for ($y=0;$y<10;$y++) for ($x=0;$x<10;$x++) {
                $n = str_pad($y*10+$x,2,'0',STR_PAD_LEFT);
                $data[] = [$x,$y,round($liability[$n]['liability'],2),$liability[$n]['bet_count'],$liability[$n]['total_amount']];
            }
        } else {
            for ($y=0;$y<25;$y++) for ($x=0;$x<40;$x++) {
                $idx = $y*40+$x;
                if ($idx<=999) {
                    $n = str_pad($idx,3,'0',STR_PAD_LEFT);
                    $data[] = [$x,$y,round($liability[$n]['liability'],2),$liability[$n]['bet_count'],$liability[$n]['total_amount']];
                }
            }
        }
        return $data;
    }

    private function getTopExposure($liability, $limit, $maxPayout)
    {
        uasort($liability, fn($a,$b) => $b['liability']-$a['liability']);
        $top    = array_slice($liability, 0, $limit, true);
        $result = [];
        foreach ($top as $number => $data) {
            $pct      = ($data['liability'] / $maxPayout) * 100;
            $result[] = [
                'number'          => $number,
                'liability'       => $data['liability'],
                'bet_count'       => $data['bet_count'],
                'total_amount'    => $data['total_amount'],
                'percentage'      => $pct,
                'status'          => $this->getExposureStatus($pct),
                'should_transfer' => $pct >= 100,
            ];
        }
        return $result;
    }

    private function getExposureStatus($pct)
    {
        if ($pct >= 100) return 'critical';
        if ($pct >= 50)  return 'warning';
        return 'safe';
    }

    // ── Delete Bet ──
    public function deleteBet(Request $request, $betId)
    {
        $deleteCode = Setting::get('delete_code', '');
        if (empty($deleteCode)) return response()->json(['success'=>false,'message'=>'กรุณาตั้งรหัสลบก่อนใช้งาน'],400);

        $request->validate(['delete_code'=>'required|digits:6'],['delete_code.required'=>'กรุณากรอกรหัสลบ','delete_code.digits'=>'รหัสลบต้องเป็นตัวเลข 6 หลัก']);
        if ($request->delete_code !== $deleteCode) return response()->json(['success'=>false,'message'=>'รหัสลบไม่ถูกต้อง'],403);

        $bet  = LotteryBet::findOrFail($betId);
        $draw = LotteryDraw::where('draw_date', $bet->draw_date)->first();
        if ($draw && $draw->is_announced) return response()->json(['success'=>false,'message'=>'ไม่สามารถลบได้ เนื่องจากงวดนี้ประกาศผลแล้ว'],403);

        $bet->deleted_at = now();
        $bet->deleted_by = Auth::id();
        $bet->save();
        return response()->json(['success'=>true,'message'=>'ลบรายการเรียบร้อยแล้ว']);
    }

    // ── Customer Summary ──
    private function getCustomerSummary($bets, $commissionRate)
    {
        $summary = [];
        foreach ($bets as $bet) {
            $customer = $bet->customer_name;
            if (!isset($summary[$customer])) {
                $summary[$customer] = [
                    'customer_name'            => $customer,
                    'total_bet_before_discount'=> 0,
                    'discount'                 => 0,
                    'total_bet_after_discount' => 0,
                    'total_payout'             => 0,
                    'net_amount'               => 0,
                    'winning_numbers'          => [],
                    'created_by'               => $bet->creator ? $bet->creator->name : 'ระบบ',
                    'created_at'               => $bet->created_at,
                ];
            }
            $betAmount = $bet->amount_top + $bet->amount_bottom + $bet->amount_toad + ($bet->amount_bottom_3??0);
            $summary[$customer]['total_bet_before_discount'] += $betAmount;

            $payout = $bet->payout_top + $bet->payout_bottom + $bet->payout_toad + ($bet->payout_bottom_3??0);
            $summary[$customer]['total_payout'] += $payout;

            if ($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad || ($bet->is_win_bottom_3??false)) {
                $winBetAmount = 0; $winPayout = 0;
                if ($bet->is_win_top)                      { $winBetAmount += $bet->amount_top;      $winPayout += $bet->payout_top; }
                if ($bet->is_win_bottom)                   { $winBetAmount += $bet->amount_bottom;   $winPayout += $bet->payout_bottom; }
                if ($bet->is_win_toad)                     { $winBetAmount += $bet->amount_toad;     $winPayout += $bet->payout_toad; }
                if ($bet->is_win_bottom_3??false)          { $winBetAmount += $bet->amount_bottom_3; $winPayout += $bet->payout_bottom_3; }
                $summary[$customer]['winning_numbers'][] = [
                    'number'     => $bet->number,
                    'win_type'   => $this->getWinType($bet),
                    'bet_amount' => $winBetAmount,
                    'payout'     => $winPayout,
                ];
            }
        }
        foreach ($summary as $customer => &$data) {
            $data['discount']                 = $data['total_bet_before_discount'] * ($commissionRate / 100);
            $data['total_bet_after_discount'] = $data['total_bet_before_discount'] - $data['discount'];
            $data['net_amount']               = $data['total_bet_after_discount'] - $data['total_payout'];
        }
        uasort($summary, fn($a,$b) => $b['total_payout'] - $a['total_payout']);
        return $summary;
    }

    private function getWinType($bet)
    {
        $types = [];
        if ($bet->is_win_top)                  $types[] = strlen($bet->number)===2 ? '2 ตัวบน' : '3 ตัวตรง';
        if ($bet->is_win_bottom)               $types[] = '2 ตัวล่าง';
        if ($bet->is_win_toad)                 $types[] = '3 ตัวโต๊ด';
        if ($bet->is_win_bottom_3??false)      $types[] = '3 ตัวล่าง';
        return implode(', ', $types);
    }

    // ── Toad helpers ──
    private function getToadNumbers($number)
    {
        $digits = str_split($number);
        $perms  = [];
        $this->generatePermutations($digits, 0, count($digits)-1, $perms);
        return array_unique(array_map(fn($p) => implode('',$p), $perms));
    }

    private function generatePermutations(&$arr, $left, $right, &$result)
    {
        if ($left == $right) { $result[] = $arr; return; }
        for ($i=$left;$i<=$right;$i++) {
            [$arr[$left],$arr[$i]] = [$arr[$i],$arr[$left]];
            $this->generatePermutations($arr,$left+1,$right,$result);
            [$arr[$left],$arr[$i]] = [$arr[$i],$arr[$left]];
        }
    }

    // ── Legacy calculation methods (kept for backward compat) ──
    private function calculateTwoDigitLiability($bets, $settings)
    {
        $top    = $this->calculateTwoTopLiability($bets, $settings);
        $bottom = $this->calculateTwoBottomLiability($bets, $settings);
        return $this->mergeTwoLiability($top, $bottom);
    }

    private function calculateThreeDigitLiability($bets, $settings)
    {
        $top  = $this->calculateThreeTopLiability($bets, $settings);
        $toad = $this->calculateThreeToadLiability($bets, $settings);
        return $this->mergeThreeLiability($top, $toad);
    }

    // ── Export Excel (bet history) ──
    public function exportExcel(Request $request, $drawId)
    {
        $draw  = LotteryDraw::findOrFail($drawId);
        $query = LotteryBet::with(['creator'])->whereNull('deleted_at')->where('draw_date', $draw->draw_date);

        if ($request->has('customer_names') && count($request->customer_names)) $query->whereIn('customer_name', $request->customer_names);
        if ($request->search_number) $query->where('number','like','%'.$request->search_number.'%');
        if ($request->win_status === 'won')  $query->where(fn($q) => $q->where('is_win_top',true)->orWhere('is_win_bottom',true)->orWhere('is_win_toad',true)->orWhere('is_win_bottom_3',true));
        if ($request->win_status === 'lost') $query->where('is_win_top',false)->where('is_win_bottom',false)->where('is_win_toad',false)->where('is_win_bottom_3',false);
        if ($request->number_type === '2_digit')  $query->whereRaw('LENGTH(number)=2');
        if ($request->number_type === '3_digit')  $query->whereRaw('LENGTH(number)=3');
        if ($request->number_type === '3_bottom') $query->whereRaw('LENGTH(number)=3')->where('amount_bottom_3','>',0);

        $bets  = $query->orderBy('customer_name')->orderBy('created_at')->get();
        $rows  = [];
        $rows[] = ['ชื่อลูกค้า','เลข','บน','ล่าง','โต๊ด','3ตัวล่าง','รวม (฿)','บันทึกโดย','วันที่บันทึก','ผล'];

        foreach ($bets as $bet) {
            $total   = $bet->total_amount;
            $created = \Carbon\Carbon::parse($bet->created_at)->format('d/m/y H:i');
            $creator = $bet->creator ? $bet->creator->name : '-';

            if ($draw->is_announced) {
                $won = $bet->is_win_top||$bet->is_win_bottom||$bet->is_win_toad||($bet->is_win_bottom_3??false);
                $pay = $bet->payout_top+$bet->payout_bottom+$bet->payout_toad+($bet->payout_bottom_3??0);
                $res = $won ? 'ถูก '.number_format($pay,0).' ฿' : 'ไม่ถูก';
            } else {
                $res = 'รอประกาศ';
            }

            $rows[] = [
                $bet->customer_name,
                $bet->number,
                $bet->amount_top    > 0 ? $bet->amount_top    : '',
                $bet->amount_bottom > 0 ? $bet->amount_bottom : '',
                $bet->amount_toad   > 0 ? $bet->amount_toad   : '',
                ($bet->amount_bottom_3??0) > 0 ? $bet->amount_bottom_3 : '',
                $total, $creator, $created, $res,
            ];
        }

        $dateStr     = \Carbon\Carbon::parse($draw->draw_date)->format('Y-m-d');
        $filename    = 'bets_'.$dateStr.'.csv';
        $filenameUtf = rawurlencode('บัญชีแทงหวย_'.$dateStr.'.csv');
        $handle      = fopen('php://temp','r+');
        fwrite($handle,"\xEF\xBB\xBF");
        foreach ($rows as $r) fputcsv($handle,$r);
        rewind($handle); $csv=stream_get_contents($handle); fclose($handle);
        return response($csv,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>"attachment; filename=\"{$filename}\"; filename*=UTF-8''{$filenameUtf}"]);
    }

    // ── Export Customer Summary ──
    public function exportCustomerSummary(Request $request, $drawId)
    {
        $draw     = LotteryDraw::findOrFail($drawId);
        $settings = ['commission_rate' => Setting::get('commission_rate', 10)];
        $bets     = LotteryBet::with(['creator'])->whereNull('deleted_at')->where('draw_date',$draw->draw_date)->get();
        $customerSummary = $this->getCustomerSummary($bets, $settings['commission_rate']);

        $rows   = [];
        $rows[] = ['ชื่อลูกค้า','บันทึกโดย','ยอดซื้อ (฿)','ส่วนลด (฿)','หลังหัก (฿)','รางวัล (฿)','สุทธิ','เลขที่ถูก'];
        foreach ($customerSummary as $s) {
            $winStr = '';
            if (!empty($s['winning_numbers'])) {
                $winStr = implode(', ', array_map(fn($w) => $w['number'].'('.$w['win_type'].')='.number_format($w['payout'],0), $s['winning_numbers']));
            }
            $netLabel = $s['net_amount'] < 0 ? 'จ่าย '.number_format(abs($s['net_amount']),0) : 'รับ '.number_format($s['net_amount'],0);
            $rows[]   = [$s['customer_name'],$s['created_by'],number_format($s['total_bet_before_discount'],0),number_format($s['discount'],0),number_format($s['total_bet_after_discount'],0),number_format($s['total_payout'],0),$netLabel,$winStr];
        }

        $dateStr = \Carbon\Carbon::parse($draw->draw_date)->format('Y-m-d');
        $fn      = 'customer_summary_'.$dateStr.'.csv';
        $fnUtf   = rawurlencode('สรุปรายบุคคล_'.$dateStr.'.csv');
        $handle  = fopen('php://temp','r+'); fwrite($handle,"\xEF\xBB\xBF");
        foreach ($rows as $r) fputcsv($handle,$r);
        rewind($handle); $csv=stream_get_contents($handle); fclose($handle);
        return response($csv,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>"attachment; filename=\"{$fn}\"; filename*=UTF-8''{$fnUtf}"]);
    }

    // ── Export Over Limit ──
    public function exportOverLimit(Request $request, $drawId)
    {
        $draw = LotteryDraw::findOrFail($drawId);
        $settings = [
            'max_payout_2_digit'  => Setting::get('max_payout_2_digit', 50000),
            'max_payout_3_digit'  => Setting::get('max_payout_3_digit', 100000),
            'max_payout_3_toad'   => Setting::get('max_payout_3_toad', 50000),
            'max_payout_3_bottom' => Setting::get('max_payout_3_bottom', 50000),
            'rate_2_top'          => Setting::get('rate_2_top', 90),
            'rate_2_bottom'       => Setting::get('rate_2_bottom', 90),
            'rate_3_top'          => Setting::get('rate_3_top', 900),
            'rate_3_toad'         => Setting::get('rate_3_toad', 120),
            'rate_3_bottom'       => Setting::get('rate_3_bottom', 500),
            'commission_rate'     => Setting::get('commission_rate', 10),
        ];
        $bets = LotteryBet::whereNull('deleted_at')->where('draw_date',$draw->draw_date)->get();

        $twoTopLiab    = $this->calculateTwoTopLiability($bets, $settings);
        $twoBottomLiab = $this->calculateTwoBottomLiability($bets, $settings);
        $threeTopLiab  = $this->calculateThreeTopLiability($bets, $settings);
        $threeToadLiab = $this->calculateThreeToadLiability($bets, $settings);
        $threeBottomLiab = $this->calculateThreeBottomLiability($bets, $settings);

        $maxPay2     = $settings['max_payout_2_digit'];
        $maxPay3     = $settings['max_payout_3_digit'];
        // โต๊ดใช้เพดาน max_payout_3_digit (ยืนยันจาก Correct CSV: เพดาน 50,000)
        $maxPay3Toad = $settings['max_payout_3_digit'];
        $maxPay3Bot  = $settings['max_payout_3_bottom'];

        $filterOver = function ($liab, $maxPayout) {
            $result = [];
            foreach ($liab as $number => $data) {
                if ($data['liability'] <= 0) continue;
                $pct = ($data['liability'] / $maxPayout) * 100;
                if ($pct >= 100) $result[] = ['number'=>$number,'bet_count'=>$data['bet_count'],'total_amount'=>$data['total_amount'],'liability'=>round($data['liability']),'percentage'=>round($pct,2)];
            }
            usort($result, fn($a,$b) => $b['liability']-$a['liability']);
            return $result;
        };

        $over2Top    = $filterOver($twoTopLiab, $maxPay2);
        $over2Bottom = $filterOver($twoBottomLiab, $maxPay2);
        $over3Top    = $filterOver($threeTopLiab, $maxPay3);
        $over3Toad   = $filterOver($threeToadLiab, $maxPay3Toad);
        $over3Bottom = $filterOver($threeBottomLiab, $maxPay3Bot);

        $dLabel = \Carbon\Carbon::parse($draw->draw_date)->format('d/m/Y');
        $rows   = [];

        $addSection = function ($title, $data, $maxPayout) use (&$rows, $dLabel) {
            $rows[] = [];
            $rows[] = ["=== $title ===","งวด $dLabel","เพดาน: ".number_format($maxPayout,0)." บาท"];
            $rows[] = ['เลข','จำนวนใบ','ยอดซื้อ (฿)','ยอดจ่าย (฿)','% ของเพดาน'];
            if (empty($data)) {
                $rows[] = ['-','-','-','-','ไม่มีเลขเกินเพดาน'];
            } else {
                foreach ($data as $item)
                    $rows[] = [$item['number'],$item['bet_count'],number_format($item['total_amount'],0),number_format($item['liability'],0),number_format($item['percentage'],2).'%'];
            }
        };

        // section พิเศษสำหรับโต๊ด: ยอดจ่าย (฿) = ยอดซื้อ × rate ต่อ perm (ไม่ใช่ group liability)
        // แต่ % ของเพดาน ยังใช้ group liability / ceiling
        $addToadSection = function ($title, $data, $maxPayout, $rate) use (&$rows, $dLabel) {
            $rows[] = [];
            $rows[] = ["=== $title ===","งวด $dLabel","เพดาน: ".number_format($maxPayout,0)." บาท"];
            $rows[] = ['เลข','จำนวนใบ','ยอดซื้อ (฿)','ยอดจ่าย (฿)','% ของเพดาน'];
            if (empty($data)) {
                $rows[] = ['-','-','-','-','ไม่มีเลขเกินเพดาน'];
            } else {
                foreach ($data as $item) {
                    // ยอดจ่าย = ยอดซื้อ × rate ของ perm นั้นๆ (แยกทีละตัว)
                    $individualPayout = round($item['total_amount'] * $rate);
                    $rows[] = [
                        $item['number'],
                        $item['bet_count'],
                        number_format($item['total_amount'], 0),
                        number_format($individualPayout, 0),
                        number_format($item['percentage'], 2).'%',
                    ];
                }
            }
        };

        $addSection('2 ตัวบน (เกิน 100%)',   $over2Top,    $maxPay2);
        $addSection('2 ตัวล่าง (เกิน 100%)',  $over2Bottom, $maxPay2);
        $addSection('3 ตัวบน (เกิน 100%)',   $over3Top,    $maxPay3);
        // โต๊ด: แสดงแยกทีละ permutation พร้อมคอลัมน์ "ยอดจ่าย" (ยอดซื้อ × rate) ต่อตัว
        $addToadSection('3 ตัวโต๊ด (เกิน 100%)', $over3Toad, $maxPay3Toad, $settings['rate_3_toad']);
        $addSection('3 ตัวล่าง (เกิน 100%)',  $over3Bottom, $maxPay3Bot);

        $dateStr = \Carbon\Carbon::parse($draw->draw_date)->format('Y-m-d');
        $fn      = 'over_limit_'.$dateStr.'.csv';
        $fnUtf   = rawurlencode('เลขเกินเพดาน_'.$dateStr.'.csv');
        $handle  = fopen('php://temp','r+'); fwrite($handle,"\xEF\xBB\xBF");
        foreach ($rows as $r) fputcsv($handle,$r);
        rewind($handle); $csv=stream_get_contents($handle); fclose($handle);
        return response($csv,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>"attachment; filename=\"{$fn}\"; filename*=UTF-8''{$fnUtf}"]);
    }

    // ── [FIX #1] เพิ่ม method statistics() ที่หายไป ──

    public function statistics()
    {
        // งวดที่ประกาศผลแล้วทั้งหมด
        $announcedDraws = LotteryDraw::where('is_announced', true)
            ->orderBy('draw_date', 'desc')
            ->get();

        // สร้าง pastDraws array สำหรับ view
        $pastDraws = $announcedDraws->map(function ($draw) {
            $bets = LotteryBet::where('draw_date', $draw->draw_date)
                ->whereNull('deleted_at')->get();

            $totalBet    = $bets->sum(fn($b) => $b->amount_top + $b->amount_bottom + $b->amount_toad + ($b->amount_bottom_3 ?? 0));
            $totalPayout = $bets->sum(fn($b) => $b->payout_top + $b->payout_bottom + $b->payout_toad + ($b->payout_bottom_3 ?? 0));

            return [
                'id'           => $draw->id,
                'draw_date'    => $draw->draw_date->format('Y-m-d'),
                'result_3_top' => $draw->result_3_top,
                'result_2_top' => $draw->result_2_top,
                'result_2_bottom' => $draw->result_2_bottom,
                'total_bet'    => $totalBet,
                'total_payout' => $totalPayout,
                'profit'       => $totalBet - $totalPayout,
                'bet_count'    => $bets->count(),
            ];
        })->toArray();

        // สถิติแยกตามประเภทเดิม (รวมทุกงวด)
        $allBets = LotteryBet::whereNull('deleted_at')
            ->whereHas('draw', fn($q) => $q->where('is_announced', true))
            ->get();

        $betTypeStats = [
            'top'    => ['total_bet' => 0, 'total_payout' => 0],
            'bottom' => ['total_bet' => 0, 'total_payout' => 0],
            'toad'   => ['total_bet' => 0, 'total_payout' => 0],
        ];
        foreach ($allBets as $bet) {
            $betTypeStats['top']['total_bet']       += $bet->amount_top;
            $betTypeStats['top']['total_payout']    += $bet->payout_top;
            $betTypeStats['bottom']['total_bet']    += $bet->amount_bottom;
            $betTypeStats['bottom']['total_payout'] += $bet->payout_bottom;
            $betTypeStats['toad']['total_bet']      += $bet->amount_toad;
            $betTypeStats['toad']['total_payout']   += $bet->payout_toad;
        }

        // เลขผลที่ออกบ่อย (result_3_top และ result_2_top)
        $numberFreq = [];
        foreach ($announcedDraws as $draw) {
            foreach ([$draw->result_3_top, $draw->result_2_top, $draw->result_2_bottom] as $num) {
                if ($num) $numberFreq[$num] = ($numberFreq[$num] ?? 0) + 1;
            }
        }
        arsort($numberFreq);
        $frequentNumbers = array_slice(
            array_map(fn($n, $c) => ['number' => $n, 'count' => $c], array_keys($numberFreq), $numberFreq),
            0, 10
        );

        // 10 งวดล่าสุดสำหรับ chart
        $recentDraws = collect(array_slice($pastDraws, 0, 10))->map(fn($d) => [
            'date'         => \Carbon\Carbon::parse($d['draw_date'])->format('d/m'),
            'total_bet'    => $d['total_bet'],
            'total_payout' => $d['total_payout'],
            'profit'       => $d['profit'],
        ]);

        // สถิติรายเดือน
        $monthlyStats = collect($pastDraws)->groupBy(fn($d) => \Carbon\Carbon::parse($d['draw_date'])->format('Y-m'))
            ->map(fn($group, $month) => [
                'month'        => \Carbon\Carbon::parse($month . '-01')->locale('th')->translatedFormat('M Y'),
                'total_bet'    => collect($group)->sum('total_bet'),
                'total_payout' => collect($group)->sum('total_payout'),
                'profit'       => collect($group)->sum('profit'),
            ])->values();

        return view('admin.reports.statistics', compact(
            'pastDraws', 'betTypeStats', 'frequentNumbers', 'recentDraws', 'monthlyStats'
        ));
    }

    // ── [FIX #1] เพิ่ม method exportPDF() ที่หายไป ──

    public function exportPDF($drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => fn($q) => $q->whereNull('deleted_at')->with('creator')->orderBy('customer_name')
        ])->findOrFail($drawId);

        $bets = $draw->bets;

        $totalBetAmount = $bets->sum(fn($b) => $b->amount_top + $b->amount_bottom + $b->amount_toad + ($b->amount_bottom_3 ?? 0));
        $totalPayout    = $bets->sum(fn($b) => $b->payout_top + $b->payout_bottom + $b->payout_toad + ($b->payout_bottom_3 ?? 0));
        $profit         = $totalBetAmount - $totalPayout;

        // จัดกลุ่ม winners ตามลูกค้า
        $winnerMap = [];
        foreach ($bets as $bet) {
            if (!($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad || ($bet->is_win_bottom_3 ?? false))) {
                continue;
            }
            $customer = $bet->customer_name;
            if (!isset($winnerMap[$customer])) {
                $winnerMap[$customer] = [
                    'customer_name' => $customer,
                    'total_bet'     => 0,
                    'total_win'     => 0,
                    'details'       => [],
                ];
            }
            $winnerMap[$customer]['total_bet'] += $bet->amount_top + $bet->amount_bottom + $bet->amount_toad + ($bet->amount_bottom_3 ?? 0);
            $winnerMap[$customer]['total_win'] += $bet->payout_top + $bet->payout_bottom + $bet->payout_toad + ($bet->payout_bottom_3 ?? 0);
            $winnerMap[$customer]['details'][]  = $bet;
        }

        foreach ($winnerMap as $customer => &$data) {
            $data['net'] = $data['total_bet'] - $data['total_win'];
        }
        unset($data);

        $winners = collect(array_values($winnerMap));

        return view('admin.reports.pdf', compact('draw', 'totalBetAmount', 'totalPayout', 'profit', 'winners'));
    }
}
