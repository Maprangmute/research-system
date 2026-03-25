<?php

namespace App\Http\Controllers;

use App\Models\FteRole;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PDF;

class GanttController extends Controller
{
    public function index(Project $project)
    {
        $this->authorizeProjectAccess($project);

        return view('gantt.index', compact('project'));
    }

    public function export(Request $request, Project $project)
    {
        $this->authorizeProjectAccess($project);

        $format = strtolower(trim((string) $request->query('format', 'pdf')));
        if (!in_array($format, ['pdf', 'xls', 'excel'], true)) {
            $format = 'pdf';
        }

        $payload = $this->buildExportPayload($project, $request);
        $stamp = now()->setTimezone('Asia/Bangkok')->format('Ymd_His');
        $projectSlug = trim((string) ($project->project_code ?: $project->id));
        $baseName = "gantt_{$projectSlug}_{$stamp}";

        if ($format === 'pdf') {
            $pdf = PDF::loadView('gantt.export-pdf', $payload, [], [
                'format' => 'A3-L',
            ]);

            return $pdf->stream("{$baseName}.pdf");
        }

        $html = view('gantt.export-xls', $payload)->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$baseName}.xls\"",
        ]);
    }

    private function buildExportPayload(Project $project, Request $request): array
    {
        $filters = [
            'search' => mb_strtolower(trim((string) $request->query('search', '')), 'UTF-8'),
            'owner' => mb_strtolower(trim((string) $request->query('owner', '')), 'UTF-8'),
            'status' => trim((string) $request->query('status', 'all')),
            'scale' => trim((string) $request->query('scale', 'year')),
        ];

        $normalTasks = Task::query()
            ->where('project_id', $project->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function (Task $task) {
                return [
                    'id' => (string) $task->id,
                    'text' => trim((string) $task->title),
                    'start_date' => optional($task->start)->format('Y-m-d'),
                    'end_date' => optional($task->end)->format('Y-m-d'),
                    'parent' => $task->parent_id ? (string) $task->parent_id : '0',
                    'progress' => (int) $task->progress,
                    'workflow_status' => $this->normalizeWorkflowStatus($task->workflow_status ?? $task->status, (int) $task->progress),
                    'owner' => trim((string) $task->owner),
                    'order' => (int) ($task->order ?? 0),
                    'type' => 'task',
                    'is_fte' => false,
                ];
            })
            ->all();

        $fteTasks = $this->buildFteTaskRows($project);
        $visibleRows = array_values(array_filter(
            array_merge($normalTasks, $fteTasks),
            fn (array $row) => $this->taskMatchesFilter($row, $filters)
        ));
        $sortedRows = $this->sortTaskRowsByStartDate($visibleRows);
        $leveledRows = $this->buildExportRows($sortedRows);
        $timeline = $this->buildMonthlyTimeline($leveledRows);

        $generatedAt = now()->setTimezone('Asia/Bangkok');

        return [
            'project' => $project,
            'rows' => $timeline['rows'],
            'timeline' => $timeline,
            'generatedAt' => $generatedAt,
            'generatedAtThai' => $this->toThaiDateTimeBe($generatedAt),
            'generatedBy' => trim(((string) auth()->user()?->prefix) . ' ' . ((string) auth()->user()?->name)) ?: 'ไม่ระบุ',
            'filters' => $filters,
            'filterSummary' => $this->buildFilterSummary($filters),
            'timelineModeLabel' => 'แผนภาพ Gantt รายเดือน',
        ];
    }

    private function buildFteTaskRows(Project $project): array
    {
        $rows = FteRole::query()
            ->where('project_id', $project->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $fteTasks = [];
        $minStart = null;
        $maxEnd = null;
        $today = now()->startOfDay();

        foreach ($rows as $row) {
            $start = $this->parseFlexibleDate($row->start_date);
            $end = $this->parseFlexibleDate($row->end_date);
            if (! $start || ! $end) {
                continue;
            }

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            $minStart = $minStart ? $minStart->min($start) : $start->copy();
            $maxEnd = $maxEnd ? $maxEnd->max($end) : $end->copy();

            $fteProgress = (float) ($row->fte_value_research ?? $row->fte_value ?? 0);
            if ($fteProgress <= 1) {
                $fteProgress *= 100;
            }
            $fteProgress = (int) max(0, min(100, round($fteProgress)));

            $name = trim((string) ($row->name ?? ''));
            $taskDetail = trim((string) ($row->task_detail ?? ''));
            $roleGroup = trim((string) ($row->role_group ?? ''));
            $title = $taskDetail !== '' ? $taskDetail : ($roleGroup !== '' ? $roleGroup : 'ภาระงาน #'.$row->id);

            $fteTasks[] = [
                'id' => 'fte-'.$row->id,
                'text' => $title,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'parent' => 'fte-root',
                'progress' => $fteProgress,
                'workflow_status' => $end->lt($today) ? 'completed' : 'in_progress',
                'owner' => $name,
                'order' => (int) $row->id,
                'type' => 'fte',
                'is_fte' => true,
            ];
        }

        if ($fteTasks === []) {
            return [];
        }

        array_unshift($fteTasks, [
            'id' => 'fte-root',
            'text' => 'งานภาระงาน (จาก FTE)',
            'start_date' => ($minStart ?? now())->format('Y-m-d'),
            'end_date' => ($maxEnd ?? now())->format('Y-m-d'),
            'parent' => '0',
            'progress' => 0,
            'workflow_status' => 'in_progress',
            'owner' => '',
            'order' => -1,
            'type' => 'summary',
            'is_fte' => true,
        ]);

        return $fteTasks;
    }

    private function taskMatchesFilter(array $task, array $filters): bool
    {
        $search = $filters['search'] ?? '';
        $ownerFilter = $filters['owner'] ?? '';
        $statusFilter = $filters['status'] ?? 'all';

        if ($search !== '') {
            $haystack = mb_strtolower(trim(($task['text'] ?? '').' '.($task['owner'] ?? '').' '.($task['id'] ?? '')), 'UTF-8');
            if (!str_contains($haystack, $search)) {
                return false;
            }
        }

        if ($ownerFilter !== '') {
            $owner = mb_strtolower(trim((string) ($task['owner'] ?? '')), 'UTF-8');
            if (!str_contains($owner, $ownerFilter)) {
                return false;
            }
        }

        if ($statusFilter === 'all') {
            return true;
        }

        if ($statusFilter === 'fte') {
            return !empty($task['is_fte']);
        }

        if (!empty($task['is_fte'])) {
            return false;
        }

        return $this->normalizeWorkflowStatus($task['workflow_status'] ?? null, (int) ($task['progress'] ?? 0)) === $statusFilter;
    }

    private function sortTaskRowsByStartDate(array $rows): array
    {
        $childrenByParent = [];
        $ordered = [];
        $visited = [];

        foreach ($rows as $row) {
            $parentKey = $this->normalizeParentKey($row['parent'] ?? '0');
            $childrenByParent[$parentKey] ??= [];
            $childrenByParent[$parentKey][] = $row;
        }

        $visit = function (string $parentKey) use (&$visit, &$childrenByParent, &$ordered, &$visited) {
            $children = $childrenByParent[$parentKey] ?? [];
            usort($children, fn ($left, $right) => $this->compareTaskRowsByStartDate($left, $right));

            foreach ($children as $row) {
                $rowId = (string) ($row['id'] ?? '');
                if (isset($visited[$rowId])) {
                    continue;
                }

                $visited[$rowId] = true;
                $ordered[] = $row;
                $visit($rowId);
            }
        };

        $visit('0');

        $remaining = array_values(array_filter($rows, fn ($row) => !isset($visited[(string) ($row['id'] ?? '')])));
        usort($remaining, fn ($left, $right) => $this->compareTaskRowsByStartDate($left, $right));

        foreach ($remaining as $row) {
            $rowId = (string) ($row['id'] ?? '');
            if (isset($visited[$rowId])) {
                continue;
            }

            $visited[$rowId] = true;
            $ordered[] = $row;
            $visit($rowId);
        }

        return $ordered;
    }

    private function buildExportRows(array $rows): array
    {
        $presentIds = [];
        foreach ($rows as $row) {
            $presentIds[(string) ($row['id'] ?? '')] = true;
        }

        $levelCache = [];
        $resolveLevel = function (array $row) use (&$resolveLevel, &$levelCache, $presentIds, $rows): int {
            $rowId = (string) ($row['id'] ?? '');
            if (isset($levelCache[$rowId])) {
                return $levelCache[$rowId];
            }

            $parent = (string) ($row['parent'] ?? '0');
            if ($parent === '0' || !isset($presentIds[$parent])) {
                return $levelCache[$rowId] = 0;
            }

            foreach ($rows as $candidate) {
                if ((string) ($candidate['id'] ?? '') === $parent) {
                    return $levelCache[$rowId] = min(6, $resolveLevel($candidate) + 1);
                }
            }

            return $levelCache[$rowId] = 0;
        };

        $result = [];
        foreach ($rows as $row) {
            $start = $this->parseFlexibleDate($row['start_date'] ?? null);
            $end = $this->parseFlexibleDate($row['end_date'] ?? null);
            if (! $start || ! $end) {
                continue;
            }

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            $status = $this->normalizeWorkflowStatus($row['workflow_status'] ?? null, (int) ($row['progress'] ?? 0));
            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'text' => trim((string) ($row['text'] ?? '')) ?: '-',
                'owner' => trim((string) ($row['owner'] ?? '')) ?: '-',
                'start' => $start,
                'end' => $end,
                'start_label' => $this->toThaiDateBe($start),
                'end_label' => $this->toThaiDateBe($end),
                'status_key' => $status,
                'status_label' => $this->statusLabel($status, !empty($row['is_fte'])),
                'level' => $resolveLevel($row),
                'is_fte' => !empty($row['is_fte']),
                'is_summary' => (string) ($row['id'] ?? '') === 'fte-root',
            ];
        }

        return $result;
    }

    private function buildMonthlyTimeline(array $rows): array
    {
        if ($rows === []) {
            $today = now()->startOfMonth();
            return [
                'rows' => [],
                'periods' => [[
                    'key' => $today->format('Y-m'),
                    'label' => $this->toThaiMonthYear($today),
                    'start' => $today->copy()->startOfMonth(),
                    'end' => $today->copy()->endOfMonth(),
                ]],
                'column_count' => 1,
                'font_size' => '11px',
                'cell_width' => '86px',
            ];
        }

        $minStart = collect($rows)->min(fn ($row) => $row['start']->copy()->startOfMonth()->timestamp);
        $maxEnd = collect($rows)->max(fn ($row) => $row['end']->copy()->endOfMonth()->timestamp);

        $cursor = Carbon::createFromTimestamp($minStart)->startOfMonth();
        $end = Carbon::createFromTimestamp($maxEnd)->endOfMonth();
        $periods = [];

        while ($cursor->lte($end)) {
            $periods[] = [
                'key' => $cursor->format('Y-m'),
                'label' => $this->toThaiMonthYear($cursor),
                'start' => $cursor->copy()->startOfMonth(),
                'end' => $cursor->copy()->endOfMonth(),
            ];
            $cursor->addMonthNoOverflow()->startOfMonth();
        }

        $columnCount = count($periods);
        $fontSize = $columnCount > 30 ? '8px' : ($columnCount > 20 ? '9px' : '10px');
        $cellWidth = $columnCount > 30 ? '52px' : ($columnCount > 20 ? '62px' : '76px');

        foreach ($rows as &$row) {
            $cells = [];
            foreach ($periods as $period) {
                $isActive = $row['start']->lte($period['end']) && $row['end']->gte($period['start']);
                $isStart = $row['start']->betweenIncluded($period['start'], $period['end']);
                $isEnd = $row['end']->betweenIncluded($period['start'], $period['end']);
                $cells[] = [
                    'active' => $isActive,
                    'class' => $isActive
                        ? ($isStart && $isEnd ? 'is-single' : ($isStart ? 'is-start' : ($isEnd ? 'is-end' : 'is-mid')))
                        : '',
                ];
            }
            $row['cells'] = $cells;
        }
        unset($row);

        return [
            'rows' => $rows,
            'periods' => $periods,
            'column_count' => $columnCount,
            'font_size' => $fontSize,
            'cell_width' => $cellWidth,
        ];
    }

    private function buildFilterSummary(array $filters): array
    {
        $summary = [];
        if (($filters['search'] ?? '') !== '') {
            $summary[] = 'ค้นหา: '.$filters['search'];
        }
        if (($filters['owner'] ?? '') !== '') {
            $summary[] = 'ผู้รับผิดชอบ: '.$filters['owner'];
        }
        if (($filters['status'] ?? 'all') !== 'all') {
            $summary[] = 'สถานะ: '.$this->statusLabel($filters['status'], $filters['status'] === 'fte');
        }

        return $summary;
    }

    private function normalizeWorkflowStatus($value, int $progress = 0): string
    {
        $status = mb_strtolower(trim((string) $value), 'UTF-8');

        $map = [
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'delayed' => 'delayed',
            'starting' => 'starting',
            'paused' => 'paused',
            'not_started' => 'not_started',
            'ongoing' => 'in_progress',
            'done' => 'completed',
        ];

        if (isset($map[$status])) {
            return $map[$status];
        }

        return $progress >= 100 ? 'completed' : 'in_progress';
    }

    private function compareTaskRowsByStartDate(array $left, array $right): int
    {
        $leftId = (string) ($left['id'] ?? '');
        $rightId = (string) ($right['id'] ?? '');

        if ($leftId === 'fte-root' && $rightId !== 'fte-root') {
            return -1;
        }
        if ($rightId === 'fte-root' && $leftId !== 'fte-root') {
            return 1;
        }

        $leftStart = $this->parseFlexibleDate($left['start_date'] ?? null);
        $rightStart = $this->parseFlexibleDate($right['start_date'] ?? null);
        if ($leftStart && $rightStart) {
            $startDiff = $leftStart->getTimestamp() <=> $rightStart->getTimestamp();
            if ($startDiff !== 0) {
                return $startDiff;
            }
        } elseif ($leftStart || $rightStart) {
            return $leftStart ? -1 : 1;
        }

        $leftEnd = $this->parseFlexibleDate($left['end_date'] ?? null);
        $rightEnd = $this->parseFlexibleDate($right['end_date'] ?? null);
        if ($leftEnd && $rightEnd) {
            $endDiff = $leftEnd->getTimestamp() <=> $rightEnd->getTimestamp();
            if ($endDiff !== 0) {
                return $endDiff;
            }
        } elseif ($leftEnd || $rightEnd) {
            return $leftEnd ? -1 : 1;
        }

        return mb_strtolower((string) ($left['text'] ?? ''), 'UTF-8') <=> mb_strtolower((string) ($right['text'] ?? ''), 'UTF-8');
    }

    private function normalizeParentKey($parent): string
    {
        $text = trim((string) $parent);
        return $text === '' || $text === '0' ? '0' : $text;
    }

    private function authorizeProjectAccess(Project $project): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin' || $user->role === 'user_all') {
            return;
        }

        $fullName = trim(($user->prefix ?? '').' '.$user->name);
        $hasPermission = (int) $user->id === (int) $project->user_id
            || $project->fteRoles()->where('name', $fullName)->exists();

        if (! $hasPermission) {
            abort(403);
        }
    }

    private function parseFlexibleDate($value): ?Carbon
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->startOfDay();
            } catch (\Throwable $e) {
                // continue
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function statusLabel(string $status, bool $isFte = false): string
    {
        if ($isFte) {
            return 'งานภาระงาน (FTE)';
        }

        return match ($status) {
            'not_started' => 'ยังไม่เริ่มทำ',
            'starting' => 'กำลังเริ่ม',
            'paused' => 'พักการทำ',
            'delayed' => 'ล้าช้า',
            'completed' => 'เสร็จสิ้น',
            default => 'กำลังดำเนินการ',
        };
    }

    private function toThaiDateBe(Carbon $date): string
    {
        $months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return $date->day.' '.$months[$date->month - 1].' '.($date->year + 543);
    }

    private function toThaiMonthYear(Carbon $date): string
    {
        $months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        $yearBe = (string) ($date->year + 543);
        return $months[$date->month - 1].'<br>'.$yearBe;
    }

    private function toThaiDateTimeBe(Carbon $date): string
    {
        $months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return $date->day.' '.$months[$date->month - 1].' '.($date->year + 543).' '.$date->format('H:i');
    }
}
