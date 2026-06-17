<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\WeekItem;
use Illuminate\Http\Request;

class WeekItemController extends Controller
{
    // POST /api/courses/{courseId}/week-items
    public function store(Request $request, $courseId)
    {
        $course = $request->user()->courses()->findOrFail($courseId);

        $request->validate([
            'weekNumber' => 'required|integer',
            'title'      => 'required|string|max:255',
            'type'       => 'required|in:study_task,midterm,final,quiz,assignment,project,presentation,lab,submission,reading_session',
            'date'       => 'required|date',
        ]);

        $item = $course->weekItems()->create([
            'week_number' => $request->weekNumber,
            'week_title'  => $request->weekTitle,
            'title'       => $request->title,
            'type'        => $request->type,
            'date'        => $request->date,
            'time'        => $request->time,
            'end_time'    => $request->endTime,
            'description' => $request->description,
            'status'      => $request->status ?? 'upcoming',
            'priority'    => $request->priority ?? 'normal',
            'location'    => $request->location,
            'is_all_day'  => $request->isAllDay ?? false,
            'completed'   => $request->completed ?? false,
            'submitted'   => $request->submitted ?? false,
        ]);

        return response()->json($this->formatItem($item), 201);
    }

    // PATCH /api/courses/{courseId}/week-items/{id}
    public function update(Request $request, $courseId, $id)
    {
        $course = $request->user()->courses()->findOrFail($courseId);
        $item   = $course->weekItems()->findOrFail($id);

        $item->update([
            'week_number' => $request->weekNumber ?? $item->week_number,
            'week_title'  => $request->weekTitle ?? $item->week_title,
            'title'       => $request->title ?? $item->title,
            'type'        => $request->type ?? $item->type,
            'date'        => $request->date ?? $item->date,
            'time'        => $request->time ?? $item->time,
            'end_time'    => $request->endTime ?? $item->end_time,
            'description' => $request->description ?? $item->description,
            'status'      => $request->status ?? $item->status,
            'priority'    => $request->priority ?? $item->priority,
            'location'    => $request->location ?? $item->location,
            'is_all_day'  => $request->isAllDay ?? $item->is_all_day,
            'completed'   => $request->completed ?? $item->completed,
            'submitted'   => $request->submitted ?? $item->submitted,
        ]);

        return response()->json($this->formatItem($item->fresh()));
    }

    // DELETE /api/courses/{courseId}/week-items/{id}
    public function destroy(Request $request, $courseId, $id)
    {
        $course = $request->user()->courses()->findOrFail($courseId);
        $item   = $course->weekItems()->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Week item deleted successfully']);
    }

    private function formatItem(WeekItem $i): array
    {
        return [
            'id'         => (string) $i->id,
            'title'      => $i->title,
            'type'       => $i->type,
            'weekNumber' => $i->week_number,
            'date'       => $i->date,
            'time'       => $i->time,
            'endTime'    => $i->end_time,
            'description'=> $i->description,
            'status'     => $i->status,
            'priority'   => $i->priority,
            'location'   => $i->location,
            'isAllDay'   => $i->is_all_day,
            'completed'  => $i->completed,
            'submitted'  => $i->submitted,
        ];
    }
}
