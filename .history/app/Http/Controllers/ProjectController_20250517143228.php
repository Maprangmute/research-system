<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\FteRole;
use App\Models\Output;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index(Request $request)
{
    $query = Project::query();

    if (Auth::user()->role !== 'admin') {
        $query->where('user_id', Auth::id());
    }

    if ($request->filled('year')) {
        $query->where('start_year', $request->year);
    }

    if ($request->filled('type')) {
        $query->where('project_type', 'like', '%' . $request->type . '%');
    }

    if ($request->filled('search')) {
        $query->where('project_name_th', 'like', '%' . $request->search . '%');
    }

    $projects = $query->latest()->paginate(10);
    return view('projects.index', compact('projects'));
}

    public function create()
    {
        return view('projects.create');
    }
    public function store(Request $request)
{
    $validated = $request->validate([
        'project_name_th' => 'required',
        'project_type' => 'required',
        'theme' => 'required',
        'start_month' => 'required',
        'start_year' => 'required',

        // Optional validation for funding fields
        'funding_type' => 'nullable|string',
        'funding_source' => 'nullable|string',
        'funding_org_type' => 'nullable|string',
        'funding_name' => 'nullable|string',
        'budget' => 'nullable|numeric',
    ]);

    $project = new Project();
    $project->fill([
        'project_name_th' => $request->project_name_th,
        'project_name_en' => $request->project_name_en,
        'project_type' => $request->project_type,
        'theme' => $request->theme,

        // DA102–DA106
        'funding_type' => $request->funding_type,
        'funding_source' => $request->funding_source,
        'funding_org_type' => $request->funding_org_type,
        'funding_name' => $request->funding_name,
        'budget' => $request->budget,

        'shortcut_link' => $request->shortcut_link,
        'start_month' => $request->start_month,
        'start_year' => $request->start_year,
        'end_month' => $request->end_month,
        'end_year' => $request->end_year,
        'planning_status' => $request->planning_status,
        'proposal_date' => $request->proposal_date,
        'execution_status' => $request->execution_status,
        'contract_date' => $request->contract_date,
        'start_date' => $request->start_date,
        'review_status' => $request->review_status,
        'closure_status' => $request->closure_status,
        'io_code' => $request->io_code,
        'course_code' => $request->course_code,
        'r_codes' => json_encode($request->r_codes ?? []),
        'irb_codes' => json_encode($request->irb_codes ?? []),
        'irb_sources' => json_encode($request->irb_sources ?? []),
        'user_id' => Auth::id(),
    ]);

    

    // ยุทธศาสตร์
    for ($i = 1; $i <= 5; $i++) {
        for ($j = 1; $j <= 5; $j++) {
            $field = "strategy_{$i}_{$j}";
            if (in_array($field, array_keys($request->all()))) {
                $project->$field = true;
            }
        }
        $main = "strategy_{$i}";
        $project->$main = collect(range(1, 5))->contains(function ($j) use ($request, $i) {
            return $request->has("strategy_{$i}_{$j}");
        });
    }

    $project->save();

    // FTE
    foreach ($request->fte_roles ?? [] as $fte) {
        if ($fte['name'] ?? false) {
            FteRole::create([
                'project_id' => $project->id,
                'role_group' => $fte['role_group'] ?? '',
                'name' => $fte['name'],
                'fte_value' => $fte['fte_value'] ?? 0,
            ]);
        }
    }

    // Output
    foreach ($request->outputs ?? [] as $output) {
        if ($output['title'] ?? false) {
            Output::create([
                'project_id' => $project->id,
                'type' => $output['type'] ?? '',
                'title' => $output['title'],
                'year' => $output['year'] ?? '',
            ]);
        }
    }

    return redirect()->route('projects.create')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
}


    public function edit($id)
    {
        $project = Project::find($id);
    
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'ไม่พบโครงการ');
        }
    
        if (Auth::user()->role !== 'admin' && Auth::id() !== $project->user_id) {
            abort(403);
        }
    
        $project->load('fteRoles', 'outputs');
        $project->r_codes = json_decode($project->r_codes ?? '[]', true);
        $project->irb_codes = json_decode($project->irb_codes ?? '[]', true);
        $project->irb_sources = json_decode($project->irb_sources ?? '[]', true);
    
        return view('projects.edit', compact('project'));
    }
public function update(Request $request, Project $project)
{
    if (Auth::user()->role !== 'admin' && Auth::id() !== $project->user_id) {
        abort(403);
    }

    $request->validate([
        'project_name_th' => 'required',
        'project_type' => 'required',
        'theme' => 'required',
        'start_month' => 'required',
        'start_year' => 'required',
    ]);

    $project->fill([
        'project_name_th' => $request->project_name_th,
        'project_name_en' => $request->project_name_en,
        'project_type' => $request->project_type,
        'theme' => $request->theme,
        'funding_type' => $request->funding_type,
        'funding_source' => $request->funding_source,
        'funding_org_type' => $request->funding_org_type,
        'funding_name' => $request->funding_name,
        'budget' => $request->budget,
        'shortcut_link' => $request->shortcut_link,
        'start_month' => $request->start_month,
        'start_year' => $request->start_year,
        'end_month' => $request->end_month,
        'end_year' => $request->end_year,
        'planning_status' => $request->planning_status,
        'proposal_date' => $request->proposal_date,
        'execution_status' => $request->execution_status,
        'contract_date' => $request->contract_date,
        'start_date' => $request->start_date,
        'review_status' => $request->review_status,
        'closure_status' => $request->closure_status,
        'io_code' => $request->io_code,
        'course_code' => $request->course_code,
        'r_codes' => json_encode($request->r_codes ?? []),
        'irb_codes' => json_encode($request->irb_codes ?? []),
        'irb_sources' => json_encode($request->irb_sources ?? []),
    ]);

    // ยุทธศาสตร์
    for ($i = 1; $i <= 5; $i++) {
        for ($j = 1; $j <= 5; $j++) {
            $field = "strategy_{$i}_{$j}";
            $project->$field = $request->has($field);
        }
        $main = "strategy_{$i}";
        $project->$main = collect(range(1, 5))->contains(fn($j) => $request->has("strategy_{$i}_{$j}"));
    }

    $project->save();

    // FTE: ลบทิ้งแล้วเพิ่มใหม่
    $project->fteRoles()->delete();
    foreach ($request->fte_roles ?? [] as $fte) {
        if ($fte['name'] ?? false) {
            FteRole::create([
                'project_id' => $project->id,
                'role_group' => $fte['role_group'] ?? '',
                'name' => $fte['name'],
                'fte_value' => $fte['fte_value'] ?? 0,
            ]);
        }
    }

    // Output
    $project->outputs()->delete();
    foreach ($request->outputs ?? [] as $output) {
        if ($output['title'] ?? false) {
            Output::create([
                'project_id' => $project->id,
                'type' => $output['type'] ?? '',
                'title' => $output['title'],
                'year' => $output['year'] ?? '',
            ]);
        }
    }

    // ✅ COPI
foreach ($request->copis ?? [] as $copi) {
    if (!empty($copi['name'])) {
        Copi::create([
            'project_id' => $project->id,
            'name' => $copi['name'],
            'affiliation' => $copi['affiliation'] ?? '',
        ]);
    }
}


    return redirect()->route('projects.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
}


    // ... methods index, edit, update ...
}
