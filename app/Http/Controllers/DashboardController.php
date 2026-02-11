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
            'total_bets' => $todayBets->count(),
            'total_amount' => $todayBets->sum(DB::raw('amount_top + amount_bottom + amount_toad')),
            'total_top' => $todayBets->sum('amount_top'),
            'total_bottom' => $todayBets->sum('amount_bottom'),
            'total_toad' => $todayBets->sum('amount_toad'),
        ];

        // งวดล่าสุดที่ยังไม่ประกาศผล
        $upcomingDraw = LotteryDraw::where('is_announced', false)
            ->orderBy('draw_date', 'asc')
            ->first();

        // งวดล่าสุดที่ประกาศผลแล้ว
        $latestDraw = LotteryDraw::where('is_announced', true)
            ->orderBy('draw_date', 'desc')
            ->first();

        // รายการแทงล่าสุด
        $recentBets = LotteryBet::with(['creator', 'draw'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // สถิติเฉพาะ Admin
        $adminStats = null;
        if ($user->isAdmin()) {
            $adminStats = [
                'total_users' => User::count(),
                'total_draws' => LotteryDraw::count(),
                'announced_draws' => LotteryDraw::where('is_announced', true)->count(),
            ];
        }

        return view('dashboard', compact('todayStats', 'upcomingDraw', 'latestDraw', 'recentBets', 'adminStats', 'user'));
    }

    public function realtimeSales()
    {
        $today = now()->toDateString();
        $todayBets = LotteryBet::whereDate('created_at', $today)
            ->whereNull('deleted_at');

        return response()->json([
            'totalSales' => $todayBets->sum(DB::raw('amount_top + amount_bottom + amount_toad')),
            'totalTop' => $todayBets->sum('amount_top'),
            'totalBottom' => $todayBets->sum('amount_bottom'),
            'totalToad' => $todayBets->sum('amount_toad'),
        ]);
    }
}