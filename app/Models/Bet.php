<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'bet_type',
        'number',
        'amount',
        'bet_date',
        'status',
        'payout'
    ];

    protected $casts = [
        'bet_date' => 'date',
        'amount' => 'decimal:2',
        'payout' => 'decimal:2'
    ];

    public function lotteryResult()
    {
        return $this->belongsTo(LotteryResult::class, 'bet_date', 'draw_date');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('bet_date', $date);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('bet_type', $type);
    }
}