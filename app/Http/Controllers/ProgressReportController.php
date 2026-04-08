<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProgressReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressReportController extends Controller
{
    private function sanitizeRowPayload(Request $request): void
    {
        $request->merge([
            'progress_rows' => $this->normalizeProgressRows($request->input('progress_rows', [])),
            'issue_rows' => $this->normalizeIssueRows($request->input('issue_rows', [])),
        ]);
    }

    private function reportValidationRules(): array
    {
        return [
            'submit_date' => 'required|date_format:d-m-Y',
            'progress_title' => 'required|string|max:255',
            'file_info' => 'nullable|string|max:255',
            'progress_rows' => 'nullable|array',
            'progress_rows.*.plan' => 'required|string|max:2000',
            'progress_rows.*.actual' => 'required|string|max:2000',
            'progress_rows.*.alignment' => 'required|string|max:255',
            'issue_rows' => 'nullable|array',
            'issue_rows.*.problem' => 'required|string|max:2000',
            'issue_rows.*.solution' => 'required|string|max:2000',
            'issue_rows.*.consultation' => 'required|string|max:2000',
        ];
    }

    private function reportValidationMessages(): array
    {
        return [
            'required' => 'กรุณากรอก :attribute',
        ];
    }

    private function reportValidationAttributes(): array
    {
        return [
            'submit_date' => 'วันที่รายงาน',
            'progress_title' => 'หัวข้อรายงาน',
            'progress_rows.*.plan' => 'แผนการดำเนินงาน',
            'progress_rows.*.actual' => 'การดำเนินงานจริง',
            'progress_rows.*.alignment' => 'ความสอดคล้องกับแผน',
            'issue_rows.*.problem' => 'ปัญหาอุปสรรค',
            'issue_rows.*.solution' => 'แนวทางการแก้ปัญหา',
            'issue_rows.*.consultation' => 'ประเด็นขอคำปรึกษาจากที่ประชุม',
        ];
    }

    private function progressIndexRouteParams(Project $project, Request $request): array
    {
        $params = ['project' => $project];
        if ($request->boolean('modal')) {
            $params['modal'] = 1;
        }

        return $params;
    }

    private function buildModalSummary(Project $project): array
    {
        $latestReport = $project->progressReports()
            ->orderByDesc('submit_date')
            ->orderByDesc('id')
            ->first();

        $formatDateThai = function ($value) {
            if (!$value) {
                return '-';
            }

            try {
                $d = Carbon::parse($value)->locale('th');

                return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
            } catch (\Throwable $e) {
                return (string) $value;
            }
        };

        $latestTitle = $latestReport
            ? (trim((string) ($latestReport->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน')
            : '';

        return [
            'count' => $project->progressReports()->count(),
            'latestDate' => $latestReport ? $formatDateThai($latestReport->submit_date) : '',
            'submitter' => $latestReport ? (string) ($latestReport->submitter_name ?? '') : '',
            'title' => $latestTitle,
            'editUrl' => $latestReport
                ? route('projects.progress_reports.edit', [
                    'project' => $project,
                    'progress_report' => $latestReport->id,
                    'modal' => 1,
                ])
                : '',
        ];
    }

    private function modalSuccessResponse(Project $project, string $message)
    {
        return response()->view('progress_reports.modal_close', [
            'project' => $project,
            'message' => $message,
            'summary' => $this->buildModalSummary($project),
        ]);
    }

    private function authorizeProject(Project $project): void
    {
        $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::user()->role === 'user_all'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (!$hasPermission) {
            abort(403);
        }
    }

    private function ensureReportBelongsToProject(Project $project, ProgressReport $report): void
    {
        if ((int) $report->project_id !== (int) $project->id) {
            abort(404);
        }
    }

    private function normalizeProgressRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                $plan = trim((string) ($row['plan'] ?? ''));
                $actual = trim((string) ($row['actual'] ?? ''));
                $alignment = trim((string) ($row['alignment'] ?? ''));

                return [
                    'plan' => $plan,
                    'actual' => $actual,
                    'alignment' => $alignment,
                ];
            })
            ->filter(fn($row) => $row['plan'] !== '' || $row['actual'] !== '' || $row['alignment'] !== '')
            ->values()
            ->all();
    }

    private function normalizeIssueRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                $problem = trim((string) ($row['problem'] ?? ''));
                $solution = trim((string) ($row['solution'] ?? ''));
                $consultation = trim((string) ($row['consultation'] ?? ''));

                return [
                    'problem' => $problem,
                    'solution' => $solution,
                    'consultation' => $consultation,
                ];
            })
            ->filter(fn($row) => $row['problem'] !== '' || $row['solution'] !== '' || $row['consultation'] !== '')
            ->values()
            ->all();
    }

    private function normalizeSubmitDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        foreach (['d-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    // แสดง progress ทั้งหมดของโครงการ
    public function index(Project $project)
    {
        $this->authorizeProject($project);

        $progressReports = $project->progressReports()
            ->with(['creator', 'updater'])
            ->orderByDesc('submit_date')
            ->orderByDesc('id')
            ->get();

        return view('progress_reports.index', compact('project', 'progressReports'));
    }

    // ฟอร์มสร้าง progress
    public function create(Project $project)
    {
        $this->authorizeProject($project);

        return view('progress_reports.create', compact('project'));
    }

    // บันทึก progress ใหม่
    public function store(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $this->sanitizeRowPayload($request);

        $data = $request->validate(
            $this->reportValidationRules(),
            $this->reportValidationMessages(),
            $this->reportValidationAttributes()
        );
        $data['project_id'] = $project->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['submitter_name'] = trim((string) (Auth::user()->prefix ?? '') . ' ' . (string) (Auth::user()->name ?? ''));
        if ($data['submitter_name'] === '') {
            $data['submitter_name'] = '-';
        }
        $data['submit_date'] = $this->normalizeSubmitDate((string) ($data['submit_date'] ?? ''));
        $data['progress_title'] = trim((string) ($data['progress_title'] ?? ''));
        $data['progress_subtitle'] = '';
        $data['progress_rows'] = $request->input('progress_rows', []);
        $data['issue_rows'] = $request->input('issue_rows', []);

        ProgressReport::create($data);

        if ($request->boolean('modal')) {
            return $this->modalSuccessResponse($project, 'เพิ่ม Progress Report สำเร็จ');
        }

        return redirect()->route('projects.progress_reports.index', $this->progressIndexRouteParams($project, $request))
            ->with('success', 'เพิ่ม Progress Report สำเร็จ');
    }

    // ดูรายละเอียด progress
    public function show(Project $project, ProgressReport $progress_report)
    {
        $this->authorizeProject($project);
        $this->ensureReportBelongsToProject($project, $progress_report);

        return view('progress_reports.show', [
            'project' => $project,
            'report' => $progress_report
        ]);
    }

    // ฟอร์มแก้ไข progress
    public function edit(Project $project, ProgressReport $progress_report)
    {
        $this->authorizeProject($project);
        $this->ensureReportBelongsToProject($project, $progress_report);

        return view('progress_reports.edit', [
            'project' => $project,
            'report' => $progress_report
        ]);
    }

    // อัปเดต progress
    public function update(Request $request, Project $project, ProgressReport $progress_report)
    {
        $this->authorizeProject($project);
        $this->ensureReportBelongsToProject($project, $progress_report);

        $this->sanitizeRowPayload($request);

        $data = $request->validate(
            $this->reportValidationRules(),
            $this->reportValidationMessages(),
            $this->reportValidationAttributes()
        );
        $data['submitter_name'] = trim((string) (Auth::user()->prefix ?? '') . ' ' . (string) (Auth::user()->name ?? ''));
        if ($data['submitter_name'] === '') {
            $data['submitter_name'] = trim((string) ($progress_report->submitter_name ?? '')) ?: '-';
        }
        $data['submit_date'] = $this->normalizeSubmitDate((string) ($data['submit_date'] ?? ''));
        $data['updated_by'] = Auth::id();
        $data['progress_title'] = trim((string) ($data['progress_title'] ?? ''));
        $data['progress_subtitle'] = '';
        $data['progress_rows'] = $request->input('progress_rows', []);
        $data['issue_rows'] = $request->input('issue_rows', []);

        $progress_report->update($data);

        if ($request->boolean('modal')) {
            return $this->modalSuccessResponse($project, 'อัปเดต Progress Report สำเร็จ');
        }

        return redirect()->route('projects.progress_reports.index', $this->progressIndexRouteParams($project, $request))
            ->with('success', 'อัปเดต Progress Report สำเร็จ');
    }

    // ลบ progress
    public function destroy(Request $request, Project $project, ProgressReport $progress_report)
    {
        $this->authorizeProject($project);
        $this->ensureReportBelongsToProject($project, $progress_report);

        $progress_report->delete();
        return redirect()->route('projects.progress_reports.index', $this->progressIndexRouteParams($project, $request))
            ->with('success', 'ลบ Progress Report สำเร็จ');
    }
}
