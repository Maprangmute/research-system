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
            'irb_codes' => json_encode($request->irb_codes ?? []),
            'r_codes' => json_encode($request->r_codes ?? []),
            'io_code' => $request->io_code,
            'course_code' => $request->course_code,
            'planning_status' => $request->planning_status,
            'proposal_date' => $request->proposal_date,
            'execution_status' => $request->execution_status,
            'contract_date' => $request->contract_date,
            'start_date' => $request->start_date,
            'review_status' => $request->review_status,
            'closure_status' => $request->closure_status,
            'strategy_1' => $request->has('strategy_1'),
            'strategy_1_1' => $request->has('strategy_1_1'),
            'strategy_1_2' => $request->has('strategy_1_2'),
            'strategy_1_3' => $request->has('strategy_1_3'),
            'strategy_1_4' => $request->has('strategy_1_4'),
            'strategy_1_5' => $request->has('strategy_1_5'),
            'strategy_2' => $request->has('strategy_2'),
            'strategy_2_1' => $request->has('strategy_2_1'),
            'strategy_2_2' => $request->has('strategy_2_2'),
            'strategy_2_3' => $request->has('strategy_2_3'),
            'strategy_3' => $request->has('strategy_3'),
            'strategy_3_1' => $request->has('strategy_3_1'),
            'strategy_3_2' => $request->has('strategy_3_2'),
            'strategy_3_3' => $request->has('strategy_3_3'),
            'strategy_3_4' => $request->has('strategy_3_4'),
            'strategy_3_5' => $request->has('strategy_3_5'),
            'strategy_4' => $request->has('strategy_4'),
            'strategy_4_1' => $request->has('strategy_4_1'),
            'strategy_4_2' => $request->has('strategy_4_2'),
            'strategy_5' => $request->has('strategy_5'),
            'strategy_5_1' => $request->has('strategy_5_1'),
            'strategy_5_2' => $request->has('strategy_5_2'),
            'user_id' => Auth::id(),
        ]);

        foreach ($request->fte_roles as $fte) {
            FteRole::create([
                'project_id' => $project->id,
                'role_group' => $fte['role_group'],
                'name' => $fte['name'],
                'fte_value' => $fte['fte_value'],
            ]);
        }

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

    // ... methods index, edit, update ...
}
