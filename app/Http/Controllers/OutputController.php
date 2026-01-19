<?php

namespace App\Http\Controllers;

use App\Models\Output;
use App\Models\Project;
use Illuminate\Http\Request;

class OutputController extends Controller
{
    public function index(Request $request)
    {
        $query = Output::with('project:id,project_name_th,project_code');
        $user = auth()->user();

        if (!($user->role === 'admin' || $user->role === 'user_all')) {
            $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
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

        $outputs = $query->orderBy($sort, $dir)
            ->paginate(15)
            ->appends($request->query());

        $projectQuery = Project::query();
        if (!($user->role === 'admin' || $user->role === 'user_all')) {
            $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
            $projectQuery->where(function ($q) use ($user, $fullName) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        $projectOptions = $projectQuery->orderBy('project_name_th')->get(['id', 'project_name_th']);
        $typeOptions = Output::select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $yearOptions = Output::select('year')->whereNotNull('year')->distinct()->pluck('year')
            ->map(function ($raw) {
                if (preg_match('/\d{4}/', (string) $raw, $m)) {
                    $y = (int) $m[0];
                    if ($y < 2400) {
                        $y += 543;
                    }
                    return (string) $y;
                }
                return null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        return view('outputs.index', [
            'outputs' => $outputs,
            'projectOptions' => $projectOptions,
            'typeOptions' => $typeOptions,
            'yearOptions' => $yearOptions,
        ]);
    }
}
