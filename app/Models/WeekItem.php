<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeekItem extends Model
{
    protected $fillable = [
        'course_id', 'week_number', 'week_title', 'title', 'type',
        'date', 'time', 'end_time', 'description', 'status', 'priority',
        'location', 'is_all_day', 'completed', 'submitted',
    ];

    protected function casts(): array
    {
        return [
            'is_all_day' => 'boolean',
            'completed'  => 'boolean',
            'submitted'  => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

}
