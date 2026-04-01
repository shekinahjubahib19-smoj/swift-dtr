<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTimeRecord extends Model
{
    // This tells Laravel which table to use
    protected $table = 'daily_time_records';

    // This allows these fields to be saved into the database
    protected $fillable = [
        'user_id', 
        'log_date', 
        'am_in', 
        'am_out', 
        'pm_in', 
        'pm_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}