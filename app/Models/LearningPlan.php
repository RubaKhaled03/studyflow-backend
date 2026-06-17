<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPlan extends Model
{
    protected $fillable = [
    'user_id', 'title', 'description', 'goal',
    'category', 'target_skill', 'start_date', 'end_date', 'status', 'resources',
];

protected function casts(): array
{
    return ['resources' => 'array'];
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(LearningStage::class)->orderBy('order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(LearningMilestone::class);
    }
}
