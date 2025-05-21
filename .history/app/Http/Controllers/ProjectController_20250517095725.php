<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\FteRole;
use App\Models\Output;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name_th' => 'required',
            'project_type' => 'required',
            'theme' => 'required',
            'start_month' => 'required',
            'start_year' => 'required',
        ]);

        $project = Project::create([
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
            'user_id' => Auth::id(),
        ]);

        // Save FTE Roles
        foreach ($request->fte_roles as $fte) {
            FteRole::create([
                'project_id' => $project->id,
                'role_group' => $fte['role_group'],
                'name' => $fte['name'],
                'fte_value' => $fte['fte_value'],
            ]);
        }

        // Save Outputs
        foreach ($request->outputs as $output) {
            Output::create([
                'project_id' => $project->id,
                'type' => $output['type'],
                'title' => $output['title'],
                'year' => $output['year'],
            ]);
        }

        return redirect()->route('projects.create')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }


public function index(Request $request)
{
    $query = Project::query();

    // Filter เฉพาะโครงการของผู้ใช้ ถ้าไม่ใช่ admin
    if (Auth::user()->role !== 'admin') {
        $query->where('user_id', Auth::id());
    }

    // กรองจากฟิลด์ในฟอร์ม (optional)
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
}
