<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

// Eloquent models
use App\Models\Project;
use App\Models\FteRole;
use App\Models\Output;
use App\Models\BackOfficeTask;

class AgentController extends Controller
{
    /* ==========================
     * Config / Modes
     * ========================== */
    private const MODE_AUTO   = 'AUTO';
    private const MODE_HYBRID = 'HYBRID';
    private const MODE_GEMINI = 'GEMINI_ONLY';

    // เรียกผ่าน Gateway ที่คุณตั้งไว้ (ถ้าใช้ Hybrid/LLM)
    private const REMOTE_GINI_URL = 'https://back.sihealthpolicy.com/api/ask-gemini';

    private array $geminiModels = [
        'gemini-2.5-flash',
        'gemini-2.5-pro',
        'gemini-flash-latest',
    ];
    private string $defaultMode = self::MODE_HYBRID;

    /* ==========================
     * Small helpers
     * ========================== */
    private function nowBE(): int { return (int)date('Y') + 543; }
    private function fmtMil(float $n): string { return number_format($n / 1_000_000, 2).' ล้านบ.'; }

    /** ดึงตัวเลข 4 หลักแรกจากสตริง → ปี (BE/CE) */
    private function firstYearDigits(?string $raw): ?int {
        if (!$raw) return null;
        if (preg_match('/(19|20|24|25)\d{2}/u', $raw, $m)) return (int)$m[0];
        if (preg_match('/\d{4}/u', $raw, $m2)) return (int)$m2[0];
        return null;
    }
    /** แปลงปีใดๆ ให้เป็น พ.ศ. */
    private function toBEFromAny(?int $y): ?int {
        if (!$y) return null;
        return ($y <= 2100) ? $y + 543 : $y;
    }
    /** อ่านปี พ.ศ. จากฟิลด์ข้อความ */
    private function beIntFromText(?string $txt): ?int {
        $d = $this->firstYearDigits($txt);
        return $this->toBEFromAny($d);
    }
    /** แปลง budget text → float */
    private function parseMoney($raw): float {
        if ($raw === null) return 0.0;
        $s = trim((string)$raw);
        $s = str_replace([",", " ", "฿"], "", $s);
        return is_numeric($s) ? (float)$s : 0.0;
    }
    /** normalize ประเภททุน */
    private function normFundingType($t): string {
        $t = trim((string)$t);
        if ($t === '') return 'ไม่ระบุ';
        if (Str::contains($t, 'ใน')) return 'ภายใน';
        if (Str::contains($t, 'out') || Str::contains($t, 'ภาย')) return 'ภายนอก';
        if (Str::startsWith($t, 'ภายใน')) return 'ภายใน';
        if (Str::startsWith($t, 'ภายนอก')) return 'ภายนอก';
        return $t;
    }

    private function yearExpr(string $col): string {
        return "YEAR(COALESCE(STR_TO_DATE($col,'%d-%m-%Y'),STR_TO_DATE($col,'%Y-%m-%d'),$col))";
    }
    private function scopeFTEActiveInCEYear($q, int $yearCE) {
        $ys=$this->yearExpr('`start_date`'); $ye=$this->yearExpr('`end_date`');
        return $q->where(function($w) use($yearCE,$ys,$ye){
            $w->whereRaw("? BETWEEN $ys AND $ye",[$yearCE])
              ->orWhere(function($x) use($yearCE,$ys){
                  $x->where(function($z){ $z->whereNull('end_date')->orWhere('end_date',''); })
                    ->whereRaw("$ys = ?",[$yearCE]);
              });
        });
    }

