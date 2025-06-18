<?php

namespace App\Http\Controllers;

use App\Models\ProjectLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectLogController extends Controller
{
    /**
     * แสดง log ทั้งหมด
     */
    public function index()
    {
        // ต้องการให้ดูทุก log หรือเฉพาะ admin ปรับ logic ได้
        $logs = ProjectLog::with(['user', 'project'])->latest()->paginate(50);
        return view('project_logs.index', compact('logs'));
    }

    /**
     * แสดง log ทั้งหมดของ project เดียว (แสดงใน project detail)
     */
    public function projectLogs($projectId)
    {
        $project = Project::findOrFail($projectId);
        $logs = $project->logs()->with('user')->latest()->get();
        return view('project_logs.project', compact('project', 'logs'));
    }

    /**
     * แสดง log รายการเดียว
     */
    public function show($id)
    {
        $log = ProjectLog::with(['user', 'project'])->findOrFail($id);
        return view('project_logs.show', compact('log'));
    }

    /**
     * ลบ log (option สำหรับ admin)
     */
    public function destroy($id)
    {
        $log = ProjectLog::findOrFail($id);
        // สามารถเพิ่ม permission เช็คว่าเป็น admin เท่านั้น
        $log->delete();
        return redirect()->back()->with('success', 'ลบ log สำเร็จ');
    }
}
