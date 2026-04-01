<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DtrSetting extends Model
{
    protected $table = 'dtr_settings';

    protected $fillable = [
        'user_id', 
        'full_name', 
        'total_hours', 
        'company', 
        'department', 
        'position', 
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