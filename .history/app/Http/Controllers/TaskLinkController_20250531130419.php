<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskLink;

class TaskLinkController extends Controller
{
    public function store(Request $request)
    {
        $link = TaskLink::create($request->only(['project_id', 'source', 'target', 'type']));
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
}
