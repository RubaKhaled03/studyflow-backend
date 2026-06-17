<?php

namespace App\Http\Controllers;

use App\Models\LearningPlan;
use App\Models\LearningStage;
use App\Models\LearningMilestone;
use Illuminate\Http\Request;

class LearningPlanController extends Controller
{
    // GET /api/self-learning
    public function index(Request $request)
    {
        $plans = $request->user()->learningPlans()
            ->with(['stages', 'milestones'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($plans->map(fn($p) => $this->formatPlan($p)));
    }

    // POST /api/self-learning
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'goal'        => 'required|string',
            'description' => 'nullable|string',
            'category'    => 'nullable|string',
            'targetSkill' => 'nullable|string',
            'startDate'   => 'required|date',
            'endDate'     => 'nullable|date',
            'status'      => 'nullable|in:planned,active,completed,paused',
        ]);

        $plan = $request->user()->learningPlans()->create([
            'title'        => $request->title,
            'goal'         => $request->goal,
            'description'  => $request->description,
            'category'     => $request->category,
            'target_skill' => $request->targetSkill,
            'start_date'   => $request->startDate,
            'end_date'     => $request->endDate,
            'status'       => $request->status ?? 'planned',
        ]);

        return response()->json($this->formatPlan($plan->load(['stages', 'milestones'])), 201);
    }

    // GET /api/self-learning/{id}
    public function show(Request $request, $id)
    {
        $plan = $request->user()->learningPlans()
            ->with(['stages', 'milestones'])
            ->findOrFail($id);

        return response()->json($this->formatPlan($plan));
    }

    // PATCH /api/self-learning/{id}
    public function update(Request $request, $id)
    {
        $plan = $request->user()->learningPlans()->findOrFail($id);

        $plan->update([
            'title'        => $request->title ?? $plan->title,
            'goal'         => $request->goal ?? $plan->goal,
            'description'  => $request->description ?? $plan->description,
            'category'     => $request->category ?? $plan->category,
            'target_skill' => $request->targetSkill ?? $plan->target_skill,
            'start_date'   => $request->startDate ?? $plan->start_date,
            'end_date'     => $request->endDate ?? $plan->end_date,
            'status'       => $request->status ?? $plan->status,
            'resources' => $request->resources ?? $plan->resources,
        ]);

        return response()->json($this->formatPlan($plan->fresh()->load(['stages', 'milestones'])));
    }

    // DELETE /api/self-learning/{id}
    public function destroy(Request $request, $id)
    {
        $plan = $request->user()->learningPlans()->findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Learning plan deleted successfully']);
    }

    // POST /api/self-learning/{id}/stages
    public function storeStage(Request $request, $id)
    {
        $plan = $request->user()->learningPlans()->findOrFail($id);

        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'targetDuration' => 'nullable|string',
            'goals'          => 'nullable|string',
            'order'          => 'nullable|integer',
        ]);

        $stage = $plan->stages()->create([
            'title'           => $request->title,
            'description'     => $request->description,
            'target_duration' => $request->targetDuration,
            'goals'           => $request->goals,
            'order'           => $request->order ?? 0,
            'status'          => 'not-started',
        ]);

        return response()->json($this->formatStage($stage), 201);
    }

    // PATCH /api/self-learning/{id}/stages/{stageId}
    public function updateStage(Request $request, $id, $stageId)
    {
        $plan  = $request->user()->learningPlans()->findOrFail($id);
        $stage = $plan->stages()->findOrFail($stageId);

        $stage->update([
            'title'           => $request->title ?? $stage->title,
            'description'     => $request->description ?? $stage->description,
            'target_duration' => $request->targetDuration ?? $stage->target_duration,
            'goals'           => $request->goals ?? $stage->goals,
            'status'          => $request->status ?? $stage->status,
            'order'           => $request->order ?? $stage->order,
        ]);

        return response()->json($this->formatStage($stage->fresh()));
    }

    // POST /api/self-learning/{id}/milestones
    public function storeMilestone(Request $request, $id)
    {
        $plan = $request->user()->learningPlans()->findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'targetDate'  => 'nullable|date',
            'notes'       => 'nullable|string',
        ]);

        $milestone = $plan->milestones()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'target_date' => $request->targetDate,
            'notes'       => $request->notes,
            'completed'   => false,
        ]);

        return response()->json($this->formatMilestone($milestone), 201);
    }

    // PATCH /api/self-learning/{id}/milestones/{milestoneId}
    public function updateMilestone(Request $request, $id, $milestoneId)
    {
        $plan      = $request->user()->learningPlans()->findOrFail($id);
        $milestone = $plan->milestones()->findOrFail($milestoneId);

        $milestone->update([
            'title'       => $request->title ?? $milestone->title,
            'description' => $request->description ?? $milestone->description,
            'target_date' => $request->targetDate ?? $milestone->target_date,
            'completed'   => $request->completed ?? $milestone->completed,
            'notes'       => $request->notes ?? $milestone->notes,
        ]);

        return response()->json($this->formatMilestone($milestone->fresh()));
    }

    private function formatPlan(LearningPlan $p): array
    {
        return [
            'id'           => (string) $p->id,
            'title'        => $p->title,
            'description'  => $p->description,
            'goal'         => $p->goal,
            'category'     => $p->category,
            'targetSkill'  => $p->target_skill,
            'startDate'    => $p->start_date,
            'endDate'      => $p->end_date,
            'status'       => $p->status,
            'stages'       => $p->stages->map(fn($s) => $this->formatStage($s))->values(),
            'milestones'   => $p->milestones->map(fn($m) => $this->formatMilestone($m))->values(),
            'resources' => $p->resources ?? [],
            'createdAt'    => $p->created_at,
            'updatedAt'    => $p->updated_at,
        ];
    }

    private function formatStage(LearningStage $s): array
    {
        return [
            'id'             => (string) $s->id,
            'planId'         => (string) $s->learning_plan_id,
            'title'          => $s->title,
            'description'    => $s->description,
            'targetDuration' => $s->target_duration,
            'status'         => $s->status,
            'goals'          => $s->goals,
            'order'          => $s->order,
            'resources'      => [],
            'tasks'          => [],
            'createdAt'      => $s->created_at,
            'updatedAt'      => $s->updated_at,
        ];
    }

    private function formatMilestone(LearningMilestone $m): array
    {
        return [
            'id'          => (string) $m->id,
            'planId'      => (string) $m->learning_plan_id,
            'title'       => $m->title,
            'description' => $m->description,
            'targetDate'  => $m->target_date,
            'completed'   => $m->completed,
            'notes'       => $m->notes,
            'createdAt'   => $m->created_at,
        ];
    }
}
