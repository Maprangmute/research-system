<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;


class OverviewController extends Controller
{
    public function index()
    {
        $projectsQuery = Project::query()->select([
            'id',
            'project_code',
            'project_name_th',
            'project_name_en',
            'project_type',
            'funding_type',
            'execution_status',
            'budget',
            'start_year',
            'start_date',
            'end_date',
            'pi_name',
            'user_id',
        ]);

        if (Auth::user()?->role !== 'admin') {
            $projectsQuery->where('user_id', Auth::id());
        }

        $projects = $projectsQuery
            ->orderByDesc('start_year')
            ->orderBy('project_name_th')
            ->get();

        $projectsData = $projects->map(function (Project $project): array {
            return [
                'id' => (int) $project->id,
                'project_code' => (string) ($project->project_code ?? ''),
                'project_name_th' => (string) ($project->project_name_th ?? ''),
                'project_name_en' => (string) ($project->project_name_en ?? ''),
                'project_type' => (string) ($project->project_type ?? ''),
                'funding_type' => (string) ($project->funding_type ?? ''),
                'execution_status' => (string) ($project->execution_status ?? ''),
                'start_year' => (string) ($project->start_year ?? ''),
                'start_date' => (string) ($project->start_date ?? ''),
                'end_date' => (string) ($project->end_date ?? ''),
                'budget' => (float) ($project->budget ?? 0),
                'pi_name' => (string) ($project->pi_name ?? ''),
            ];
        })->values();

        $allYears = $projectsData->pluck('start_year')
            ->map(fn ($year) => trim($year))
            ->filter()
            ->unique()
            ->sortByDesc(fn ($year) => is_numeric($year) ? (int) $year : -1)
            ->values()
            ->all();

        $allTypes = $this->extractFilterOptions($projectsData, 'project_type');
        $allFundingTypes = $this->extractFilterOptions($projectsData, 'funding_type');
        $allStatuses = $this->extractFilterOptions($projectsData, 'execution_status');

        return view('overview.index', [
            'projectsData' => $projectsData,
            'allYears' => $allYears,
            'allTypes' => $allTypes,
            'allFundingTypes' => $allFundingTypes,
            'allStatuses' => $allStatuses,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);
    }

    private function extractFilterOptions(Collection $rows, string $key): array
    {
        return $rows->pluck($key)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
