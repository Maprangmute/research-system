<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::with([
            'copis',
            'coResearches',
            'fteRoles',
            'outputs',
            'irbs',
            'progressReports'
        ])
        ->select(
            'id',
            'project_name_th',
            'start_year',
            'planning_status',
            'theme',
            'pi_name',
            'budget',
            'funding_type',   // << ต้องมี
            'funding_source'  // << เผื่อใช้กราฟอื่น
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

