<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DtrLog extends Model
{
    // This array allows these fields to be saved into the database
    protected $fillable = [
        // Fields matching the migration and seeders
        'intern_id',
        'log_date',
        'am_in',
        'am_out',
        'pm_in',
        'pm_out',
        'daily_total_hours',
        'user_id',
        'full_name',
        'total_hours_to_render',
        'department',
        'position',
        'company',
        'time_in_am',
        'time_out_am',
        'time_in_pm',
        'time_out_pm',
        'split_shift'
    ];

    /**
     * Get the user that owns the DTR settings.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}