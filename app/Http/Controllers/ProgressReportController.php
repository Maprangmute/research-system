<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProgressReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressReportController extends Controller
{
    // แสดง progress ทั้งหมดของโครงการ
    public function index(Project $project)
    {
        $progressReports = $project->progressReports()->orderByDesc('submit_date')->get();
        $fullName = trim(Auth::user()->prefix . ' ' . Auth::user()->name);
        $hasPermission = Auth::user()->role === 'admin'
            || Auth::id() === $project->user_id
            || $project->fteRoles->contains('name', $fullName);

        if (!$hasPermission) {
            abort(403);
        }
        return view('progress_reports.index', compact('project', 'progressReports'));
    }

    // ฟอร์มสร้าง progress
    public function create(Project $project)
    {
        return view('progress_reports.create', compact('project'));
    }

    // บันทึก progress ใหม่
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'submitter_name' => 'required|string|max:255',
            'submit_date'    => 'required|date',
            'file_info'      => 'nullable|string|max:255',
        ]);
        $data['project_id'] = $project->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $progressReport = ProgressReport::create($data);

        return redirect()->route('projects.progress_reports.index', $project->id)
            ->with('success', 'เพิ่ม Progress Report สำเร็จ');
    }

    // ดูรายละเอียด progress
    public function show(Project $project, ProgressReport $progress_report)
    {
        return view('progress_reports.show', [
            'project' => $project,
            'report' => $progress_report
        ]);
    }

    // ฟอร์มแก้ไข progress
    public function edit(Project $project, ProgressReport $progress_report)
    {
        return view('progress_reports.edit', [
            'project' => $project,
            'report' => $progress_report
        ]);
    }

    // อัปเดต progress
    public function update(Request $request, Project $project, ProgressReport $progress_report)
    {
        $data = $request->validate([
            'submitter_name' => 'required|string|max:255',
            'submit_date'    => 'required|date',
            'file_info'      => 'nullable|string|max:255',
        ]);
        $data['updated_by'] = Auth::id();

        $progress_report->update($data);

        return redirect()->route('projects.progress_reports.index', $project->id)
            ->with('success', 'อัปเดต Progress Report สำเร็จ');
    }

    // ลบ progress
    public function destroy(Project $project, ProgressReport $progress_report)
    {
        $progress_report->delete();
        return redirect()->route('projects.progress_reports.index', $project->id)
            ->with('success', 'ลบ Progress Report สำเร็จ');
    }
}
