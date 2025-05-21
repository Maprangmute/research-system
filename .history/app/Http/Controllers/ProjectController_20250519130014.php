<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\FteRole;
use App\Models\Output;
use App\Models\Copi;
use App\Models\Irb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Barryvdh\DomPDF\Facade\Pdf;


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
        $validated = $this->validateProject($request);

        $project = new Project($validated + [
            'user_id' => Auth::id(),
        ]);

        $this->fillStrategies($request, $project);
        $project->save();

        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);

        return redirect()->route('projects.create')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $project = Project::with('fteRoles', 'outputs', 'copis', 'irbs')->find($id);

        if (!$project) {
            return redirect('/rm')->with('error', 'ไม่พบข้อมูลโครงการ');
        }

        if (Auth::user()->role !== 'admin' && Auth::id() !== $project->user_id) {
            abort(403);
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() !== $project->user_id) {
            abort(403);
        }

        $validated = $this->validateProject($request);
        $project->fill($validated);
        $this->fillStrategies($request, $project);
        $project->save();

        $project->fteRoles()->delete();
        $this->syncFTERoles($request, $project);

        $project->outputs()->delete();
        $this->syncOutputs($request, $project);

        $project->copis()->delete();
        $this->syncCopis($request, $project);

        $project->irbs()->delete();
        $this->syncIrbs($request, $project);

        return redirect()->route('projects.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    // ---------------------------
    // 🔐 Private helper methods
    // ---------------------------

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'project_name_th' => 'required|string|max:255',
            'project_name_en' => 'nullable|string|max:255',
            'project_type' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'shortcut_link' => 'nullable|string',
            'size' => 'nullable|string|max:10',
            'funding_type' => 'nullable|string',
            'funding_source' => 'nullable|string',
            'funding_org_type' => 'nullable|string',
            'funding_name' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'start_month' => 'required|string',
            'start_year' => 'required|string',
            'end_month' => 'nullable|string',
            'end_year' => 'nullable|string',
            'planning_status' => 'nullable|string',
            'proposal_date' => 'nullable|string',
            'execution_status' => 'nullable|string',
            'contract_date' => 'nullable|string',
            'start_date' => 'nullable|string',
            'review_status' => 'nullable|string',
            'closure_status' => 'nullable|string',
            'io_code' => 'nullable|string',
            'course_code' => 'nullable|string',
        ]);
    }

    private function fillStrategies(Request $request, Project $project): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $main = "strategy_{$i}";
            $project->$main = false;

            for ($j = 1; $j <= 5; $j++) {
                $field = "strategy_{$i}_{$j}";
                $has = $request->has($field);
                $project->$field = $has;
                if ($has) {
                    $project->$main = true;
                }
            }
        }
    }

    private function syncFTERoles(Request $request, Project $project): void
    {
        foreach ($request->fte_roles ?? [] as $fte) {
            if (!empty($fte['name'])) {
                FteRole::create([
                    'project_id' => $project->id,
                    'role_group' => $fte['role_group'] ?? '',
                    'name' => $fte['name'],
                    'fte_value' => $fte['fte_value'] ?? 0,
                ]);
            }
        }
    }

    private function syncOutputs(Request $request, Project $project): void
    {
        foreach ($request->outputs ?? [] as $output) {
            if (!empty($output['title'])) {
                Output::create([
                    'project_id' => $project->id,
                    'type' => $output['type'] ?? '',
                    'title' => $output['title'],
                    'year' => $output['year'] ?? '',
                ]);
            }
        }
    }

    private function syncCopis(Request $request, Project $project): void
    {
        foreach ($request->copis ?? [] as $copi) {
            if (!empty($copi['name'])) {
                Copi::create([
                    'project_id' => $project->id,
                    'name' => $copi['name'],
                    'affiliation' => $copi['affiliation'] ?? '',
                ]);
            }
        }
    }

    private function syncIrbs(Request $request, Project $project): void
    {
        foreach ($request->irbs ?? [] as $irb) {
            if (!empty($irb['code'])) {
                Irb::create([
                    'project_id' => $project->id,
                    'code' => $irb['code'],
                    'source' => $irb['source'] ?? '',
                ]);
            }
        }
    }

    public function exportPdf(Project $project)
    {
        $project->load([
            'copis',
            'irbs',
            'fteRoles',
            'outputs'
        ]);

        $pdf = Pdf::loadView('projects.pdf', compact('project'));
        return $pdf->stream("project-{$project->id}.pdf");
    }
    public function show(Project $project)
    {
        $project->load(['copis', 'irbs', 'fteRoles', 'outputs']);
        return view('projects.detail', compact('project'));
    }
}
