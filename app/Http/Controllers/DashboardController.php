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

        // รายการแทงล่าสุด 100 รายการ
        $recentBets = LotteryBet::with(['creator', 'draw'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->take(25)
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
    
    /**
     * API สำหรับดึงรายการงวดที่เปิดรับแทง (งวดปัจจุบัน + อนาคต)
     */
    public function getOpenDraws()
    {
        $today = now()->startOfDay(); // เริ่มต้นวัน 00:00:00
        $draws = [];
        
        // สร้างงวดอนาคต 6 งวด (ครอบคลุม 3 เดือน)
        for ($i = 0; $i < 6; $i++) {
            $month = $today->copy()->addMonths($i);
            
            // งวดวันที่ 1
            $date1 = $month->copy()->day(1);
            // Close time = 23:59 วันก่อนหน้า
            $closeTime1 = $date1->copy()->subDay()->setTime(23, 59, 59);
            
            // งวดวันที่ 16
            $date16 = $month->copy()->day(16);
            $closeTime16 = $date16->copy()->subDay()->setTime(23, 59, 59);
            
            // สร้างหรืออัพเดทงวด
            LotteryDraw::updateOrCreate(
                ['draw_date' => $date1->format('Y-m-d')],
                [
                    'close_time' => $closeTime1,
                    'is_announced' => false
                ]
            );
            
            LotteryDraw::updateOrCreate(
                ['draw_date' => $date16->format('Y-m-d')],
                [
                    'close_time' => $closeTime16,
                    'is_announced' => false
                ]
            );
        }
        
        // ดึงงวดที่ยังไม่ประกาศผล และเป็นวันนี้หรืออนาคต
        $openDraws = LotteryDraw::where('draw_date', '>=', $today->format('Y-m-d'))
            ->where('is_announced', false)
            ->orderBy('draw_date', 'asc')
            ->take(6)
            ->get();
        
        return response()->json([
            'success' => true,
            'draws' => $openDraws->map(function ($draw) {
                return [
                    'value' => $draw->draw_date->format('Y-m-d'),
                    'label' => $draw->draw_date->locale('th')->translatedFormat('j F Y'),
                    'close_time' => $draw->close_time ? $draw->close_time->toIso8601String() : null,
                    'is_announced' => $draw->is_announced,
                ];
            })
        ]);
    }
}