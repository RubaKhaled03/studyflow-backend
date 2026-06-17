<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    // GET /api/semesters
    public function index(Request $request)
    {
        $semesters = $request->user()->semesters()->orderBy('created_at', 'desc')->get();

        return response()->json($semesters->map(fn($s) => $this->formatSemester($s)));
    }

    // POST /api/semesters
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'status'       => 'required|in:planned,current,completed',
            'weeksCount'   => 'nullable|integer',
            'academicYear' => 'nullable|string',
            'startDate'    => 'nullable|date',
            'endDate'      => 'nullable|date',
            'notes'        => 'nullable|string',
        ]);

        $semester = $request->user()->semesters()->create([
            'name'          => $request->name,
            'status'        => $request->status,
            'weeks_count'   => $request->weeksCount ?? 16,
            'academic_year' => $request->academicYear,
            'start_date'    => $request->startDate,
            'end_date'      => $request->endDate,
            'notes'         => $request->notes,
        ]);

        return response()->json($this->formatSemester($semester), 201);
    }

    // GET /api/semesters/{id}
    public function show(Request $request, $id)
    {
        $semester = $request->user()->semesters()->findOrFail($id);

        return response()->json($this->formatSemester($semester));
    }

    // PATCH /api/semesters/{id}
    public function update(Request $request, $id)
    {
        $semester = $request->user()->semesters()->findOrFail($id);

        $request->validate([
            'name'         => 'sometimes|string|max:255',
            'status'       => 'sometimes|in:planned,current,completed',
            'weeksCount'   => 'nullable|integer',
            'academicYear' => 'nullable|string',
            'startDate'    => 'nullable|date',
            'endDate'      => 'nullable|date',
            'notes'        => 'nullable|string',
        ]);

        $semester->update([
            'name'          => $request->name ?? $semester->name,
            'status'        => $request->status ?? $semester->status,
            'weeks_count'   => $request->weeksCount ?? $semester->weeks_count,
            'academic_year' => $request->academicYear ?? $semester->academic_year,
            'start_date'    => $request->startDate ?? $semester->start_date,
            'end_date'      => $request->endDate ?? $semester->end_date,
            'notes'         => $request->notes ?? $semester->notes,
        ]);

        return response()->json($this->formatSemester($semester->fresh()));
    }

    // DELETE /api/semesters/{id}
    public function destroy(Request $request, $id)
    {
        $semester = $request->user()->semesters()->findOrFail($id);
        $semester->delete();

        return response()->json(['message' => 'Semester deleted successfully']);
    }

    private function formatSemester(Semester $s): array
    {
        return [
            'id'           => (string) $s->id,
            'name'         => $s->name,
            'status'       => $s->status,
            'weeksCount'   => $s->weeks_count,
            'academicYear' => $s->academic_year,
            'startDate'    => $s->start_date,
            'endDate'      => $s->end_date,
            'notes'        => $s->notes,
            'createdAt'    => $s->created_at,
            'updatedAt'    => $s->updated_at,
        ];
    }
}
