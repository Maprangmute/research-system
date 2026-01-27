<?php

namespace App\Http\Controllers;

use App\Models\ProjectLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectLogController extends Controller
{
    private function resolveView(Request $request, string $base): string
    {
        $view = $base;
        $forceView = $request->query('view');
        if ($forceView === 'mobile') {
            $view = $base . '_mobile';
        } elseif ($forceView !== 'desktop') {
            $ua = (string) $request->header('User-Agent', '');
            $isMobile = (bool) preg_match(
                '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
                $ua
            );
            if ($isMobile) {
                $view = $base . '_mobile';
            }
        }
        return $view;
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }
    }

    /**
     * แสดง log ทั้งหมด
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();
        // ต้องการให้ดูทุก log หรือเฉพาะ admin ปรับ logic ได้
        $query = ProjectLog::with(['user', 'project']);

        $entity = $request->input('entity');
        if (!empty($entity) && $entity !== 'all') {
            if ($entity === 'project') {
                $query->whereIn('action', ['create', 'update', 'delete', 'restore']);
            } else {
                $query->where('action', 'like', $entity . '_%');
            }
        }

        $verb = $request->input('verb');
        if (!empty($verb) && $verb !== 'all') {
            $query->where(function ($q) use ($verb) {
                $q->where('action', $verb)
                    ->orWhere('action', 'like', '%_' . $verb);
            });
        }

        $sort = $request->input('sort', 'latest');
        $sortMap = [
            'latest' => ['project_logs.created_at', 'desc'],
            'oldest' => ['project_logs.created_at', 'asc'],
            'project_asc' => ['projects.project_name_th', 'asc'],
            'project_desc' => ['projects.project_name_th', 'desc'],
            'user_asc' => ['users.name', 'asc'],
            'user_desc' => ['users.name', 'desc'],
            'action_asc' => ['project_logs.action', 'asc'],
            'action_desc' => ['project_logs.action', 'desc'],
        ];

        if (array_key_exists($sort, $sortMap)) {
            $query->leftJoin('projects', 'project_logs.project_id', '=', 'projects.id')
                ->leftJoin('users', 'project_logs.user_id', '=', 'users.id')
                ->select('project_logs.*')
                ->orderBy($sortMap[$sort][0], $sortMap[$sort][1]);
        } else {
            $query->latest();
        }

        $search = trim((string) $request->input('q'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($project) use ($search) {
                    $project->where('project_name_th', 'like', "%{$search}%")
                        ->orWhere('project_name_en', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%");
                })->orWhereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', "%{$search}%")
                        ->orWhere('prefix', 'like', "%{$search}%");
                });
            });
        }

        $logs = $query->paginate(50)->appends($request->query());
        $groupedLogs = $logs->getCollection()->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d H:i:s') . '|' . ($log->project_id ?? '-') . '|' . ($log->user_id ?? '-');
        });

        $entityOptions = [
            'all' => 'ทั้งหมด',
            'project' => 'โครงการ',
            'output' => 'ผลผลิต',
            'output_author' => 'ผู้เขียนผลงาน',
            'fte' => 'FTE',
            'task' => 'งาน',
            'tasklink' => 'ลิงก์งาน',
            'copi' => 'ผู้ร่วมวิจัย',
            'coresearch' => 'นักวิจัยร่วม',
            'irb' => 'IRB',
        ];

        $verbOptions = [
            'all' => 'ทั้งหมด',
            'create' => 'สร้าง',
            'update' => 'แก้ไข',
            'delete' => 'ลบ',
            'restore' => 'กู้คืน',
        ];

        $view = $this->resolveView($request, 'project_logs.index');
        return view($view, compact('logs', 'groupedLogs', 'entityOptions', 'verbOptions', 'entity', 'verb', 'search', 'sort'));
    }

    /**
     * แสดง log ทั้งหมดของ project เดียว (แสดงใน project detail)
     */
    public function projectLogs(Request $request, $projectId)
    {
        $this->authorizeAdmin();
        $project = Project::findOrFail($projectId);
        $logs = $project->logs()->with('user')->latest()->get();
        $groupedLogs = $logs->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d H:i:s') . '|' . ($log->user_id ?? '-');
        });
        $view = $this->resolveView($request, 'project_logs.project');
        return view($view, compact('project', 'logs', 'groupedLogs'));
    }

    /**
     * แสดง log รายการเดียว
     */
    public function show(Request $request, $id)
    {
        $this->authorizeAdmin();
        $log = ProjectLog::with(['user', 'project'])->findOrFail($id);
        $view = $this->resolveView($request, 'project_logs.show');
        return view($view, compact('log'));
    }

    /**
     * ลบ log (option สำหรับ admin)
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();
        $log = ProjectLog::findOrFail($id);
        // สามารถเพิ่ม permission เช็คว่าเป็น admin เท่านั้น
        $log->delete();
        return redirect()->back()->with('success', 'ลบ log สำเร็จ');
    }

    /**
     * ลบ log ที่เก่ากว่า 3 เดือน
     */
    public function purgeOld(Request $request)
    {
        $this->authorizeAdmin();
        $cutoff = now()->subMonths(3);
        $deleted = ProjectLog::where('created_at', '<', $cutoff)->delete();

        return redirect()->back()->with('success', "ลบ log เก่ากว่า 3 เดือนแล้ว {$deleted} รายการ");
    }
}
