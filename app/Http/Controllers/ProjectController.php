<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\FteRole;
use App\Models\Output;
use App\Models\Copi;
use App\Models\Irb;
use App\Models\CoResearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();

        if (Auth::user()->role !== 'admin') {
            $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);

            $query->where(function ($q) use ($fullName) {
                $q->where('user_id', Auth::id())
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
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

        $lastProject = Project::orderByDesc('id')->first();
        $nextNumber = $lastProject && $lastProject->project_code
            ? ((int) filter_var($lastProject->project_code, FILTER_SANITIZE_NUMBER_INT)) + 1
            : 1;

        $validated['project_code'] = 'SP_' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $validated['user_id'] = Auth::id();
        $validated['updated_by'] = Auth::id(); // <--- สำคัญ!

        $project = new Project($validated);
        $this->fillStrategies($request, $project);
        $project->save();

        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);
        $this->syncCoResearch($request, $project);

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $project = Project::with('fteRoles', 'outputs', 'copis', 'irbs', 'coResearches')->find($id);

        if (!$project) {
            return redirect('/rm')->with('error', 'ไม่พบข้อมูลโครงการ');
        }

        $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin' || Auth::id() === $project->user_id || $project->fteRoles->contains('name', $fullName);

        if (!$hasPermission) {
            abort(403);
        }

        return view('projects.edit', compact('project'));
    }

    public function show(Project $project)
    {
        $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin' || Auth::id() === $project->user_id || $project->fteRoles->contains('name', $fullName);

        if (!$hasPermission) {
            abort(403);
        }

        $project->load(['copis', 'irbs', 'fteRoles', 'outputs', 'updatedBy']);
        return view('projects.detail', compact('project'));
    }

    public function update(Request $request, Project $project)
{
    $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
    $hasPermission = Auth::user()->role === 'admin' || Auth::id() === $project->user_id || $project->fteRoles->contains('name', $fullName);

    if (!$hasPermission) {
        abort(403);
    }

    $validated = $this->validateProject($request);

    // ตั้งค่า updated_by เป็น user ที่กำลังล็อกอิน
    $validated['updated_by'] = Auth::id();

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

    $project->coResearches()->delete();
    $this->syncCoResearch($request, $project);

    return redirect()->route('projects.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
}
    public function destroy(Project $project)
    {
        $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin' || Auth::id() === $project->user_id || $project->fteRoles->contains('name', $fullName);

        if (!$hasPermission) {
            abort(403);
        }

        $project->fteRoles()->delete();
        $project->outputs()->delete();
        $project->copis()->delete();
        $project->irbs()->delete();
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'ลบโครงการเรียบร้อยแล้ว');
    }

