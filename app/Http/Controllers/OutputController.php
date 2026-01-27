<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeEntryOutput;
use App\Models\BackOfficeTaskOutput;
use App\Models\Output;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class OutputController extends Controller
{
    public function index(Request $request)
    {
        $query = Output::with('project:id,project_name_th,project_code');
        $user = auth()->user();
        $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
        $canViewAll = $user->role === 'admin' || $user->role === 'user_all';

        if (!$canViewAll) {
            $query->whereHas('project', function ($q) use ($user, $fullName) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('year')) {
            $year = trim($request->year);
            $query->where(function ($q) use ($year) {
                $q->where('year', 'like', '%' . $year . '%');
                if (is_numeric($year) && (int) $year > 2400) {
                    $ce = (int) $year - 543;
                    $q->orWhere('year', 'like', '%' . $ce . '%');
                }
            });
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $sortable = ['created_at', 'year', 'type', 'title'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $projectOutputs = $query->orderBy($sort, $dir)->get();

        $projectQuery = Project::query();
        if (!$canViewAll) {
            $projectQuery->where(function ($q) use ($user, $fullName) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        $projectOptions = $projectQuery->orderBy('project_name_th')->get(['id', 'project_name_th']);
        $typeOptions = Output::select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');

        $parseYear = function ($raw) {
            if (!is_string($raw)) {
                $raw = (string) $raw;
            }
            if (preg_match('/\d{4}/', $raw, $m)) {
                $y = (int) $m[0];
                if ($y < 2400) {
                    $y += 543;
                }
                return $y;
            }
            return null;
        };

        $yearOptions = Output::select('year')->whereNotNull('year')->distinct()->pluck('year')
            ->map(fn($raw) => $parseYear($raw))
            ->filter()
            ->unique()
            ->values();

        $taskOutputQuery = BackOfficeTaskOutput::query()
            ->with(['task:id,title,created_by'])
            ->when(!$canViewAll, function ($q) use ($user, $fullName) {
                $q->whereHas('task', function ($taskQ) use ($user, $fullName) {
                    $taskQ->where('created_by', $user->id)
                        ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                            $entryQ->where('person_name', $fullName);
                        });
                });
            });

        $entryOutputQuery = BackOfficeEntryOutput::query()
            ->with(['entry:id,task_id,job_detail,person_name', 'entry.task:id,title,created_by'])
            ->when(!$canViewAll, function ($q) use ($user, $fullName) {
                $q->whereHas('entry.task', function ($taskQ) use ($user, $fullName) {
                    $taskQ->where('created_by', $user->id)
                        ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                            $entryQ->where('person_name', $fullName);
                        });
                });
            });

        if ($request->filled('search')) {
            $taskOutputQuery->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('task', function ($taskQ) use ($request) {
                        $taskQ->where('title', 'like', '%' . $request->search . '%');
                    });
            });
            $entryOutputQuery->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('entry', function ($entryQ) use ($request) {
                        $entryQ->where('job_detail', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('entry.task', function ($taskQ) use ($request) {
                        $taskQ->where('title', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'งานนอกโครงการ') {
                $entryOutputQuery = null;
            } elseif ($type === 'ผลงานภาระงาน') {
                $taskOutputQuery = null;
            } else {
                $taskOutputQuery = null;
                $entryOutputQuery = null;
            }
        }

        if ($request->filled('project_id')) {
            $taskOutputQuery = null;
            $entryOutputQuery = null;
        }

        $taskOutputs = $taskOutputQuery ? $taskOutputQuery->get() : collect();
        $entryOutputs = $entryOutputQuery ? $entryOutputQuery->get() : collect();

        $taskOutputYears = $taskOutputs->pluck('delivered_at')
            ->map(fn($raw) => $parseYear((string) $raw))
            ->filter()
            ->unique();
        $entryOutputYears = $entryOutputs->pluck('delivered_at')
            ->map(fn($raw) => $parseYear((string) $raw))
            ->filter()
            ->unique();
        $yearOptions = $yearOptions->merge($taskOutputYears)->merge($entryOutputYears)->unique()->sortDesc()->values();

        if ($taskOutputs->isNotEmpty()) {
            $typeOptions = $typeOptions->push('งานนอกโครงการ');
        }
        if ($entryOutputs->isNotEmpty()) {
            $typeOptions = $typeOptions->push('ผลงานภาระงาน');
        }

        $normalizeYear = function ($raw) use ($parseYear) {
            $y = $parseYear((string) $raw);
            return $y ? (int) $y : null;
        };

        $items = collect();

        foreach ($projectOutputs as $output) {
            $yearBe = $normalizeYear($output->year ?? '');
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $output->type ?: '-',
                'year_be' => $yearBe,
                'project_label' => $output->project?->project_name_th,
                'detail' => null,
                'link' => $output->project ? route('projects.show', $output->project->id) : null,
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => mb_strtolower($output->type ?? ''),
            ]);
        }

        $yearFilterBe = null;
        if ($request->filled('year')) {
            $yearFilterBe = $normalizeYear($request->year);
        }

        foreach ($taskOutputs as $output) {
            $yearBe = $normalizeYear($output->delivered_at ?? '');
            if ($yearFilterBe && $yearBe !== $yearFilterBe) {
                continue;
            }
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => 'งานนอกโครงการ',
                'year_be' => $yearBe,
                'project_label' => $output->task?->title,
                'detail' => null,
                'link' => $output->task ? route('back_office.show', $output->task->id) : null,
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => 'งานนอกโครงการ',
            ]);
        }

        foreach ($entryOutputs as $output) {
            $yearBe = $normalizeYear($output->delivered_at ?? '');
            if ($yearFilterBe && $yearBe !== $yearFilterBe) {
                continue;
            }
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => 'ผลงานภาระงาน',
                'year_be' => $yearBe,
                'project_label' => $output->entry?->task?->title,
                'detail' => $output->entry?->job_detail,
                'link' => $output->entry?->task ? route('back_office.show', $output->entry->task->id) : null,
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => 'ผลงานภาระงาน',
            ]);
        }

        $items = $items->sortBy(function ($item) use ($sort) {
            return match ($sort) {
                'year' => $item->sort_year,
                'type' => $item->sort_type,
                'title' => $item->sort_title,
                default => $item->created_at?->timestamp ?? 0,
            };
        });
        if ($dir === 'desc') {
            $items = $items->reverse();
        }

        $perPage = 15;
        $page = (int) $request->get('page', 1);
        $paged = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $outputs = new LengthAwarePaginator($paged, $items->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('outputs.index', [
            'outputs' => $outputs,
            'projectOptions' => $projectOptions,
            'typeOptions' => $typeOptions->unique()->sort()->values(),
            'yearOptions' => $yearOptions,
        ]);
    }
}
