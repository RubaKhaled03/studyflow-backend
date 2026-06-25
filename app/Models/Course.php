<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = [
        'user_id',
        'semester_id',
        'title',
        'code',
        'instructor',
        'credits',
        'status',
        'image_url',
        'duration_weeks',
        'current_week',
        'description',
        'start_date',
        'end_date',
        'progress',
        'final_grade',
        'numeric_grade',
        'academic_period',
        'completed_weeks',
        'resources',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
    public function weekItems()
{
    return $this->hasMany(WeekItem::class);
}
protected function casts(): array
{
    return ['completed_weeks' => 'array',
    'resources'       => 'array',
    ];

}
public function examModeStates()
    {
        return $this->hasMany(ExamModeState::class);
    }

}
