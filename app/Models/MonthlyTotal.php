<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyTotal extends Model
{
    protected $table = 'monthly_totals';

    protected $fillable = ['user_id', 'year_month', 'total_hours'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
