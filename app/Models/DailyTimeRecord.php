<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Crucial for time calculations

class DailyTimeRecord extends Model
{
    // This tells Laravel which table to use
    protected $table = 'daily_time_records';

    // Added 'total_hours' to fillable so the controller can save it
    protected $fillable = [
        'user_id', 
        'log_date', 
        'am_in', 
        'am_out', 
        'pm_in', 
        'pm_out',
        'total_hours' 
    ];

    /**
     * Accessor: This allows you to call $record->calculated_hours in your Blade.
     * It ignores empty fields, so if AM is missing, it still counts PM.
     */
   public function getCalculatedHoursAttribute()
{
    $totalMinutes = 0;
    $ignoreTimes = ['12:00:00', '00:00:00', ''];

    // Morning Block
    if (!empty($this->am_in) && !empty($this->am_out) && !in_array($this->am_out, $ignoreTimes)) {
        $amIn = \Carbon\Carbon::parse($this->am_in);
        $amOut = \Carbon\Carbon::parse($this->am_out);
        // Use abs() to ensure the difference is always positive
        $totalMinutes += abs($amOut->diffInMinutes($amIn));
    }

    // Afternoon Block
    if (!empty($this->pm_in) && !empty($this->pm_out)) {
        $pmIn = \Carbon\Carbon::parse($this->pm_in);
        $pmOut = \Carbon\Carbon::parse($this->pm_out);
        $totalMinutes += abs($pmOut->diffInMinutes($pmIn));
    }

    return round($totalMinutes / 60, 2);
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}