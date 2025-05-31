<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->title,
                'start_date' => $task->start->format('Y-m-d'),
                'end_date' => $task->end->format('Y-m-d'),
                'parent' => $task->parent_id ?? 0,
                'progress' => $task->progress,
                'status' => $task->status,
                'owner' => $task->owner,
                'open' => true,
                'readonly' => false,
                'unscheduled' => false
            ];
        });

        $links = TaskLink::where('project_id', $projectId)->get()->map(function ($link) {
            return [
                'id' => $link->id,
                'source' => $link->source,
                'target' => $link->target,
                'type' => $link->type,
            ];
        });

        return response()->json(['data' => $tasks, 'links' => $links]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'parent_id' => 'nullable|integer|exists:tasks,id',
            'status' => 'required|in:ongoing,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'owner' => 'nullable|string|max:255',
        ]);

        $task = Task::create($validated);
        return response()->json(["action" => "inserted", "tid" => $task->id]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'start' => 'sometimes|required|date',
            'end' => 'sometimes|required|date|after_or_equal:start',
            'parent_id' => 'nullable|integer|exists:tasks,id',
            'status' => 'sometimes|required|in:ongoing,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'owner' => 'nullable|string|max:255',
        ]);

        $task->update($validated);
        return response()->json(["action" => "updated"]);
    }

    public function destroy($id)
    {
        Task::destroy($id);
        return response()->json(["action" => "deleted"]);
    }

    public function storeLink(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'source' => 'required|exists:tasks,id',
            'target' => 'required|exists:tasks,id',
            'type' => 'required|string|max:10',
        ]);

        $link = TaskLink::create($validated);
        return response()->json(["action" => "inserted", "tid" => $link->id]);
    }

    public function destroyLink($id)
    {
        TaskLink::destroy($id);
        return response()->json(["action" => "deleted"]);
    }
}

// migration for task_links table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskLinksTable extends Migration
{
    public function up()
    {
        Schema::create('task_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('source');
            $table->unsignedBigInteger('target');
            $table->string('type')->default('0');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_links');
    }
}

// TaskLink Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLink extends Model
{
    protected $fillable = ['project_id', 'source', 'target', 'type'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
