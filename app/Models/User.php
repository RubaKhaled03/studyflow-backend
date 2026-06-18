<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'university',
        'major',
        'academic_year',
        'current_gpa',
        'total_credit_hours',
        'completed_credit_hours',
        'avatar_url',
        'onboarding_completed',
        'theme_preference',
        'focus_preferences',
        'reminder_preferences',
        'streak_current',
        'streak_longest',
        'streak_last_active_date',
        'streak_active_days',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'focus_preferences' => 'array',
            'reminder_preferences' => 'array',
            'streak_active_days'     => 'array',
        ];
    }
    public function semesters()
      {
    return $this->hasMany(Semester::class);
       }
    public function courses()
     {
    return $this->hasMany(Course::class);
      }
    public function tasks()
     {
    return $this->hasMany(Task::class);
     }
     public function learningPlans()
     {
    return $this->hasMany(LearningPlan::class);
     }

     public function reflections()
    {
    return $this->hasMany(Reflection::class);
    }
    public function focusSessions()
{
    return $this->hasMany(FocusSession::class);
}
}
