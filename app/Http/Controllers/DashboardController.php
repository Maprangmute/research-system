<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::with('outputs')
            ->select('id', 'project_name_th', 'funding_name', 'budget', 'start_year', 'theme')
            ->get();

        // ดึง Theme ทั้งหมดที่ไม่ซ้ำ
        $themes = Project::distinct()->pluck('theme');

        return view('dashboard', compact('projects', 'themes'));
    }
}
