<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ExamModeState;
use Illuminate\Http\Request;

class ExamModeController extends Controller
{
    // GET /api/courses/{courseId}/exam-mode/{examId}
    // بيرجع الحالة لو موجودة، أو حالة فاضية لو أول مرة يفتح الطالب هاد الامتحان
    public function show(Request $request, $courseId, $examId)
    {
        // نتأكد إن الكورس فعلاً لليوزر الحالي
        $course = $request->user()->courses()->findOrFail($courseId);

        $state = $course->examModeStates()
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $examId)
            ->with('topics')
            ->first();

        if (! $state) {
            return response()->json([
                'examId'    => $examId,
                'courseId'  => (string) $course->id,
                'topics'    => [],
                'notes'     => null,
                'createdAt' => null,
                'updatedAt' => null,
            ]);
        }

        return response()->json($this->formatState($state));
    }

    // PATCH /api/courses/{courseId}/exam-mode/{examId}
    // بيحدث/بيعمل create لملاحظات الامتحان العامة (notes)
    public function update(Request $request, $courseId, $examId)
    {
        $course = $request->user()->courses()->findOrFail($courseId);

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $state = ExamModeState::firstOrCreate(
            [
                'user_id'   => $request->user()->id,
                'course_id' => $course->id,
                'exam_id'   => $examId,
            ],
            ['notes' => null]
        );

        if ($request->has('notes')) {
            $state->update(['notes' => $request->notes]);
        }

        return response()->json($this->formatState($state->fresh()->load('topics')));
    }

    // POST /api/courses/{courseId}/exam-mode/{examId}/topics
    public function storeTopic(Request $request, $courseId, $examId)
    {
        $course = $request->user()->courses()->findOrFail($courseId);

        $request->validate([
            'title'    => 'required|string|max:255',
            'priority' => 'nullable|in:high,medium,low',
            'notes'    => 'nullable|string',
        ]);

        $state = ExamModeState::firstOrCreate([
            'user_id'   => $request->user()->id,
            'course_id' => $course->id,
            'exam_id'   => $examId,
        ]);

        $topic = $state->topics()->create([
            'title'    => $request->title,
            'priority' => $request->priority,
            'notes'    => $request->notes,
        ]);

        return response()->json($this->formatTopic($topic), 201);
    }

    // PATCH /api/courses/{courseId}/exam-mode/{examId}/topics/{topicId}
    public function updateTopic(Request $request, $courseId, $examId, $topicId)
    {
        $course = $request->user()->courses()->findOrFail($courseId);

        $state = $course->examModeStates()
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $examId)
            ->firstOrFail();

        $topic = $state->topics()->findOrFail($topicId);

        $request->validate([
            'title'     => 'sometimes|string|max:255',
            'completed' => 'sometimes|boolean',
            'priority'  => 'sometimes|nullable|in:high,medium,low',
            'notes'     => 'sometimes|nullable|string',
        ]);

        $topic->update([
            'title'     => $request->title ?? $topic->title,
            'completed' => $request->has('completed') ? $request->completed : $topic->completed,
            'priority'  => $request->has('priority') ? $request->priority : $topic->priority,
            'notes'     => $request->has('notes') ? $request->notes : $topic->notes,
        ]);

        return response()->json($this->formatTopic($topic->fresh()));
    }

    // DELETE /api/courses/{courseId}/exam-mode/{examId}/topics/{topicId}
    public function destroyTopic(Request $request, $courseId, $examId, $topicId)
    {
        $course = $request->user()->courses()->findOrFail($courseId);

        $state = $course->examModeStates()
            ->where('user_id', $request->user()->id)
            ->where('exam_id', $examId)
            ->firstOrFail();

        $topic = $state->topics()->findOrFail($topicId);
        $topic->delete();

        return response()->json(['message' => 'Topic deleted successfully']);
    }

    private function formatState(ExamModeState $state): array
    {
        return [
            'examId'    => $state->exam_id,
            'courseId'  => (string) $state->course_id,
            'topics'    => $state->topics->map(fn($t) => $this->formatTopic($t)),
            'notes'     => $state->notes,
            'createdAt' => $state->created_at,
            'updatedAt' => $state->updated_at,
        ];
    }

    private function formatTopic($topic): array
    {
        return [
            'id'        => (string) $topic->id,
            'title'     => $topic->title,
            'completed' => $topic->completed,
            'priority'  => $topic->priority,
            'notes'     => $topic->notes,
        ];
    }
}
