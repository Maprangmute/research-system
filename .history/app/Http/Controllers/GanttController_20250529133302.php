<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class GanttController extends Controller
{
    public function index(Project $project)
    {
        return view('gantt.index', compact('project')); // ✅ ไม่ต้องส่ง $tasks ถ้าไม่ได้ใช้ใน Blade
    }
}
