<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\LotteryBet;
use App\Models\LotteryDraw;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // สถิติวันนี้
        $today = now()->toDateString();
        $todayBets = LotteryBet::whereDate('created_at', $today)
            ->whereNull('deleted_at');

        $todayStats = [
            'total_bets'   => $todayBets->count(),
            // [FIX #3] เพิ่ม COALESCE(amount_bottom_3, 0) เข้าไปในสูตรคำนวณ total_amount
            'total_amount' => $todayBets->sum(DB::raw('amount_top + amount_bottom + amount_toad + COALESCE(amount_bottom_3, 0)')),
            'total_top'    => $todayBets->sum('amount_top'),
            'total_bottom' => $todayBets->sum('amount_bottom'),
            'total_toad'   => $todayBets->sum('amount_toad'),
            // [FIX #3] เพิ่ม total_bottom_3 แยกต่างหากเพื่อให้ Dashboard แสดงครบ
            'total_bottom_3' => $todayBets->sum('amount_bottom_3'),
        ];

        // งวดล่าสุดที่ยังไม่ประกาศผล
        $upcomingDraw = LotteryDraw::where('is_announced', false)
            ->orderBy('draw_date', 'asc')
            ->first();

        // งวดล่าสุดที่ประกาศผลแล้ว
        $latestDraw = LotteryDraw::where('is_announced', true)
            ->orderBy('draw_date', 'desc')
            ->first();

        // รายการแทงล่าสุด 25 รายการ
        $recentBets = LotteryBet::with(['creator', 'draw'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        // สถิติเฉพาะ Admin
        $adminStats = null;
        if ($user->isAdmin()) {
            $adminStats = [
                'total_users'      => User::count(),
                'total_draws'      => LotteryDraw::count(),
                'announced_draws'  => LotteryDraw::where('is_announced', true)->count(),
            ];
        }

        return view('dashboard', compact('todayStats', 'upcomingDraw', 'latestDraw', 'recentBets', 'adminStats', 'user'));
    }

    // [FIX #7] method นี้มีอยู่แล้ว — เพิ่ม route ใน web.php แล้ว (api.realtime-sales)
    public function realtimeSales()
    {
        $today = now()->toDateString();
        $todayBets = LotteryBet::whereDate('created_at', $today)
            ->whereNull('deleted_at');

        return response()->json([
            // [FIX #3] รวม amount_bottom_3 ใน totalSales ด้วย
            'totalSales'   => $todayBets->sum(DB::raw('amount_top + amount_bottom + amount_toad + COALESCE(amount_bottom_3, 0)')),
            'totalTop'     => $todayBets->sum('amount_top'),
            'totalBottom'  => $todayBets->sum('amount_bottom'),
            'totalToad'    => $todayBets->sum('amount_toad'),
            'totalBottom3' => $todayBets->sum('amount_bottom_3'),
        ]);
    }

    /**
     * API สำหรับดึงรายการงวดที่เปิดรับแทง (งวดปัจจุบัน + อนาคต)
     */
    public function getOpenDraws()
    {
        $today = now()->startOfDay();

        $openDraws = LotteryDraw::where('draw_date', '>=', $today->format('Y-m-d'))
            ->where('is_announced', false)
            ->orderBy('draw_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'draws'   => $openDraws->map(function ($draw) {
                return [
                    'value'        => $draw->draw_date->format('Y-m-d'),
                    'label'        => $draw->draw_date->locale('th')->translatedFormat('j F Y'),
                    'is_announced' => $draw->is_announced,
                ];
            })
        ]);
    }
}
