<?php

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

    public function bets()
    {
        return $this->hasMany(Bet::class, 'bet_date', 'draw_date');
    }
}