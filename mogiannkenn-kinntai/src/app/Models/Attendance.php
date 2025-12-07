<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'work_time',
    ];

    protected $casts = [
        'date'        => 'date',
        'start_time'  => 'datetime',
        'end_time'    => 'datetime',
        'break_start' => 'datetime',
        'break_end'   => 'datetime',
    ];

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

