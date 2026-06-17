<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reflection extends Model
{
    protected $fillable = [
        'user_id', 'title', 'content', 'mood', 'tags', 'date',
        'achieved', 'difficult', 'learned', 'improve_next', 'gratitude',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
