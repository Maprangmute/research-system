<?php

namespace App\Http\Controllers;

use App\Models\FteRole;
use App\Models\Project; // ถ้าคุณใช้โมเดลโปรเจ็กต์
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FteRoleController extends Controller
{
    /**
     * รับฟอร์มแบบ:
     * roles[<id>][fte_value_research]
     * roles[<id>][fte_value_management]
     * roles[<id>][management_approved]
     */
    public function bulkUpdate(Request $request, Project $project)
    {
        $data = $request->validate([
            'roles' => ['required','array'],
            'roles.*.fte_value_research'   => ['nullable','numeric','min:0','max:1'],
            'roles.*.fte_value_management' => ['nullable','numeric','min:0','max:1'],
            'roles.*.management_approved'  => ['nullable','boolean'],
        ]);

        // ดึง FTE ทั้งหมดของโปรเจ็กต์นี้
        $fteRows = FteRole::where('project_id', $project->id)
                    ->whereIn('id', array_keys($data['roles']))
                    ->get()
                    ->keyBy('id');

        foreach ($data['roles'] as $id => $payload) {
            if (!isset($fteRows[$id])) continue;
            $fte = $fteRows[$id];

            // ฝั่งทีมวิจัย
            if (array_key_exists('fte_value_research', $payload)) {
                $this->authorize('updateResearch', $fte);
                $fte->fte_value_research = $payload['fte_value_research'];
            }

            // ฝั่งผู้บริหาร (admin)
            if (array_key_exists('fte_value_management', $payload) || array_key_exists('management_approved', $payload)) {
                $this->authorize('updateManagement', $fte);

                if (array_key_exists('fte_value_management', $payload)) {
                    $fte->fte_value_management = $payload['fte_value_management'];
                }

                if (array_key_exists('management_approved', $payload)) {
                    $fte->management_approved = (bool)$payload['management_approved'];
                    $fte->approved_by = Auth::id();
                    $fte->approved_at = now();
                }
            }

            $fte->save();
        }

        $project->updated_by = Auth::id();
        if ($project->isDirty('updated_by')) {
            $project->save();
        } else {
            $project->touch();
        }

        return back()->with('success', 'บันทึกข้อมูล FTE เรียบร้อย');
    }

    public function index(\App\Models\Project $project)
{
    // ดึงรายการ FTE ของโปรเจ็กต์นี้
    $query = \App\Models\FteRole::where('project_id', $project->id);
    $user = Auth::user();

    if ($user?->mustLimitFteToSelf()) {
        $query->where('name', trim(($user->prefix ?? '').' '.($user->name ?? '')));
    }

    $fteRoles = $query
        ->orderBy('name')
        ->get();

    return view('projects.fte', compact('project', 'fteRoles'));
}

}
