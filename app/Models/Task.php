<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'description',
        'type',
        'source_module',
        'linked_course_title',
        'linked_week_id',
        'linked_week_label',
        'linked_learning_plan_id',
        'linked_learning_plan_title',
        'due_date',
        'due_time',
        'priority',
        'status',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
