<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskLink;

class TaskLinkController extends Controller
{
    public function store(Request $request)
    {
        $link = TaskLink::create([
            'project_id' => $request->input('project_id') ?? 1, // default หรือรับจาก frontend
            'source' => $request->input('source'),
            'target' => $request->input('target'),
            'type' => $request->input('type')
        ]);
       return response()->json([
    'data' => $taskLinks->map(function ($link) {
        return [
            'id' => $link->id,
            'source' => $link->source,
            'target' => $link->target,
            'type' => $link->type,
        ];
    })
]);

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
}
