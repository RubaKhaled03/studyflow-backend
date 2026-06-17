<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'duration_minutes',
        'start_time',
        'end_time',
        'mode',
        'completed',
        'linked_task_id',
        'linked_course_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
            'completed'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