    /* ==========================
     * Robust Search (ชื่อใกล้เคียง)
     * ========================== */
    private function tokens(string $q): array {
        $stop = ['โครงการ','โครงการวิจัย','project','งานวิจัย','และ','หรือ'];
        $raw  = preg_split('/[\\s,]+/u', trim($q)) ?: [];
        $tok  = [];
        foreach ($raw as $t) {
            $t = trim($t);
            if ($t !== '' && !in_array($t, $stop, true)) $tok[] = $t;
        }
        return $tok ?: ($q!==''?[$q]:[]);
    }
    private function robustProjectCandidates(string $q, ?int $yearBE = null): array {
        $tok = $this->tokens($q);
        $likeSets = [];
        foreach ($tok as $t) {
            $like   = '%'.str_replace(['%','_'],['\%','\_'],$t).'%';
            $noSp   = '%'.str_replace(' ','',$t).'%';
            $likeSets[] = compact('like','noSp');
        }

        $candidates = collect();

        // projects
        $proj = Project::query()
            ->select('id')
            ->when(!empty($likeSets), function($qB) use($likeSets){
                $cols = [
                    'project_name_th','project_name_en','project_code','theme','pi_name',
                    'funding_type','funding_source','project_type','execution_status',
                    'review_status','closure_status','size','r_code','project_list'
                ];
                $qB->where(function($W) use($cols,$likeSets){
                    foreach($likeSets as $s){
                        $W->where(function($OR) use($cols,$s){
                            foreach($cols as $c){
                                $OR->orWhere($c,'like',$s['like']);
                                $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?",[$s['noSp']]);
                            }
                        });
                    }
                });
            })
            ->when($yearBE, fn($qq)=>$qq->where('start_year',$yearBE))
            ->limit(200)->pluck('id');

        $candidates = $candidates->merge($proj);

        // fte_roles
        if (Schema::hasTable('fte_roles')) {
            $fte = DB::table('fte_roles')
                ->select('project_id as id')
                ->when(!empty($likeSets), function($qB) use($likeSets){
                    $cols = ['name','role_group','job_detail','task_detail','approved_by'];
                    $qB->where(function($W) use($cols,$likeSets){
                        foreach($likeSets as $s){
                            $W->where(function($OR) use($cols,$s){
                                foreach($cols as $c){
                                    $OR->orWhere($c,'like',$s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?",[$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)->pluck('id');
            $candidates = $candidates->merge($fte);
        }

        // outputs
        if (Schema::hasTable('outputs')) {
            $out = DB::table('outputs')
                ->select('project_id as id')
                ->when(!empty($likeSets), function($qB) use($likeSets){
                    $cols = ['title','type','journal','quartile','impact_factor','year','project_output'];
                    $qB->where(function($W) use($cols,$likeSets){
                        foreach($likeSets as $s){
                            $W->where(function($OR) use($cols,$s){
                                foreach($cols as $c){
                                    $OR->orWhere($c,'like',$s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?",[$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)->pluck('id');
            $candidates = $candidates->merge($out);
        }

        // copi
        if (Schema::hasTable('copis')) {
            $copi = DB::table('copis')
                ->select('project_id as id')
                ->when(!empty($likeSets), function($qB) use($likeSets){
                    $cols = ['name','affiliation'];
                    $qB->where(function($W) use($cols,$likeSets){
                        foreach($likeSets as $s){
                            $W->where(function($OR) use($cols,$s){
                                foreach($cols as $c){
                                    $OR->orWhere($c,'like',$s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?",[$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)->pluck('id');
            $candidates = $candidates->merge($copi);
        }

        // co_research
        if (Schema::hasTable('co_research')) {
            $co = DB::table('co_research')
                ->select('project_id as id')
                ->when(!empty($likeSets), function($qB) use($likeSets){
                    $cols = ['name','affiliation','unit_status'];
                    $qB->where(function($W) use($cols,$likeSets){
                        foreach($likeSets as $s){
                            $W->where(function($OR) use($cols,$s){
                                foreach($cols as $c){
                                    $OR->orWhere($c,'like',$s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?",[$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)->pluck('id');
            $candidates = $candidates->merge($co);
        }

        return $candidates->filter()->unique()->values()->take(300)->all();
    }

    /** รวมข้อมูลโปรเจกต์ที่ UI ใช้งานได้ทันที */
    private function hydrateProjectsWithJoins(array $projectIds): array {
        if (empty($projectIds)) return [];

        $projects = Project::query()
            ->whereIn('id',$projectIds)
            ->select([
                'id',
                DB::raw("COALESCE(project_name_th, project_name_en, CONCAT('โครงการ #',id),'ไม่ระบุ') AS project_name"),
                DB::raw("COALESCE(pi_name,'ไม่ระบุ') AS pi_name"),
                DB::raw("COALESCE(funding_type,'ไม่ระบุ') AS funding_type"),
                DB::raw("COALESCE(funding_source,'ไม่ระบุ') AS funding_source"),
                DB::raw("COALESCE(project_type,'ไม่ระบุ') AS project_type"),
                DB::raw("COALESCE(theme,'ไม่ระบุ') AS theme"),
                DB::raw("COALESCE(size,'ไม่ระบุ') AS size"),
                DB::raw("COALESCE(project_code,'-') AS project_code"),
                DB::raw("COALESCE(start_year,0) AS start_year"),
                DB::raw("COALESCE(budget,0) AS budget"),
            ])
            ->get()
            ->keyBy('id');

        if ($projects->isEmpty()) return [];
        $ids = $projects->keys();

        // Co-PI
        $copi = Schema::hasTable('copis')
            ? DB::table('copis')->select('project_id',
                DB::raw("GROUP_CONCAT(CONCAT(COALESCE(name,'ไม่ระบุ'),' (',COALESCE(affiliation,'-'),')') SEPARATOR '; ') AS copi_txt")
              )->whereIn('project_id',$ids)->groupBy('project_id')->pluck('copi_txt','project_id')
            : collect();

        // Co-Research
        $core = Schema::hasTable('co_research')
            ? DB::table('co_research')->select('project_id',
                DB::raw("GROUP_CONCAT(CONCAT(COALESCE(name,'ไม่ระบุ'),' [',COALESCE(unit_status,'-'),']') SEPARATOR '; ') AS core_txt")
              )->whereIn('project_id',$ids)->groupBy('project_id')->pluck('core_txt','project_id')
            : collect();

        // FTE
        $fte = Schema::hasTable('fte_roles')
            ? DB::table('fte_roles')->select('project_id',
                DB::raw("GROUP_CONCAT(CONCAT(COALESCE(name,'ไม่ระบุ'),':',COALESCE(ROUND(fte_value,2),0)) SEPARATOR '; ') AS fte_txt"),
                DB::raw("SUM(COALESCE(fte_value,0)) AS fte_sum")
              )->whereIn('project_id',$ids)->groupBy('project_id')->get()->keyBy('project_id')
            : collect();

        // Outputs
        $out = Schema::hasTable('outputs')
            ? DB::table('outputs')->select('project_id',
                DB::raw("COUNT(*) AS out_cnt"),
                DB::raw("GROUP_CONCAT(CONCAT('[',COALESCE(year,'-'),'] ',COALESCE(type,'-'),' - ',LEFT(COALESCE(title,'ไม่ระบุ'),80)) SEPARATOR ' | ') AS out_txt")
              )->whereIn('project_id',$ids)->groupBy('project_id')->get()->keyBy('project_id')
            : collect();

        // IRB
        $irb = Schema::hasTable('irbs')
            ? DB::table('irbs')->select('project_id',
                DB::raw("MAX(exp_date) AS latest_exp"),
                DB::raw("GROUP_CONCAT(CONCAT(COALESCE(code,'-'),':',COALESCE(exp_date,'-')) SEPARATOR '; ') AS irb_txt")
              )->whereIn('project_id',$ids)->groupBy('project_id')->get()->keyBy('project_id')
            : collect();

        $result = [];
        foreach ($projects as $pid => $p) {
            $result[] = [
                'project_id'     => (int)$pid,
                'project_name'   => $p->project_name,
                'pi_name'        => $p->pi_name,
                'start_year'     => (int)$p->start_year,
                'funding_type'   => $p->funding_type,
                'funding_source' => $p->funding_source,
                'project_type'   => $p->project_type,
                'theme'          => $p->theme,
                'size'           => $p->size,
                'project_code'   => $p->project_code,
                'budget'         => (float)$p->budget,
                'copi'           => (string)($copi[$pid] ?? '-'),
                'co_research'    => (string)($core[$pid] ?? '-'),
                'fte_sum'        => (float)($fte[$pid]->fte_sum ?? 0),
                'fte_brief'      => (string)($fte[$pid]->fte_txt ?? '-'),
                'outputs_count'  => (int)($out[$pid]->out_cnt ?? 0),
                'outputs_brief'  => (string)($out[$pid]->out_txt ?? '-'),
                'irb_latest'     => isset($irb[$pid]) ? (string)($irb[$pid]->latest_exp ?? '-') : '-',
                'irb_brief'      => isset($irb[$pid]) ? (string)($irb[$pid]->irb_txt ?? '-') : '-',
                'href'           => url('/rm/projects/'.$pid),
            ];
        }
        return $result;
    }

    /** ใช้ start_year เท่านั้น (ไม่มี end_year/finish_year) */
    private function isProjectActiveInYearBE($p, int $yBE): bool {
        $sBE = $this->beIntFromText($p->start_year ?? null);
        return $sBE !== null && $sBE === $yBE;
    }

    /** hybrid pack fallback */
    private function makeSkeletonIfEmpty(array $packs, string $userQuery): array {
        if (!empty($packs)) return $packs;
        return [[
            'skeleton'      => true,
            'notice'        => 'ไม่มีระเบียนที่ตรง แต่สร้างโครงสรุปเพื่อให้วิเคราะห์ต่อได้',
            'hint_from'     => 'fallback',
            'query'         => $userQuery,
            'project_id'    => 0,
            'project_name'  => 'ไม่ระบุ',
            'pi_name'       => 'ไม่ระบุ',
            'start_year'    => 0,
            'funding_type'  => 'ไม่ระบุ',
            'funding_source'=> 'ไม่ระบุ',
            'project_type'  => 'ไม่ระบุ',
            'theme'         => 'ไม่ระบุ',
            'size'          => 'ไม่ระบุ',
            'project_code'  => '-',
            'budget'        => 0,
            'copi'          => '-',
            'co_research'   => '-',
            'fte_sum'       => 0,
            'fte_brief'     => '-',
            'outputs_count' => 0,
            'outputs_brief' => '-',
            'irb_latest'    => '-',
            'irb_brief'     => '-',
            'href'          => '#',
        ]];
    }
    private function packForLLM(array $rows, int $maxItems = 20): string {
        $rows = array_slice($rows, 0, $maxItems);
        foreach ($rows as &$r) {
            foreach (['copi','co_research','fte_brief','outputs_brief','irb_brief'] as $k) {
                if (!isset($r[$k])) continue;
                $r[$k] = Str::limit((string)$r[$k], 900, '…');
            }
            if (isset($r['budget'])) $r['budget_million'] = round(((float)$r['budget'])/1_000_000, 3);
        }
        unset($r);
        return json_encode([
            'generated_at' => now()->toIso8601String(),
            'schema'       => 'sihp/hybrid-pack-v1',
            'items'        => $rows,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function taskOverlapsBEYear($task, int $yearBE): bool {
        $startBE = $this->beIntFromText($task->start_date ?? null);
        $endBE = $this->beIntFromText($task->end_date ?? null);

        if ($startBE !== null && $endBE !== null) {
            return $startBE <= $yearBE && $endBE >= $yearBE;
        }

        if ($startBE !== null) return $startBE === $yearBE;
        if ($endBE !== null) return $endBE === $yearBE;
        return false;
    }

    private function outputMatchesBEYear($output, int $yearBE): bool {
        $yearFromField = $this->beIntFromText($output->year ?? null);
        if ($yearFromField !== null && $yearFromField === $yearBE) return true;

        $deliveredBE = $this->beIntFromText($output->delivered_at ?? null);
        return $deliveredBE !== null && $deliveredBE === $yearBE;
    }

    private function robustBackOfficeTaskCandidates(string $q, ?int $yearBE = null): array {
        if (!Schema::hasTable('back_office_tasks')) return [];

        $tok = $this->tokens($q);
        $likeSets = [];
        foreach ($tok as $t) {
            $like = '%'.str_replace(['%','_'],['\%','\_'],$t).'%';
            $noSp = '%'.str_replace(' ','',$t).'%';
            $likeSets[] = compact('like', 'noSp');
        }

        $candidates = collect();

        $taskIds = DB::table('back_office_tasks')
            ->select('id')
            ->when(!empty($likeSets), function ($qB) use ($likeSets) {
                $cols = ['title', 'description', 'purpose', 'start_date', 'end_date'];
                $qB->where(function ($W) use ($cols, $likeSets) {
                    foreach ($likeSets as $s) {
                        $W->where(function ($OR) use ($cols, $s) {
                            foreach ($cols as $c) {
                                $OR->orWhere($c, 'like', $s['like']);
                                $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?", [$s['noSp']]);
                            }
                        });
                    }
                });
            })
            ->limit(200)
            ->pluck('id');
        $candidates = $candidates->merge($taskIds);

        if (Schema::hasTable('back_office_entries')) {
            $entryTaskIds = DB::table('back_office_entries')
                ->select('task_id as id')
                ->when(!empty($likeSets), function ($qB) use ($likeSets) {
                    $cols = ['person_name', 'category', 'job_detail', 'start_date', 'end_date', 'document_link'];
                    $qB->where(function ($W) use ($cols, $likeSets) {
                        foreach ($likeSets as $s) {
                            $W->where(function ($OR) use ($cols, $s) {
                                foreach ($cols as $c) {
                                    $OR->orWhere($c, 'like', $s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?", [$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)
                ->pluck('id');
            $candidates = $candidates->merge($entryTaskIds);
        }

        if (Schema::hasTable('back_office_task_outputs')) {
            $taskOutputIds = DB::table('back_office_task_outputs')
                ->select('task_id as id')
                ->when(!empty($likeSets), function ($qB) use ($likeSets) {
                    $cols = ['title', 'type', 'project_output', 'year', 'journal', 'description', 'document_link'];
                    $qB->where(function ($W) use ($cols, $likeSets) {
                        foreach ($likeSets as $s) {
                            $W->where(function ($OR) use ($cols, $s) {
                                foreach ($cols as $c) {
                                    $OR->orWhere($c, 'like', $s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?", [$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)
                ->pluck('id');
            $candidates = $candidates->merge($taskOutputIds);
        }

        if (Schema::hasTable('back_office_entry_outputs') && Schema::hasTable('back_office_entries')) {
            $entryOutputIds = DB::table('back_office_entry_outputs as beo')
                ->join('back_office_entries as boe', 'boe.id', '=', 'beo.entry_id')
                ->select('boe.task_id as id')
                ->when(!empty($likeSets), function ($qB) use ($likeSets) {
                    $cols = ['beo.title', 'beo.type', 'beo.project_output', 'beo.year', 'beo.journal', 'beo.description', 'beo.document_link'];
                    $qB->where(function ($W) use ($cols, $likeSets) {
                        foreach ($likeSets as $s) {
                            $W->where(function ($OR) use ($cols, $s) {
                                foreach ($cols as $c) {
                                    $OR->orWhere($c, 'like', $s['like']);
                                    $OR->orWhereRaw("REPLACE($c,' ','') LIKE ?", [$s['noSp']]);
                                }
                            });
                        }
                    });
                })
                ->limit(500)
                ->pluck('id');
            $candidates = $candidates->merge($entryOutputIds);
        }

        $ids = $candidates->filter()->unique()->values();
        if ($yearBE === null || $ids->isEmpty()) {
            return $ids->take(250)->all();
        }

        $rows = $this->hydrateBackOfficeTasks($ids->take(250)->all(), $yearBE);
        return array_values(array_unique(array_map(fn ($row) => $row['task_id'], $rows)));
    }

    private function hydrateBackOfficeTasks(array $taskIds, ?int $yearBE = null): array {
        if (empty($taskIds) || !Schema::hasTable('back_office_tasks')) return [];

        $tasks = BackOfficeTask::query()
            ->with(['entries.outputs', 'outputs', 'creator'])
            ->whereIn('id', $taskIds)
            ->orderByDesc('created_at')
            ->get();

        if ($yearBE !== null) {
            $tasks = $tasks->filter(function ($task) use ($yearBE) {
                if ($this->taskOverlapsBEYear($task, $yearBE)) return true;

                foreach ($task->entries as $entry) {
                    if ($this->taskOverlapsBEYear($entry, $yearBE)) return true;
                    foreach ($entry->outputs as $output) {
                        if ($this->outputMatchesBEYear($output, $yearBE)) return true;
                    }
                }

                foreach ($task->outputs as $output) {
                    if ($this->outputMatchesBEYear($output, $yearBE)) return true;
                }

                return false;
            })->values();
        }

        $rows = [];
        foreach ($tasks as $task) {
            $entries = $task->entries ?? collect();
            $taskOutputs = $task->outputs ?? collect();
            $entryOutputs = $entries->flatMap(fn ($entry) => $entry->outputs ?? collect());
            $allOutputs = $taskOutputs->concat($entryOutputs);
            $leaders = collect($task->leaders ?? [])->filter(fn ($v) => trim((string)$v) !== '')->values();
            $people = $entries->pluck('person_name')->filter(fn ($v) => trim((string)$v) !== '')->unique()->values();
            $categories = $entries
                ->groupBy(fn ($entry) => trim((string)($entry->category ?: 'ไม่ระบุ')))
                ->map->count()
                ->sortDesc();
            $outputTypes = $allOutputs
                ->groupBy(fn ($output) => trim((string)($output->type ?? $output->project_output ?? 'ไม่ระบุ')))
                ->map->count()
                ->sortDesc();

            $rows[] = [
                'task_id' => (int)$task->id,
                'title' => (string)($task->title ?? 'ไม่ระบุ'),
                'start_date' => (string)($task->start_date ?? '-'),
                'end_date' => (string)($task->end_date ?? '-'),
                'description' => (string)($task->description ?? '-'),
                'purpose' => (string)($task->purpose ?? '-'),
                'leaders' => $leaders->isNotEmpty() ? $leaders->implode(', ') : '-',
                'creator' => trim((string)optional($task->creator)->prefix.' '.(string)optional($task->creator)->name) ?: '-',
                'entries_count' => (int)$entries->count(),
                'people_count' => (int)$people->count(),
                'hours_sum' => round((float)$entries->sum('hours'), 2),
                'fte_sum' => round((float)$entries->sum('fte_value'), 6),
                'task_outputs_count' => (int)$taskOutputs->count(),
                'entry_outputs_count' => (int)$entryOutputs->count(),
                'outputs_total' => (int)$allOutputs->count(),
                'categories' => $categories->all(),
                'output_types' => $outputTypes->all(),
                'categories_brief' => $categories->take(5)->map(fn ($count, $name) => "{$name} ({$count})")->implode('; ') ?: '-',
                'people_brief' => $people->take(6)->implode('; ') ?: '-',
                'outputs_brief' => $allOutputs->take(6)->map(function ($output) {
                    $type = trim((string)($output->type ?? $output->project_output ?? 'ไม่ระบุ'));
                    $year = trim((string)($output->year ?? ''));
                    $title = Str::limit((string)($output->title ?? 'ไม่ระบุ'), 80, '…');
                    return ($year !== '' ? "[{$year}] " : '').$type.' - '.$title;
                })->implode(' | ') ?: '-',
                'href' => route('back_office.show', $task->id),
            ];
        }

        return array_values($rows);
    }

    private function packForLLMEnvelope(array $projectRows, array $backOfficeRows, int $maxProjectItems = 20, int $maxTaskItems = 12): string {
        $projectRows = array_slice($projectRows, 0, $maxProjectItems);
        $backOfficeRows = array_slice($backOfficeRows, 0, $maxTaskItems);

        foreach ($projectRows as &$row) {
            foreach (['copi', 'co_research', 'fte_brief', 'outputs_brief', 'irb_brief'] as $key) {
                if (!isset($row[$key])) continue;
                $row[$key] = Str::limit((string)$row[$key], 900, '…');
            }
            if (isset($row['budget'])) $row['budget_million'] = round(((float)$row['budget']) / 1_000_000, 3);
        }
        unset($row);

        foreach ($backOfficeRows as &$row) {
            foreach (['description', 'purpose', 'categories_brief', 'people_brief', 'outputs_brief'] as $key) {
                if (!isset($row[$key])) continue;
                $row[$key] = Str::limit((string)$row[$key], 900, '…');
            }
        }
        unset($row);

        return json_encode([
            'generated_at' => now()->toIso8601String(),
            'schema' => 'sihp/hybrid-pack-v2',
            'projects' => $projectRows,
            'back_office' => $backOfficeRows,
        ], JSON_UNESCAPED_UNICODE);
    }

    /* ==========================
     * Gemini via Gateway (optional)
     * ========================== */
    private function callGemini(string $model, string $prompt): array {
        $endpoint = self::REMOTE_GINI_URL;
        $payloads = [
            ['tag'=>'minimal', 'body'=>['model'=>$model, 'prompt'=>trim($prompt), 'payload'=>'minimal']],
            ['tag'=>'inline',  'body'=>['model'=>$model, 'prompt'=>trim($prompt), 'payload'=>'inline']],
        ];
        foreach ($payloads as $t) {
            try {
                $resp = Http::timeout(25)->withHeaders(['Content-Type'=>'application/json'])->post($endpoint, $t['body']);
                if (!$resp->ok()) return ['ok'=>false,'reason'=>'http_'.$resp->status(),'status'=>$resp->status(),'body'=>$resp->body()];
                $j = $resp->json();
                if (($j['ok'] ?? false) && isset($j['text']))  return ['ok'=>true,'text'=>trim((string)$j['text']) ?: ''];
                if (isset($j['reply']))                         return ['ok'=>true,'text'=>trim((string)$j['reply']) ?: ''];
                if (isset($j['candidates'][0]['content']['parts'])) {
                    $parts = $j['candidates'][0]['content']['parts'];
                    $buf=[]; foreach($parts as $p) if (!empty($p['text'])) $buf[]=$p['text'];
                    return ['ok'=>true,'text'=>trim(implode("\n",$buf)) ?: ''];
                }
                $guess = trim((string)($j['message'] ?? $j['data'] ?? ''));
                if ($guess !== '') return ['ok'=>true,'text'=>$guess];
                return ['ok'=>false,'reason'=>'unknown_response_shape'];
            } catch (\Throwable $e) {
                return ['ok'=>false,'reason'=>'network_exception','error'=>$e->getMessage()];
            }
        }
        return ['ok'=>false,'reason'=>'exhausted'];
    }
    private function askGeminiRobust(string $prompt): array {
        $errs = [];
        foreach ($this->geminiModels as $m) {
            $r = $this->callGemini($m, $prompt);
            if ($r['ok'] ?? false) return $r;
            $errs[] = $m.': '.($r['reason'] ?? 'unknown');
        }
        return ['ok'=>false,'reason'=>implode(' | ',$errs)];
    }

    /* ==========================
     * งบประมาณ: ปีเดียว + 5 ปี
     * ========================== */
    /** สรุปงบปีเดียว + split + Top3/ประเภท (budget เท่านั้น) */
    private function budgetForYearWithTops($projects, int $yBE): array {
        $rows = $projects->filter(fn($p)=> $this->beIntFromText($p->start_year ?? null) === $yBE);

        $sumAll = 0.0;
        $byType = ['ภายใน'=>0.0,'ภายนอก'=>0.0,'ไม่ระบุ'=>0.0];
        $bySource = [];
        $listInternal = [];
        $listExternal = [];

        foreach ($rows as $p) {
            $amt = $this->parseMoney($p->budget ?? 0);
            $sumAll += $amt;

            $t = $this->normFundingType($p->funding_type ?? null);
            if (!isset($byType[$t])) $byType[$t] = 0.0;
            $byType[$t] += $amt;

            $src = trim((string)($p->funding_source ?? 'ไม่ระบุ'));
            $bySource[$src] = ($bySource[$src] ?? 0.0) + $amt;

            $item = [
                'project'=>$p->project_name_th ?? ('#'.$p->id),
                'budget'=>$amt,
                'pi'=>$p->pi_name ?? '',
                'id'=>$p->id,
                'href'=>url('/rm/projects/'.$p->id),
            ];
            if ($t === 'ภายใน')       $listInternal[] = $item;
            elseif ($t === 'ภายนอก') $listExternal[] = $item;
        }

        arsort($bySource);
        usort($listInternal, fn($a,$b)=> $b['budget'] <=> $a['budget']);
        usort($listExternal, fn($a,$b)=> $b['budget'] <=> $a['budget']);

        return [
            'total'        => $sumAll,
            'by_type'      => $byType,
            'by_source'    => $bySource,
            'top_internal' => array_slice($listInternal,0,3),
            'top_external' => array_slice($listExternal,0,3),
        ];
    }

    /** 5 ปีล่าสุด (หรือช่วงที่บอก) → stacked graph + Top3 */
    private function budgetLastNYearsSplitAndTops(int $n = 5, ?int $endBE = null): array {
        $endBE   = $endBE ?? $this->nowBE();
        $startBE = $endBE - ($n - 1);

        $projects = Project::select(['id','project_name_th','pi_name','start_year','budget','funding_type','funding_source'])->get();

        $years    = [];
        $seriesI  = []; // ภายใน
        $seriesE  = []; // ภายนอก
        $seriesU  = []; // ไม่ระบุ
        $accumI   = []; // สำหรับหา top รวม
        $accumE   = [];

        for ($y=$startBE; $y <= $endBE; $y++) {
            $years[] = $y;
            $sumI = $sumE = $sumU = 0.0;

            foreach ($projects as $p) {
                $py = $this->beIntFromText($p->start_year ?? null);
                if ($py !== $y) continue;

                $amt = $this->parseMoney($p->budget ?? 0);
                $type = $this->normFundingType($p->funding_type ?? null);

                if ($type === 'ภายใน') {
                    $sumI += $amt;
                    $accumI[] = ['id'=>$p->id,'name'=>$p->project_name_th ?? ('#'.$p->id),'budget'=>$amt,'pi'=>$p->pi_name ?? '','href'=>url('/rm/projects/'.$p->id)];
                } elseif ($type === 'ภายนอก') {
                    $sumE += $amt;
                    $accumE[] = ['id'=>$p->id,'name'=>$p->project_name_th ?? ('#'.$p->id),'budget'=>$amt,'pi'=>$p->pi_name ?? '','href'=>url('/rm/projects/'.$p->id)];
                } else {
                    $sumU += $amt;
                }
            }

            $seriesI[] = $sumI;
            $seriesE[] = $sumE;
            $seriesU[] = $sumU;
        }

        usort($accumI, fn($a,$b)=> $b['budget'] <=> $a['budget']);
        usort($accumE, fn($a,$b)=> $b['budget'] <=> $a['budget']);

        return [
            'years'         => $years,
            'series_inside' => $seriesI,
            'series_outside'=> $seriesE,
            'series_unknown'=> $seriesU,
            'top3_inside'   => array_slice($accumI,0,3),
            'top3_outside'  => array_slice($accumE,0,3),
        ];
    }

    /* ==========================
     * Mode resolver
     * ========================== */
    private function resolveMode(Request $req, string $prompt): string {
        $pfx = Str::lower(Str::substr(trim($prompt),0,2));
        if ($pfx==='g!') return self::MODE_GEMINI;
        if ($pfx==='h!') return self::MODE_HYBRID;

        $q = Str::upper((string)$req->query('mode',''));
        if (in_array($q,[self::MODE_GEMINI,self::MODE_HYBRID,self::MODE_AUTO],true)) return $q;

        $env = Str::upper((string)env('AGENT_MODE',''));
        if (in_array($env,[self::MODE_GEMINI,self::MODE_HYBRID,self::MODE_AUTO],true)) return $env;

        return $this->defaultMode;
    }

    /* ==========================
     * Main endpoint
     * ========================== */
    public function ask(Request $req)
    {
        try {
            $prompt = trim((string)$req->input('prompt',''));
            if ($prompt==='') {
                return response()->json([
                    'reply' => 'พิมพ์คำถามได้เลย เช่น “งบภายนอก ปี 2566”, “Outputs ปี 2567”, “FTE ปี 2568 เกิน 1”, “IRB ใกล้หมดอายุ 60 วัน”, หรือ “ค้นหา: เบาหวาน”'
                ]);
            }

            $mode = $this->resolveMode($req,$prompt);
            $t    = Str::lower($prompt);

            // year hint
            $yearHintBE = null;
            if (preg_match('/(25\d{2}|20\d{2})/u', $prompt, $m)) {
                $y=(int)$m[1]; $yearHintBE = ($y>2400 ? $y : $y+543);
            } elseif (Str::contains($t,'ปีนี้')) {
                $yearHintBE = $this->nowBE();
            } elseif (Str::contains($t,'ปีก่อน')) {
                $yearHintBE = $this->nowBE()-1;
            }

            /* ===================== DB-FIRST QUICK ANSWERS ===================== */
            // โค้ดส่วนนี้ไม่ได้เปลี่ยนแปลง (Quick Answers)

            /** 0) โปรเจคทั้งหมด/โครงการทั้งหมด */
            if (Str::contains($t,'โปรเจคทั้งหมด') || Str::contains($t,'โครงการทั้งหมด') || $t==='all projects') {
                $rows = Project::select(['id','project_name_th','pi_name','start_year','funding_type','funding_source'])
                    ->orderByDesc('id')->limit(200)->get();

                $list = $rows->map(function($p){
                    return [
                        'id'      => $p->id,
                        'project' => $p->project_name_th ?? ('#'.$p->id),
                        'year_be' => $this->beIntFromText($p->start_year),
                        'pi'      => $p->pi_name ?? '-',
                        'funding' => ($p->funding_type ?? 'ไม่ระบุ').' / '.($p->funding_source ?? 'ไม่ระบุ'),
                        'href'    => url('/rm/projects/'.$p->id),
                    ];
                })->values();

                return response()->json([
                    'reply' => 'รายการโครงการทั้งหมด (ล่าสุด 200 รายการ)',
                    'list'  => $list,
                ]);
            }

            /** 1) งบประมาณรวมต่อปี {Y} */
            if (Str::contains($t,'งบประมาณรวมต่อปี') || Str::startsWith($t,'งบประมาณรวม') || Str::contains($t,'งบรวมต่อปี')) {
                $targetBE = $yearHintBE ?? $this->nowBE();

                $projects = Project::select(['id','project_name_th','pi_name','start_year','budget','funding_type','funding_source'])->get();
                $sum      = $this->budgetForYearWithTops($projects, $targetBE);

                $reply = "งบรวมปี พ.ศ. {$targetBE}: <b>".$this->fmtMil($sum['total'])."</b> ".
                         "(ภายใน ".$this->fmtMil($sum['by_type']['ภายใน']).", ภายนอก ".$this->fmtMil($sum['by_type']['ภายนอก']).", ไม่ระบุ ".$this->fmtMil($sum['by_type']['ไม่ระบุ']).")";

                // ตาราง Top 3
                $topInt = array_map(function($r){
                    return ['project'=>$r['project'],'pi'=>$r['pi'],'budget'=>number_format($r['budget'],2),'href'=>$r['href']];
                }, $sum['top_internal']);
                $topExt = array_map(function($r){
                    return ['project'=>$r['name'] ?? $r['project'],'pi'=>$r['pi'],'budget'=>number_format($r['budget'],2),'href'=>$r['href']];
                }, $sum['top_external']);

                return response()->json([
                    'reply' => $reply,
                    'table' => [
                        'title'   => "Top 3 งบสูงสุด ปี {$targetBE} — แยกประเภททุน",
                        'headers' => ['ประเภท','โครงการ','PI','งบประมาณ(บาท)','ลิงก์'],
                        'fields'  => ['type','project','pi','budget','href'],
                        'rows'    => array_merge(
                            array_map(fn($r)=>['type'=>'ภายใน'] + $r, $topInt),
                            array_map(fn($r)=>['type'=>'ภายนอก'] + $r, $topExt)
                        )
                    ],
                    'chart' => [
                        'type'    => 'doughnut',
                        'labels'  => ['ภายใน','ภายนอก','ไม่ระบุ'],
                        'datasets'=> [[ 'label'=>'ล้านบาท', 'data'=>[
                            round($sum['by_type']['ภายใน']/1_000_000,2),
                            round($sum['by_type']['ภายนอก']/1_000_000,2),
                            round($sum['by_type']['ไม่ระบุ']/1_000_000,2),
                        ]]],
                        'options' => ['plugins'=>['legend'=>['position'=>'bottom']]],
                    ]
                ]);
            }

            /** 2) งบประมาณ 5 ปี */
            if (Str::contains($t,'งบประมาณ 5 ปี') || Str::contains($t,'งบ 5 ปี') || Str::contains($t,'ห้าปี')) {
                $endBE = $yearHintBE ?? $this->nowBE();
                $pack  = $this->budgetLastNYearsSplitAndTops(5, $endBE);

                $reply = "งบประมาณย้อนหลัง 5 ปี (พ.ศ. ".min($pack['years'])."–".max($pack['years']).") — แยกภายใน/ภายนอก/ไม่ระบุ";

                // Top รวมทั้งช่วง
                $topI = array_map(fn($r)=>['type'=>'ภายใน','project'=>$r['name'],'pi'=>$r['pi'],'budget'=>number_format($r['budget'],2),'href'=>$r['href']], $pack['top3_inside']);
                $topE = array_map(fn($r)=>['type'=>'ภายนอก','project'=>$r['name'],'pi'=>$r['pi'],'budget'=>number_format($r['budget'],2),'href'=>$r['href']], $pack['top3_outside']);

                return response()->json([
                    'reply' => $reply,
                    'table' => [
                        'title'   => "Top 3 งบรวมสูงสุด (รวม 5 ปี) — แยกประเภททุน",
                        'headers' => ['ประเภท','โครงการ','PI','งบประมาณ(บาท)','ลิงก์'],
                        'fields'  => ['type','project','pi','budget','href'],
                        'rows'    => array_merge($topI,$topE),
                    ],
                    'chart' => [
                        'type'    => 'bar',
                        'labels'  => array_map(fn($y)=>(string)$y,$pack['years']),
                        'datasets'=> [
                            ['label'=>'ภายใน (ล้านบาท)','data'=>array_map(fn($v)=>round($v/1_000_000,2), $pack['series_inside'])],
                            ['label'=>'ภายนอก (ล้านบาท)','data'=>array_map(fn($v)=>round($v/1_000_000,2), $pack['series_outside'])],
                            ['label'=>'ไม่ระบุ (ล้านบาท)','data'=>array_map(fn($v)=>round($v/1_000_000,2), $pack['series_unknown'])],
                        ],
                        'options' => [
                            'responsive'=>true,
                            'plugins'=>[
                                'legend'=>['position'=>'bottom'],
                            ],
                            'scales'=>[
                                'y'=>['beginAtZero'=>true],
                            ],
                        ],
                    ]
                ]);
            }

            /** 3) FTE ปี X (รองรับ "เกิน X") */
            if (Str::contains($t,'fte') || Str::contains($t,'เอฟทีอี')) {
                // หา threshold จากคำว่า "เกิน x"
                $threshold = null;
                if (preg_match('/เกิน\s*([0-9]+(?:\.[0-9]+)?)/u', $t, $m)) {
                    $threshold = (float)$m[1];
                }
                $targetBE = $yearHintBE ?? $this->nowBE();
                $targetCE = $targetBE - 543;

                $rows = FteRole::query()
                    ->selectRaw('name, SUM(COALESCE(fte_value,0)) AS total_fte')
                    ->where(function($w) use($targetCE){ $this->scopeFTEActiveInCEYear($w,$targetCE); })
                    ->groupBy('name')->get();

                // กรองเฉพาะที่เกิน threshold ถ้ามี
                if ($threshold !== null) {
                    $rows = $rows->filter(fn($r)=>(float)$r->total_fte > $threshold)->values();
                }

                $rows = $rows->sortByDesc('total_fte')->values();

                return response()->json([
                    'reply' => 'FTE ต่อบุคคล ปี พ.ศ. '.$targetBE.($threshold!==null? " (แสดงเฉพาะ > {$threshold})":''),
                    'table' => [
                        'title'=>"FTE ต่อคน ปี {$targetBE}",
                        'headers'=>['ชื่อ','FTE'],
                        'fields' =>['name','total_fte'],
                        'rows'   =>$rows,
                    ],
                    'chart' => [
                        'type'=>'bar',
                        'labels'=>$rows->pluck('name'),
                        'datasets'=>[[ 'label'=>'FTE','data'=>$rows->pluck('total_fte'),'borderWidth'=>1 ]],
                        'options'=>['plugins'=>['legend'=>['display'=>false]]],
                    ],
                ]);
            }

            /** 3.1) สถานะโครงการในปี {Y} — ใช้ start_year เท่านั้น */
            if (Str::contains($t,'สถานะโครงการ') || Str::contains($t,'project status') || Str::contains($t,'status โครงการ')) {
                $targetBE = $yearHintBE ?? $this->nowBE();
                $projects = Project::select(['id','project_name_th','pi_name','execution_status','start_year'])->get();

                // Active = เริ่มในปีนั้น (เพราะไม่มีปีสิ้นสุดให้เช็คช่วง)
                $active  = $projects->filter(fn($p)=> $this->isProjectActiveInYearBE($p, $targetBE));

                $grouped = $active->groupBy(function($p){
                    $s = trim((string)($p->execution_status ?? 'ไม่ระบุ'));
                    return $s === '' ? 'ไม่ระระบุ' : $s;
                })->map->count();

                $rows = $grouped->map(function($cnt,$k){ return ['status'=>$k, 'count'=>$cnt]; })->values();

                return response()->json([
                    'reply' => "สถานะโครงการในปี {$targetBE}",
                    'table' => [
                        'title'   => "สถานะโครงการในปี {$targetBE}",
                        'headers' => ['สถานะ','จำนวน'],
                        'fields'  => ['status','count'],
                        'rows'    => $rows,
                    ],
                    'chart' => [
                        'type'=>'bar',
                        'labels'=>$rows->pluck('status'),
                        'datasets'=>[[ 'label'=>'จำนวนโครงการ','data'=>$rows->pluck('count') ]],
                        'options'=>['plugins'=>['legend'=>['display'=>false]]],
                    ],
                ]);
            }

            /** 3.2) Back Office / งานนอกโครงการ */
            if (Str::contains($t, 'back office') || Str::contains($t, 'งานนอกโครงการ')) {
                if (!Schema::hasTable('back_office_tasks')) {
                    return response()->json(['reply' => 'ยังไม่มีข้อมูล Back Office ในระบบ']);
                }

                $taskIds = $this->robustBackOfficeTaskCandidates($prompt, $yearHintBE);
                if (empty($taskIds)) {
                    $taskIds = BackOfficeTask::query()
                        ->orderByDesc('created_at')
                        ->limit(250)
                        ->pluck('id')
                        ->all();
                }

                $taskRows = $this->hydrateBackOfficeTasks($taskIds, $yearHintBE);
                if (empty($taskRows)) {
                    return response()->json([
                        'reply' => $yearHintBE
                            ? "ไม่พบข้อมูล Back Office ที่ตรงกับปี {$yearHintBE}"
                            : 'ไม่พบข้อมูล Back Office ที่ตรงกับคำค้น',
                    ]);
                }

                $totalTasks = count($taskRows);
                $totalEntries = array_sum(array_map(fn ($row) => (int)($row['entries_count'] ?? 0), $taskRows));
                $totalHours = array_sum(array_map(fn ($row) => (float)($row['hours_sum'] ?? 0), $taskRows));
                $totalFte = array_sum(array_map(fn ($row) => (float)($row['fte_sum'] ?? 0), $taskRows));
                $totalOutputs = array_sum(array_map(fn ($row) => (int)($row['outputs_total'] ?? 0), $taskRows));

                $categoryMap = [];
                $outputTypeMap = [];
                foreach ($taskRows as $row) {
                    foreach (($row['categories'] ?? []) as $name => $count) {
                        $label = trim((string)$name) ?: 'ไม่ระบุ';
                        $categoryMap[$label] = ($categoryMap[$label] ?? 0) + (int)$count;
                    }
                    foreach (($row['output_types'] ?? []) as $name => $count) {
                        $label = trim((string)$name) ?: 'ไม่ระบุ';
                        $outputTypeMap[$label] = ($outputTypeMap[$label] ?? 0) + (int)$count;
                    }
                }
                arsort($categoryMap);
                arsort($outputTypeMap);

                $periodLabel = $yearHintBE ? "ปี {$yearHintBE}" : 'ทั้งหมดในระบบ';
                $reply = "Back Office {$periodLabel}: {$totalTasks} งาน, {$totalEntries} รายการงาน, "
                    .number_format($totalHours, 2)." ชั่วโมง, FTE รวม ".number_format($totalFte, 4)
                    .", outputs รวม {$totalOutputs} รายการ";

                $response = [
                    'reply' => $reply,
                    'table' => [
                        'title' => "งาน Back Office {$periodLabel} (เรียงตามชั่วโมงสูงสุด)",
                        'headers' => ['งาน', 'ผู้รับผิดชอบหลัก', 'รายการงาน', 'ชั่วโมง', 'FTE', 'Outputs', 'ลิงก์'],
                        'fields' => ['task', 'leaders', 'entries', 'hours', 'fte', 'outputs', 'href'],
                        'rows' => collect($taskRows)
                            ->sortByDesc('hours_sum')
                            ->take(10)
                            ->map(fn ($row) => [
                                'task' => $row['title'],
                                'leaders' => $row['leaders'],
                                'entries' => $row['entries_count'],
                                'hours' => number_format((float)$row['hours_sum'], 2),
                                'fte' => number_format((float)$row['fte_sum'], 4),
                                'outputs' => $row['outputs_total'],
                                'href' => $row['href'],
                            ])->values(),
                    ],
                ];

                if (!empty($categoryMap)) {
                    $categoryPairs = collect($categoryMap)->take(8);
                    $response['chart'] = [
                        'type' => 'bar',
                        'labels' => $categoryPairs->keys()->values(),
                        'datasets' => [[
                            'label' => 'จำนวนรายการงาน',
                            'data' => $categoryPairs->values()->values(),
                            'backgroundColor' => 'rgba(14, 165, 233, 0.75)',
                            'borderColor' => '#0284c7',
                            'borderWidth' => 1,
                        ]],
                        'options' => [
                            'plugins' => ['legend' => ['display' => false]],
                            'scales' => ['y' => ['beginAtZero' => true]],
                        ],
                    ];
                }

                if (!empty($outputTypeMap)) {
                    $outputPairs = collect($outputTypeMap)->take(6);
                    $response['chart2'] = [
                        'type' => 'doughnut',
                        'labels' => $outputPairs->keys()->values(),
                        'datasets' => [[
                            'label' => 'จำนวน outputs',
                            'data' => $outputPairs->values()->values(),
                        ]],
                        'options' => [
                            'plugins' => ['legend' => ['position' => 'bottom']],
                        ],
                    ];
                }

                return response()->json($response);
            }

            /** 3.3) Outputs ปี {Y} แยกประเภท */
            if (Str::contains($t,'outputs') || Str::contains($t,'ผลงาน') || Str::contains($t,'ตีพิมพ์')) {
                if (!Schema::hasTable('outputs')) {
                    return response()->json(['reply'=>'ยังไม่มีตาราง Outputs ในระบบ']);
                }
                $targetBE = $yearHintBE ?? $this->nowBE();

                // ดึงข้อมูล Outputs ทั้งหมดเพื่อนำมาแปลงปีและกรองใน PHP (เนื่องจากปีเป็น VARCHAR และมีการแปลง พ.ศ.)
                $outs = DB::table('outputs')->select(['id','type','year'])->get(); 

                // แปลงปี → BE และกรองใน PHP
                $rows = $outs->map(function($o) use ($targetBE){
                    $yBE = $this->beIntFromText($o->year ?? null); 
                    
                    // กรองเฉพาะที่ตรงกับปีที่ต้องการ
                    if ($yBE === $targetBE) {
                        return ['type'=>$o->type ?? 'ไม่ระบุ', 'y'=>$yBE];
                    }
                    return null;
                })->filter(); 

                $byType = $rows->groupBy('type')->map->count();
                
                if ($byType->isEmpty()) {
                    return response()->json(['reply'=>"ไม่พบ Outputs ปี {$targetBE}"]);
                }

                $tableRows = $byType->map(fn($c,$k)=> ['type'=>$k,'count'=>$c])->values();

                return response()->json([
                    'reply' => "จำนวน Outputs ปี {$targetBE} แยกประเภท",
                    'table' => [
                        'title'   => "Outputs ปี {$targetBE} แยกตามประเภท",
                        'headers' => ['ประเภท','จำนวน'],
                        'fields'  => ['type','count'],
                        'rows'    => $tableRows,
                    ],
                    'chart' => [
                        'type'=>'bar',
                        'labels'=>$tableRows->pluck('type'),
                        'datasets'=>[[ 'label'=>'จำนวน','data'=>$tableRows->pluck('count') ]],
                        'options'=>['plugins'=>['legend'=>['display'=>false]]],
                    ],
                ]);
            }

            /** 3.4) แหล่งทุนที่เด่นที่สุด ปี {Y} */
            if (Str::contains($t,'แหล่งทุนที่เด่น') || Str::contains($t,'top แหล่งทุน') || (Str::contains($t,'แหล่งทุน') && Str::contains($t,'ปี'))) {
                $targetBE = $yearHintBE ?? $this->nowBE();
                $projects = Project::select(['id','project_name_th','pi_name','start_year','budget','funding_source'])->get();

                // ใช้ฟังก์ชันงบปีเดียวแล้วรวบ by_source
                $sum   = $this->budgetForYearWithTops($projects, $targetBE);
                $pairs = collect($sum['by_source'])->map(fn($amt,$src)=> ['source'=>$src,'budget'=>$amt])
                            ->sortByDesc('budget')->values()->take(10);

                $reply = "แหล่งทุนที่เด่นปี {$targetBE} (ตามวงเงินรวม)";
                return response()->json([
                    'reply' => $reply,
                    'table' => [
                        'title'   => "Top แหล่งทุน ปี {$targetBE}",
                        'headers' => ['แหล่งทุน','งบประมาณ(บาท)'],
                        'fields'  => ['source','budget'],
                        'rows'    => $pairs->map(fn($r)=> ['source'=>$r['source'],'budget'=>number_format($r['budget'],2)]),
                    ],
                    'chart' => [
                        'type'=>'doughnut',
                        'labels'=>$pairs->pluck('source'),
                        'datasets'=>[[ 'label'=>'ล้านบาท','data'=>$pairs->pluck('budget')->map(fn($v)=>round($v/1_000_000,2)) ]],
                        'options'=>['plugins'=>['legend'=>['position'=>'bottom']]],
                    ],
                ]);
            }

            /** 4) IRB ใกล้หมดอายุ */
            if (Str::contains($t,'irb') && (Str::contains($t,'ใกล้') || Str::contains($t,'หมดอายุ'))) {
                $days = 90;
                if (preg_match('/(\d+)\s*วัน/u', $t, $m)) $days = max(1, (int)$m[1]);

                if (!Schema::hasTable('irbs')) {
                    return response()->json(['reply'=>'❌ ไม่พบตาราง IRB ในระบบ (irbs)']);
                }

                $today = date('Y-m-d');

                $irb = DB::table('irbs')
                    ->join('projects','projects.id','=','irbs.project_id')
                    ->select([
                        'projects.id as project_id',
                        'projects.project_name_th as project',
                        'projects.pi_name',
                        DB::raw("irbs.code AS irb_code"),
                        'irbs.exp_date'
                    ])
                    ->whereNotNull('irbs.exp_date')
                    // ใช้ STR_TO_DATE เพื่อให้การเปรียบเทียบวันที่ถูกต้อง
                    ->whereRaw("STR_TO_DATE(irbs.exp_date, '%d-%m-%Y') BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)", [$days])
                    ->orderByRaw("STR_TO_DATE(irbs.exp_date, '%d-%m-%Y')")
                    ->get();

                $rows = $irb->map(function($r){
                    return [
                        'project'  => $r->project ?? ('#'.$r->project_code),
                        'pi'       => $r->pi_name ?? '-',
                        'irb_code' => $r->irb_code ?? '-',
                        'exp_date' => $r->exp_date ?? '-',
                        'href'     => url('/rm/projects/'.$r->project_id),
                    ];
                });

                return response()->json([
                    'reply' => "IRB ที่จะหมดอายุภายใน {$days} วัน (รวม ".count($rows)." รายการ)",
                    'table' => [
                        'title'=>"IRB ใกล้หมดอายุ (ภายใน {$days} วัน)",
                        'headers'=>['โครงการ','PI','IRB Code','วันหมดอายุ','ลิงก์'],
                        'fields' =>['project','pi','irb_code','exp_date','href'],
                        'rows'   =>$rows,
                    ],
                ]);
            }

            /** 5) ค้นหาโปรเจกต์จากคำ (ชื่อใกล้เคียง) — เฉพาะเมื่อพิมพ์ "ค้นหา:" หรือ "หาโครงการ/หาโปรเจค" */
            if (Str::startsWith($t,'ค้นหา:') || Str::startsWith($t,'ค้นหา :') || Str::contains($t,'หาโปรเจค') || Str::contains($t,'หาโครงการ')) {
                $query = trim(Str::of($prompt)->replaceFirst('ค้นหา:','')->replaceFirst('ค้นหา :',''));
                $pids  = $this->robustProjectCandidates($query, $yearHintBE);
                $rows  = $this->hydrateProjectsWithJoins($pids);

                // คืนลิสต์สั้นๆ เพื่อให้ผู้ใช้ “คลิกเลือก”
                $list = collect($rows)->map(function($r){
                    return [
                        'id'          => $r['project_code'],
                        'project'     => $r['project_name'],
                        'year_be'     => $r['start_year'],
                        'pi'          => $r['pi_name'],
                        'funding'     => $r['funding_type'].' / '.$r['funding_source'],
                        'href'        => $r['href'],
                    ];
                })->sortByDesc('year_be')->values();

                return response()->json([
                    'reply' => 'ผลการค้นหา (คลิกเพื่อดูรายละเอียด)',
                    'list'  => $list,
                ]);
            }

            /* ===== Hybrid Pack (LLM) — เผื่อคำถามอื่น ๆ ===== */
            $pids = $this->robustProjectCandidates($prompt, $yearHintBE);
            $taskIds = $this->robustBackOfficeTaskCandidates($prompt, $yearHintBE);

            // FIX 4: หากการค้นหาแบบ Fuzzy Search (Robust Search) ล้มเหลว 
            // แต่มีการระบุปีในคำถาม ให้ดึง Project ID ทั้งหมดในปีนั้นแทน
            if (empty($pids) && $yearHintBE !== null) {
                // ดึง Project IDs ทั้งหมดที่ตรงกับปีที่ระบุโดยตรง
                $pids = Project::where('start_year', (string)$yearHintBE)->pluck('id')->all();
            }

            if (empty($taskIds) && $yearHintBE !== null && Schema::hasTable('back_office_tasks')) {
                $taskIds = BackOfficeTask::query()
                    ->orderByDesc('created_at')
                    ->limit(250)
                    ->pluck('id')
                    ->all();
            }

            $backOfficeRows = $this->hydrateBackOfficeTasks($taskIds, $yearHintBE);

            // ************************************************
            // ✅ FIX 5: CHECK FOR GENERAL KNOWLEDGE QUESTION
            // ************************************************
            $hasRelevantPIDs = !empty($pids);
            $hasRelevantBackOffice = !empty($backOfficeRows);
            
            // ถ้าไม่พบโครงการที่เกี่ยวข้อง (และไม่มีปีระบุที่ใช้เป็นตัวกรองได้) 
            // ให้ถือว่าเป็นคำถามความรู้ทั่วไป และข้ามการส่ง Data Pack ไป
            if (!$hasRelevantPIDs && !$hasRelevantBackOffice && $yearHintBE === null) {
                // Treat as General Knowledge Question
                $generalPrompt = "คุณคือผู้ช่วย AI ที่มีความรู้กว้างขวาง โปรดตอบคำถามนี้ตามหลักการและความรู้ทั่วไปที่ทันสมัย ตอบเป็นภาษาไทย กระชับ 8-12 บรรทัด คำถาม: {$prompt}";
                $g = $this->askGeminiRobust($generalPrompt);
                
                if ($g['ok']) {
                    return response()->json(['reply' => $g['text'], 'mode_hint' => 'GENERAL_AI']);
                }
                // Fallback for General AI failure
                return response()->json([
                    'reply' => 'ขออภัย ระบบตอบคำถามทั่วไปไม่พร้อมใช้งานในขณะนี้: '.$g['reason'],
                ], 503);
            }
            // ************************************************
            // สิ้นสุดส่วน FIX 5: ถ้าถึงจุดนี้ แสดงว่าต้องใช้ข้อมูลในการวิเคราะห์ต่อ

            $rows = $this->hydrateProjectsWithJoins($pids);
            if (empty($rows) && empty($backOfficeRows)) {
                $rows = $this->makeSkeletonIfEmpty($rows, $prompt);
            }
            $pack = $this->packForLLMEnvelope($rows, $backOfficeRows, 24, 16);

            if ($mode === self::MODE_GEMINI) {
                $g = $this->askGeminiRobust("คุณเป็นผู้ช่วยวิเคราะห์ฐานข้อมูลวิจัยและงาน Back Office ตอบไทย ไม่เกิน 12 บรรทัด\n\nคำถาม: {$prompt}\n\nข้อมูล: {$pack}");
                if ($g['ok']) return response()->json(['reply'=>$g['text'],'data_pack'=>json_decode($pack,true)]);
                return response()->json(['reply'=>"[GEMINI] ไม่พร้อมใช้งาน: ".$g['reason'],'data_pack'=>json_decode($pack,true)], 503);
            }

            // AUTO/HYBRID → ลอง LLM สรุปสั้นๆ แต่มี data_pack ให้ front ใช้
            $g = $this->askGeminiRobust("คุณเป็นผู้ช่วยวิเคราะห์ฐานข้อมูลวิจัยและงาน Back Office ตอบไทย กระชับ 8–12 บรรทัด\n\nคำถาม: {$prompt}\n\nข้อมูล: {$pack}\n\nสรุปโดยยึดข้อมูลจริง ห้ามแต่งตัวเลข");
            $fallbackLines = collect();
            if (!empty($rows)) {
                $fallbackLines = $fallbackLines->merge(collect($rows)->take(4)->map(function ($r) {
                    return "• โครงการ: {$r['project_name']} / PI: {$r['pi_name']} / ปี: {$r['start_year']} / งบ(ลบ.): ".number_format(($r['budget'] ?? 0) / 1_000_000, 2);
                }));
            }
            if (!empty($backOfficeRows)) {
                $fallbackLines = $fallbackLines->merge(collect($backOfficeRows)->take(4)->map(function ($r) {
                    return "• Back Office: {$r['title']} / ชั่วโมง: ".number_format((float)($r['hours_sum'] ?? 0), 2)." / FTE: ".number_format((float)($r['fte_sum'] ?? 0), 4)." / Outputs: ".(int)($r['outputs_total'] ?? 0);
                }));
            }
            $reply = $g['ok'] ? $g['text'] : $fallbackLines->implode("\n");

            return response()->json([
                'reply'     => $reply,
                'data_pack' => json_decode($pack, true),
            ]);

        } catch (\Throwable $e) {
            \Log::error('[Agent.ask] '.$e->getMessage());
            return response()->json([
                'reply' => '❌ เกิดข้อผิดพลาดในฝั่งเซิร์ฟเวอร์: '.$e->getMessage(),
            ], 500);
        }
    }
}
