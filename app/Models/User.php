<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

// app/Models/Config.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    public static function get($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    public static function set($key, $value, $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }
}

// app/Models/LotteryDraw.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryDraw extends Model
{
    protected $fillable = [
        'draw_date',
        'result_3_top',
        'result_2_top',
        'result_2_bottom',
        'is_announced',
        'announced_at',
        'announced_by',
    ];

    protected $casts = [
        'draw_date' => 'date',
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
}

// app/Models/LotteryBet.php
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
        'payout_top',
        'payout_bottom',
        'payout_toad',
        'is_win_top',
        'is_win_bottom',
        'is_win_toad',
        'created_by',
        'deleted_by',
    ];

    protected $casts = [
        'draw_date' => 'date',
        'amount_top' => 'decimal:2',
        'amount_bottom' => 'decimal:2',
        'amount_toad' => 'decimal:2',
        'payout_top' => 'decimal:2',
        'payout_bottom' => 'decimal:2',
        'payout_toad' => 'decimal:2',
        'is_win_top' => 'boolean',
        'is_win_bottom' => 'boolean',
        'is_win_toad' => 'boolean',
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
        return $this->amount_top + $this->amount_bottom + $this->amount_toad;
    }

    public function getTotalPayoutAttribute()
    {
        return $this->payout_top + $this->payout_bottom + $this->payout_toad;
    }

    public function getNetProfitAttribute()
    {
        return $this->total_payout - $this->total_amount;
    }
}