<?php
// [FIX #8] แก้ LotteryResult model ที่ reference Bet::class ผิด → LotteryBet::class
// หมายเหตุ: model นี้ยังไม่ได้ถูกใช้งานในโปรเจค ถ้าไม่ได้ใช้ควรลบออกเพื่อลด confusion

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_date',
        'three_digit',
        'two_digit'
    ];

    protected $casts = [
        'draw_date' => 'date'
    ];

    // [FIX #8] เปลี่ยนจาก Bet::class (ไม่มีใน namespace) → LotteryBet::class
    public function bets()
    {
        return $this->hasMany(LotteryBet::class, 'draw_date', 'draw_date');
    }
}
