<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index(Request $request)
    {
        $query = $request->user()->tasks()->orderBy('created_at', 'desc');

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->sourceModule) {
            $query->where('source_module', $request->sourceModule);
        }

        $tasks = $query->get();

        return response()->json($tasks->map(fn($t) => $this->formatTask($t)));
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'nullable|in:general,study-task,assignment,quiz,exam,self-learning-milestone',
            'sourceModule' => 'nullable|in:general,course,self-learning',
            'priority'     => 'nullable|in:high,medium,low',
            'status'       => 'nullable|in:todo,in-progress,done',
            'dueDate'      => 'nullable|date',
            'dueTime'      => 'nullable|string',
            'courseId'     => 'nullable|exists:courses,id',
            'description'  => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        $task = $request->user()->tasks()->create([
            'course_id'                  => $request->courseId,
            'title'                      => $request->title,
            'description'                => $request->description,
            'type'                       => $request->type ?? 'general',
            'source_module'              => $request->sourceModule ?? 'general',
            'linked_course_title'        => $request->linkedCourseTitle,
            'linked_week_id'             => $request->linkedWeekId,
            'linked_week_label'          => $request->linkedWeekLabel,
            'linked_learning_plan_id'    => $request->linkedLearningPlanId,
            'linked_learning_plan_title' => $request->linkedLearningPlanTitle,
            'due_date'                   => $request->dueDate,
            'due_time'                   => $request->dueTime,
            'priority'                   => $request->priority ?? 'medium',
            'status'                     => $request->status ?? 'todo',
            'notes'                      => $request->notes,
        ]);

        return response()->json($this->formatTask($task), 201);
    }

    // GET /api/tasks/{id}
    public function show(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        return response()->json($this->formatTask($task));
    }

    // PATCH /api/tasks/{id}
    public function update(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        $request->validate([
            'title'    => 'sometimes|string|max:255',
            'status'   => 'sometimes|in:todo,in-progress,done',
            'priority' => 'sometimes|in:high,medium,low',
            'dueDate'  => 'nullable|date',
            'dueTime'  => 'nullable|string',
            'notes'    => 'nullable|string',
        ]);

        $task->update([
            'title'    => $request->title ?? $task->title,
            'status'   => $request->status ?? $task->status,
            'priority' => $request->priority ?? $task->priority,
            'due_date' => $request->dueDate ?? $task->due_date,
            'due_time' => $request->dueTime ?? $task->due_time,
            'notes'    => $request->notes ?? $task->notes,
            'description' => $request->description ?? $task->description,
        ]);

        return response()->json($this->formatTask($task->fresh()));
    }

    // DELETE /api/tasks/{id}
    public function destroy(Request $request, $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    private function formatTask(Task $t): array
    {
        return [
            'id'                       => (string) $t->id,
            'title'                    => $t->title,
            'description'              => $t->description,
            'type'                     => $t->type,
            'sourceModule'             => $t->source_module,
            'linkedCourseId'           => $t->course_id ? (string) $t->course_id : null,
            'linkedCourseTitle'        => $t->linked_course_title,
            'linkedWeekId'             => $t->linked_week_id,
            'linkedWeekLabel'          => $t->linked_week_label,
            'linkedLearningPlanId'     => $t->linked_learning_plan_id,
            'linkedLearningPlanTitle'  => $t->linked_learning_plan_title,
            'dueDate'                  => $t->due_date,
            'dueTime'                  => $t->due_time,
            'priority'                 => $t->priority,
            'status'                   => $t->status,
            'notes'                    => $t->notes,
            'createdAt'                => $t->created_at,
            'updatedAt'                => $t->updated_at,
        ];
    }
}
