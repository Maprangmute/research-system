<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

// app/Http/Controllers/DashboardController.php
class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::with([
            // ดึงเฉพาะฟิลด์ที่ต้องใช้จาก coResearches
            'coResearches:id,project_id,unit_status',

            // อื่น ๆ ตามเดิม (จะกรองฟิลด์หรือไม่ก็ได้)
            'copis',
            'fteRoles',
            'outputs',
            'irbs',
            'progressReports',
        ])
        ->select(
            'id',
            'project_name_th',
            'project_list',
            'pi_affiliation',
            'start_year',
            'planning_status',
            'theme',
            'pi_name',
            'budget',
            'funding_type',
            'funding_source'
        )
        ->get();

        $copis = $projects->flatMap->copis;
        $coResearches = $projects->flatMap->coResearches;
        $fteRoles = $projects->flatMap->fteRoles;
        $outputs = $projects->flatMap->outputs;
        $irbs = $projects->flatMap->irbs;
        $progressReports = $projects->flatMap->progressReports;

        $themes = Project::select('theme')->distinct()->pluck('theme');

        return view('dashboard', compact(
            'projects',
            'copis',
            'coResearches',
            'fteRoles',
            'outputs',
            'irbs',
            'progressReports',
            'themes'
        ));
    }
}

