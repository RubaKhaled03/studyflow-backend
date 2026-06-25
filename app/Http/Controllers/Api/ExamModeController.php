<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamModeState;
use Illuminate\Http\Request;

class ExamModeController extends Controller
{
    // GET /api/courses/{courseId}/exam-mode/{examId}
    public function show(Request $request, $courseId, $examId)
    {
        $state = $request->user()->examModeStates()
            ->where('course_id', $courseId)
            ->where('exam_id', $examId)
            ->first();

        if (! $state) {
            // No saved state yet — return an empty default so the frontend
            // doesn't have to handle a 404 as a special case.
            return response()->json([
                'examId'    => (string) $examId,
                'courseId'  => (string) $courseId,
                'topics'    => [],
                'notes'     => null,
                'createdAt' => null,
                'updatedAt' => null,
            ]);
        }

        return response()->json($this->formatState($state));
    }

    // PUT /api/courses/{courseId}/exam-mode/{examId}
    public function upsert(Request $request, $courseId, $examId)
    {
        $request->validate([
            'topics' => 'nullable|array',
            'notes'  => 'nullable|string',
        ]);

        $state = $request->user()->examModeStates()->updateOrCreate(
            [
                'course_id' => $courseId,
                'exam_id'   => $examId,
            ],
            [
                'topics' => $request->topics ?? [],
                'notes'  => $request->notes,
            ]
        );

        return response()->json($this->formatState($state));
    }

    private function formatState(ExamModeState $state): array
    {
        return [
            'examId'    => (string) $state->exam_id,
            'courseId'  => (string) $state->course_id,
            'topics'    => $state->topics ?? [],
            'notes'     => $state->notes,
            'createdAt' => $state->created_at,
            'updatedAt' => $state->updated_at,
        ];
    }
}
