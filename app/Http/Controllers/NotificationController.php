<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request)
    {
        $this->generateTaskReminders($request->user());

        $notifications = $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications->map(fn($n) => $this->formatNotification($n)));
    }

    // بيفحص الـ tasks اللي قربت ومستحقة تنبيه، وبيولّد notification لكل واحدة لسه ما تنبّهت
    private function generateTaskReminders($user): void
    {
        $now = now();

        $tasks = $user->tasks()
            ->where('reminder_enabled', true)
            ->where('status', '!=', 'done')
            ->whereNull('reminder_sent_at')
            ->whereNotNull('due_date')
            ->get();

        foreach ($tasks as $task) {
            $dueAt = \Illuminate\Support\Carbon::parse(
                $task->due_date . ' ' . ($task->due_time ?? '09:00')
            );

            $minutesBefore = match ($task->reminder_timing_unit) {
                'minutes' => $task->reminder_timing_value,
                'hours'   => $task->reminder_timing_value * 60,
                'days'    => $task->reminder_timing_value * 60 * 24,
                default   => 0,
            };

            $reminderAt = $dueAt->copy()->subMinutes($minutesBefore);

            // لسه بدري، أو الموعد فات بدون ما نلحق - بالحالتين منتجاهل
            if ($now->lt($reminderAt) || $now->gt($dueAt)) {
                continue;
            }

            $user->notifications()->create([
                'title'        => 'Upcoming: ' . $task->title,
                'message'      => 'This task is due ' . $dueAt->diffForHumans(),
                'type'         => in_array($task->type, ['exam', 'quiz']) ? 'exam' : 'task',
                'target_route' => '/tasks',
                'target_id'    => (string) $task->id,
            ]);

            $task->update(['reminder_sent_at' => $now]);
        }
    }

    // POST /api/notifications
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'message'     => 'required|string',
            'type'        => 'nullable|in:task,course,exam,assignment,reflection,system,reminder',
            'targetRoute' => 'nullable|string',
            'targetId'    => 'nullable|string',
            'scheduledAt' => 'nullable|date',
        ]);

        $notification = $request->user()->notifications()->create([
            'title'        => $request->title,
            'message'      => $request->message,
            'type'         => $request->type ?? 'system',
            'target_route' => $request->targetRoute,
            'target_id'    => $request->targetId,
            'scheduled_at' => $request->scheduledAt,
        ]);

        return response()->json($this->formatNotification($notification), 201);
    }

    // PATCH /api/notifications/{id}
    // بنستخدمها لتعليم notification واحدة كـ "مقروءة"
    public function update(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        $request->validate([
            'read' => 'sometimes|boolean',
        ]);

        $notification->update([
            'read' => $request->has('read') ? $request->read : $notification->read,
        ]);

        return response()->json($this->formatNotification($notification->fresh()));
    }

    // PATCH /api/notifications/mark-all-read
    public function markAllRead(Request $request)
    {
        $request->user()->notifications()->where('read', false)->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    // DELETE /api/notifications/{id}
    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    // DELETE /api/notifications
    // بنستخدمها لمسح كل notifications اليوزر (زر "Clear all")
    public function destroyAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json(['message' => 'All notifications cleared']);
    }

    private function formatNotification(Notification $n): array
    {
        return [
            'id'          => (string) $n->id,
            'title'       => $n->title,
            'message'     => $n->message,
            'type'        => $n->type,
            'read'        => $n->read,
            'createdAt'   => $n->created_at,
            'targetRoute' => $n->target_route,
            'targetId'    => $n->target_id,
        ];
    }
}
