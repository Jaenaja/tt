<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'type',
        'frequency',
        'last_drawn'
    ];

    protected $casts = [
        'frequency' => 'integer',
        'last_drawn' => 'date'
    ];

    public function incrementFrequency()
    {
        $this->increment('frequency');
        $this->update(['last_drawn' => now()]);
    }
}