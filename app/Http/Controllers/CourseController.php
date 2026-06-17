<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\WeekItem;

class CourseController extends Controller
{
    // GET /api/courses
    public function index(Request $request)
    {
        $courses = $request->user()->courses()->orderBy('created_at', 'desc')->get();

        return response()->json($courses->map(fn($c) => $this->formatCourse($c)));
    }

    // POST /api/courses
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'status'        => 'required|in:current,completed,planned',
            'credits'       => 'nullable|integer',
            'instructor'    => 'nullable|string',
            'code'          => 'nullable|string',
            'semesterId'    => 'nullable|exists:semesters,id',
            'durationWeeks' => 'nullable|integer',
            'description'   => 'nullable|string',
            'startDate'     => 'nullable|date',
            'endDate'       => 'nullable|date',
            'imageUrl'      => 'nullable|string',
        ]);

        $course = $request->user()->courses()->create([
            'semester_id'    => $request->semesterId,
            'title'          => $request->title,
            'code'           => $request->code,
            'instructor'     => $request->instructor,
            'credits'        => $request->credits ?? 3,
            'status'         => $request->status,
            'image_url'      => $request->imageUrl,
            'duration_weeks' => $request->durationWeeks ?? 16,
            'description'    => $request->description,
            'start_date'     => $request->startDate,
            'end_date'       => $request->endDate,
            'progress'       => 0,
        ]);

        return response()->json($this->formatCourse($course), 201);
    }

    // GET /api/courses/{id}
    public function show(Request $request, $id)
    {
        $course = $request->user()->courses()->findOrFail($id);

        return response()->json($this->formatCourse($course));
    }

    // PATCH /api/courses/{id}
    public function update(Request $request, $id)
    {
        $course = $request->user()->courses()->findOrFail($id);

        $request->validate([
            'title'          => 'sometimes|string|max:255',
            'status'         => 'sometimes|in:current,completed,planned',
            'credits'        => 'nullable|integer',
            'instructor'     => 'nullable|string',
            'code'           => 'nullable|string',
            'semesterId'     => 'nullable|exists:semesters,id',
            'durationWeeks'  => 'nullable|integer',
            'currentWeek'    => 'nullable|integer',
            'description'    => 'nullable|string',
            'startDate'      => 'nullable|date',
            'endDate'        => 'nullable|date',
            'imageUrl'       => 'nullable|string',
            'progress'       => 'nullable|integer|min:0|max:100',
            'finalGrade'     => 'nullable|string',
            'numericGrade'   => 'nullable|integer',
            'academicPeriod' => 'nullable|string',
        ]);

        $course->update([
            'semester_id'    => $request->semesterId ?? $course->semester_id,
            'title'          => $request->title ?? $course->title,
            'code'           => $request->code ?? $course->code,
            'instructor'     => $request->instructor ?? $course->instructor,
            'credits'        => $request->credits ?? $course->credits,
            'status'         => $request->status ?? $course->status,
            'image_url'      => $request->imageUrl ?? $course->image_url,
            'duration_weeks' => $request->durationWeeks ?? $course->duration_weeks,
            'current_week'   => $request->currentWeek ?? $course->current_week,
            'description'    => $request->description ?? $course->description,
            'start_date'     => $request->startDate ?? $course->start_date,
            'end_date'       => $request->endDate ?? $course->end_date,
            'progress'       => $request->progress ?? $course->progress,
            'final_grade'    => $request->finalGrade ?? $course->final_grade,
            'numeric_grade'  => $request->numericGrade ?? $course->numeric_grade,
            'academic_period'=> $request->academicPeriod ?? $course->academic_period,
            'resources' => $request->resources ?? $course->resources,
            ]);

       if ($request->has('weeklyPlan')) {
            $this->syncWeeklyPlan($course, $request->weeklyPlan);
        }
          if ($request->has('weeklyPlan')) {
       $this->syncWeeklyPlan($course, $request->weeklyPlan);
       $this->syncCompletedWeeks($course, $request->weeklyPlan);
     }
        return response()->json($this->formatCourse($course->fresh()));
    }

    // DELETE /api/courses/{id}
    public function destroy(Request $request, $id)
    {
        $course = $request->user()->courses()->findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    private function formatCourse(Course $c): array
    {
        return [
            'id'             => (string) $c->id,
            'semesterId'     => $c->semester_id ? (string) $c->semester_id : null,
            'title'          => $c->title,
            'code'           => $c->code,
            'instructor'     => $c->instructor ?? '',
            'credits'        => $c->credits,
            'status'         => $c->status,
            'imageUrl'       => $c->image_url ?? '/courses/default.png',
            'durationWeeks'  => $c->duration_weeks,
            'currentWeek'    => $c->current_week,
            'description'    => $c->description,
            'startDate'      => $c->start_date,
            'endDate'        => $c->end_date,
            'progress'       => $c->progress,
            'finalGrade'     => $c->final_grade,
            'numericGrade'   => $c->numeric_grade,
            'academicPeriod' => $c->academic_period,
            'weeklyPlan'     => $this->buildWeeklyPlan($c),
            'createdAt'      => $c->created_at,
            'updatedAt'      => $c->updated_at,
            'resources' => $c->resources ?? [],
        ];
    }

    private function buildWeeklyPlan(Course $c): array
    {
        $items = $c->weekItems()->orderBy('week_number')->get();
        $grouped = $items->groupBy('week_number');

        $weeks = [];
        foreach ($grouped as $weekNumber => $weekItems) {
            $studyTasks = $weekItems->where('type', 'study_task')->map(fn($i) => [
                'id'        => (string) $i->id,
                'title'     => $i->title,
                'completed' => $i->completed,
                'dueDate'   => $i->date,
            ])->values();

            $assignments = $weekItems->where('type', 'assignment')->map(fn($i) => [
                'id'          => (string) $i->id,
                'title'       => $i->title,
                'description' => $i->description,
                'dueDate'     => $i->date,
                'status'      => $i->status,
            ])->values();

            $exams = $weekItems->whereIn('type', ['midterm', 'final', 'quiz'])->map(fn($i) => [
                'id'        => (string) $i->id,
                'title'     => $i->title,
                'date'      => $i->date,
                'time'      => $i->time,
                'duration'  => 60,
                'location'  => $i->location,
                'completed' => $i->completed,
            ])->values();

            $weeks[] = [
                'weekNumber'  => (int) $weekNumber,
                'title'       => $weekItems->first()->week_title ?? "Week {$weekNumber}",
                'completed'   => in_array((int) $weekNumber, $c->completed_weeks ?? []),
                'studyTasks'  => $studyTasks,
                'assignments' => $assignments,
                'exams'       => $exams,
                'items'       => $weekItems->map(fn($i) => [
                    'id'          => (string) $i->id,
                    'title'       => $i->title,
                    'type'        => $i->type,
                    'weekNumber'  => $i->week_number,
                    'date'        => $i->date,
                    'time'        => $i->time,
                    'endTime'     => $i->end_time,
                    'description' => $i->description,
                    'status'      => $i->status,
                    'priority'    => $i->priority,
                    'location'    => $i->location,
                    'isAllDay'    => $i->is_all_day,
                    'completed'   => $i->completed,
                    'submitted'   => $i->submitted,
                ])->values(),
            ];
        }

        return $weeks;
    }

    private function syncWeeklyPlan(Course $course, array $weeklyPlan): void
    {
        $existingIds = $course->weekItems()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $incomingIds = [];

        foreach ($weeklyPlan as $week) {
            $weekNumber = $week['weekNumber'] ?? 1;
            $weekTitle  = $week['title'] ?? null;

            $studyTasks  = $week['studyTasks'] ?? [];
            $assignments = $week['assignments'] ?? [];
            $exams       = $week['exams'] ?? [];

            foreach ($studyTasks as $task) {
                $incomingIds[] = $this->upsertWeekItem($course, $existingIds, $weekNumber, $weekTitle, [
                    'id'        => $task['id'] ?? null,
                    'title'     => $task['title'] ?? '',
                    'type'      => 'study_task',
                    'date'      => $task['dueDate'] ?? now()->toDateString(),
                    'completed' => $task['completed'] ?? false,
                    'status'    => ($task['completed'] ?? false) ? 'completed' : 'upcoming',
                ]);
            }

            foreach ($assignments as $assignment) {
                $incomingIds[] = $this->upsertWeekItem($course, $existingIds, $weekNumber, $weekTitle, [
                    'id'          => $assignment['id'] ?? null,
                    'title'       => $assignment['title'] ?? '',
                    'type'        => 'assignment',
                    'date'        => $assignment['dueDate'] ?? now()->toDateString(),
                    'description' => $assignment['description'] ?? null,
                    'status'      => $assignment['status'] ?? 'upcoming',
                ]);
            }

            foreach ($exams as $exam) {
                $incomingIds[] = $this->upsertWeekItem($course, $existingIds, $weekNumber, $weekTitle, [
                    'id'        => $exam['id'] ?? null,
                    'title'     => $exam['title'] ?? '',
                    'type'      => 'final',
                    'date'      => $exam['date'] ?? now()->toDateString(),
                    'time'      => $exam['time'] ?? null,
                    'location'  => $exam['location'] ?? null,
                    'completed' => $exam['completed'] ?? false,
                ]);
            }
        }

        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $course->weekItems()->whereIn('id', $toDelete)->delete();
        }
    }
    private function upsertWeekItem(Course $course, array $existingIds, $weekNumber, $weekTitle, array $fields): string
    {
        $itemId = $fields['id'] ?? null;
        unset($fields['id']);

        $data = array_merge([
            'week_number' => $weekNumber,
            'week_title'  => $weekTitle,
            'type'        => 'study_task',
            'date'        => now()->toDateString(),
            'status'      => 'upcoming',
            'priority'    => 'normal',
            'time'        => null,
            'end_time'    => null,
            'description' => null,
            'location'    => null,
            'is_all_day'  => false,
            'completed'   => false,
            'submitted'   => false,
        ], $fields);

        if ($itemId && is_numeric($itemId) && in_array((string) $itemId, $existingIds)) {
            $course->weekItems()->where('id', $itemId)->update($data);
            return (string) $itemId;
        }

        $newItem = $course->weekItems()->create($data);
        return (string) $newItem->id;
    }
    private function syncCompletedWeeks(Course $course, array $weeklyPlan): void
{
    $completedWeeks = [];
    foreach ($weeklyPlan as $week) {
        if (!empty($week['completed'])) {
            $completedWeeks[] = $week['weekNumber'];
        }
    }
    $course->update(['completed_weeks' => $completedWeeks]);
}
}
