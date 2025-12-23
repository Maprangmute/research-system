<?php

namespace App\Http\Controllers;

use App\Models\Output;
use App\Models\Project;
// ⬅️ เพิ่ม
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();
        $user = auth()->user();

        if ($user->role === 'admin' || $user->role === 'user_all') {
            // ✅ admin และ user_all เห็นทุกโปรเจค
        } else {
            // ✅ user ปกติ เห็นเฉพาะที่เกี่ยวข้อง
            $fullName = trim(($user->prefix ?? '').' '.$user->name);
            $query->where(function ($q) use ($fullName, $user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        // filter
        if ($request->filled('year')) {
            $query->where('start_year', $request->year);
        }
        if ($request->filled('type')) {
            $query->where('project_type', 'like', '%'.$request->type.'%');
        }
        if ($request->filled('search')) {
            $query->where('project_name_th', 'like', '%'.$request->search.'%');
        }

        // sort
        $sortable = ['id', 'project_code', 'project_name_th', 'project_type', 'funding_type', 'theme', 'start_year', 'updated_at', 'created_at'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'id';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $projects = $query->orderBy($sort, $dir)
            ->paginate(10)
            ->appends($request->query());

        $fullName = auth()->user()->prefix.' '.auth()->user()->name;

        $missingFTE = \App\Models\FteRole::where('name', $fullName)
            ->where(function ($q) {
                $q->whereNull('fte_value')
                    ->orWhere('fte_value', 0);
            })
            ->with('project:id,project_code,project_name_th,start_year')
            ->get();

        return view('projects.index', [
            'projects' => $projects,
            'missingFTE' => $missingFTE,
        ]);
    }

    public function create()
    {
        return view('projects.create');
    }

public function store(Request $request)
{
    $validated = $this->validateProject($request);

    // ✅ กรอง IRB ที่ไม่มี code ออกก่อน
    $irbs = collect($request->input('irbs', []))
        ->filter(fn ($irb) => !empty($irb['code']))
        ->values()
        ->toArray();

    $request->merge(['irbs' => $irbs]);

    // ✅ validate เฉพาะ IRB ที่มี code จริง
    $request->validate([
        'irbs.*.exp_date' => 'required',
    ]);

    DB::transaction(function () use ($validated, $request, &$project) {

        // 🔐 lock sequence row (atomic 100%)
        $sequence = DB::table('project_sequences')
            ->where('prefix', 'SP')
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new \Exception('Project sequence SP not found');
        }

        $nextNumber = $sequence->current_number + 1;

        DB::table('project_sequences')
            ->where('id', $sequence->id)
            ->update([
                'current_number' => $nextNumber,
                'updated_at' => now(),
            ]);

        $validated['project_code'] = 'SP_'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $validated['user_id'] = Auth::id();

        $project = new Project($validated);
        $this->fillStrategies($request, $project);

        $project->updated_by = Auth::id();
        $project->save();

        // sync relations
        $this->validateOutputs($request);
        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);
        $this->syncCoResearch($request, $project);
    });

    return redirect()
        ->route('projects.show', $project->id)
        ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
}

    public function edit($id)
    {
        $project = Project::with('outputs.authors')->find($id);

        if (! $project) {
            return redirect('/rm')->with('error', 'ไม่พบข้อมูลโครงการ');
        }

        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || Auth::user()->role === 'user_all'
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        return view('projects.edit', compact('project'));
    }

    public function show(Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName)
            || Auth::user()->role === 'user_all';   // all user check

        if (! $hasPermission) {
            abort(403);
        }

        $project->load([
            'copis',
            'irbs',
            'fteRoles',
            'outputs.authors',
            'outputs.conferences',
            'updatedBy',
            'createdBy',   // ✅ เพิ่มตรงนี้
        ]);

        return view('projects.detail', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || Auth::user()->role === 'user_all' // all user check
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        $validated = $this->validateProject($request);
        $this->validateOutputs($request);

        $project->fill($validated);
        $this->fillStrategies($request, $project);

        // ✅ กำกับตรงทุกครั้งที่มีการแก้ไข
        $project->updated_by = Auth::id();
        $project->save();

        $this->syncFTERoles($request, $project);
        $this->syncOutputs($request, $project);
        $this->syncCopis($request, $project);
        $this->syncIrbs($request, $project);
        $this->syncCoResearch($request, $project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(Project $project)
    {
        $fullName = trim(Auth::user()->prefix.' '.Auth::user()->name);

        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (! $hasPermission) {
            abort(403);
        }

        // ลบความสัมพันธ์ที่เกี่ยวข้องก่อน (ถ้ามี foreign key)
        $project->fteRoles()->delete();
        $project->outputs()->delete();
        $project->copis()->delete();
        $project->irbs()->delete();
        $project->coResearches()->delete();

        // แล้วค่อยลบตัวโปรเจค
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'ลบโครงการเรียบร้อยแล้ว');
    }

    public function exportPdf(Project $project)
    {
        $project->load(['copis', 'irbs', 'fteRoles', 'outputs.authors', 'updatedBy', 'createdBy']); // ⬅️ authors

        $user = Auth::user();
        $author = $project->createdBy ? ($project->createdBy->prefix.$project->createdBy->name) : '';
        $creater = $user ? ($user->prefix.$user->name) : '';
        $dateStr = \Carbon\Carbon::now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $filename = "{$dateStr}_{$creater}_{$project->project_name_th}.pdf";

        $pdf = PDF::loadView('projects.pdf', compact('project', 'user'));

        return $pdf->stream($filename);
    }

    // ====== SUB-FUNCTIONS ======

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'project_name_th' => 'required|string',
            'project_name_en' => 'nullable|string',
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
            'project_list' => 'nullable|string|max:255',
        ]);
    }

    private function validateOutputs(Request $request): void
    {
        $request->validate([
            'outputs' => ['nullable', 'array'],
            'outputs.*.id' => ['nullable', 'integer', 'exists:outputs,id'],
            'outputs.*.type' => ['nullable', 'string'],
            'outputs.*.title' => ['nullable', 'string'],
            'outputs.*.project_output' => ['nullable', 'string'],
            'outputs.*.year' => ['nullable', 'string'],

            'outputs.*.journal' => ['nullable', 'string'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],

            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.id' => ['nullable', 'integer', 'exists:output_authors,id'],
            'outputs.*.authors.*.role' => ['nullable', 'string'],
            'outputs.*.authors.*.name' => ['nullable', 'string'],

            // EA103 (Conferences)
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.id' => ['nullable', 'integer', 'exists:output_conferences,id'],
            'outputs.*.conferences.*.name' => ['nullable', 'string'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'string'],
            'outputs.*.conferences.*.location' => ['nullable', 'string'],
            'outputs.*.conferences.*.note' => ['nullable', 'string'],
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
                if ($has != $project->$field) {
                    $project->$field = $has;
                }
                if ($has) {
                    $mainChecked = true;
                }
            }
            $mainField = "strategy_$main";
            if ($mainChecked != $project->$mainField) {
                $project->$mainField = $mainChecked;
            }
        }
    }

    private function syncFTERoles(Request $request, Project $project): void
    {
        $ids = [];

        foreach ($request->fte_roles ?? [] as $fte) {
            $payload = [
                'role_group' => $fte['role_group'] ?? '',
                'name' => $fte['name'] ?? null,
                'job_detail' => $fte['job_detail'] ?? null,
                'start_date' => $fte['start_date'] ?? null,
                'hours' => $fte['hours'] ?? null,
                'end_date' => $fte['end_date'] ?? null,
                'fte_value' => $fte['fte_value'] ?? 0,
                'fte_value_research' => $fte['fte_value_research'] ?? 0,
                'task_detail' => $fte['task_detail'] ?? null,
            ];

            if (! empty($fte['id'])) {
                $fteRole = $project->fteRoles()->whereKey($fte['id'])->first();
                if ($fteRole) {
                    $fteRole->update($payload);
                    $ids[] = $fteRole->id;
                }
            } elseif (! empty($fte['name'])) {
                $new = $project->fteRoles()->create($payload);
                $ids[] = $new->id;
            }
        }

        $project->fteRoles()->whereNotIn('id', $ids)->delete();
    }

    private function syncOutputs(Request $request, Project $project): void
    {
        $rows = $request->input('outputs', []);
        $keepIds = [];

        foreach ($rows as $row) {
            if (! $this->hasAnyOutputData($row)) {
                continue;
            }

            $payload = [
                'project_id' => $project->id,
                'type' => $row['type'] ?? null,
                'title' => $row['title'] ?? null,
                'project_output' => $row['project_output'] ?? ($row['output'] ?? null),
                'year' => $row['year'] ?? null,
                'journal' => null,
                'quartile' => null,
                'impact_factor' => null,
            ];

            $type = (string) ($payload['type'] ?? '');
            $isEA101 = str_starts_with($type, 'EA101');
            $isEA103 = str_starts_with($type, 'EA103');

            if ($isEA101) {
                $payload['journal'] = $row['journal'] ?? null;
                $payload['quartile'] = $this->normalizeQuartile($row['quartile'] ?? null);
                $payload['impact_factor'] = $row['impact_factor'] ?? null;
            }

            if (! empty($row['id'])) {
                $model = $project->outputs()->whereKey($row['id'])->first();
                if ($model) {
                    $model->update($payload);

                    // relations
                    $this->syncOutputAuthors($model, $row['authors'] ?? null, $isEA101);
                    $this->syncOutputConferences($model, $row['conferences'] ?? null, $isEA103);

                    $keepIds[] = $model->id;
                }
            } else {
                $new = $project->outputs()->create($payload);

                // relations
                $this->syncOutputAuthors($new, $row['authors'] ?? null, $isEA101);
                $this->syncOutputConferences($new, $row['conferences'] ?? null, $isEA103);

                $keepIds[] = $new->id;
            }
        }

        $project->outputs()
            ->when($keepIds, fn ($q) => $q->whereNotIn('id', $keepIds))
            ->delete();
    }

    private function syncOutputAuthors(Output $output, ?array $authors, bool $isEA101): void
    {
        if (! $isEA101) {
            $output->authors()->delete();

            return;
        }

        $authors = $authors ?? []; // ป้องกัน null

        if (! $authors) {
            $output->authors()->delete();

            return;
        }

        $ids = [];
        foreach ($authors as $author) {
            if (empty($author['name']) || empty($author['role'])) {
                continue;
            }

            $payload = [
                'role' => strtolower(trim($author['role'])),
                'name' => trim($author['name']),
            ];

            if (! empty($author['id'])) {
                $model = $output->authors()->find($author['id']);
                if ($model) {
                    $model->update($payload);
                    $ids[] = $model->id;
                }
            } else {
                $new = $output->authors()->create($payload);
                $ids[] = $new->id;
            }
        }

        if ($ids) {
            $output->authors()->whereNotIn('id', $ids)->delete();
        }
    }

    private function syncOutputConferences(Output $output, ?array $confs, bool $isEA103): void
    {
        if (! $isEA103) {
            $output->conferences()->delete();

            return;
        }
        if ($confs === null) {
            // ไม่ได้แตะ conferences -> ไม่ทำอะไร
            return;
        }

        $ids = [];
        foreach ($confs as $conf) {
            $payload = [
                'name' => $conf['name'] ?? null,
                'presentation_type' => $conf['presentation_type'] ?? null,
                'location' => $conf['location'] ?? null,
                'note' => $conf['note'] ?? null,
            ];

            // ว่างหมดก็ข้าม
            if (
                empty($payload['name']) &&
                empty($payload['presentation_type']) &&
                empty($payload['location']) &&
                empty($payload['note'])
            ) {
                continue;
            }

            if (! empty($conf['id'])) {
                $m = $output->conferences()->whereKey($conf['id'])->first();
                if ($m) {
                    $m->update($payload);
                    $ids[] = $m->id;
                }
            } else {
                $new = $output->conferences()->create($payload);
                $ids[] = $new->id;
            }
        }

        if (! empty($ids)) {
            $output->conferences()->whereNotIn('id', $ids)->delete();
        }
    }

    private function hasAnyOutputData(array $row): bool
    {
        return (bool) (
            ($row['type'] ?? null) ||
            ($row['title'] ?? null) ||
            ($row['project_output'] ?? ($row['output'] ?? null)) ||
            ($row['year'] ?? null) ||
            ($row['journal'] ?? null) ||
            ($row['quartile'] ?? null) ||
            ($row['impact_factor'] ?? null) ||
            (! empty($row['authors']))
        );
    }

    private function normalizeQuartile(?string $q): ?string
    {
        if (! $q) {
            return null;
        }
        $q = strtoupper(trim($q));
        if (in_array($q, ['Q1', 'Q2', 'Q3', 'Q4'], true)) {
            return $q;
        }
        if (in_array($q, ['1', '2', '3', '4'], true)) {
            return 'Q'.$q;
        }

        return null;
    }

    private function syncCopis(Request $request, Project $project): void
    {
        $ids = [];
        foreach ($request->copis ?? [] as $copi) {
            if (! empty($copi['id'])) {
                $copiModel = \App\Models\Copi::find($copi['id']);
                if ($copiModel) {
                    $copiModel->update([
                        'name' => $copi['name'],
                        'affiliation' => $copi['affiliation'] ?? '',
                    ]);
                    $ids[] = $copiModel->id;
                }
            } elseif (! empty($copi['name'])) {
                $new = \App\Models\Copi::create([
                    'project_id' => $project->id,
                    'name' => $copi['name'],
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
            if (! empty($irb['id'])) {
                $irbModel = \App\Models\Irb::find($irb['id']);
                if ($irbModel) {
                    $irbModel->update([
                        'code' => $irb['code'],
                        'exp_date' => $irb['exp_date'],
                        'source' => $irb['source'] ?? '',
                    ]);
                    $ids[] = $irbModel->id;
                }
            } elseif (! empty($irb['code'])) {
                $new = \App\Models\Irb::create([
                    'project_id' => $project->id,
                    'code' => $irb['code'],
                    'exp_date' => $irb['exp_date'],
                    'source' => $irb['source'] ?? '',
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
            if (! empty($core['id'])) {
                $coreModel = \App\Models\CoResearch::find($core['id']);
                if ($coreModel) {
                    $coreModel->update([
                        'name' => $core['name'],
                        'unit_status' => $core['unit_status'],
                        'affiliation' => $core['affiliation'] ?? '',
                    ]);
                    $ids[] = $coreModel->id;
                }
            } elseif (! empty($core['name'])) {
                $new = \App\Models\CoResearch::create([
                    'project_id' => $project->id,
                    'name' => $core['name'],
                    'unit_status' => $core['unit_status'],
                    'affiliation' => $core['affiliation'] ?? '',
                ]);
                $ids[] = $new->id;
            }
        }
        $project->coResearches()->whereNotIn('id', $ids)->delete();
    }

    public function checkProjectName(Request $request)
    {
        $name = $request->input('project_name_th');

        $exact = Project::where('project_name_th', $name)->first();

        $similar = Project::where('project_name_th', 'LIKE', '%'.$name.'%')
            ->take(5)
            ->get(['project_name_th', 'pi_name', 'start_year'])
            ->map(function ($project) {
                return [
                    'project_name_th' => $project->project_name_th,
                    'pi_name' => $project->pi_name ?? 'ไม่ได้ลงข้อมูล',
                    'start_year' => $project->start_year ?? 'ไม่ได้ลงข้อมูล',
                ];
            });

        return response()->json([
            'exact' => $exact ? true : false,
            'similar' => $similar,
        ]);
    }
}
