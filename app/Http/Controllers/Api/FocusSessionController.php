<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FocusSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FocusSessionController extends Controller
{
    // GET /api/focus/sessions
    public function index(Request $request)
    {
        $sessions = $request->user()->focusSessions()
            ->orderByDesc('start_time')
            ->get();

        return response()->json($sessions->map(fn($s) => $this->formatSession($s)));
    }

    // POST /api/focus/sessions
    public function store(Request $request)
    {
        $request->validate([
            'durationMinutes' => 'required|integer|min:1',
            'startTime'       => 'required|date',
            'endTime'         => 'nullable|date',
            'mode'            => 'required|in:pomodoro,stopwatch',
            'completed'       => 'sometimes|boolean',
            'linkedTaskId'    => 'nullable|string',
            'linkedCourseId'  => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $session = $request->user()->focusSessions()->create([
            'duration_minutes' => $request->durationMinutes,
            'start_time'       => $request->startTime,
            'end_time'         => $request->endTime,
            'mode'             => $request->mode,
            'completed'        => $request->completed ?? true,
            'linked_task_id'   => $request->linkedTaskId,
            'linked_course_id' => $request->linkedCourseId,
            'notes'            => $request->notes,
        ]);

        return response()->json($this->formatSession($session), 201);
    }

    // GET /api/focus/sessions/{id}
    public function show(Request $request, $id)
    {
        $session = $request->user()->focusSessions()->findOrFail($id);
        return response()->json($this->formatSession($session));
    }

    // DELETE /api/focus/sessions/{id}
    public function destroy(Request $request, $id)
    {
        $session = $request->user()->focusSessions()->findOrFail($id);
        $session->delete();

        return response()->json(['message' => 'Session deleted']);
    }

    // GET /api/focus/analytics
    public function analytics(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        $weekSessions = $user->focusSessions()
            ->where('start_time', '>=', $weekStart)
            ->get();

        $monthSessions = $user->focusSessions()
            ->where('start_time', '>=', $monthStart)
            ->get();

        $allSessions = $user->focusSessions()->get();

        // Daily breakdown for current week (Sun..Sat)
        $dailyBreakdown = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dayLabel = $day->format('Y-m-d');
            $minutesThatDay = $weekSessions
                ->filter(fn($s) => Carbon::parse($s->start_time)->isSameDay($day))
                ->sum('duration_minutes');

            $dailyBreakdown[] = [
                'date'    => $dayLabel,
                'minutes' => $minutesThatDay,
            ];
        }

        return response()->json([
            'totalSessions'      => $allSessions->count(),
            'totalMinutesAllTime'=> $allSessions->sum('duration_minutes'),
            'weekly' => [
                'totalSessions' => $weekSessions->count(),
                'totalMinutes'  => $weekSessions->sum('duration_minutes'),
                'dailyBreakdown'=> $dailyBreakdown,
            ],
            'monthly' => [
                'totalSessions' => $monthSessions->count(),
                'totalMinutes'  => $monthSessions->sum('duration_minutes'),
            ],
            'averageSessionMinutes' => $allSessions->count() > 0
                ? round($allSessions->avg('duration_minutes'), 1)
                : 0,
        ]);
    }

    // Helper — camelCase formatting to match the frontend types
    private function formatSession(FocusSession $session): array
    {
        return [
            'id'              => (string) $session->id,
            'durationMinutes' => $session->duration_minutes,
            'startTime'       => $session->start_time,
            'endTime'         => $session->end_time,
            'mode'            => $session->mode,
            'completed'       => $session->completed,
            'linkedTaskId'    => $session->linked_task_id,
            'linkedCourseId'  => $session->linked_course_id,
            'notes'           => $session->notes,
            'createdAt'       => $session->created_at,
            'updatedAt'       => $session->updated_at,
        ];
    }
}
