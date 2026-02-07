<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\NumberStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    public function index()
    {
        $statistics = NumberStatistic::orderBy('frequency', 'desc')->get();
        $recentBets = Bet::latest()->take(10)->get();
        
        return view('bets.index', compact('statistics', 'recentBets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'bet_type' => 'required|in:three_digit,two_digit',
            'number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'bet_date' => 'required|date'
        ]);

        if ($validated['bet_type'] === 'three_digit' && strlen($validated['number']) !== 3) {
            return back()->withErrors(['number' => 'กรุณากรอกเลข 3 หลัก']);
        }
        
        if ($validated['bet_type'] === 'two_digit' && strlen($validated['number']) !== 2) {
            return back()->withErrors(['number' => 'กรุณากรอกเลข 2 หลัก']);
        }

        $maxAmount = $validated['bet_type'] === 'three_digit' ? 500 : 1000;
        if ($validated['amount'] > $maxAmount) {
            return back()->withErrors(['amount' => "จำนวนเงินเดิมพันสูงสุดสำหรับ " . 
                ($validated['bet_type'] === 'three_digit' ? '3 หลัก' : '2 หลัก') . 
                " คือ {$maxAmount} บาท"]);
        }

        $bet = Bet::create($validated);

        return redirect()->route('bets.index')
            ->with('success', 'บันทึกการเดิมพันเรียบร้อยแล้ว');
    }

    public function statistics()
    {
        $twoDigitStats = NumberStatistic::where('type', 'two_digit')
            ->orderBy('frequency', 'desc')
            ->take(10)
            ->get();
            
        $threeDigitStats = NumberStatistic::where('type', 'three_digit')
            ->orderBy('frequency', 'desc')
            ->take(10)
            ->get();

        return view('bets.statistics', compact('twoDigitStats', 'threeDigitStats'));
    }

    public function sales(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $sales = Bet::byDate($date)
            ->select('bet_type', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('bet_type')
            ->get();

        $totalSales = $sales->sum('total_amount');
        $threeDigitSales = $sales->where('bet_type', 'three_digit')->first()->total_amount ?? 0;
        $twoDigitSales = $sales->where('bet_type', 'two_digit')->first()->total_amount ?? 0;

        return view('bets.sales', compact('date', 'totalSales', 'threeDigitSales', 'twoDigitSales', 'sales'));
    }

    public function history()
    {
        $history = Bet::with('lotteryResult')
            ->orderBy('bet_date', 'desc')
            ->paginate(20);

        return view('bets.history', compact('history'));
    }
}