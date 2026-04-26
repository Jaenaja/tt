<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'username', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_active' => 'boolean'];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    public function isGeneral()
    {
        return $this->role === 'general';
    }
    public function bets()
    {
        return $this->hasMany(LotteryBet::class, 'created_by');
    }
}

// --------------------------------------------------------
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['key', 'value', 'description'];
    public static function get($key, $default = null)
    {
        $c = self::where('key', $key)->first();
        return $c ? $c->value : $default;
    }
    public static function set($key, $value, $description = null)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value, 'description' => $description]);
    }
}

// --------------------------------------------------------
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LotteryDraw extends Model
{
    protected $fillable = [
        'draw_date',
        'close_time',
        'result_3_top',
        'result_2_top',
        'result_2_bottom',
        'result_3_bottom',   // "355,108,868,424"
        'is_announced',
        'announced_at',
        'announced_by',
    ];
    protected $casts = [
        'draw_date' => 'date',
        'close_time' => 'datetime',
        'is_announced' => 'boolean',
        'announced_at' => 'datetime',
    ];
    public function announcedBy()
    {
        return $this->belongsTo(User::class, 'announced_by');
    }
    public function bets()
    {
        return $this->hasMany(LotteryBet::class, 'draw_date', 'draw_date');
    }
    public function getResult3BottomArrayAttribute(): array
    {
        if (empty($this->result_3_bottom))
            return [];
        return array_map('trim', explode(',', $this->result_3_bottom));
    }
    public function isClosed(): bool
    {
        if (!$this->close_time)
            return false;
        return now()->isAfter($this->close_time);
    }
    public function scopeOpen($query)
    {
        return $query->where(fn($q) => $q->whereNull('close_time')->orWhere('close_time', '>', now()));
    }
}

// --------------------------------------------------------
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LotteryBet extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'draw_date',
        'customer_name',
        'number',
        'amount_top',
        'amount_bottom',
        'amount_toad',
        'amount_bottom_3',
        'payout_top',
        'payout_bottom',
        'payout_toad',
        'payout_bottom_3',
        'is_win_top',
        'is_win_bottom',
        'is_win_toad',
        'is_win_bottom_3',
        'created_by',
        'deleted_by',
    ];
    protected $casts = [
        'draw_date' => 'date',
        'amount_top' => 'decimal:2',
        'amount_bottom' => 'decimal:2',
        'amount_toad' => 'decimal:2',
        'amount_bottom_3' => 'decimal:2',
        'payout_top' => 'decimal:2',
        'payout_bottom' => 'decimal:2',
        'payout_toad' => 'decimal:2',
        'payout_bottom_3' => 'decimal:2',
        'is_win_top' => 'boolean',
        'is_win_bottom' => 'boolean',
        'is_win_toad' => 'boolean',
        'is_win_bottom_3' => 'boolean',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
    public function draw()
    {
        return $this->belongsTo(LotteryDraw::class, 'draw_date', 'draw_date');
    }
    public function getTotalAmountAttribute()
    {
        return $this->amount_top + $this->amount_bottom + $this->amount_toad + ($this->amount_bottom_3 ?? 0);
    }
    public function getTotalPayoutAttribute()
    {
        return $this->payout_top + $this->payout_bottom + $this->payout_toad + ($this->payout_bottom_3 ?? 0);
    }
    public function getNetProfitAttribute()
    {
        return $this->total_payout - $this->total_amount;
    }
}
