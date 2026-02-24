<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiDashboardController extends Controller
{
    public function index()
    {
        return view('rm.ai_dashboard'); // ไม่ส่งตัวแปรใด ๆ ไป Blade
    }

    public function analyze(Request $request)
    {
        $prompt = trim((string) $request->input('prompt', 'auto'));
        if ($prompt === '') $prompt = 'auto';

        // 1) เตรียม DataPack รวมทั้งหมด (หนักแค่ครั้งเดียว)
        $pack = $this->buildDataPack();

        // 2) ให้ LLM เลือก widgets + chart types (หรือ fallback)
        $plan = $this->makePlanByLLMOrFallback($prompt, $pack);

        // 3) สร้าง widgets จริงตาม plan
        $widgets = $this->buildWidgetsFromPlan($plan, $pack);

        // 4) Insights (RED / WARNING / INFO)
        $insights = $this->buildInsights($pack);

        return response()->json([
            'reply'      => $plan['narrative_html'] ?? $this->defaultNarrative($pack),
            'mode_hint'  => $plan['mode_hint'] ?? 'auto',
            'insights'   => $insights,
            'widgets'    => $widgets,    // ✅ UI จะ render widgets ทั้งหมด + drag/collapse
            'data_pack'  => $pack,       // (optional debug)
        ]);
    }

    /* =========================================================
     * DATA PACK (DB-first)
     * ========================================================= */
    private function buildDataPack(): array
    {
        $now = Carbon::now();
        $thisYearAD = (int) $now->year;
        $thisYearBE = $thisYearAD + 543;

        // ---- Projects base ----
        $projectsQ = DB::table('projects');

        $hasStart = Schema::hasColumn('projects', 'date_start');
        $hasEnd   = Schema::hasColumn('projects', 'date_end');
        $hasStatus= Schema::hasColumn('projects', 'planning_status'); // หรือ status field ในระบบคุณ
        $hasYear  = Schema::hasColumn('projects', 'year');            // ถ้ามี
        $hasBudget= Schema::hasColumn('projects', 'budget');
        $hasFundType = Schema::hasColumn('projects', 'funding_type'); // internal/external/unknown
        $hasFundName = Schema::hasColumn('projects', 'funding_name'); // แหล่งทุน

        // ปีของโครงการ: ถ้ามี year ใช้ year, ถ้าไม่มีก็ดึงจาก date_start / created_at
        $yearExpr = null;
        if ($hasYear) {
            $yearExpr = "projects.year";
        } elseif ($hasStart) {
            $yearExpr = "YEAR(projects.date_start)";
        } else {
            $yearExpr = "YEAR(projects.created_at)";
        }

        // ---- RED ZONE / Overdue ----
        $overdue = [];
        $nearEnd = [];
        $timeline = [];

        if ($hasEnd) {
            $overdue = DB::table('projects')
                ->select('id', 'project_code', 'project_name', 'pi_name', DB::raw($yearExpr.' as year_ad'), 'funding_name', 'funding_type', 'date_end')
                ->when(Schema::hasColumn('projects', 'project_name'), fn($q)=>$q, fn($q)=>$q) // noop
                ->whereNotNull('date_end')
                ->where('date_end', '<', $now->toDateString())
                ->orderBy('date_end', 'asc')
                ->limit(200)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'project' => $r->project_name ?? ('Project #'.$r->id),
                    'pi' => $r->pi_name ?? '-',
                    'year_ad' => (int) ($r->year_ad ?? $thisYearAD),
                    'year_be' => (int) (($r->year_ad ?? $thisYearAD) + 543),
                    'funding' => $r->funding_name ?? '',
                    'fund_type' => $r->funding_type ?? 'unknown',
                    'date_end' => (string) $r->date_end,
                    'href' => url('/rm/projects/'.($r->project_code ?: $r->id)),
                ])->values()->all();

            $nearEnd = DB::table('projects')
                ->select('id', 'project_code', 'project_name', 'pi_name', DB::raw($yearExpr.' as year_ad'), 'funding_name', 'funding_type', 'date_end')
                ->whereNotNull('date_end')
                ->whereBetween('date_end', [$now->toDateString(), $now->copy()->addDays(60)->toDateString()])
                ->orderBy('date_end', 'asc')
                ->limit(200)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'project' => $r->project_name ?? ('Project #'.$r->id),
                    'pi' => $r->pi_name ?? '-',
                    'year_ad' => (int) ($r->year_ad ?? $thisYearAD),
                    'year_be' => (int) (($r->year_ad ?? $thisYearAD) + 543),
                    'funding' => $r->funding_name ?? '',
                    'fund_type' => $r->funding_type ?? 'unknown',
                    'date_end' => (string) $r->date_end,
                    'days_left' => Carbon::parse($r->date_end)->diffInDays($now, false) * -1,
                    'href' => url('/rm/projects/'.($r->project_code ?: $r->id)),
                ])->values()->all();

            // timeline buckets: overdue / 0-30 / 31-60 / 61-120 / >120 days
            $buckets = [
                'Overdue' => 0,
                '0-30' => 0,
                '31-60' => 0,
                '61-120' => 0,
                '>120' => 0,
            ];

            $allEndDates = DB::table('projects')
                ->select('date_end')
                ->whereNotNull('date_end')
                ->get();

            foreach ($allEndDates as $row) {
                $d = Carbon::parse($row->date_end);
                $diff = $now->diffInDays($d, false); // + future, - past
                if ($diff < 0) $buckets['Overdue']++;
                elseif ($diff <= 30) $buckets['0-30']++;
                elseif ($diff <= 60) $buckets['31-60']++;
                elseif ($diff <= 120) $buckets['61-120']++;
                else $buckets['>120']++;
            }

            $timeline = $buckets;
        }

        // gauge value = % projects in red-zone (overdue + within 60 days) among projects with end date
        $endCount = $hasEnd ? (int) DB::table('projects')->whereNotNull('date_end')->count() : 0;
        $overdueCount = count($overdue);
        $nearEndCount = count($nearEnd);
        $redPct = ($endCount > 0) ? round((($overdueCount + $nearEndCount) / $endCount) * 100, 1) : 0;

        // ---- Projects per year (รวม / new / ongoing) ----
        // “new” = start year == year, “ongoing” = year within start-end
        $projectsPerYear = [];
        $projectsNewPerYear = [];
        $projectsOngoingPerYear = [];

        $years = DB::table('projects')
            ->selectRaw("DISTINCT {$yearExpr} as y")
            ->whereNotNull(DB::raw($yearExpr))
            ->orderBy('y','asc')
            ->limit(30)
            ->pluck('y')
            ->filter()
            ->map(fn($y)=> (int)$y)
            ->values()
            ->all();

        foreach ($years as $y) {
            // total by year expr
            $projectsPerYear[$y] = (int) DB::table('projects')
                ->whereRaw("{$yearExpr} = ?", [$y])
                ->count();

            if ($hasStart) {
                $projectsNewPerYear[$y] = (int) DB::table('projects')
                    ->whereNotNull('date_start')
                    ->whereRaw("YEAR(date_start) = ?", [$y])
                    ->count();
            } else {
                $projectsNewPerYear[$y] = 0;
            }

            if ($hasStart && $hasEnd) {
                $projectsOngoingPerYear[$y] = (int) DB::table('projects')
                    ->whereNotNull('date_start')
                    ->whereNotNull('date_end')
                    ->whereRaw("? BETWEEN YEAR(date_start) AND YEAR(date_end)", [$y])
                    ->count();
            } else {
                $projectsOngoingPerYear[$y] = 0;
            }
        }

        // ---- Budget per year (รวมรายปี) + fund inside/outside split per year ----
        $budgetPerYear = [];
        $fundSplitPerYear = []; // year => [internal, external, unknown]
        if ($hasBudget) {
            foreach ($years as $y) {
                $sum = (float) DB::table('projects')
                    ->whereRaw("{$yearExpr} = ?", [$y])
                    ->sum('budget');

                $budgetPerYear[$y] = round($sum / 1_000_000, 3); // million

                if ($hasFundType) {
                    $internal = (float) DB::table('projects')->whereRaw("{$yearExpr} = ?", [$y])->where('funding_type', 'internal')->sum('budget');
                    $external = (float) DB::table('projects')->whereRaw("{$yearExpr} = ?", [$y])->where('funding_type', 'external')->sum('budget');
                    $unknown  = (float) DB::table('projects')->whereRaw("{$yearExpr} = ?", [$y])->whereNotIn('funding_type', ['internal','external'])->sum('budget');

                    $fundSplitPerYear[$y] = [
                        'internal' => round($internal/1_000_000, 3),
                        'external' => round($external/1_000_000, 3),
                        'unknown'  => round($unknown/1_000_000, 3),
                    ];
                } else {
                    $fundSplitPerYear[$y] = ['internal'=>0,'external'=>0,'unknown'=>0];
                }
            }
        }

        // ---- Fund sources breakdown (internal/external) overall + per year ----
        $externalBySource = [];
        $internalBySource = [];
        $externalBySourcePerYear = [];
        $internalBySourcePerYear = [];

        if ($hasFundName) {
            if ($hasFundType) {
                $externalBySource = DB::table('projects')
                    ->selectRaw("COALESCE(funding_name,'(ไม่ระบุ)') as k, COUNT(*) as n")
                    ->where('funding_type', 'external')
                    ->groupBy('k')
                    ->orderByDesc('n')
                    ->limit(20)
                    ->get()
                    ->map(fn($r)=>['k'=>$r->k,'n'=>(int)$r->n])
                    ->all();

                $internalBySource = DB::table('projects')
                    ->selectRaw("COALESCE(funding_name,'(ไม่ระบุ)') as k, COUNT(*) as n")
                    ->where('funding_type', 'internal')
                    ->groupBy('k')
                    ->orderByDesc('n')
                    ->limit(20)
                    ->get()
                    ->map(fn($r)=>['k'=>$r->k,'n'=>(int)$r->n])
                    ->all();

                foreach ($years as $y) {
                    $externalBySourcePerYear[$y] = DB::table('projects')
                        ->selectRaw("COALESCE(funding_name,'(ไม่ระบุ)') as k, COUNT(*) as n")
                        ->whereRaw("{$yearExpr} = ?", [$y])
                        ->where('funding_type','external')
                        ->groupBy('k')
                        ->orderByDesc('n')
                        ->limit(12)
                        ->get()
                        ->map(fn($r)=>['k'=>$r->k,'n'=>(int)$r->n])
                        ->all();

                    $internalBySourcePerYear[$y] = DB::table('projects')
                        ->selectRaw("COALESCE(funding_name,'(ไม่ระบุ)') as k, COUNT(*) as n")
                        ->whereRaw("{$yearExpr} = ?", [$y])
                        ->where('funding_type','internal')
                        ->groupBy('k')
                        ->orderByDesc('n')
                        ->limit(12)
                        ->get()
                        ->map(fn($r)=>['k'=>$r->k,'n'=>(int)$r->n])
                        ->all();
                }
            }
        }

        // ---- Status per year ----
        $statusPerYear = []; // year => [status => count]
        if ($hasStatus) {
            foreach ($years as $y) {
                $rows = DB::table('projects')
                    ->selectRaw("COALESCE(planning_status,'(ไม่ระบุ)') as s, COUNT(*) as n")
                    ->whereRaw("{$yearExpr} = ?", [$y])
                    ->groupBy('s')
                    ->orderByDesc('n')
                    ->get();

                $statusPerYear[$y] = [];
                foreach ($rows as $r) $statusPerYear[$y][$r->s] = (int)$r->n;
            }
        }

        // ---- Collaboration ratio (นอกคณะ/ในคณะ/ในหน่วย) ----
        // ปรับชื่อ field ให้ตรงของคุณ: เช่น collaboration_type / collaboration_level / partner_scope
        $collabRatio = [];
        $collabField = null;
        foreach (['collaboration_scope','collaboration_type','collaboration_level','partner_scope'] as $f) {
            if (Schema::hasColumn('projects', $f)) { $collabField = $f; break; }
        }
        if ($collabField) {
            $collabRatio = DB::table('projects')
                ->selectRaw("COALESCE({$collabField},'(ไม่ระบุ)') as k, COUNT(*) as n")
                ->groupBy('k')
                ->orderByDesc('n')
                ->get()
                ->map(fn($r)=>['k'=>$r->k,'n'=>(int)$r->n])
                ->all();
        }

        // ---- PI workload in unit ----
        // เอา “PI name” + “unit/department” ถ้ามี field unit
        $piUnitField = null;
        foreach (['pi_affiliation','unit','department','division'] as $f) {
            if (Schema::hasColumn('projects', $f)) { $piUnitField = $f; break; }
        }
        $piWorkload = [];
        if (Schema::hasColumn('projects','pi_name')) {
            $piWorkload = DB::table('projects')
                ->selectRaw("COALESCE(pi_name,'(ไม่ระบุ)') as pi, COUNT(*) as n")
                ->groupBy('pi')
                ->orderByDesc('n')
                ->limit(20)
                ->get()
                ->map(fn($r)=>['pi'=>$r->pi,'n'=>(int)$r->n])
                ->all();
        }

        // ---- FTE distribution ----
        $fteDist = [];
        if (Schema::hasTable('fte_roles') && Schema::hasColumn('fte_roles','fte_value')) {
            $fteDist = DB::table('fte_roles')
                ->selectRaw("ROUND(fte_value,2) as v, COUNT(*) as n")
                ->groupBy('v')
                ->orderBy('v','asc')
                ->get()
                ->map(fn($r)=>['v'=>(float)$r->v,'n'=>(int)$r->n])
                ->all();
        }

        // ---- Co-PI list ----
        // ถ้ามีตาราง copis: project_id, name, affiliation
        $copisTop = [];
        if (Schema::hasTable('copis')) {
            $copisTop = DB::table('copis')
                ->selectRaw("COALESCE(name,'(ไม่ระบุ)') as name, COUNT(DISTINCT project_id) as n")
                ->groupBy('name')
                ->orderByDesc('n')
                ->limit(30)
                ->get()
                ->map(fn($r)=>['name'=>$r->name,'n'=>(int)$r->n])
                ->all();
        }

        // ---- Outputs per year / Publication per year / authorship roles EA101 ----
        $outputsPerYear = [];
        $pubPerYear = [];
        $authorRoleCounts = []; // first/co/corresponding
        if (Schema::hasTable('outputs')) {
            $outYearExpr = Schema::hasColumn('outputs','year')
                ? "outputs.year"
                : (Schema::hasColumn('outputs','published_at') ? "YEAR(outputs.published_at)" : "YEAR(outputs.created_at)");

            $outYears = DB::table('outputs')
                ->selectRaw("DISTINCT {$outYearExpr} as y")
                ->whereNotNull(DB::raw($outYearExpr))
                ->orderBy('y','asc')
                ->limit(30)
                ->pluck('y')
                ->filter()
                ->map(fn($y)=>(int)$y)
                ->values()
                ->all();

            foreach ($outYears as $y) {
                $outputsPerYear[$y] = (int) DB::table('outputs')->whereRaw("{$outYearExpr} = ?", [$y])->count();

                // publication = type contains 'publication' OR 'journal' OR 'paper'
                if (Schema::hasColumn('outputs','type')) {
                    $pubPerYear[$y] = (int) DB::table('outputs')
                        ->whereRaw("{$outYearExpr} = ?", [$y])
                        ->where(function($q){
                            $q->where('type','like','%publication%')
                              ->orWhere('type','like','%journal%')
                              ->orWhere('type','like','%paper%')
                              ->orWhere('type','like','%ตีพิมพ์%');
                        })
                        ->count();
                } else {
                    $pubPerYear[$y] = 0;
                }
            }

            // authorship roles (ถ้ามี outputs_authors table: output_id, role)
            if (Schema::hasTable('output_authors')) {
                $first = DB::table('output_authors')->whereIn('role',['first','first-author','first_author','First Author'])->count();
                $co    = DB::table('output_authors')->whereIn('role',['co','co-author','co_author','Co Author'])->count();
                $corr  = DB::table('output_authors')->whereIn('role',['corresponding','corresponding-author','Corresponding Author'])->count();
                $authorRoleCounts = ['first'=>$first,'co'=>$co,'corresponding'=>$corr];
            } else {
                $authorRoleCounts = ['first'=>0,'co'=>0,'corresponding'=>0];
            }
        }

        return [
            'meta' => [
                'now' => $now->toDateTimeString(),
                'this_year_ad' => $thisYearAD,
                'this_year_be' => $thisYearBE,
                'years_ad' => $years,
                'years_be' => array_map(fn($y)=>$y+543, $years),
            ],
            'red_zone' => [
                'end_count' => $endCount,
                'overdue_count' => $overdueCount,
                'near_end_count' => $nearEndCount,
                'red_pct' => $redPct,
                'timeline' => $timeline,
                'overdue_list' => $overdue,
                'near_end_list' => $nearEnd,
            ],
            'projects' => [
                'per_year' => $projectsPerYear,
                'new_per_year' => $projectsNewPerYear,
                'ongoing_per_year' => $projectsOngoingPerYear,
            ],
            'budget' => [
                'per_year_mil' => $budgetPerYear,
                'fund_split_per_year_mil' => $fundSplitPerYear,
            ],
            'fund_sources' => [
                'external_by_source' => $externalBySource,
                'internal_by_source' => $internalBySource,
                'external_by_source_per_year' => $externalBySourcePerYear,
                'internal_by_source_per_year' => $internalBySourcePerYear,
            ],
            'status' => [
                'per_year' => $statusPerYear,
            ],
            'outputs' => [
                'per_year' => $outputsPerYear,
                'pub_per_year' => $pubPerYear,
                'author_roles' => $authorRoleCounts,
            ],
            'collaboration' => [
                'ratio' => $collabRatio,
            ],
            'pi' => [
                'workload' => $piWorkload,
            ],
            'fte' => [
                'distribution' => $fteDist,
            ],
            'copi' => [
                'top' => $copisTop,
            ],
        ];
    }

    /* =========================================================
     * LLM Plan (or fallback)
     * ========================================================= */
    private function makePlanByLLMOrFallback(string $prompt, array $pack): array
    {
        // ถ้าไม่มี LLM URL → fallback
        $llmUrl = (string) config('rm.llm_url', env('RM_LLM_URL', ''));
        $llmOn  = filter_var(config('rm.llm_enabled', env('RM_LLM_ENABLED', false)), FILTER_VALIDATE_BOOL);

        // default: show all 18 + redzone
        $fallbackPlan = $this->fallbackPlan($prompt, $pack);

        if (!$llmOn || !$llmUrl) return $fallbackPlan;

        try {
            $schemaHint = $this->planSchemaHint();
            $payload = [
                'prompt' => $prompt,
                'schema' => $schemaHint,
                'data_pack_summary' => $this->summarizePackForLLM($pack),
                'rules' => [
                    'Return JSON only',
                    'Choose chart types among: bar, line, doughnut, stacked_bar',
                    'Prefer: line for time series, doughnut for part-to-whole, stacked_bar for multi-series per year',
                    'Select up to 18 widgets. Always include red_zone_gauge and red_zone_timeline.',
                ],
            ];

            $res = Http::timeout(15)->post($llmUrl, $payload);
            if (!$res->ok()) return $fallbackPlan;

            $j = $res->json();
            if (!is_array($j)) return $fallbackPlan;

            // minimal validate
            if (empty($j['widgets']) || !is_array($j['widgets'])) return $fallbackPlan;

            // sanitize
            $allowed = ['bar','line','doughnut','stacked_bar','timeline','gauge','table','list'];
            foreach ($j['widgets'] as &$w) {
                if (!isset($w['id'])) $w['id'] = Str::uuid()->toString();
                if (!isset($w['kind']) || !in_array($w['kind'], $allowed, true)) $w['kind'] = 'bar';
            }

            $j['mode_hint'] = 'llm';
            return $j;

        } catch (\Throwable $e) {
            return $fallbackPlan;
        }
    }

    private function fallbackPlan(string $prompt, array $pack): array
    {
        // แผนหลัก: เอาทั้งหมด (18 กราฟ + red zone + overdue list)
        // ถ้าผู้ใช้ถามเจาะจง ก็ filter เฉพาะบางส่วนได้ (แต่คุณอยาก “เอาหมดเลย” => default all)
        $t = mb_strtolower($prompt);

        $all = [
            ['id'=>'red_zone_gauge','kind'=>'gauge'],
            ['id'=>'red_zone_timeline','kind'=>'timeline'],
            ['id'=>'overdue_list','kind'=>'table'],
            ['id'=>'projects_year_total','kind'=>'line'],
            ['id'=>'projects_year_new_ongoing','kind'=>'stacked_bar'],
            ['id'=>'budget_year_total','kind'=>'line'],
            ['id'=>'fund_split_year','kind'=>'doughnut'], // ปีล่าสุด
            ['id'=>'fund_split_year_stacked','kind'=>'stacked_bar'], // รายปี
            ['id'=>'external_by_source','kind'=>'bar'],
            ['id'=>'internal_by_source','kind'=>'bar'],
            ['id'=>'external_by_source_per_year','kind'=>'stacked_bar'],
            ['id'=>'internal_by_source_per_year','kind'=>'stacked_bar'],
            ['id'=>'outputs_per_year','kind'=>'line'],
            ['id'=>'pub_per_year','kind'=>'bar'],
            ['id'=>'status_per_year','kind'=>'stacked_bar'],
            ['id'=>'author_roles','kind'=>'doughnut'],
            ['id'=>'collab_ratio','kind'=>'doughnut'],
            ['id'=>'pi_workload','kind'=>'bar'],
            ['id'=>'fte_distribution','kind'=>'bar'],
            ['id'=>'copi_top','kind'=>'bar'],
        ];

        // ถ้าพิมพ์ “auto” หรืออยากทั้งหมด ก็ส่งเต็ม
        $widgets = $all;

        // heuristic: ถ้าถามคำว่า “งบ” ก็เอางบเด่นก่อน แต่ยังคงทั้งหมด (คุณขอหมด)
        return [
            'mode_hint' => 'fallback',
            'narrative_html' => $this->defaultNarrative($pack),
            'widgets' => $widgets,
        ];
    }

    private function planSchemaHint(): array
    {
        return [
            'mode_hint' => 'llm|fallback',
            'narrative_html' => '<p>...</p>',
            'widgets' => [
                [
                    'id' => 'string',
                    'kind' => 'gauge|timeline|table|list|bar|line|doughnut|stacked_bar',
                    'title' => 'optional string',
                    'note' => 'optional string',
                ]
            ]
        ];
    }

    private function summarizePackForLLM(array $pack): array
    {
        return [
            'meta' => $pack['meta'],
            'red_zone' => [
                'end_count' => $pack['red_zone']['end_count'],
                'overdue_count' => $pack['red_zone']['overdue_count'],
                'near_end_count' => $pack['red_zone']['near_end_count'],
                'red_pct' => $pack['red_zone']['red_pct'],
                'timeline' => $pack['red_zone']['timeline'],
            ],
            'years_ad' => $pack['meta']['years_ad'],
            'projects_per_year' => $pack['projects']['per_year'],
            'budget_per_year_mil' => $pack['budget']['per_year_mil'],
            'outputs_per_year' => $pack['outputs']['per_year'],
            'pub_per_year' => $pack['outputs']['pub_per_year'],
            'status_year_keys' => array_keys($pack['status']['per_year'] ?? []),
        ];
    }

    /* =========================================================
     * Widgets Builder (18 graphs + redzone)
     * ========================================================= */
    private function buildWidgetsFromPlan(array $plan, array $pack): array
    {
        $widgetsReq = $plan['widgets'] ?? [];
        $out = [];

        foreach ($widgetsReq as $w) {
            $id = $w['id'] ?? Str::uuid()->toString();
            $kind = $w['kind'] ?? 'bar';

            $built = $this->buildWidgetById($id, $kind, $pack, $w);
            if ($built) $out[] = $built;
        }

        // ensure unique ids
        $seen = [];
        $out = array_values(array_filter($out, function($x) use (&$seen){
            if (isset($seen[$x['id']])) return false;
            $seen[$x['id']] = true;
            return true;
        }));

        return $out;
    }

    private function buildWidgetById(string $id, string $kind, array $pack, array $hint): ?array
    {
        $title = $hint['title'] ?? null;
        $note  = $hint['note'] ?? null;

        // helper to return widget skeleton
        $mk = function($id, $kind, $title, $payload = [], $note=null, $size='md'){
            return [
                'id' => $id,
                'kind' => $kind,      // gauge|timeline|table|chart
                'title' => $title,
                'note' => $note,
                'size' => $size,      // sm|md|lg
                'payload' => $payload // chart config / table rows / timeline bars
            ];
        };

        // choose “latest year”
        $years = $pack['meta']['years_ad'] ?? [];
        $latestY = $years ? max($years) : (int)date('Y');

        switch ($id) {

            /* ================= RED ZONE ================= */
            case 'red_zone_gauge':
                return $mk(
                    $id,
                    'gauge',
                    $title ?? '🔥 RED ZONE Gauge',
                    [
                        'value' => $pack['red_zone']['red_pct'] ?? 0,
                        'subtitle' => 'สัดส่วนโครงการที่ Overdue หรือใกล้สิ้นสุด (60 วัน) ในกลุ่มที่มีวันสิ้นสุด',
                        'meta' => [
                            'end_count' => $pack['red_zone']['end_count'],
                            'overdue' => $pack['red_zone']['overdue_count'],
                            'near_end' => $pack['red_zone']['near_end_count'],
                        ]
                    ],
                    $note ?? null,
                    'md'
                );

            case 'red_zone_timeline':
                return $mk(
                    $id,
                    'timeline',
                    $title ?? '📅 Timeline วันสิ้นสุดโครงการ',
                    [
                        'buckets' => $pack['red_zone']['timeline'] ?? [],
                        'legend'  => ['Overdue','0-30','31-60','61-120','>120'],
                    ],
                    'นับจากวันสิ้นสุด (date_end) ของโครงการ',
                    'md'
                );

            case 'overdue_list':
                return $mk(
                    $id,
                    'table',
                    $title ?? '🔥 โครงการที่เกินกำหนด (Overdue)',
                    [
                        'headers' => ['โครงการ','PI','ปี','วันสิ้นสุด','ทุน','เปิด'],
                        'fields'  => ['project','pi','year_be','date_end','funding','href'],
                        'rows'    => $pack['red_zone']['overdue_list'] ?? []
                    ],
                    $note ?? 'เรียงตามวันสิ้นสุดเก่าที่สุดก่อน',
                    'lg'
                );

            /* ================= Projects ================= */
            case 'projects_year_total':
                return $mk($id,'chart',$title ?? '📈 จำนวนโครงการรายปี (รวม)', $this->chartLineFromKV($pack['projects']['per_year'] ?? [], 'จำนวนโครงการ'), null,'md');

            case 'projects_year_new_ongoing':
                return $mk($id,'chart',$title ?? '📅 โครงการทั้งหมด / ใหม่ / กำลังดำเนินการ (รายปี)', $this->chartStackedFrom3KV(
                    $pack['projects']['per_year'] ?? [],
                    $pack['projects']['new_per_year'] ?? [],
                    $pack['projects']['ongoing_per_year'] ?? [],
                    ['ทั้งหมด','ใหม่','กำลังดำเนินการ']
                ), 'stacked', 'md');

            /* ================= Budget / Fund ================= */
            case 'budget_year_total':
                return $mk($id,'chart',$title ?? '💰 งบประมาณที่ได้รับ (รวมรายปี)', $this->chartLineFromKV($pack['budget']['per_year_mil'] ?? [], 'ล้านบาท', true), 'หน่วย: ล้านบาท','md');

            case 'fund_split_year':
                // donut ปีล่าสุด
                $split = $pack['budget']['fund_split_per_year_mil'][$latestY] ?? ['internal'=>0,'external'=>0,'unknown'=>0];
                return $mk($id,'chart',$title ?? "🏷️ สัดส่วนทุน ภายใน/ภายนอก (. $latestY+543. )", $this->chartDonut(['ภายใน','ภายนอก','ไม่ระบุ'], [$split['internal'],$split['external'],$split['unknown']], 'ล้านบาท'),
                    'ปีล่าสุด', 'sm');

            case 'fund_split_year_stacked':
                return $mk($id,'chart',$title ?? '🏷️ สัดส่วนทุน ภายใน/ภายนอก (รายปี)',
                    $this->chartStackedFundSplit($pack['budget']['fund_split_per_year_mil'] ?? []),
                    'stacked (ล้านบาท)','md');

            case 'external_by_source':
                return $mk($id,'chart',$title ?? '🏦 ทุนภายนอก แยกตามแหล่งทุน', $this->chartBarFromPairs($pack['fund_sources']['external_by_source'] ?? [], 'จำนวนโครงการ'), 'Top 20','md');

            case 'internal_by_source':
                return $mk($id,'chart',$title ?? '🏛️ ทุนภายใน แยกตามแหล่งทุน', $this->chartBarFromPairs($pack['fund_sources']['internal_by_source'] ?? [], 'จำนวนโครงการ'), 'Top 20','md');

            case 'external_by_source_per_year':
                return $mk($id,'chart',$title ?? '🏦 ทุนภายนอก แยกตามแหล่งทุน (ต่อปี)', $this->chartStackedSourcesPerYear($pack['fund_sources']['external_by_source_per_year'] ?? []), 'stacked','lg');

            case 'internal_by_source_per_year':
                return $mk($id,'chart',$title ?? '🏛️ ทุนภายใน แยกตามแหล่งทุน (ต่อปี)', $this->chartStackedSourcesPerYear($pack['fund_sources']['internal_by_source_per_year'] ?? []), 'stacked','lg');

            /* ================= Outputs ================= */
            case 'outputs_per_year':
                return $mk($id,'chart',$title ?? '📝 Output (รายปี)', $this->chartLineFromKV($pack['outputs']['per_year'] ?? [], 'จำนวน Output'), null,'md');

            case 'pub_per_year':
                return $mk($id,'chart',$title ?? '📚 Publication รายปี', $this->chartBarFromKV($pack['outputs']['pub_per_year'] ?? [], 'จำนวน Publication'), 'แท่งแนวตั้ง','md');

            case 'author_roles':
                $ar = $pack['outputs']['author_roles'] ?? ['first'=>0,'co'=>0,'corresponding'=>0];
                return $mk($id,'chart',$title ?? 'จำนวน First/Co/Corresponding (EA101 Authors)', $this->chartDonut(
                    ['First Author','Co Author','Corresponding'],
                    [(int)$ar['first'],(int)$ar['co'],(int)$ar['corresponding']],
                    'จำนวน'
                ), 'ต้องมีตาราง output_authors เพื่อให้ตัวเลขจริง','sm');

            /* ================= Status ================= */
            case 'status_per_year':
                return $mk($id,'chart',$title ?? '🧩 สถานะของโครงการ (รายปี)', $this->chartStackedStatusPerYear($pack['status']['per_year'] ?? []), 'stacked','lg');

            /* ================= Collaboration ================= */
            case 'collab_ratio':
                $pairs = $pack['collaboration']['ratio'] ?? [];
                return $mk($id,'chart',$title ?? '🤝 สัดส่วนโครงการ (นอกคณะ/ในคณะ/ในหน่วย)', $this->chartDonutFromPairs($pairs, 'จำนวนโครงการ'), 'ถ้า field collaboration_scope ไม่พบ จะเป็นค่าว่าง','sm');

            /* ================= PI / FTE / COPI ================= */
            case 'pi_workload':
                return $mk($id,'chart',$title ?? '👨‍🔬 จำนวนงานของ PI', $this->chartBarFromPiPairs($pack['pi']['workload'] ?? []), 'Top 20','md');

            case 'fte_distribution':
                return $mk($id,'chart',$title ?? '📈 การกระจาย FTE', $this->chartBarFteDist($pack['fte']['distribution'] ?? []), 'จำนวนเรคคอร์ดต่อค่า fte_value','md');

            case 'copi_top':
                return $mk($id,'chart',$title ?? '🤝 รายชื่อผู้วิจัยร่วม CO-PI', $this->chartBarFromNamePairs($pack['copi']['top'] ?? []), 'Top 30','md');
        }

        // ถ้า kind เป็น chart แต่ id ไม่รู้จัก ก็ไม่สร้าง
        return null;
    }

    /* =========================================================
     * Insights
     * ========================================================= */
    private function buildInsights(array $pack): array
    {
        $ins = [];

        $rz = $pack['red_zone'];
        if (($rz['overdue_count'] ?? 0) > 0) {
            $ins[] = [
                'level' => 'danger',
                'title' => '🔥 Overdue',
                'message' => 'พบโครงการที่เกินกำหนดแล้ว: '.$rz['overdue_count'].' โครงการ'
            ];
        } else {
            $ins[] = [
                'level' => 'info',
                'title' => 'Overdue',
                'message' => 'ยังไม่พบโครงการที่เกินกำหนด (ในกลุ่มที่มี date_end)'
            ];
        }

        if (($rz['near_end_count'] ?? 0) > 0) {
            $ins[] = [
                'level' => 'warning',
                'title' => '⏳ ใกล้สิ้นสุด 60 วัน',
                'message' => 'พบโครงการใกล้สิ้นสุด: '.$rz['near_end_count'].' โครงการ'
            ];
        }

        $years = $pack['meta']['years_ad'] ?? [];
        if ($years) {
            $latest = max($years);
            $p = $pack['projects']['per_year'][$latest] ?? 0;
            $b = $pack['budget']['per_year_mil'][$latest] ?? 0;
            $ins[] = [
                'level' => 'info',
                'title' => 'ปีล่าสุด ('.($latest+543).')',
                'message' => "โครงการ {$p} • งบ {$b} ล้านบาท"
            ];
        }

        return $ins;
    }

    private function defaultNarrative(array $pack): string
    {
        $rz = $pack['red_zone'];
        $y  = $pack['meta']['years_be'] ? max($pack['meta']['years_be']) : null;

        return "<div>
            <b>ภาพรวมอัตโนมัติ</b><br>
            • RED ZONE: {$rz['red_pct']}% (Overdue {$rz['overdue_count']} + ใกล้สิ้นสุด {$rz['near_end_count']})<br>
            • ปีล่าสุด: ".($y ? $y : '-')."
        </div>";
    }

    /* =========================================================
     * Chart helpers (Chart.js configs)
     * ========================================================= */
    private function chartLineFromKV(array $kv, string $label, bool $isMillion=false): array
    {
        ksort($kv);
        $labels = array_map(fn($y)=> (string)((int)$y + 543), array_keys($kv));
        $data   = array_values($kv);

        return [
            'type' => 'line',
            'intent' => $isMillion ? 'budget' : 'series',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label.($isMillion ? ' (ล้าน)' : ''),
                    'data' => $data,
                    'tension' => 0.3
                ]]
            ],
            'options' => [
                'responsive' => true
            ]
        ];
    }

    private function chartBarFromKV(array $kv, string $label): array
    {
        ksort($kv);
        $labels = array_map(fn($y)=> (string)((int)$y + 543), array_keys($kv));
        $data   = array_values($kv);

        return [
            'type' => 'bar',
            'intent' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                ]]
            ],
            'options' => ['responsive'=>true]
        ];
    }

    private function chartStackedFrom3KV(array $a, array $b, array $c, array $labels3): array
    {
        $years = array_unique(array_merge(array_keys($a), array_keys($b), array_keys($c)));
        sort($years);
        $x = array_map(fn($y)=> (string)((int)$y + 543), $years);

        $d1=[];$d2=[];$d3=[];
        foreach ($years as $y){
            $d1[] = (int)($a[$y] ?? 0);
            $d2[] = (int)($b[$y] ?? 0);
            $d3[] = (int)($c[$y] ?? 0);
        }

        return [
            'type' => 'bar',
            'intent' => 'stacked_bar',
            'data' => [
                'labels' => $x,
                'datasets' => [
                    ['label'=>$labels3[0],'data'=>$d1,'stack'=>'s'],
                    ['label'=>$labels3[1],'data'=>$d2,'stack'=>'s'],
                    ['label'=>$labels3[2],'data'=>$d3,'stack'=>'s'],
                ]
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'x' => ['stacked'=>true],
                    'y' => ['stacked'=>true],
                ]
            ]
        ];
    }

    private function chartDonut(array $labels, array $data, string $unit): array
    {
        return [
            'type' => 'doughnut',
            'intent' => 'donut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $unit,
                    'data' => $data,
                ]]
            ],
            'options' => ['responsive'=>true]
        ];
    }

    private function chartBarFromPairs(array $pairs, string $label): array
    {
        $labels = array_map(fn($p)=> (string)$p['k'], $pairs);
        $data   = array_map(fn($p)=> (int)$p['n'], $pairs);

        return [
            'type'=>'bar',
            'intent'=>'bar',
            'data'=>[
                'labels'=>$labels,
                'datasets'=>[[
                    'label'=>$label,
                    'data'=>$data
                ]]
            ],
            'options'=>['responsive'=>true]
        ];
    }

    private function chartDonutFromPairs(array $pairs, string $unit): array
    {
        $labels = array_map(fn($p)=> (string)$p['k'], $pairs);
        $data   = array_map(fn($p)=> (int)$p['n'], $pairs);
        return $this->chartDonut($labels,$data,$unit);
    }

    private function chartBarFromPiPairs(array $pairs): array
    {
        $labels = array_map(fn($p)=> (string)$p['pi'], $pairs);
        $data   = array_map(fn($p)=> (int)$p['n'], $pairs);

        return [
            'type'=>'bar',
            'intent'=>'bar',
            'data'=>[
                'labels'=>$labels,
                'datasets'=>[[
                    'label'=>'จำนวนโครงการ',
                    'data'=>$data
                ]]
            ],
            'options'=>['responsive'=>true]
        ];
    }

    private function chartBarFromNamePairs(array $pairs): array
    {
        $labels = array_map(fn($p)=> (string)$p['name'], $pairs);
        $data   = array_map(fn($p)=> (int)$p['n'], $pairs);

        return [
            'type'=>'bar',
            'intent'=>'bar',
            'data'=>[
                'labels'=>$labels,
                'datasets'=>[[
                    'label'=>'จำนวนโครงการที่เกี่ยวข้อง',
                    'data'=>$data
                ]]
            ],
            'options'=>['responsive'=>true]
        ];
    }

    private function chartBarFteDist(array $pairs): array
    {
        $labels = array_map(fn($p)=> (string)$p['v'], $pairs);
        $data   = array_map(fn($p)=> (int)$p['n'], $pairs);

        return [
            'type'=>'bar',
            'intent'=>'bar',
            'data'=>[
                'labels'=>$labels,
                'datasets'=>[[
                    'label'=>'จำนวนเรคคอร์ด',
                    'data'=>$data
                ]]
            ],
            'options'=>['responsive'=>true]
        ];
    }

    private function chartStackedFundSplit(array $splitPerYear): array
    {
        $years = array_keys($splitPerYear);
        sort($years);
        $labels = array_map(fn($y)=>(string)((int)$y+543), $years);

        $internal=[];$external=[];$unknown=[];
        foreach ($years as $y){
            $internal[] = (float)($splitPerYear[$y]['internal'] ?? 0);
            $external[] = (float)($splitPerYear[$y]['external'] ?? 0);
            $unknown[]  = (float)($splitPerYear[$y]['unknown'] ?? 0);
        }

        return [
            'type'=>'bar',
            'intent'=>'stacked_bar',
            'data'=>[
                'labels'=>$labels,
                'datasets'=>[
                    ['label'=>'ภายใน (ล้าน)','data'=>$internal,'stack'=>'s'],
                    ['label'=>'ภายนอก (ล้าน)','data'=>$external,'stack'=>'s'],
                    ['label'=>'ไม่ระบุ (ล้าน)','data'=>$unknown,'stack'=>'s'],
                ]
            ],
            'options'=>[
                'responsive'=>true,
                'scales'=>[
                    'x'=>['stacked'=>true],
                    'y'=>['stacked'=>true],
                ]
            ]
        ];
    }

    private function chartStackedSourcesPerYear(array $byYear): array
    {
        // byYear: year => [{k,n}]
        $years = array_keys($byYear);
        sort($years);
        $x = array_map(fn($y)=>(string)((int)$y+543), $years);

        // collect top sources across years
        $sources = [];
        foreach ($byYear as $y=>$pairs){
            foreach ($pairs as $p) $sources[$p['k']] = true;
        }
        $sources = array_keys($sources);
        $sources = array_slice($sources, 0, 12);

        $datasets = [];
        foreach ($sources as $s) {
            $data = [];
            foreach ($years as $y) {
                $pairs = $byYear[$y] ?? [];
                $found = 0;
               a: foreach ($pairs as $p) {
                    if ($p['k'] === $s) { $found = (int)$p['n']; break; }
                }
                $data[] = $found;
            }
            $datasets[] = ['label'=>$s,'data'=>$data,'stack'=>'s'];
        }

        return [
            'type'=>'bar',
            'intent'=>'stacked_bar',
            'data'=>[
                'labels'=>$x,
                'datasets'=>$datasets
            ],
            'options'=>[
                'responsive'=>true,
                'scales'=>[
                    'x'=>['stacked'=>true],
                    'y'=>['stacked'=>true],
                ]
            ]
        ];
    }

    private function chartStackedStatusPerYear(array $perYear): array
    {
        $years = array_keys($perYear);
        sort($years);
        $x = array_map(fn($y)=>(string)((int)$y+543), $years);

        $statuses = [];
        foreach ($perYear as $y=>$m) foreach ($m as $s=>$n) $statuses[$s]=true;
        $statuses = array_keys($statuses);

        $datasets = [];
        foreach ($statuses as $s) {
            $data=[];
            foreach ($years as $y) $data[] = (int)($perYear[$y][$s] ?? 0);
            $datasets[] = ['label'=>$s,'data'=>$data,'stack'=>'s'];
        }

        return [
            'type'=>'bar',
            'intent'=>'stacked_bar',
            'data'=>[
                'labels'=>$x,
                'datasets'=>$datasets
            ],
            'options'=>[
                'responsive'=>true,
                'scales'=>[
                    'x'=>['stacked'=>true],
                    'y'=>['stacked'=>true],
                ]
            ]
        ];
    }
}
