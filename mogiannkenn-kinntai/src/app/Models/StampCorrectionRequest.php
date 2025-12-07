<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $table = 'stamp_correction_requests'; 

    protected $fillable = [
        'attendance_id',
        'user_id',
        'new_start_time',
        'new_end_time',
        'new_break_start_time',
        'new_break_end_time',
        'reason',
        'status',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}

