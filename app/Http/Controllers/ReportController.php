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
    // หน้าสรุปผลแต่ละงวด
    public function summary($drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => function ($query) {
                $query->whereNull('deleted_at')
                    ->with('creator');
            }
        ])->findOrFail($drawId);

        if (!$draw->is_announced) {
            return redirect()->back()->with('error', 'งวดนี้ยังไม่ประกาศผล');
        }

        // คำนวณสรุปผล
        $totalBetAmount = $draw->bets->sum('total_amount');
        $totalPayout = $draw->bets->sum('total_payout');
        $profit = $totalBetAmount - $totalPayout;

        // รายการผู้ถูกรางวัลที่ต้องจ่ายเงิน
        $winners = $draw->bets->filter(function ($bet) {
            return $bet->total_payout > 0;
        })->groupBy('customer_name')->map(function ($bets, $customerName) {
            return [
                'customer_name' => $customerName,
                'total_bet' => $bets->sum('total_amount'),
                'total_win' => $bets->sum('total_payout'),
                'net' => $bets->sum('total_payout') - $bets->sum('total_amount'),
                'details' => $bets->map(function ($bet) {
                    return [
                        'number' => $bet->number,
                        'bet_amount' => $bet->total_amount,
                        'win_amount' => $bet->total_payout,
                        'win_types' => [
                            'top' => $bet->is_win_top ? $bet->payout_top : 0,
                            'bottom' => $bet->is_win_bottom ? $bet->payout_bottom : 0,
                            'toad' => $bet->is_win_toad ? $bet->payout_toad : 0,
                        ]
                    ];
                })
            ];
        })->sortByDesc('net');

        // สถิติตามประเภท
        $stats = [
            'total_top_bet' => $draw->bets->sum('amount_top'),
            'total_bottom_bet' => $draw->bets->sum('amount_bottom'),
            'total_toad_bet' => $draw->bets->sum('amount_toad'),
            'total_top_payout' => $draw->bets->sum('payout_top'),
            'total_bottom_payout' => $draw->bets->sum('payout_bottom'),
            'total_toad_payout' => $draw->bets->sum('payout_toad'),
            'winners_top' => $draw->bets->where('is_win_top', true)->count(),
            'winners_bottom' => $draw->bets->where('is_win_bottom', true)->count(),
            'winners_toad' => $draw->bets->where('is_win_toad', true)->count(),
        ];

        return view('admin.reports.summary', compact('draw', 'totalBetAmount', 'totalPayout', 'profit', 'winners', 'stats'));
    }

    // Export PDF
    public function exportPDF($drawId)
    {
        $draw = LotteryDraw::with([
            'bets' => function ($query) {
                $query->whereNull('deleted_at')->with('creator');
            }
        ])->findOrFail($drawId);

        $totalBetAmount = $draw->bets->sum('total_amount');
        $totalPayout = $draw->bets->sum('total_payout');
        $profit = $totalBetAmount - $totalPayout;

        $winners = $draw->bets->filter(function ($bet) {
            return $bet->total_payout > 0;
        })->groupBy('customer_name')->map(function ($bets, $customerName) {
            return [
                'customer_name' => $customerName,
                'total_bet' => $bets->sum('total_amount'),
                'total_win' => $bets->sum('total_payout'),
                'net' => $bets->sum('total_payout') - $bets->sum('total_amount'),
                'details' => $bets
            ];
        })->sortByDesc('net');

        $pdf = Pdf::loadView('admin.reports.pdf', compact('draw', 'totalBetAmount', 'totalPayout', 'profit', 'winners'));

        $filename = 'สรุปผล_งวด_' . $draw->draw_date->format('d-m-Y') . '.pdf';
        return $pdf->download($filename);
    }

    // หน้าสถิติกราฟ
    public function statistics()
    {
        // สถิติงวด 10 งวดล่าสุด
        $recentDraws = LotteryDraw::where('is_announced', true)
            ->orderBy('draw_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($draw) {
                $bets = LotteryBet::where('draw_date', $draw->draw_date)
                    ->whereNull('deleted_at')
                    ->get();

                return [
                    'date' => $draw->draw_date->format('d/m/y'),
                    'total_bet' => $bets->sum('total_amount'),
                    'total_payout' => $bets->sum('total_payout'),
                    'profit' => $bets->sum('total_amount') - $bets->sum('total_payout'),
                    'bet_count' => $bets->count(),
                ];
            });

        // สถิติเลขออกบ่อย
        $frequentNumbers = DB::table('lottery_draws')
            ->where('is_announced', true)
            ->select('result_2_top as number')
            ->union(
                DB::table('lottery_draws')
                    ->where('is_announced', true)
                    ->select('result_2_bottom as number')
            )
            ->get()
            ->groupBy('number')
            ->map(function ($items, $number) {
                return [
                    'number' => $number,
                    'count' => $items->count()
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();

        // สถิติรายเดือน
        $monthlyStats = LotteryDraw::where('is_announced', true)
            ->whereYear('draw_date', '>=', now()->subMonths(6)->year)
            ->get()
            ->map(function ($draw) {
                $bets = LotteryBet::where('draw_date', $draw->draw_date)
                    ->whereNull('deleted_at')
                    ->get();

                return [
                    'month' => $draw->draw_date->format('Y-m'),
                    'month_label' => $draw->draw_date->format('m/Y'),
                    'total_bet' => $bets->sum('total_amount'),
                    'total_payout' => $bets->sum('total_payout'),
                    'profit' => $bets->sum('total_amount') - $bets->sum('total_payout'),
                ];
            })
            ->groupBy('month')
            ->map(function ($items, $month) {
                return [
                    'month' => $items->first()['month_label'],
                    'total_bet' => $items->sum('total_bet'),
                    'total_payout' => $items->sum('total_payout'),
                    'profit' => $items->sum('profit'),
                ];
            })
            ->values();

        // สถิติประเภทการแทง
        $betTypeStats = [
            'top' => [
                'total_bet' => LotteryBet::whereNull('deleted_at')->sum('amount_top'),
                'total_payout' => LotteryBet::whereNull('deleted_at')->sum('payout_top'),
            ],
            'bottom' => [
                'total_bet' => LotteryBet::whereNull('deleted_at')->sum('amount_bottom'),
                'total_payout' => LotteryBet::whereNull('deleted_at')->sum('payout_bottom'),
            ],
            'toad' => [
                'total_bet' => LotteryBet::whereNull('deleted_at')->sum('amount_toad'),
                'total_payout' => LotteryBet::whereNull('deleted_at')->sum('payout_toad'),
            ],
        ];
        $pastDraws = LotteryDraw::where('is_announced', true)
            ->orderBy('draw_date', 'desc')
            ->get()
            ->map(function ($draw) {
                $bets = LotteryBet::where('draw_date', $draw->draw_date)
                    ->whereNull('deleted_at')
                    ->get();

                return [
                    'id' => $draw->id,
                    'draw_date' => $draw->draw_date,
                    'result_2_top' => $draw->result_2_top,
                    'result_2_bottom' => $draw->result_2_bottom,
                    'total_bet' => $bets->sum('total_amount'),
                    'total_payout' => $bets->sum('total_payout'),
                    'profit' => $bets->sum('total_amount') - $bets->sum('total_payout'),
                    'bet_count' => $bets->count(),
                ];
            });
        // แก้ไข return statement ให้เป็น:
        return view('admin.reports.statistics', compact('recentDraws', 'frequentNumbers', 'monthlyStats', 'betTypeStats', 'pastDraws'));



    }
}