public function exportPdf(Project $project)
{
    $project->load(['copis', 'irbs', 'fteRoles', 'outputs', 'updatedBy', 'createdBy']);

    $user = Auth::user();

    // $author = $project->createdBy ? ($project->createdBy->prefix . $project->createdBy->name) : '';
    $author = $project->createdBy ? ($project->createdBy->prefix . $project->createdBy->name) : '';
    $creater = $user ? ($user->prefix . $user->name) : '';
    $dateStr = \Carbon\Carbon::now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
    $filename = "{$dateStr}_{$creater}_{$project->project_name_th}.pdf";

    $pdf = PDF::loadView('projects.pdf', compact('project', 'user'));
    return $pdf->stream($filename);
}


    // ====== SUB-FUNCTIONS ======
    private function validateProject(Request $request): array
    {
        return $request->validate([
            'project_name_th' => 'required|string|max:255',
            'project_name_en' => 'nullable|string|max:255',
            'project_type' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'shortcut_link' => 'nullable|string',
            'r_code' => 'nullable|string',
            'size' => 'nullable|string|max:10',
            'funding_type' => 'nullable|string',
            'funding_source' => 'nullable|string',
            'funding_org_type' => 'nullable|string',
            'funding_name' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'start_year' => 'nullable|string',
            'team_start_date' => 'nullable|date',
            'team_end_date' => 'nullable|date',
            'pi_name' => 'nullable|string',
            'pi_affiliation' => 'nullable|string',
            'planning_status' => 'nullable|string',
            'proposal_date' => 'nullable|string',
            'execution_status' => 'nullable|string',
            'contract_date' => 'nullable|string',
            'start_date' => 'nullable|string',
            'review_status' => 'nullable|string',
            'closure_status' => 'nullable|string',
            'io_code' => 'nullable|string',
            'course_code' => 'nullable|string',
            'end_date' => 'nullable|string',
            'updated_by' => 'nullable|integer',
        ]);
    }

    private function fillStrategies(Request $request, Project $project): void
    {
        $valid_strategies = [
            '1' => [1, 2, 3, 4, 5],
            '2' => [1, 2, 3],
            '3' => [1, 2, 3, 4, 5],
            '4' => [1, 2],
            '5' => [1, 2],
        ];

        foreach ($valid_strategies as $main => $subs) {
            $mainChecked = false;
            foreach ($subs as $sub) {
                $field = "strategy_{$main}_{$sub}";
                $has = $request->has($field);
                if ($project->$field != $has) {
                    $project->$field = $has;
                }
                if ($has) {
                    $mainChecked = true;
                }
            }
            $mainField = "strategy_$main";
            if ($project->$mainField != $mainChecked) {
                $project->$mainField = $mainChecked;
            }
        }
    }

    private function syncFTERoles(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->fte_roles ?? [] as $fte) {
            if (!empty($fte['id'])) {
                $fteRole = \App\Models\FteRole::find($fte['id']);
                if ($fteRole) {
                    $fteRole->update([
                        'role_group'  => $fte['role_group'] ?? '',
                        'name'        => $fte['name'],
                        'job_detail'  => $fte['job_detail'],
                        'start_date'  => $fte['start_date'],
                        'end_date'    => $fte['end_date'],
                        'fte_value'   => $fte['fte_value'] ?? 0,
                        'task_detail' => $fte['task_detail'] ?? null,
                    ]);
                    $ids[] = $fteRole->id;
                }
            } else if (!empty($fte['name'])) {
                $new = \App\Models\FteRole::create([
                    'project_id'  => $project->id,
                    'role_group'  => $fte['role_group'] ?? '',
                    'name'        => $fte['name'],
                    'job_detail'  => $fte['job_detail'],
                    'start_date'  => $fte['start_date'],
                    'end_date'    => $fte['end_date'],
                    'fte_value'   => $fte['fte_value'] ?? 0,
                    'task_detail' => $fte['task_detail'] ?? null,
                ]);
                $ids[] = $new->id;
            }
        }
        $project->fteRoles()->whereNotIn('id', $ids)->delete();
    }

    private function syncOutputs(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->outputs ?? [] as $output) {
            if (!empty($output['id'])) {
                $outputModel = \App\Models\Output::find($output['id']);
                if ($outputModel) {
                    $outputModel->update([
                        'type'           => $output['type'] ?? '',
                        'title'          => $output['title'],
                        'project_output' => $output['project_output'],
                        'year'           => $output['year'] ?? '',
                    ]);
                    $ids[] = $outputModel->id;
                }
            } else if (!empty($output['title'])) {
                $new = \App\Models\Output::create([
                    'project_id'      => $project->id,
                    'type'            => $output['type'] ?? '',
                    'title'           => $output['title'],
                    'project_output'  => $output['project_output'],
                    'year'            => $output['year'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->outputs()->whereNotIn('id', $ids)->delete();
    }

    private function syncCopis(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->copis ?? [] as $copi) {
            if (!empty($copi['id'])) {
                $copiModel = \App\Models\Copi::find($copi['id']);
                if ($copiModel) {
                    $copiModel->update([
                        'name'        => $copi['name'],
                        'affiliation' => $copi['affiliation'] ?? '',
                    ]);
                    $ids[] = $copiModel->id;
                }
            } else if (!empty($copi['name'])) {
                $new = \App\Models\Copi::create([
                    'project_id'  => $project->id,
                    'name'        => $copi['name'],
                    'affiliation' => $copi['affiliation'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->copis()->whereNotIn('id', $ids)->delete();
    }

    private function syncIrbs(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->irbs ?? [] as $irb) {
            if (!empty($irb['id'])) {
                $irbModel = \App\Models\Irb::find($irb['id']);
                if ($irbModel) {
                    $irbModel->update([
                        'code'    => $irb['code'],
                        'exp_date'=> $irb['exp_date'],
                        'source'  => $irb['source'] ?? '',
                    ]);
                    $ids[] = $irbModel->id;
                }
            } else if (!empty($irb['code'])) {
                $new = \App\Models\Irb::create([
                    'project_id' => $project->id,
                    'code'       => $irb['code'],
                    'exp_date'   => $irb['exp_date'],
                    'source'     => $irb['source'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->irbs()->whereNotIn('id', $ids)->delete();
    }

    private function syncCoResearch(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->coresearch ?? [] as $core) {
            if (!empty($core['id'])) {
                $coreModel = \App\Models\CoResearch::find($core['id']);
                if ($coreModel) {
                    $coreModel->update([
                        'name'        => $core['name'],
                        'unit_status' => $core['unit_status'],
                        'affiliation' => $core['affiliation'] ?? '',
                    ]);
                    $ids[] = $coreModel->id;
                }
            } else if (!empty($core['name'])) {
                $new = \App\Models\CoResearch::create([
                    'project_id'  => $project->id,
                    'name'        => $core['name'],
                    'unit_status' => $core['unit_status'],
                    'affiliation' => $core['affiliation'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->coResearches()->whereNotIn('id', $ids)->delete();
    }
}
