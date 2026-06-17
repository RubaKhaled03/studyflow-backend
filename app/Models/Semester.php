<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'status',
        'weeks_count',
        'academic_year',
        'start_date',
        'end_date',
        'notes',
        'gpa',
        'credit_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
