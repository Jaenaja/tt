<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\NumberStatistic;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        
        // คำนวณยอดขาย
        $totalSales = Bet::whereDate('bet_date', $today)->sum('amount');
        $twoDigitSales = Bet::whereDate('bet_date', $today)
            ->where('bet_type', 'two_digit')->sum('amount');
        $threeDigitSales = Bet::whereDate('bet_date', $today)
            ->where('bet_type', 'three_digit')->sum('amount');
        
        // ดึงสถิติ
        $twoDigitStats = NumberStatistic::where('type', 'two_digit')
            ->orderBy('frequency', 'desc')->take(10)->get();
        $threeDigitStats = NumberStatistic::where('type', 'three_digit')
            ->orderBy('frequency', 'desc')->take(10)->get();
        
        // รายการเดิมพันล่าสุด
        $recentBets = Bet::latest()->take(10)->get();
        
        return view('dashboard', compact(
            'totalSales',
            'twoDigitSales', 
            'threeDigitSales',
            'twoDigitStats',
            'threeDigitStats',
            'recentBets'
        ));
    }

    // API สำหรับ Real-time updates
    public function realtimeSales()
    {
        $today = now()->toDateString();
        
        $totalSales = Bet::whereDate('bet_date', $today)->sum('amount');
        $twoDigitSales = Bet::whereDate('bet_date', $today)
            ->where('bet_type', 'two_digit')->sum('amount');
        $threeDigitSales = Bet::whereDate('bet_date', $today)
            ->where('bet_type', 'three_digit')->sum('amount');

        return response()->json([
            'totalSales' => $totalSales,
            'twoDigitSales' => $twoDigitSales,
            'threeDigitSales' => $threeDigitSales
        ]);
    }
}