<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications
   public function index(Request $request)
{
    $user = $request->user();

    $tasks = $user->tasks()
        ->where('reminder_enabled', true)
        ->where('status', '!=', 'done')
        ->whereNull('reminder_sent_at')
        ->whereNotNull('due_date')
        ->get();



    $this->generateTaskReminders($user);

    $notifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->get();



    return response()->json($notifications->map(fn($n) => $this->formatNotification($n)));
}
    /**
     * بيفحص الـ tasks اللي قربت ومستحقة تنبيه، وبيولّد notification لكل واحدة لسه ما تنبّهت.
     * الـ window = من وقت الـ reminder لغاية 24 ساعة بعد الـ due date (عشان ما يفوت تنبيه).
     */
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
    $task->due_date . ' ' . ($task->due_time ?? '23:59'),
    'Asia/Hebron'
)->utc();

            $minutesBefore = match ($task->reminder_timing_unit) {
                'minutes' => (int) $task->reminder_timing_value,
                'hours'   => (int) $task->reminder_timing_value * 60,
                'days'    => (int) $task->reminder_timing_value * 60 * 24,
                default   => 60, // default: hour before
            };

            $reminderAt = $dueAt->copy()->subMinutes($minutesBefore);

            // لسه بدري — ما يجي وقت التنبيه
            if ($now->lt($reminderAt)) {
                continue;
            }

            // الـ task فات أكثر من 24 ساعة — تأخر كثير، تجاهل
            if ($now->gt($dueAt->copy()->addHours(24))) {
                // نعلّم إنه أُرسل حتى ما نكرره لاحقاً
                $task->update(['reminder_sent_at' => $now]);
                continue;
            }

            $isOverdue = $now->gt($dueAt);
            $message = $isOverdue
                ? 'This task was due ' . $dueAt->diffForHumans() . ' — don\'t forget to complete it!'
                : 'This task is due ' . $dueAt->diffForHumans();

            $user->notifications()->create([
                'title'        => ($isOverdue ? '⚠️ Overdue: ' : '⏰ Upcoming: ') . $task->title,
                'message'      => $message,
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
