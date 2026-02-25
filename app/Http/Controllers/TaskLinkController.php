<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskLink;
use Illuminate\Http\Request;

class TaskLinkController extends Controller
{

    public function index(Request $request)
{
    $projectParam = trim((string) ($request->input('project_id') ?? ''));

    $query = TaskLink::query();
    if ($projectParam !== '') {
        $project = $this->resolveProjectFromParam($projectParam);
        $this->authorizeProjectAccess($project);
        $query->where('project_id', $project->id);
    }

    $links = $query->get();

    return response()->json([
        'data' => $links->map(function ($link) {
            return [
                'id' => $link->id,
                'source' => $link->source,
                'target' => $link->target,
                'type' => $link->type
            ];
        })
    ]);
}

public function projectLinks($projectId)
{
    $project = $this->resolveProjectFromParam($projectId);
    $this->authorizeProjectAccess($project);

    $links = TaskLink::where('project_id', $project->id)->get();

    return response()->json([
        'data' => $links->map(function ($link) {
            return [
                'id' => $link->id,
                'source' => $link->source,
                'target' => $link->target,
                'type' => $link->type
            ];
        })
    ]);
}


    public function store(Request $request)
    {
        $project = $this->resolveProjectFromParam($request->input('project_id') ?? '');
        $this->authorizeProjectAccess($project);

        $link = TaskLink::create([
            'project_id' => $project->id,
            'source' => $request->input('source'),
            'target' => $request->input('target'),
            'type' => $request->input('type')
        ]);
        return response()->json(['action' => 'inserted', 'tid' => $link->id]);
    }

    public function update(Request $request, $id)
    {
        $link = TaskLink::findOrFail($id);
        $link->update($request->only(['source', 'target', 'type']));
        return response()->json(['action' => 'updated']);
    }

    public function destroy($id)
    {
        TaskLink::destroy($id);
        return response()->json(['action' => 'deleted']);
    }

    private function resolveProjectFromParam($projectParam): Project
    {
        $lookup = trim((string) $projectParam);
        if ($lookup === '') {
            abort(404);
        }

        $project = null;
        if (ctype_digit($lookup)) {
            $project = Project::query()->whereKey((int) $lookup)->first();
        }

        if (! $project) {
            $project = Project::query()
                ->whereRaw('LOWER(TRIM(project_code)) = ?', [mb_strtolower($lookup, 'UTF-8')])
                ->first();
        }

        if (! $project) {
            abort(404);
        }

        return $project;
    }

    private function authorizeProjectAccess(Project $project): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin' || $user->role === 'user_all') {
            return;
        }

        $fullName = trim(($user->prefix ?? '').' '.$user->name);
        $hasPermission = (int) $user->id === (int) $project->user_id
            || $project->fteRoles()->where('name', $fullName)->exists();

        if (! $hasPermission) {
            abort(403);
        }
    }
}
