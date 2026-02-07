<?php

namespace App\Http\Controllers;

use App\Models\LotteryResult;
use App\Models\Bet;
use App\Models\NumberStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotteryResultController extends Controller
{
    public function index()
    {
        $results = LotteryResult::orderBy('draw_date', 'desc')->paginate(10);
        return view('lottery.index', compact('results'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'draw_date' => 'required|date|unique:lottery_results,draw_date',
            'three_digit' => 'required|string|size:3',
            'two_digit' => 'required|string|size:2'
        ]);

        DB::transaction(function () use ($validated) {
            $result = LotteryResult::create($validated);

            $this->updateStatistics($validated['three_digit'], 'three_digit');
            $this->updateStatistics($validated['two_digit'], 'two_digit');

            $this->processBets($result);
        });

        return redirect()->route('lottery.index')
            ->with('success', 'บันทึกผลหวยเรียบร้อยแล้ว');
    }

    private function updateStatistics($number, $type)
    {
        $stat = NumberStatistic::firstOrCreate(
            ['number' => $number, 'type' => $type],
            ['frequency' => 0]
        );
        $stat->incrementFrequency();
    }

    private function processBets(LotteryResult $result)
    {
        Bet::where('bet_date', $result->draw_date)
            ->where('bet_type', 'three_digit')
            ->where('number', $result->three_digit)
            ->update([
                'status' => 'won',
                'payout' => DB::raw('amount * 500')
            ]);

        Bet::where('bet_date', $result->draw_date)
            ->where('bet_type', 'two_digit')
            ->where('number', $result->two_digit)
            ->update([
                'status' => 'won',
                'payout' => DB::raw('amount * 90')
            ]);

        Bet::where('bet_date', $result->draw_date)
            ->where('status', 'pending')
            ->update(['status' => 'lost']);
    }
}