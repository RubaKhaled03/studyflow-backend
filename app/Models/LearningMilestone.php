<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningMilestone extends Model
{
    protected $fillable = [
        'learning_plan_id', 'title', 'description',
        'target_date', 'completed', 'notes',
    ];

    protected function casts(): array
    {
        return ['completed' => 'boolean'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LearningPlan::class);
    }
}
