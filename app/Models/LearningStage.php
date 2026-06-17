<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningStage extends Model
{
    protected $fillable = [
        'learning_plan_id', 'title', 'description',
        'target_duration', 'status', 'goals', 'order',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LearningPlan::class);
    }
}
