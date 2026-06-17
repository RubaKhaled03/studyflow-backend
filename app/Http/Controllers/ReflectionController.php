<?php

namespace App\Http\Controllers;

use App\Models\Reflection;
use Illuminate\Http\Request;

class ReflectionController extends Controller
{
    // GET /api/reflections
    public function index(Request $request)
    {
        $reflections = $request->user()->reflections()
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($reflections->map(fn($r) => $this->formatReflection($r)));
    }

    // POST /api/reflections
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'mood'        => 'required|in:excellent,good,neutral,stressed,tired,sad',
            'date'        => 'required|date',
            'achieved'    => 'nullable|string',
            'difficult'   => 'nullable|string',
            'learned'     => 'nullable|string',
            'improveNext' => 'nullable|string',
            'gratitude'   => 'nullable|string',
            'tags'        => 'nullable|array',
        ]);

        $reflection = $request->user()->reflections()->create([
            'title'        => $request->title,
            'mood'         => $request->mood,
            'date'         => $request->date,
            'achieved'     => $request->achieved,
            'difficult'    => $request->difficult,
            'learned'      => $request->learned,
            'improve_next' => $request->improveNext,
            'gratitude'    => $request->gratitude,
            'tags'         => $request->tags ? implode(',', $request->tags) : null,
            'content'      => $request->achieved ?? '',
        ]);

        return response()->json($this->formatReflection($reflection), 201);
    }

    // GET /api/reflections/{id}
    public function show(Request $request, $id)
    {
        $reflection = $request->user()->reflections()->findOrFail($id);

        return response()->json($this->formatReflection($reflection));
    }

    // PATCH /api/reflections/{id}
    public function update(Request $request, $id)
    {
        $reflection = $request->user()->reflections()->findOrFail($id);

        $reflection->update([
            'title'        => $request->title ?? $reflection->title,
            'mood'         => $request->mood ?? $reflection->mood,
            'date'         => $request->date ?? $reflection->date,
            'achieved'     => $request->achieved ?? $reflection->achieved,
            'difficult'    => $request->difficult ?? $reflection->difficult,
            'learned'      => $request->learned ?? $reflection->learned,
            'improve_next' => $request->improveNext ?? $reflection->improve_next,
            'gratitude'    => $request->gratitude ?? $reflection->gratitude,
            'tags'         => $request->tags ? implode(',', $request->tags) : $reflection->tags,
        ]);

        return response()->json($this->formatReflection($reflection->fresh()));
    }

    // DELETE /api/reflections/{id}
    public function destroy(Request $request, $id)
    {
        $reflection = $request->user()->reflections()->findOrFail($id);
        $reflection->delete();

        return response()->json(['message' => 'Reflection deleted successfully']);
    }

    private function formatReflection(Reflection $r): array
    {
        return [
            'id'          => (string) $r->id,
            'title'       => $r->title,
            'date'        => $r->date,
            'mood'        => $r->mood,
            'achieved'    => $r->achieved ?? '',
            'difficult'   => $r->difficult ?? '',
            'learned'     => $r->learned ?? '',
            'improveNext' => $r->improve_next ?? '',
            'gratitude'   => $r->gratitude,
            'tags'        => $r->tags ? explode(',', $r->tags) : [],
            'createdAt'   => $r->created_at,
            'updatedAt'   => $r->updated_at,
        ];
    }
}
