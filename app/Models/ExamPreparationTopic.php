<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPreparationTopic extends Model
{
    protected $fillable = [
        'exam_mode_state_id',
        'title',
        'completed',
        'priority',
        'notes',
    ];

    protected function casts(): array
    {
        return ['completed' => 'boolean'];
    }

    public function examModeState(): BelongsTo
    {
        return $this->belongsTo(ExamModeState::class);
    }
}
