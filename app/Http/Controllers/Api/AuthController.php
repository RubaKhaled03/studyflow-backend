<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/auth/register
    public function register(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('studyflow')->plainTextToken;

    return response()->json([
        'message' => 'Account created successfully',
        'token'   => $token,
        'user'    => $this->formatUser($user),
    ], 201);
}

    // POST /api/auth/login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('studyflow')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    // GET /api/auth/profile
    public function profile(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    // PATCH /api/auth/profile — partial profile update (used by the Settings page)
public function updateProfile(Request $request)
{
    $request->validate([
        'university'             => 'sometimes|nullable|string',
        'major'                  => 'sometimes|nullable|string',
        'academicYear'           => 'sometimes|nullable|string',
        'currentGPA'             => 'sometimes|nullable|string',
        'totalCreditHours'       => 'sometimes|nullable|string',
        'completedCreditHours'   => 'sometimes|nullable|string',
        'name'                   => 'sometimes|nullable|string|max:255',
        'avatarUrl'              => 'sometimes|nullable|string',
        'themePreference'        => 'sometimes|nullable|string|in:light,dark,system',
        'focusPreferences'       => 'sometimes|nullable|array',
        'reminderPreferences'    => 'sometimes|nullable|array',
        'onboardingCompleted'    => 'sometimes|nullable|boolean',
        ]);

    $data = [];

    if ($request->has('name')) $data['name'] = $request->name;
    if ($request->has('university')) $data['university'] = $request->university;
    if ($request->has('major')) $data['major'] = $request->major;
    if ($request->has('academicYear')) $data['academic_year'] = $request->academicYear;
    if ($request->has('currentGPA')) $data['current_gpa'] = $request->currentGPA;
    if ($request->has('totalCreditHours')) $data['total_credit_hours'] = $request->totalCreditHours;
    if ($request->has('completedCreditHours')) $data['completed_credit_hours'] = $request->completedCreditHours;
    if ($request->has('avatarUrl')) $data['avatar_url'] = $request->avatarUrl;
    if ($request->has('themePreference')) $data['theme_preference'] = $request->themePreference;
    if ($request->has('focusPreferences')) $data['focus_preferences'] = $request->focusPreferences;
    if ($request->has('reminderPreferences')) $data['reminder_preferences'] = $request->reminderPreferences;
    if ($request->has('onboardingCompleted')) $data['onboarding_completed'] = $request->onboardingCompleted;
    $request->user()->update($data);

    return response()->json($this->formatUser($request->user()->fresh()));
}

    // POST /api/auth/setup
    public function setup(Request $request)
    {
        $request->validate([
            'university'            => 'required|string',
            'major'                 => 'required|string',
            'academicYear'          => 'required|string',
            'currentGPA'            => 'nullable|string',
            'totalCreditHours'      => 'nullable|string',
            'completedCreditHours'  => 'nullable|string',
        ]);

        $request->user()->update([
            'university'             => $request->university,
            'major'                  => $request->major,
            'academic_year'          => $request->academicYear,
            'current_gpa'            => $request->currentGPA,
            'total_credit_hours'     => $request->totalCreditHours,
            'completed_credit_hours' => $request->completedCreditHours,
            'onboarding_completed'   => true,
        ]);

        return response()->json($this->formatUser($request->user()->fresh()));
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    // Helper — يحوّل بيانات الـ User لـ camelCase زي ما الفرونت يتوقع
    private function formatUser(User $user): array
    {
        return [
            'id'                     => (string) $user->id,
            'name'                   => $user->name,
            'university'             => $user->university ?? '',
            'major'                  => $user->major ?? '',
            'academicYear'           => $user->academic_year ?? '',
            'currentGPA'             => $user->current_gpa ?? '',
            'totalCreditHours'       => $user->total_credit_hours ?? '',
            'completedCreditHours'   => $user->completed_credit_hours ?? '',
            'avatarUrl'              => $user->avatar_url,
            'onboardingCompleted'    => $user->onboarding_completed,
            'themePreference'        => $user->theme_preference ?? 'system',
            'focusPreferences'       => $user->focus_preferences ?? [
                'preferredSessionDuration' => 25,
                'preferredBreakDuration'   => 5,
                'autoStartBreak'           => false,
                'defaultFocusMode'         => 'pomodoro',
            ],
            'reminderPreferences'    => $user->reminder_preferences ?? [
                'remindersEnabled'              => true,
                'defaultReminderTiming'         => 30,
                'defaultReminderUnit'           => 'minutes',
                'emailNotificationsEnabled'     => false,
                'inAppNotificationsEnabled'     => true,
            ],
            'createdAt'              => $user->created_at,
            'updatedAt'              => $user->updated_at,
        'streak' => [
    'currentCount'   => $user->streak_current ?? 0,
    'longestCount'   => $user->streak_longest ?? 0,
    'lastActiveDate' => $user->streak_last_active_date ?? '',
    'activeDays'     => $user->streak_active_days ?? [],
],
            ];

    }
    // POST /api/auth/streak
public function updateStreak(Request $request)
{
    $request->validate([
        'currentCount'   => 'required|integer',
        'longestCount'   => 'required|integer',
        'lastActiveDate' => 'required|string',
        'activeDays'     => 'nullable|array',
    ]);

    $request->user()->update([
        'streak_current'           => $request->currentCount,
        'streak_longest'           => $request->longestCount,
        'streak_last_active_date'  => $request->lastActiveDate,
        'streak_active_days'       => $request->activeDays ?? [],
    ]);

    return response()->json($this->formatUser($request->user()->fresh()));
}
}
