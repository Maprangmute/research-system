<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

// Eloquent models
use App\Models\Project;
use App\Models\FteRole;
use App\Models\Output;
use App\Models\BackOfficeEntry;
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
    private function normalizeThaiDigits(?string $value): string {
        return preg_replace_callback('/[๐-๙]/u', static fn ($match) => (string) mb_strpos('๐๑๒๓๔๕๖๗๘๙', $match[0]), (string) $value) ?: '';
    }
    private function formatDateBE(Carbon $date): string {
        return sprintf('%02d-%02d-%04d', (int) $date->day, (int) $date->month, (int) $date->year + 543);
    }
    private function parseFlexibleDate(?string $value): ?Carbon {
        $raw = trim($this->normalizeThaiDigits($value));
        if ($raw === '') return null;

        $raw = preg_replace('/\s+/u', ' ', $raw) ?: $raw;

        if (preg_match('/^(\d{1,2})([-\/])(\d{1,2})\2(\d{4})$/u', $raw, $m)) {
            $day = (int) $m[1];
            $sep = $m[2];
            $month = (int) $m[3];
            $year = (int) $m[4];
            if ($year > 2400) $year -= 543;
            $raw = sprintf('%02d%s%02d%s%04d', $day, $sep, $month, $sep, $year);
        } elseif (preg_match('/^(\d{4})([-\/])(\d{1,2})\2(\d{1,2})$/u', $raw, $m)) {
            $year = (int) $m[1];
            $sep = $m[2];
            $month = (int) $m[3];
            $day = (int) $m[4];
            if ($year > 2400) $year -= 543;
            $raw = sprintf('%04d%s%02d%s%02d', $year, $sep, $month, $sep, $day);
        }

        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d', 'j-n-Y', 'j/n/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->startOfDay();
            } catch (\Throwable $e) {
            }
        }

        if (preg_match('/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/u', $raw, $m)) {
            $day = (int) $m[1];
            $monthRaw = trim((string) $m[2]);
            $year = (int) $m[3];
            $monthMap = [
                'มกราคม' => 1, 'ม.ค.' => 1, 'ม.ค' => 1,
                'กุมภาพันธ์' => 2, 'ก.พ.' => 2, 'ก.พ' => 2,
                'มีนาคม' => 3, 'มี.ค.' => 3, 'มี.ค' => 3,
                'เมษายน' => 4, 'เม.ย.' => 4, 'เม.ย' => 4,
                'พฤษภาคม' => 5, 'พ.ค.' => 5, 'พ.ค' => 5,
                'มิถุนายน' => 6, 'มิ.ย.' => 6, 'มิ.ย' => 6,
                'กรกฎาคม' => 7, 'ก.ค.' => 7, 'ก.ค' => 7,
                'สิงหาคม' => 8, 'ส.ค.' => 8, 'ส.ค' => 8,
                'กันยายน' => 9, 'ก.ย.' => 9, 'ก.ย' => 9,
                'ตุลาคม' => 10, 'ต.ค.' => 10, 'ต.ค' => 10,
                'พฤศจิกายน' => 11, 'พ.ย.' => 11, 'พ.ย' => 11,
                'ธันวาคม' => 12, 'ธ.ค.' => 12, 'ธ.ค' => 12,
            ];

            if (isset($monthMap[$monthRaw])) {
                if ($year > 2400) $year -= 543;
                try {
                    return Carbon::create($year, $monthMap[$monthRaw], $day, 0, 0, 0)->startOfDay();
                } catch (\Throwable $e) {
                }
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
    private function extractPromptDateRange(string $prompt): array {
        $normalized = trim($this->normalizeThaiDigits($prompt));
        $datePattern = '(?:\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}|\d{4}[\/-]\d{1,2}[\/-]\d{1,2}|\d{1,2}\s+[ก-๙A-Za-z\.]+\s+\d{4})';

        $candidates = [];
        foreach ([
            '/ตั้งแต่\s*('.$datePattern.')\s*(?:ถึง|จนถึง|to|-|–)\s*('.$datePattern.')/ui',
            '/('.$datePattern.')\s*(?:ถึง|จนถึง|to|-|–)\s*('.$datePattern.')/ui',
        ] as $pattern) {
            if (preg_match($pattern, $normalized, $m)) {
                $candidates = [$m[1] ?? null, $m[2] ?? null];
                break;
            }
        }

        if (count(array_filter($candidates)) < 2) {
            preg_match_all('/'.$datePattern.'/u', $normalized, $matches);
            $candidates = array_slice($matches[0] ?? [], 0, 2);
        }

        $from = isset($candidates[0]) ? $this->parseFlexibleDate($candidates[0]) : null;
        $to = isset($candidates[1]) ? $this->parseFlexibleDate($candidates[1]) : null;
        if (!$from || !$to) {
            return ['from' => null, 'to' => null, 'label' => null];
        }
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [
            'from' => $from->copy()->startOfDay(),
            'to' => $to->copy()->endOfDay(),
            'label' => 'ช่วงวันที่ '.$this->formatDateBE($from).' ถึง '.$this->formatDateBE($to),
        ];
    }
    private function resolvePromptWindow(?int $yearBE, array $dateRange = []): array {
        $from = $dateRange['from'] ?? null;
        $to = $dateRange['to'] ?? null;
        if ($from instanceof Carbon && $to instanceof Carbon) {
            return [
                'mode' => 'custom',
                'from' => $from,
                'to' => $to,
                'label' => $dateRange['label'] ?? ('ช่วงวันที่ '.$this->formatDateBE($from).' ถึง '.$this->formatDateBE($to)),
                'query' => [
                    'mode' => 'custom',
                    'start' => $from->format('d-m-Y'),
                    'end' => $to->format('d-m-Y'),
                ],
            ];
        }

        if ($yearBE !== null) {
            $yearCE = $yearBE > 2400 ? $yearBE - 543 : $yearBE;
            $from = Carbon::create($yearCE, 1, 1, 0, 0, 0)->startOfDay();
            $to = Carbon::create($yearCE, 12, 31, 0, 0, 0)->endOfDay();

            return [
                'mode' => 'year',
                'from' => $from,
                'to' => $to,
                'label' => "ปี {$yearBE}",
                'query' => [
                    'mode' => 'year',
                    'year' => $yearBE,
                ],
            ];
        }

        return [
            'mode' => 'all',
            'from' => null,
            'to' => null,
            'label' => 'ทั้งหมด',
            'query' => [
                'mode' => 'all',
            ],
        ];
    }
    private function parseContributionBounds(?string $startRaw, ?string $endRaw): ?array {
        $start = $this->parseFlexibleDate($startRaw);
        $end = $this->parseFlexibleDate($endRaw);
        if (!$start || !$end) return null;
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }
        return ['start' => $start->copy()->startOfDay(), 'end' => $end->copy()->endOfDay()];
    }
    private function monthLabelBE(string $monthKey): string {
        try {
            $month = Carbon::createFromFormat('Y-m', $monthKey);
            return $month->format('m').'/'.($month->year + 543);
        } catch (\Throwable $e) {
            return $monthKey;
        }
    }
    private function buildDownloadLinkItem(string $title, string $href, string $meta = ''): array {
        return [
            'title' => $title,
            'funding' => $meta,
            'href' => $href,
        ];
    }
    private function buildDownloadActionResponse(string $reply, string $title, string $href, string $meta = ''): array {
        return [
            'reply' => $reply,
            'list' => [
                $this->buildDownloadLinkItem($title, $href, $meta),
            ],
        ];
    }
    private function buildProjectPdfListResponse(string $reply, array $rows): array {
        $list = collect($rows)->map(function ($row) {
            return [
                'title' => $row['project_name'],
                'year_be' => $row['start_year'] ?? null,
                'pi' => $row['pi_name'] ?? '-',
                'funding' => 'PDF โครงการ',
                'href' => route('projects.pdf', $row['project_id']),
            ];
        })->values()->all();

        return [
            'reply' => $reply,
            'list' => $list,
        ];
    }
    private function resolvePersonDisplayName(string $q): string {
        $best = $this->approximatePersonCandidates($q, 1, 0.18);
        if (!empty($best)) {
            return trim((string)($best[0]['label'] ?? $best[0]['text'] ?? ''));
        }

        return trim((string)$this->personQueryLabel($q));
    }
    private function buildPersonFteWindowResponse(string $personQuery, ?int $yearBE = null, array $dateRange = [], bool $includePdfLink = false): ?array {
        $window = $this->resolvePromptWindow($yearBE, $dateRange);
        $candidateNames = collect([
            $this->resolvePersonDisplayName($personQuery),
            $this->personQueryLabel($personQuery),
            trim((string)$personQuery),
        ])->filter()->unique()->values();

        $personName = trim((string)($candidateNames->first() ?? ''));
        $fteRows = collect();
        $backOfficeRows = collect();

        foreach ($candidateNames as $candidateName) {
            $personName = trim((string)$candidateName);
            $fteRows = FteRole::with('project:id,project_name_th,project_code,pi_name')
                ->where('name', $personName)
                ->get();
            $backOfficeRows = Schema::hasTable('back_office_entries')
                ? BackOfficeEntry::with('task:id,title,start_date,end_date')
                    ->where('person_name', $personName)
                    ->get()
                : collect();

            if ($fteRows->isNotEmpty() || $backOfficeRows->isNotEmpty()) {
                break;
            }
        }

        if ($fteRows->isEmpty() && $backOfficeRows->isEmpty()) {
            return null;
        }

        $itemBuckets = [];
        $monthlyBuckets = [];
        $filterFrom = $window['from'] ?? null;
        $filterTo = $window['to'] ?? null;

        $pushContribution = function (
            string $bucketKey,
            string $type,
            string $title,
            ?string $pi,
            ?string $href,
            Carbon $entryStart,
            Carbon $entryEnd,
            float $fteValue,
            float $hoursValue
        ) use (&$itemBuckets, &$monthlyBuckets, $filterFrom, $filterTo) {
            $effectiveStart = $entryStart->copy();
            $effectiveEnd = $entryEnd->copy();

            if ($filterFrom instanceof Carbon && $filterTo instanceof Carbon) {
                if ($entryEnd < $filterFrom || $entryStart > $filterTo) return;
                if ($effectiveStart < $filterFrom) $effectiveStart = $filterFrom->copy();
                if ($effectiveEnd > $filterTo) $effectiveEnd = $filterTo->copy();
            }

            if ($effectiveStart->gt($effectiveEnd)) return;

            $totalDays = $entryStart->diffInDays($entryEnd) + 1;
            if ($totalDays <= 0) return;

            $effectiveDays = $effectiveStart->diffInDays($effectiveEnd) + 1;
            $fteTotal = $fteValue > 0 ? ($fteValue / $totalDays) * $effectiveDays : 0.0;
            $hoursTotal = $hoursValue > 0 ? ($hoursValue / $totalDays) * $effectiveDays : 0.0;

            if (!isset($itemBuckets[$bucketKey])) {
                $itemBuckets[$bucketKey] = [
                    'type' => $type,
                    'title' => $title,
                    'pi' => $pi ?: '-',
                    'href' => $href ?: '#',
                    'fte_total' => 0.0,
                    'hours_total' => 0.0,
                    'start' => $effectiveStart->copy(),
                    'end' => $effectiveEnd->copy(),
                ];
            }

            $itemBuckets[$bucketKey]['fte_total'] += $fteTotal;
            $itemBuckets[$bucketKey]['hours_total'] += $hoursTotal;
            if ($effectiveStart < $itemBuckets[$bucketKey]['start']) {
                $itemBuckets[$bucketKey]['start'] = $effectiveStart->copy();
            }
            if ($effectiveEnd > $itemBuckets[$bucketKey]['end']) {
                $itemBuckets[$bucketKey]['end'] = $effectiveEnd->copy();
            }

            $ftePerDay = $fteTotal > 0 ? ($fteTotal / $effectiveDays) : 0.0;
            $hoursPerDay = $hoursTotal > 0 ? ($hoursTotal / $effectiveDays) : 0.0;

            $cursor = $effectiveStart->copy()->startOfMonth();
            while ($cursor <= $effectiveEnd) {
                $monthStart = $cursor->copy()->startOfMonth();
                $monthEnd = $cursor->copy()->endOfMonth();
                $overlapStart = $effectiveStart->copy()->max($monthStart);
                $overlapEnd = $effectiveEnd->copy()->min($monthEnd);

                if ($overlapStart->lte($overlapEnd)) {
                    $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                    $monthKey = $cursor->format('Y-m');

                    if (!isset($monthlyBuckets[$monthKey])) {
                        $monthlyBuckets[$monthKey] = ['fte' => 0.0, 'hours' => 0.0];
                    }

                    $monthlyBuckets[$monthKey]['fte'] += $ftePerDay * $daysInMonth;
                    $monthlyBuckets[$monthKey]['hours'] += $hoursPerDay * $daysInMonth;
                }

                $cursor->addMonth();
            }
        };

        foreach ($fteRows as $row) {
            $bounds = $this->parseContributionBounds($row->start_date ?? null, $row->end_date ?? null);
            if (!$bounds) continue;

            $project = $row->project;
            $projectId = $project?->id ?? $row->project_id ?? null;
            $projectTitle = trim((string)($project?->project_name_th ?? 'โครงการไม่ระบุ'));
            $pushContribution(
                'project:'.($projectId ?? md5($projectTitle)),
                'โครงการ',
                $projectTitle,
                trim((string)($project?->pi_name ?? '-')) ?: '-',
                $projectId ? url('/rm/projects/'.$projectId) : '#',
                $bounds['start'],
                $bounds['end'],
                (float)($row->fte_value ?? 0),
                (float)($row->hours ?? 0)
            );
        }

        foreach ($backOfficeRows as $row) {
            $startRaw = $row->start_date ?: ($row->task?->start_date);
            $endRaw = $row->end_date ?: ($row->task?->end_date);
            $bounds = $this->parseContributionBounds($startRaw, $endRaw);
            if (!$bounds) continue;

            $taskId = $row->task?->id ?? $row->task_id ?? null;
            $taskTitle = trim((string)($row->task?->title ?? 'งาน Back Office'));
            $taskRouteKey = $row->task?->getRouteKey()
                ?? ($taskId ? BackOfficeTask::formatBackOfficeId((int) $taskId) : null);
            $pushContribution(
                'task:'.($taskId ?? md5($taskTitle)),
                'Back Office',
                $taskTitle,
                null,
                $taskRouteKey ? route('back_office.show', $taskRouteKey) : '#',
                $bounds['start'],
                $bounds['end'],
                (float)($row->fte_value ?? 0),
                (float)($row->hours ?? 0)
            );
        }

        if (empty($itemBuckets)) {
            return [
                'reply' => "ไม่พบข้อมูล FTE ของ {$personName} ใน{$window['label']}",
            ];
        }

        $items = collect($itemBuckets)
            ->map(function ($row) {
                return [
                    ...$row,
                    'fte_total' => round((float)($row['fte_total'] ?? 0), 6),
                    'hours_total' => round((float)($row['hours_total'] ?? 0), 2),
                    'period' => $this->formatDateBE($row['start']).' ถึง '.$this->formatDateBE($row['end']),
                ];
            })
            ->sortByDesc(fn ($row) => ((int)round(($row['fte_total'] ?? 0) * 1000000) * 1000) + (int)round(($row['hours_total'] ?? 0)))
            ->values();

        $monthlyRows = collect($monthlyBuckets)
            ->sortKeys()
            ->map(function ($row, $monthKey) {
                return [
                    'month' => $this->monthLabelBE($monthKey),
                    'fte' => round((float)($row['fte'] ?? 0), 6),
                    'hours' => round((float)($row['hours'] ?? 0), 2),
                ];
            })
            ->values();

        $totalFte = (float)$items->sum('fte_total');
        $totalHours = (float)$items->sum('hours_total');
        $projectCount = (int)$items->where('type', 'โครงการ')->count();
        $taskCount = (int)$items->where('type', 'Back Office')->count();

        $reply = "FTE ของ {$personName} {$window['label']}: รวม ".number_format($totalFte, 6)
            ." FTE ใช้เวลา ".number_format($totalHours, 2)." ชั่วโมง";
        if ($projectCount > 0 || $taskCount > 0) {
            $reply .= " จากงานวิจัย {$projectCount} โครงการ";
            if ($taskCount > 0) {
                $reply .= " และ Back Office {$taskCount} งาน";
            }
        }

        $list = collect();
        if ($includePdfLink) {
            $pdfUrl = route('fte.pdf', ['person' => $personName]).'?'.http_build_query($window['query']);
            $list->push($this->buildDownloadLinkItem(
                "PDF FTE ของ {$personName}",
                $pdfUrl,
                "ดาวน์โหลดรายงาน {$window['label']}"
            ));
        }

        $list = $list->concat($items->take(10)->map(function ($row) {
            return [
                'title' => $row['title'],
                'pi' => $row['type'] === 'โครงการ' ? ($row['pi'] ?? '-') : null,
                'funding' => $row['type'].' / FTE '.number_format((float)$row['fte_total'], 6).' / ชั่วโมง '.number_format((float)$row['hours_total'], 2),
                'href' => $row['href'] ?? '#',
            ];
        }))->values();

        $response = [
            'reply' => $reply,
            'list' => $list->all(),
            'table' => [
                'title' => "รายละเอียด FTE ของ {$personName} ({$window['label']})",
                'headers' => ['รายการ', 'ประเภท', 'FTE', 'ชั่วโมง', 'ช่วงข้อมูล'],
                'fields' => ['title', 'type', 'fte_total', 'hours_total', 'period'],
                'rows' => $items->take(20)->map(function ($row) {
                    return [
                        'title' => $row['title'],
                        'type' => $row['type'],
                        'fte_total' => number_format((float)$row['fte_total'], 6),
                        'hours_total' => number_format((float)$row['hours_total'], 2),
                        'period' => $row['period'],
                        'href' => $row['href'] ?? '#',
                    ];
                })->values(),
            ],
        ];

        if ($monthlyRows->isNotEmpty()) {
            $response['chart'] = [
                'type' => 'bar',
                'labels' => $monthlyRows->pluck('month'),
                'datasets' => [[
                    'label' => 'FTE',
                    'data' => $monthlyRows->pluck('fte'),
                    'backgroundColor' => 'rgba(15, 118, 110, 0.78)',
                    'borderColor' => '#0f766e',
                    'borderWidth' => 1,
                ]],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ];
        }

        return $response;
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
    private function normalizeSearchText(?string $text): string {
        $normalized = mb_strtolower(trim((string)$text), 'UTF-8');
        return preg_replace('/\s+/u', ' ', $normalized) ?: '';
    }
    private function squashSearchText(?string $text): string {
        return preg_replace('/\s+/u', '', $this->normalizeSearchText($text)) ?: '';
    }
    private function searchTextChars(string $text): array {
        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
    private function searchTextBigramMap(string $text): array {
        $chars = $this->searchTextChars($text);
        $count = count($chars);
        if ($count === 0) return [];
        if ($count === 1) return [$chars[0] => 1];

        $grams = [];
        for ($index = 0; $index < $count - 1; $index++) {
            $gram = $chars[$index].$chars[$index + 1];
            $grams[$gram] = ($grams[$gram] ?? 0) + 1;
        }

        return $grams;
    }
    private function diceSimilarity(string $left, string $right): float {
        if ($left === '' || $right === '') return 0.0;
        if ($left === $right) return 1.0;

        $leftMap = $this->searchTextBigramMap($left);
        $rightMap = $this->searchTextBigramMap($right);
        if (empty($leftMap) || empty($rightMap)) {
            return Str::contains($right, $left) || Str::contains($left, $right) ? 1.0 : 0.0;
        }

        $intersection = 0;
        foreach ($leftMap as $gram => $count) {
            $intersection += min($count, $rightMap[$gram] ?? 0);
        }

        $leftCount = array_sum($leftMap);
        $rightCount = array_sum($rightMap);
        if (($leftCount + $rightCount) === 0) return 0.0;

        return (2 * $intersection) / ($leftCount + $rightCount);
    }
    private function bestContainedDiceSimilarity(string $needle, string $haystack): float {
        if ($needle === '' || $haystack === '') return 0.0;
        if ($needle === $haystack) return 1.0;
        if (Str::contains($haystack, $needle)) return 1.0;

        $needleChars = $this->searchTextChars($needle);
        $haystackChars = $this->searchTextChars($haystack);
        $needleLength = count($needleChars);
        $haystackLength = count($haystackChars);
        if ($needleLength === 0 || $haystackLength === 0) return 0.0;
        if ($needleLength === 1 || $haystackLength === 1) {
            return Str::contains($haystack, $needle) ? 1.0 : 0.0;
        }
        if ($haystackLength <= $needleLength + 2) {
            return $this->diceSimilarity($needle, $haystack);
        }

        $best = 0.0;
        $windowLengths = array_values(array_unique(array_filter([
            $needleLength - 1,
            $needleLength,
            $needleLength + 1,
            $needleLength + 2,
        ], fn ($length) => $length > 1 && $length <= $haystackLength)));

        foreach ($windowLengths as $windowLength) {
            for ($offset = 0; $offset <= $haystackLength - $windowLength; $offset++) {
                $slice = implode('', array_slice($haystackChars, $offset, $windowLength));
                $best = max($best, $this->diceSimilarity($needle, $slice));
                if ($best >= 0.985) return $best;
            }
        }

        return $best;
    }
    private function personQueryTokens(string $q): array {
        $clean = trim((string)$q);
        if ($clean === '') return [];

        $clean = preg_replace('/(?:\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}|\d{4}[\/-]\d{1,2}[\/-]\d{1,2})/u', ' ', $clean);
        $clean = preg_replace('/\d{1,2}\s+[^\s]+\s+\d{4}/u', ' ', $clean);
        $clean = preg_replace('/(25\d{2}|20\d{2})/u', ' ', $clean);
        $clean = preg_replace('/ปี\s*(ล่าสุด|นี้|ที่แล้ว|ก่อน)?/u', ' ', $clean);
        $clean = str_ireplace(['back office', 'research'], ' ', $clean);
        $clean = str_replace([
            'สรุปงานของ',
            'สรุปโครงการของ',
            'สรุปข้อมูลของ',
            'สรุปผลงานของ',
            'สรุปรายละเอียดของ',
            'สรุปของ',
            'งานของ',
            'โครงการของ',
            'ข้อมูลของ',
            'ผลงานของ',
            'รายละเอียดของ',
            'ของทีม',
            'ตั้งแต่',
            'ถึง',
            'จนถึง',
            'ระหว่าง',
            'ไฟล์',
            'ดาวน์โหลด',
            'โหลด',
        ], ' ', $clean);

        $stop = ['สรุป','ข้อมูล','งาน','โครงการ','ผลงาน','รายละเอียด','ของ','ทีม','ปี','ล่าสุด','นี้','ที่แล้ว','ก่อน','back','office','research','fte','บุคลากร','คน','รายชื่อ','pdf','csv','excel','xlsx','xls','export','ตั้งแต่','ถึง','จนถึง','ระหว่าง','โหลด','ดาวน์โหลด'];
        $raw = preg_split('/[\s,\/:()\-]+/u', trim($clean)) ?: [];
        $tok = [];
        foreach ($raw as $t) {
            $t = trim((string)$t);
            if ($t !== '' && !in_array($t, $stop, true) && !preg_match('/^\d+$/u', $t)) $tok[] = $t;
        }

        return $tok ?: $this->tokens($q);
    }
    private function personQueryLabel(string $q): string {
        $tokens = $this->personQueryTokens($q);
        return !empty($tokens) ? implode(' ', $tokens) : trim((string)$q);
    }
    private function stripPersonPrefix(?string $value): string {
        $value = trim((string)$value);
        if ($value === '') return '';

        $pattern = '/^(?:นายแพทย์หญิง|นายแพทย์|แพทย์หญิง|แพทย์|ทันตแพทย์หญิง|ทันตแพทย์|เภสัชกรหญิง|เภสัชกร|'
            .'ศ\.คลินิก|ศ\.นพ\.|ศ\.พญ\.|รศ\.นพ\.|รศ\.พญ\.|ผศ\.นพ\.|ผศ\.พญ\.|'
            .'ศ\.|รศ\.|ผศ\.|อ\.|ดร\.?|ภกญ\.|ภญ\.|ภก\.|พญ\.|นพ\.|ทพ\.ญ\.|ทพ\.|นาย|นางสาว|นาง)\s*/u';

        do {
            $next = preg_replace($pattern, '', $value);
            if ($next === null || $next === $value) break;
            $value = trim($next);
        } while ($value !== '');

        return trim(preg_replace('/\s+/u', ' ', $value) ?: '');
    }
    private function projectQueryTokens(string $q): array {
        $clean = trim((string)$q);
        if ($clean === '') return [];

        $clean = preg_replace('/(?:\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}|\d{4}[\/-]\d{1,2}[\/-]\d{1,2})/u', ' ', $clean);
        $clean = preg_replace('/\d{1,2}\s+[^\s]+\s+\d{4}/u', ' ', $clean);
        $clean = preg_replace('/(25\d{2}|20\d{2})/u', ' ', $clean);
        $clean = preg_replace('/ปี\s*(ล่าสุด|นี้|ที่แล้ว|ก่อน)?/u', ' ', $clean);
        $clean = str_ireplace(['project'], ' ', $clean);
        $clean = str_replace([
            'ค้นหา:',
            'ค้นหา :',
            'หาโครงการ',
            'หาโปรเจค',
            'ชื่อโครงการ',
            'ชื่อโปรเจค',
            'สรุปโครงการ',
            'รายละเอียดโครงการ',
            'โครงการของ',
            'โปรเจคของ',
            'ตั้งแต่',
            'ถึง',
            'จนถึง',
            'ระหว่าง',
            'ไฟล์',
            'ดาวน์โหลด',
            'โหลด',
        ], ' ', $clean);

        $stop = ['ค้นหา','หา','โครงการ','โปรเจค','project','ชื่อ','สรุป','รายละเอียด','ของ','ปี','ล่าสุด','นี้','ที่แล้ว','ก่อน','pdf','csv','excel','xlsx','xls','export','ตั้งแต่','ถึง','จนถึง','ระหว่าง','โหลด','ดาวน์โหลด'];
        $raw = preg_split('/[\s,\/:()\-]+/u', trim($clean)) ?: [];
        $tok = [];
        foreach ($raw as $t) {
            $t = trim((string)$t);
            if ($t !== '' && !in_array($t, $stop, true) && !preg_match('/^\d+$/u', $t)) $tok[] = $t;
        }

        return $tok ?: $this->tokens($q);
    }
    private function projectQueryLabel(string $q): string {
        $tokens = $this->projectQueryTokens($q);
        return !empty($tokens) ? implode(' ', $tokens) : trim((string)$q);
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
    private function robustProjectTitleCandidates(string $q, ?int $yearBE = null): array {
        $phrase = $this->projectQueryLabel($q);
        $tok = $this->projectQueryTokens($q);
        if (trim($phrase) === '' || empty($tok)) return [];

        $likeSets = [];
        foreach ($tok as $t) {
            $like = '%'.str_replace(['%','_'], ['\%', '\_'], $t).'%';
            $noSp = '%'.str_replace(' ', '', $t).'%';
            $likeSets[] = compact('like', 'noSp');
        }

        $rows = Project::query()
            ->select(['id', 'project_name_th', 'project_name_en', 'project_code', 'start_year'])
            ->where(function ($W) use ($likeSets) {
                foreach ($likeSets as $s) {
                    $W->where(function ($OR) use ($s) {
                        foreach (['project_name_th', 'project_name_en', 'project_code', 'project_list'] as $col) {
                            $OR->orWhere($col, 'like', $s['like']);
                            $OR->orWhereRaw("REPLACE($col,' ','') LIKE ?", [$s['noSp']]);
                        }
                    });
                }
            })
            ->when($yearBE, fn ($query) => $query->where('start_year', $yearBE))
            ->limit(120)
            ->get();

        $phraseNorm = $this->normalizeSearchText($phrase);
        $phraseSquashed = $this->squashSearchText($phrase);

        return $rows
            ->map(function ($row) use ($phraseNorm, $phraseSquashed, $tok) {
                $sources = collect([
                    $row->project_name_th,
                    $row->project_name_en,
                    $row->project_code,
                ])->filter(fn ($value) => trim((string)$value) !== '');

                $score = 0;
                foreach ($sources as $source) {
                    $sourceNorm = $this->normalizeSearchText($source);
                    $sourceSquashed = $this->squashSearchText($source);

                    if ($sourceNorm === $phraseNorm || $sourceSquashed === $phraseSquashed) {
                        $score = max($score, 1000);
                    } elseif (Str::startsWith($sourceNorm, $phraseNorm) || Str::startsWith($sourceSquashed, $phraseSquashed)) {
                        $score = max($score, 760);
                    } elseif (Str::contains($sourceNorm, $phraseNorm) || Str::contains($sourceSquashed, $phraseSquashed)) {
                        $score = max($score, 620);
                    }

                    $tokenScore = 0;
                    foreach ($tok as $token) {
                        $tokenNorm = $this->normalizeSearchText($token);
                        $tokenSquashed = $this->squashSearchText($token);
                        if ($tokenNorm === '' || $tokenSquashed === '') continue;

                        if (Str::startsWith($sourceNorm, $tokenNorm) || Str::startsWith($sourceSquashed, $tokenSquashed)) {
                            $tokenScore += 72;
                        } elseif (Str::contains($sourceNorm, $tokenNorm) || Str::contains($sourceSquashed, $tokenSquashed)) {
                            $tokenScore += 38;
                        }
                    }

                    $score = max($score, $tokenScore);
                }

                return [
                    'id' => (int)$row->id,
                    'start_year' => (int)($row->start_year ?? 0),
                    'score' => $score,
                ];
            })
            ->filter(fn ($row) => ($row['score'] ?? 0) > 0)
            ->sortByDesc(fn ($row) => (($row['score'] ?? 0) * 10000) + ($row['start_year'] ?? 0))
            ->pluck('id')
            ->unique()
            ->take(60)
            ->values()
            ->all();
    }
    private function approximateProjectTitleCandidates(
        string $q,
        ?int $yearBE = null,
        int $limit = 5,
        float $minSimilarity = 0.34
    ): array {
        $phrase = $this->projectQueryLabel($q);
        $phraseSquashed = $this->squashSearchText($phrase);
        if ($phraseSquashed === '' || mb_strlen($phraseSquashed, 'UTF-8') < 3) return [];

        $tokenSquashed = collect($this->projectQueryTokens($q))
            ->map(fn ($token) => $this->squashSearchText($token))
            ->filter(fn ($token) => $token !== '' && mb_strlen($token, 'UTF-8') >= 3)
            ->unique()
            ->values()
            ->all();

        $poolLimit = $yearBE !== null ? 240 : 360;
        $rows = Project::query()
            ->select(['id', 'project_name_th', 'project_name_en', 'project_code', 'start_year'])
            ->when($yearBE, fn ($query) => $query->where('start_year', $yearBE))
            ->orderByDesc('start_year')
            ->orderByDesc('id')
            ->limit($poolLimit)
            ->get();

        return $rows
            ->map(function ($row) use ($phraseSquashed, $tokenSquashed) {
                $sources = collect([
                    $row->project_name_th,
                    $row->project_name_en,
                    $row->project_code,
                ])->filter(fn ($value) => trim((string)$value) !== '');

                $similarity = 0.0;
                foreach ($sources as $source) {
                    $sourceSquashed = $this->squashSearchText($source);
                    if ($sourceSquashed === '') continue;

                    $similarity = max($similarity, $this->bestContainedDiceSimilarity($phraseSquashed, $sourceSquashed));
                    foreach ($tokenSquashed as $token) {
                        $similarity = max($similarity, $this->bestContainedDiceSimilarity($token, $sourceSquashed) * 0.92);
                    }
                }

                return [
                    'id' => (int)$row->id,
                    'start_year' => (int)($row->start_year ?? 0),
                    'similarity' => $similarity,
                ];
            })
            ->filter(fn ($row) => ($row['similarity'] ?? 0.0) >= $minSimilarity)
            ->sortByDesc(fn ($row) => ((int)round(($row['similarity'] ?? 0) * 1000) * 10000) + ($row['start_year'] ?? 0))
            ->pluck('id')
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }
    private function approximatePersonCandidates(string $q, int $limit = 5, float $minSimilarity = 0.42): array {
        $phrase = $this->stripPersonPrefix($this->personQueryLabel($q));
        $phraseSquashed = $this->squashSearchText($phrase);
        if ($phraseSquashed === '' || mb_strlen($phraseSquashed, 'UTF-8') < 3) return [];

        $tokenSquashed = collect($this->personQueryTokens($q))
            ->map(fn ($token) => $this->squashSearchText($this->stripPersonPrefix($token)))
            ->filter(fn ($token) => $token !== '' && mb_strlen($token, 'UTF-8') >= 2)
            ->unique()
            ->values()
            ->all();

        $pool = cache()->remember('agent:person-name-pool:v1', now()->addMinutes(30), function () {
            $invalidKeys = array_filter(array_map(function ($value) {
                return $this->squashSearchText($value);
            }, ['', '-', 'ไม่ระบุ', 'n/a', 'na', 'unknown']));

            $peopleMap = [];

            $pushPerson = function ($rawName, string $source, int $count = 1) use (&$peopleMap, $invalidKeys) {
                $rawName = trim((string)$rawName);
                if ($rawName === '') return;

                $queryName = $this->stripPersonPrefix($rawName);
                if ($queryName === '') $queryName = $rawName;

                $queryKey = $this->squashSearchText($queryName);
                if ($queryKey === '' || in_array($queryKey, $invalidKeys, true)) return;
                if (mb_strlen($queryName, 'UTF-8') < 2) return;

                $parts = preg_split('/\s+/u', $queryName) ?: [];
                $aliases = array_values(array_filter(array_unique([
                    $rawName,
                    $queryName,
                    preg_replace('/\s+/u', '', $rawName),
                    preg_replace('/\s+/u', '', $queryName),
                    $parts[0] ?? null,
                    count($parts) > 1 ? end($parts) : null,
                ])));

                if (!isset($peopleMap[$queryKey])) {
                    $peopleMap[$queryKey] = [
                        'text' => $queryName,
                        'label' => $rawName,
                        'aliases' => $aliases,
                        'sources' => [$source => true],
                        'hits' => max(1, $count),
                    ];
                    return;
                }

                $peopleMap[$queryKey]['hits'] += max(1, $count);
                $peopleMap[$queryKey]['sources'][$source] = true;
                $peopleMap[$queryKey]['aliases'] = array_values(array_filter(array_unique(array_merge(
                    $peopleMap[$queryKey]['aliases'],
                    $aliases
                ))));

                if (mb_strlen($rawName, 'UTF-8') > mb_strlen($peopleMap[$queryKey]['label'], 'UTF-8')) {
                    $peopleMap[$queryKey]['label'] = $rawName;
                }
            };

            $loadGroupedNames = function (string $table, string $column, string $source, int $limitPerSource = 180) use ($pushPerson) {
                if (!Schema::hasTable($table)) return;

                $rows = DB::table($table)
                    ->selectRaw("{$column} as value, COUNT(*) as aggregate_count")
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->groupBy($column)
                    ->orderByRaw('COUNT(*) DESC')
                    ->limit($limitPerSource)
                    ->get();

                foreach ($rows as $row) {
                    $pushPerson($row->value ?? '', $source, (int)($row->aggregate_count ?? 1));
                }
            };

            $loadGroupedNames('projects', 'pi_name', 'PI', 220);
            $loadGroupedNames('fte_roles', 'name', 'FTE', 220);
            $loadGroupedNames('copis', 'name', 'Co-PI', 180);
            $loadGroupedNames('co_research', 'name', 'Co-Research', 180);
            $loadGroupedNames('back_office_entries', 'person_name', 'Back Office', 260);

            return collect($peopleMap)
                ->sortByDesc(fn ($person) => (($person['hits'] ?? 0) * 10) + (count($person['sources'] ?? []) * 12))
                ->take(420)
                ->values()
                ->all();
        });

        return collect($pool)
            ->map(function ($person) use ($phraseSquashed, $tokenSquashed) {
                $variants = collect(array_merge(
                    [$person['text'] ?? '', $person['label'] ?? ''],
                    $person['aliases'] ?? []
                ))
                    ->map(fn ($value) => $this->squashSearchText($this->stripPersonPrefix($value)))
                    ->filter()
                    ->unique()
                    ->values();

                $similarity = 0.0;
                foreach ($variants as $variant) {
                    $similarity = max($similarity, $this->bestContainedDiceSimilarity($phraseSquashed, $variant));
                    foreach ($tokenSquashed as $token) {
                        $similarity = max($similarity, $this->bestContainedDiceSimilarity($token, $variant) * 0.94);
                    }
                }

                $score = $similarity
                    + min(0.08, ((int)($person['hits'] ?? 0)) * 0.003)
                    + min(0.06, count($person['sources'] ?? []) * 0.015);

                return [
                    ...$person,
                    'similarity' => $similarity,
                    'score' => $score,
                ];
            })
            ->filter(fn ($person) => ($person['similarity'] ?? 0.0) >= $minSimilarity)
            ->sortByDesc(fn ($person) => ((int)round(($person['score'] ?? 0.0) * 1000) * 1000) + ((int)($person['hits'] ?? 0)))
            ->take($limit)
            ->values()
            ->all();
    }

    private function robustPersonProjectCandidates(string $q, ?int $yearBE = null): array {
        $tok = $this->personQueryTokens($q);
        $likeSets = [];
        foreach ($tok as $t) {
            $like = '%'.str_replace(['%','_'], ['\%', '\_'], $t).'%';
            $noSp = '%'.str_replace(' ', '', $t).'%';
            $likeSets[] = compact('like', 'noSp');
        }

        if (empty($likeSets)) return [];

        $candidates = collect();

        $pi = Project::query()
            ->select('id')
            ->where(function ($W) use ($likeSets) {
                foreach ($likeSets as $s) {
                    $W->where(function ($OR) use ($s) {
                        $OR->orWhere('pi_name', 'like', $s['like']);
                        $OR->orWhereRaw("REPLACE(pi_name,' ','') LIKE ?", [$s['noSp']]);
                    });
                }
            })
            ->when($yearBE, fn ($qq) => $qq->where('start_year', $yearBE))
            ->limit(200)
            ->pluck('id');
        $candidates = $candidates->merge($pi);

        if (Schema::hasTable('fte_roles')) {
            $fte = DB::table('fte_roles')
                ->select('project_id as id')
                ->where(function ($W) use ($likeSets) {
                    foreach ($likeSets as $s) {
                        $W->where(function ($OR) use ($s) {
                            $OR->orWhere('name', 'like', $s['like']);
                            $OR->orWhereRaw("REPLACE(name,' ','') LIKE ?", [$s['noSp']]);
                        });
                    }
                })
                ->limit(300)
                ->pluck('id');
            $candidates = $candidates->merge($fte);
        }

        if (Schema::hasTable('copis')) {
            $copi = DB::table('copis')
                ->select('project_id as id')
                ->where(function ($W) use ($likeSets) {
                    foreach ($likeSets as $s) {
                        $W->where(function ($OR) use ($s) {
                            $OR->orWhere('name', 'like', $s['like']);
                            $OR->orWhereRaw("REPLACE(name,' ','') LIKE ?", [$s['noSp']]);
                        });
                    }
                })
                ->limit(300)
                ->pluck('id');
            $candidates = $candidates->merge($copi);
        }

        if (Schema::hasTable('co_research')) {
            $co = DB::table('co_research')
                ->select('project_id as id')
                ->where(function ($W) use ($likeSets) {
                    foreach ($likeSets as $s) {
                        $W->where(function ($OR) use ($s) {
                            $OR->orWhere('name', 'like', $s['like']);
                            $OR->orWhereRaw("REPLACE(name,' ','') LIKE ?", [$s['noSp']]);
                        });
                    }
                })
                ->limit(300)
                ->pluck('id');
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
    private function buildProjectListResponse(string $reply, array $rows): array {
        $list = collect($rows)->map(function ($row) {
            return [
                'id' => $row['project_code'],
                'project' => $row['project_name'],
                'year_be' => $row['start_year'],
                'pi' => $row['pi_name'],
                'funding' => $row['funding_type'].' / '.$row['funding_source'],
                'href' => $row['href'],
            ];
        })->sortByDesc('year_be')->values()->all();

        return [
            'reply' => $reply,
            'list' => $list,
        ];
    }
    private function buildApproximatePersonResponse(string $prompt, array $matches, ?int $yearBE = null): array {
        $targetPrompt = $yearBE ? "ปี {$yearBE}" : 'ปีล่าสุด';
        $list = collect($matches)->map(function ($person) use ($yearBE, $targetPrompt) {
            $name = trim((string)($person['text'] ?? $person['label'] ?? ''));
            $sources = implode(' / ', array_slice(array_keys($person['sources'] ?? []), 0, 3));
            return [
                'title' => $person['label'] ?? $name,
                'funding' => trim("แหล่งข้อมูล: {$sources}".(((int)($person['hits'] ?? 0)) > 0 ? " / พบ {$person['hits']} รายการ" : '')),
                'prompt' => "สรุปงานของ {$name} {$targetPrompt}",
                'submit_mode' => 'send',
            ];
        })->values()->all();

        return [
            'reply' => $yearBE
                ? "ไม่พบข้อมูลของ “{$prompt}” ในปี {$yearBE} แต่ชื่อที่ใกล้เคียงที่สุดคือ"
                : "ไม่พบข้อมูลของ “{$prompt}” ในระบบ แต่ชื่อที่ใกล้เคียงที่สุดคือ",
            'list' => $list,
        ];
    }
    private function personSummaryPrompt(string $name, ?int $yearBE = null): string {
        $name = trim((string)$name);
        if ($name === '') return '';
        return $yearBE
            ? "สรุปงานของ {$name} ปี {$yearBE}"
            : "สรุปงานของ {$name} ปีล่าสุด";
    }
    private function buildNameActionListResponse(string $reply, array $rows, ?int $yearBE = null): array {
        $list = collect($rows)->map(function ($row) use ($yearBE) {
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') return null;

            return [
                'title' => $name,
                'funding' => trim((string)($row['meta'] ?? '')),
                'prompt' => $this->personSummaryPrompt($name, $yearBE),
                'submit_mode' => 'send',
            ];
        })->filter()->values()->all();

        return [
            'reply' => $reply,
            'list' => $list,
        ];
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
        return $yearFromField !== null && $yearFromField === $yearBE;
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
                    $cols = ['person_name', 'category', 'job_name', 'job_detail', 'start_date', 'end_date'];
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
                    $cols = ['title', 'type', 'project_output', 'year', 'journal'];
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
            ->with(['entries', 'outputs', 'creator'])
            ->whereIn('id', $taskIds)
            ->orderByDesc('created_at')
            ->get();

        if ($yearBE !== null) {
            $tasks = $tasks->filter(function ($task) use ($yearBE) {
                if ($this->taskOverlapsBEYear($task, $yearBE)) return true;

                foreach ($task->entries as $entry) {
                    if ($this->taskOverlapsBEYear($entry, $yearBE)) return true;
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
            $leaders = collect($task->leaders ?? [])->filter(fn ($v) => trim((string)$v) !== '')->values();
            $people = $entries->pluck('person_name')->filter(fn ($v) => trim((string)$v) !== '')->unique()->values();
            $categories = $entries
                ->groupBy(fn ($entry) => trim((string)($entry->category ?: 'ไม่ระบุ')))
                ->map->count()
                ->sortDesc();
            $outputTypes = $taskOutputs
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
                'outputs_total' => (int)$taskOutputs->count(),
                'categories' => $categories->all(),
                'output_types' => $outputTypes->all(),
                'categories_brief' => $categories->take(5)->map(fn ($count, $name) => "{$name} ({$count})")->implode('; ') ?: '-',
                'people_brief' => $people->take(6)->implode('; ') ?: '-',
                'outputs_brief' => $taskOutputs->take(6)->map(function ($output) {
                    $type = trim((string)($output->type ?? $output->project_output ?? 'ไม่ระบุ'));
                    $year = trim((string)($output->year ?? ''));
                    $title = Str::limit((string)($output->title ?? 'ไม่ระบุ'), 80, '…');
                    return ($year !== '' ? "[{$year}] " : '').$type.' - '.$title;
                })->implode(' | ') ?: '-',
                'href' => route('back_office.show', $task),
            ];
        }

        return array_values($rows);
    }

    private function robustPersonBackOfficeTaskCandidates(string $q, ?int $yearBE = null): array {
        if (!Schema::hasTable('back_office_entries')) return [];

        $tok = $this->personQueryTokens($q);
        $likeSets = [];
        foreach ($tok as $t) {
            $like = '%'.str_replace(['%','_'], ['\%', '\_'], $t).'%';
            $noSp = '%'.str_replace(' ', '', $t).'%';
            $likeSets[] = compact('like', 'noSp');
        }

        if (empty($likeSets)) return [];

        $ids = DB::table('back_office_entries')
            ->select('task_id as id')
            ->where(function ($W) use ($likeSets) {
                foreach ($likeSets as $s) {
                    $W->where(function ($OR) use ($s) {
                        $OR->orWhere('person_name', 'like', $s['like']);
                        $OR->orWhereRaw("REPLACE(person_name,' ','') LIKE ?", [$s['noSp']]);
                    });
                }
            })
            ->limit(300)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($yearBE === null || $ids->isEmpty()) {
            return $ids->take(250)->all();
        }

        $rows = $this->hydrateBackOfficeTasks($ids->take(250)->all(), $yearBE);
        return array_values(array_unique(array_map(fn ($row) => $row['task_id'], $rows)));
    }

    private function personTokensNormalized(string $q): array {
        return collect($this->personQueryTokens($q))
            ->map(function ($token) {
                $normalized = preg_replace('/\s+/u', '', trim((string)$token));
                return mb_strtolower($normalized, 'UTF-8');
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function personTextMatchesTokens(?string $text, array $tokens): bool {
        if (!$text || empty($tokens)) return false;
        $normalized = mb_strtolower(preg_replace('/\s+/u', '', trim((string)$text)), 'UTF-8');
        foreach ($tokens as $token) {
            if ($token === '') continue;
            if (!Str::contains($normalized, $token)) {
                return false;
            }
        }
        return true;
    }

    private function hydratePersonBackOfficeContributions(string $q, array $taskIds, ?int $yearBE = null): array {
        if (empty($taskIds) || !Schema::hasTable('back_office_tasks')) return [];

        $tokens = $this->personTokensNormalized($q);
        if (empty($tokens)) return [];

        $tasks = BackOfficeTask::query()
            ->with(['entries', 'outputs'])
            ->whereIn('id', $taskIds)
            ->orderByDesc('created_at')
            ->get();

        $rows = [];
        foreach ($tasks as $task) {
            $matchedEntries = ($task->entries ?? collect())->filter(function ($entry) use ($tokens, $yearBE, $task) {
                if (!$this->personTextMatchesTokens($entry->person_name ?? '', $tokens)) {
                    return false;
                }

                if ($yearBE === null) return true;
                if ($this->taskOverlapsBEYear($entry, $yearBE)) return true;
                if ($this->taskOverlapsBEYear($task, $yearBE)) return true;
                foreach (($task->outputs ?? collect()) as $output) {
                    if ($this->outputMatchesBEYear($output, $yearBE)) return true;
                }
                return false;
            })->values();

            if ($matchedEntries->isEmpty()) continue;

            $matchedOutputs = $task->outputs ?? collect();
            $categories = $matchedEntries
                ->groupBy(fn ($entry) => trim((string)($entry->category ?: 'ไม่ระบุ')))
                ->map->count()
                ->sortDesc();

            $rows[] = [
                'task_id' => (int)$task->id,
                'title' => (string)($task->title ?? 'ไม่ระบุ'),
                'start_date' => (string)($matchedEntries->pluck('start_date')->filter()->sort()->first() ?? $task->start_date ?? '-'),
                'end_date' => (string)($matchedEntries->pluck('end_date')->filter()->sortDesc()->first() ?? $task->end_date ?? '-'),
                'entries_count' => (int)$matchedEntries->count(),
                'hours_sum' => round((float)$matchedEntries->sum('hours'), 2),
                'fte_sum' => round((float)$matchedEntries->sum('fte_value'), 6),
                'outputs_total' => (int)$matchedOutputs->count(),
                'categories_brief' => $categories->take(5)->map(fn ($count, $name) => "{$name} ({$count})")->implode('; ') ?: '-',
                'description' => (string)($task->description ?? '-'),
                'purpose' => (string)($task->purpose ?? '-'),
                'href' => route('back_office.show', $task),
            ];
        }

        return array_values($rows);
    }

    private function buildPersonCombinedResponse(string $prompt, array $projectRows, array $personBackOfficeRows): array {
        $name = trim($prompt);
        $projects = collect($projectRows)
            ->sortBy([
                ['start_year', 'desc'],
                ['budget', 'desc'],
                ['project_name', 'asc'],
            ])
            ->values();
        $tasks = collect($personBackOfficeRows)
            ->sortByDesc('hours_sum')
            ->values();

        $summaryParts = [];
        if ($projects->isNotEmpty()) {
            $summaryParts[] = "งานวิจัย {$projects->count()} โครงการ";
        }
        if ($tasks->isNotEmpty()) {
            $summaryParts[] = "Back Office {$tasks->count()} งาน";
        }

        $replySections = [];
        $replySections[] = "พบข้อมูลของ “{$name}” ใน ".implode(' และ ', $summaryParts);

        if ($projects->isNotEmpty()) {
            $budgetSum = (float)$projects->sum(fn ($row) => (float)($row['budget'] ?? 0));
            $years = $projects->pluck('start_year')->filter(fn ($year) => (int)$year > 0)->map(fn ($year) => (int)$year)->values();
            $yearLabel = '';
            if ($years->isNotEmpty()) {
                $minYear = $years->min();
                $maxYear = $years->max();
                $yearLabel = $minYear === $maxYear ? "ในปี พ.ศ. {$minYear}" : "ในช่วงปี พ.ศ. {$minYear}–{$maxYear}";
            }

            $projectLines = $projects->take(6)->map(function ($row, $index) {
                $budgetMil = number_format(((float)($row['budget'] ?? 0)) / 1_000_000, 2);
                $year = (int)($row['start_year'] ?? 0) > 0 ? (string)$row['start_year'] : '-';
                return ($index + 1).") {$row['project_name']} / PI: {$row['pi_name']} / ปี: {$year} / งบ(ลบ.): {$budgetMil}";
            })->implode("\n");

            $replySections[] = "ด้านโครงการวิจัย พบ {$projects->count()} โครงการ{$yearLabel}".($budgetSum > 0 ? " วงเงินรวมประมาณ ".number_format($budgetSum / 1_000_000, 2)." ล้านบาท" : '')."\n{$projectLines}";
        }

        if ($tasks->isNotEmpty()) {
            $hoursTotal = (float)$tasks->sum(fn ($row) => (float)($row['hours_sum'] ?? 0));
            $fteTotal = (float)$tasks->sum(fn ($row) => (float)($row['fte_sum'] ?? 0));
            $taskLines = $tasks->take(5)->map(function ($row, $index) {
                $period = trim(implode(' – ', array_filter([
                    $row['start_date'] && $row['start_date'] !== '-' ? $row['start_date'] : null,
                    $row['end_date'] && $row['end_date'] !== '-' ? $row['end_date'] : null,
                ])));
                $suffix = $period !== '' ? " / ช่วงงาน: {$period}" : '';
                return ($index + 1).") {$row['title']}{$suffix} / ชั่วโมง: ".number_format((float)($row['hours_sum'] ?? 0), 2)." / FTE: ".number_format((float)($row['fte_sum'] ?? 0), 6)." / หมวดงาน: ".($row['categories_brief'] ?? '-');
            })->implode("\n");

            $replySections[] = "ด้าน Back Office พบ {$tasks->count()} งาน ใช้เวลารวม ".number_format($hoursTotal, 2)." ชั่วโมง คิดเป็น ".number_format($fteTotal, 6)." FTE\n{$taskLines}";
        }

        $list = $projects->take(8)->map(function ($row) {
            return [
                'id' => $row['project_code'] ?? $row['project_id'],
                'project' => $row['project_name'],
                'year_be' => $row['start_year'] ?? null,
                'pi' => $row['pi_name'] ?? '-',
                'funding' => trim((string)($row['funding_type'] ?? 'ไม่ระบุ')).' / '.trim((string)($row['funding_source'] ?? 'ไม่ระบุ')),
                'href' => $row['href'] ?? '#',
            ];
        })->values();

        $taskList = $tasks->take(5)->map(function ($row) {
            return [
                'id' => $row['task_id'],
                'task' => $row['title'],
                'funding' => 'Back Office / '.number_format((float)($row['hours_sum'] ?? 0), 2).' ชม. / FTE '.number_format((float)($row['fte_sum'] ?? 0), 6),
                'href' => $row['href'] ?? '#',
            ];
        })->values();

        return [
            'reply' => implode("\n\n", array_filter($replySections)),
            'list' => $list->concat($taskList)->take(12)->values()->all(),
        ];
    }

    private function buildPersonProjectResponse(string $prompt, array $rows): array {
        $name = trim($prompt);
        $sorted = collect($rows)
            ->sortBy([
                ['start_year', 'desc'],
                ['budget', 'desc'],
                ['project_name', 'asc'],
            ])
            ->values();

        $count = $sorted->count();
        $years = $sorted->pluck('start_year')->filter(fn ($year) => (int)$year > 0)->map(fn ($year) => (int)$year)->values();
        $budgetSum = (float)$sorted->sum(fn ($row) => (float)($row['budget'] ?? 0));
        $yearLabel = 'ไม่ระบุปี';
        if ($years->isNotEmpty()) {
            $minYear = $years->min();
            $maxYear = $years->max();
            $yearLabel = $minYear === $maxYear ? "ปี พ.ศ. {$maxYear}" : "ช่วงปี พ.ศ. {$minYear}–{$maxYear}";
        }

        $piCount = $sorted->pluck('pi_name')->filter()->unique()->count();
        $topFunding = $sorted
            ->groupBy(fn ($row) => trim((string)($row['funding_type'] ?? 'ไม่ระบุ')).' / '.trim((string)($row['funding_source'] ?? 'ไม่ระบุ')))
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        $lines = [];
        $lines[] = "พบโครงการที่เกี่ยวข้องกับชื่อ “{$name}” ทั้งหมด {$count} โครงการ {$yearLabel}";
        if ($budgetSum > 0) {
            $lines[] = "วงเงินรวมประมาณ ".number_format($budgetSum / 1_000_000, 2)." ล้านบาท และเกี่ยวข้องกับ PI {$piCount} คน";
        } else {
            $lines[] = "พบรายชื่อ PI ที่เกี่ยวข้อง {$piCount} คน".($topFunding ? " โดยส่วนใหญ่อยู่ในกลุ่มทุน {$topFunding}" : '');
        }
        if ($topFunding) {
            $lines[] = "ประเภททุนที่พบมากคือ {$topFunding}";
        }

        $projectLines = $sorted->take(8)->map(function ($row, $index) {
            $budgetMil = number_format(((float)($row['budget'] ?? 0)) / 1_000_000, 2);
            $year = (int)($row['start_year'] ?? 0) > 0 ? (string)$row['start_year'] : '-';
            return ($index + 1).") {$row['project_name']} / PI: {$row['pi_name']} / ปี: {$year} / งบ(ลบ.): {$budgetMil}";
        });

        $reply = implode("\n\n", [
            implode(' ', array_filter($lines)),
            $projectLines->implode("\n"),
        ]);

        $list = $sorted->take(12)->map(function ($row) {
            return [
                'id' => $row['project_code'] ?? $row['project_id'],
                'project' => $row['project_name'],
                'year_be' => $row['start_year'] ?? null,
                'pi' => $row['pi_name'] ?? '-',
                'funding' => trim((string)($row['funding_type'] ?? 'ไม่ระบุ')).' / '.trim((string)($row['funding_source'] ?? 'ไม่ระบุ')),
                'href' => $row['href'] ?? '#',
            ];
        })->values()->all();

        return [
            'reply' => $reply,
            'list' => $list,
        ];
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

    private function budgetBreakdownForFundingType($projects, int $yBE, ?string $fundingType = null): array {
        $rows = collect($projects)
            ->filter(fn ($project) => $this->beIntFromText($project->start_year ?? null) === $yBE)
            ->filter(function ($project) use ($fundingType) {
                if ($fundingType === null) return true;
                return $this->normFundingType($project->funding_type ?? null) === $fundingType;
            })
            ->values();

        $totalBudget = 0.0;
        $sourceBuckets = [];
        $projectRows = [];

        foreach ($rows as $project) {
            $budget = $this->parseMoney($project->budget ?? 0);
            $source = trim((string)($project->funding_source ?? 'ไม่ระบุ')) ?: 'ไม่ระบุ';

            $totalBudget += $budget;

            if (!isset($sourceBuckets[$source])) {
                $sourceBuckets[$source] = [
                    'source' => $source,
                    'project_count' => 0,
                    'budget_total_raw' => 0.0,
                ];
            }

            $sourceBuckets[$source]['project_count']++;
            $sourceBuckets[$source]['budget_total_raw'] += $budget;

            $projectRows[] = [
                'id' => $project->id,
                'project' => $project->project_name_th ?? ('#'.$project->id),
                'pi' => $project->pi_name ?? '-',
                'budget' => $budget,
                'source' => $source,
                'href' => url('/rm/projects/'.$project->id),
            ];
        }

        usort($projectRows, fn ($left, $right) => $right['budget'] <=> $left['budget']);

        $sourceRows = collect($sourceBuckets)
            ->map(function ($row) {
                return [
                    'source' => $row['source'],
                    'project_count' => $row['project_count'],
                    'budget_total_raw' => $row['budget_total_raw'],
                    'budget_total' => number_format((float)$row['budget_total_raw'], 2),
                ];
            })
            ->sortByDesc(fn ($row) => ((float)($row['budget_total_raw'] ?? 0) * 1000) + (int)($row['project_count'] ?? 0))
            ->values()
            ->all();

        return [
            'year_be' => $yBE,
            'funding_type' => $fundingType ?? 'ทั้งหมด',
            'project_count' => $rows->count(),
            'total_budget' => $totalBudget,
            'source_rows' => $sourceRows,
            'top_projects' => array_slice($projectRows, 0, 10),
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
            } elseif (Str::contains($t, ['ปีนี้', 'ปีล่าสุด'])) {
                $yearHintBE = $this->nowBE();
            } elseif (Str::contains($t, ['ปีก่อน', 'ปีที่แล้ว'])) {
                $yearHintBE = $this->nowBE()-1;
            }

            $dateRange = $this->extractPromptDateRange($prompt);
            $wantsPdf = preg_match('/\bpdf\b/i', $prompt) === 1 || Str::contains($t, ['เป็น pdf', 'ไฟล์ pdf']);
            $wantsCsv = preg_match('/\bcsv\b/i', $prompt) === 1 || Str::contains($t, ['เป็น csv', 'ไฟล์ csv']);
            $wantsExcel = preg_match('/\b(?:xlsx|xls|excel)\b/i', $prompt) === 1 || Str::contains($t, ['excel', 'xlsx', 'xls', 'เอ็กเซล']);
            $wantsDownload = $wantsPdf || $wantsCsv || $wantsExcel || Str::contains($t, ['โหลด', 'ดาวน์โหลด', 'export']);

            /* ===================== DB-FIRST QUICK ANSWERS ===================== */
            // โค้ดส่วนนี้ไม่ได้เปลี่ยนแปลง (Quick Answers)

            if (
                $wantsDownload &&
                (Str::contains($t, ['โครงการทั้งหมด', 'โปรเจคทั้งหมด']) || $t === 'all projects')
            ) {
                $format = $wantsPdf ? 'pdf' : ($wantsCsv ? 'csv' : 'xls');
                $params = ['format' => $format];
                if ($yearHintBE !== null) {
                    $params['year'] = $yearHintBE;
                }

                $label = match ($format) {
                    'pdf' => 'PDF',
                    'csv' => 'CSV',
                    default => 'Excel',
                };

                return response()->json($this->buildDownloadActionResponse(
                    $yearHintBE
                        ? "เตรียมไฟล์รายชื่อโครงการทั้งหมดในปี {$yearHintBE} เป็น {$label}"
                        : "เตรียมไฟล์รายชื่อโครงการทั้งหมดเป็น {$label}",
                    $yearHintBE
                        ? "ดาวน์โหลดโครงการทั้งหมด ปี {$yearHintBE} ({$label})"
                        : "ดาวน์โหลดโครงการทั้งหมด ({$label})",
                    route('projects.export', $params),
                    $yearHintBE ? "ส่งออกเฉพาะปี {$yearHintBE}" : 'ส่งออกทุกโครงการที่คุณมีสิทธิ์เห็น'
                ));
            }

            /** 0) โปรเจคทั้งหมด/โครงการทั้งหมด */
            if (Str::contains($t,'โปรเจคทั้งหมด') || Str::contains($t,'โครงการทั้งหมด') || $t==='all projects') {
                $rows = Project::select(['id','project_name_th','pi_name','start_year','funding_type','funding_source'])
                    ->orderByDesc('id')->get()
                    ->filter(fn ($project) => $yearHintBE === null || $this->beIntFromText($project->start_year ?? null) === $yearHintBE)
                    ->take(200)
                    ->values();

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
                    'reply' => $yearHintBE
                        ? "รายการโครงการทั้งหมดในปี {$yearHintBE} (ล่าสุด 200 รายการ)"
                        : 'รายการโครงการทั้งหมด (ล่าสุด 200 รายการ)',
                    'list'  => $list,
                ]);
            }

            /** 0.1) ประเภททุนทั้งหมด */
            if (Str::contains($t, ['ประเภททุนทั้งหมด', 'ประเภททุน all'])) {
                $projects = Project::select(['start_year', 'budget', 'funding_type'])->get()
                    ->filter(fn ($project) => $yearHintBE === null || $this->beIntFromText($project->start_year ?? null) === $yearHintBE)
                    ->values();

                if ($projects->isEmpty()) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบข้อมูลประเภททุนในปี {$yearHintBE}" : 'ไม่พบข้อมูลประเภททุนในระบบ',
                    ]);
                }

                $rows = $projects
                    ->groupBy(fn ($project) => $this->normFundingType($project->funding_type ?? null))
                    ->map(function ($group, $type) {
                        $budgetTotal = $group->sum(fn ($project) => $this->parseMoney($project->budget ?? 0));
                        return [
                            'type' => $type,
                            'project_count' => $group->count(),
                            'budget_total' => number_format($budgetTotal, 2),
                            'budget_total_raw' => $budgetTotal,
                        ];
                    })
                    ->sortByDesc(fn ($row) => ((int)($row['project_count'] ?? 0) * 1000000000) + (int)round($row['budget_total_raw'] ?? 0))
                    ->values();

                return response()->json([
                    'reply' => $yearHintBE
                        ? "ประเภททุนทั้งหมดในปี {$yearHintBE}"
                        : 'ประเภททุนทั้งหมดในระบบ',
                    'table' => [
                        'title' => $yearHintBE ? "ประเภททุนทั้งหมด ปี {$yearHintBE}" : 'ประเภททุนทั้งหมด',
                        'headers' => ['ประเภททุน', 'จำนวนโครงการ', 'งบประมาณรวม(บาท)'],
                        'fields' => ['type', 'project_count', 'budget_total'],
                        'rows' => $rows->map(fn ($row) => [
                            'type' => $row['type'],
                            'project_count' => $row['project_count'],
                            'budget_total' => $row['budget_total'],
                        ])->values(),
                    ],
                    'chart' => [
                        'type' => 'doughnut',
                        'labels' => $rows->pluck('type'),
                        'datasets' => [[
                            'label' => 'จำนวนโครงการ',
                            'data' => $rows->pluck('project_count'),
                        ]],
                        'options' => ['plugins' => ['legend' => ['position' => 'bottom']]],
                    ],
                ]);
            }

            /** 0.2) ทุนทั้งหมด / แหล่งทุนทั้งหมด */
            if (Str::contains($t, ['ทุนทั้งหมด', 'แหล่งทุนทั้งหมด'])) {
                $projects = Project::select(['start_year', 'budget', 'funding_type', 'funding_source'])->get()
                    ->filter(fn ($project) => $yearHintBE === null || $this->beIntFromText($project->start_year ?? null) === $yearHintBE)
                    ->values();

                if ($projects->isEmpty()) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบข้อมูลทุนในปี {$yearHintBE}" : 'ไม่พบข้อมูลทุนในระบบ',
                    ]);
                }

                $sourceRows = $projects
                    ->groupBy(fn ($project) => trim((string)($project->funding_source ?? 'ไม่ระบุ')) ?: 'ไม่ระบุ')
                    ->map(function ($group, $source) {
                        $budgetTotal = $group->sum(fn ($project) => $this->parseMoney($project->budget ?? 0));
                        $topType = $group
                            ->groupBy(fn ($project) => $this->normFundingType($project->funding_type ?? null))
                            ->map->count()
                            ->sortDesc()
                            ->keys()
                            ->first() ?? 'ไม่ระบุ';

                        return [
                            'source' => $source,
                            'project_count' => $group->count(),
                            'budget_total' => number_format($budgetTotal, 2),
                            'budget_total_raw' => $budgetTotal,
                            'top_type' => $topType,
                        ];
                    })
                    ->sortByDesc(fn ($row) => ((int)($row['project_count'] ?? 0) * 1000000000) + (int)round($row['budget_total_raw'] ?? 0))
                    ->values();

                $typeRows = $projects
                    ->groupBy(fn ($project) => $this->normFundingType($project->funding_type ?? null))
                    ->map->count()
                    ->sortDesc();

                return response()->json([
                    'reply' => $yearHintBE
                        ? "แหล่งทุนทั้งหมดในปี {$yearHintBE}"
                        : 'แหล่งทุนทั้งหมดในระบบ',
                    'table' => [
                        'title' => $yearHintBE ? "แหล่งทุนทั้งหมด ปี {$yearHintBE}" : 'แหล่งทุนทั้งหมด',
                        'headers' => ['แหล่งทุน', 'จำนวนโครงการ', 'งบประมาณรวม(บาท)', 'ประเภททุนหลัก'],
                        'fields' => ['source', 'project_count', 'budget_total', 'top_type'],
                        'rows' => $sourceRows->take(120)->map(fn ($row) => [
                            'source' => $row['source'],
                            'project_count' => $row['project_count'],
                            'budget_total' => $row['budget_total'],
                            'top_type' => $row['top_type'],
                        ])->values(),
                    ],
                    'chart' => [
                        'type' => 'doughnut',
                        'labels' => $typeRows->keys()->values(),
                        'datasets' => [[
                            'label' => 'จำนวนโครงการ',
                            'data' => $typeRows->values()->values(),
                        ]],
                        'options' => ['plugins' => ['legend' => ['position' => 'bottom']]],
                    ],
                ]);
            }

            /** 0.2.1) งบภายใน / งบภายนอก / เปรียบเทียบภายใน-ภายนอก */
            $isFundingBudgetCompare = Str::contains($t, [
                'เปรียบเทียบงบภายในกับงบภายนอก',
                'เทียบงบ',
                'งบภายในกับงบภายนอก',
                'ทุนภายในกับทุนภายนอก',
            ]) || (
                Str::contains($t, ['งบ', 'ทุน'])
                && Str::contains($t, ['ภายใน', 'ทุนภายใน', 'งบภายใน'])
                && Str::contains($t, ['ภายนอก', 'ทุนภายนอก', 'งบภายนอก'])
            );

            if ($isFundingBudgetCompare) {
                $targetBE = $yearHintBE ?? $this->nowBE();
                $projects = Project::select(['id', 'project_name_th', 'pi_name', 'start_year', 'budget', 'funding_type', 'funding_source'])->get();

                $inside = $this->budgetBreakdownForFundingType($projects, $targetBE, 'ภายใน');
                $outside = $this->budgetBreakdownForFundingType($projects, $targetBE, 'ภายนอก');
                $unknown = $this->budgetBreakdownForFundingType($projects, $targetBE, 'ไม่ระบุ');

                if (($inside['project_count'] + $outside['project_count'] + $unknown['project_count']) === 0) {
                    return response()->json([
                        'reply' => "ไม่พบข้อมูลงบภายในหรือภายนอกในปี {$targetBE}",
                    ]);
                }

                $tableRows = collect([
                    [
                        'type' => 'ภายใน',
                        'project_count' => $inside['project_count'],
                        'budget_total' => number_format((float)$inside['total_budget'], 2),
                        'top_source' => $inside['source_rows'][0]['source'] ?? '-',
                    ],
                    [
                        'type' => 'ภายนอก',
                        'project_count' => $outside['project_count'],
                        'budget_total' => number_format((float)$outside['total_budget'], 2),
                        'top_source' => $outside['source_rows'][0]['source'] ?? '-',
                    ],
                    [
                        'type' => 'ไม่ระบุ',
                        'project_count' => $unknown['project_count'],
                        'budget_total' => number_format((float)$unknown['total_budget'], 2),
                        'top_source' => $unknown['source_rows'][0]['source'] ?? '-',
                    ],
                ])->filter(fn ($row) => (int)($row['project_count'] ?? 0) > 0 || (float)str_replace(',', '', (string)($row['budget_total'] ?? '0')) > 0)
                    ->values();

                return response()->json([
                    'reply' => "เปรียบเทียบงบภายในกับงบภายนอก ปี {$targetBE}: "
                        ."ภายใน <b>".$this->fmtMil((float)$inside['total_budget'])."</b> จาก {$inside['project_count']} โครงการ, "
                        ."ภายนอก <b>".$this->fmtMil((float)$outside['total_budget'])."</b> จาก {$outside['project_count']} โครงการ",
                    'table' => [
                        'title' => "เปรียบเทียบงบภายในกับงบภายนอก ปี {$targetBE}",
                        'headers' => ['ประเภททุน', 'จำนวนโครงการ', 'งบประมาณรวม(บาท)', 'แหล่งทุนเด่น'],
                        'fields' => ['type', 'project_count', 'budget_total', 'top_source'],
                        'rows' => $tableRows,
                    ],
                    'chart' => [
                        'type' => 'bar',
                        'labels' => $tableRows->pluck('type'),
                        'datasets' => [[
                            'label' => 'ล้านบาท',
                            'data' => $tableRows->map(fn ($row) => round(((float)str_replace(',', '', (string)$row['budget_total'])) / 1_000_000, 2))->values(),
                            'backgroundColor' => ['#0f766e', '#2563eb', '#94a3b8'],
                        ]],
                        'options' => [
                            'plugins' => ['legend' => ['display' => false]],
                            'scales' => ['y' => ['beginAtZero' => true]],
                        ],
                    ],
                    'chart2' => [
                        'type' => 'doughnut',
                        'labels' => $tableRows->pluck('type'),
                        'datasets' => [[
                            'label' => 'จำนวนโครงการ',
                            'data' => $tableRows->pluck('project_count'),
                        ]],
                        'options' => [
                            'plugins' => ['legend' => ['position' => 'bottom']],
                        ],
                    ],
                ]);
            }

            $targetFundingType = null;
            if (Str::contains($t, ['งบภายใน', 'ทุนภายใน'])) {
                $targetFundingType = 'ภายใน';
            } elseif (Str::contains($t, ['งบภายนอก', 'ทุนภายนอก'])) {
                $targetFundingType = 'ภายนอก';
            }

            if ($targetFundingType !== null) {
                $targetBE = $yearHintBE ?? $this->nowBE();
                $projects = Project::select(['id', 'project_name_th', 'pi_name', 'start_year', 'budget', 'funding_type', 'funding_source'])->get();
                $summary = $this->budgetBreakdownForFundingType($projects, $targetBE, $targetFundingType);
                $budgetLabel = $targetFundingType === 'ภายใน' ? 'งบภายใน' : 'งบภายนอก';

                if (($summary['project_count'] ?? 0) === 0) {
                    return response()->json([
                        'reply' => "ไม่พบข้อมูล{$budgetLabel}ในปี {$targetBE}",
                    ]);
                }

                $topSourcesText = collect($summary['source_rows'])
                    ->take(3)
                    ->map(fn ($row) => trim((string)($row['source'] ?? 'ไม่ระบุ')).' '.$this->fmtMil((float)($row['budget_total_raw'] ?? 0)))
                    ->implode(', ');

                $reply = "{$budgetLabel} ปี {$targetBE}: <b>".$this->fmtMil((float)$summary['total_budget'])."</b> จาก {$summary['project_count']} โครงการ";
                if ($topSourcesText !== '') {
                    $reply .= "<br>แหล่งทุนเด่น: {$topSourcesText}";
                }

                $sourceRows = collect($summary['source_rows'])->take(20)->values();
                $chartRows = collect($summary['source_rows'])->take(8)->values();

                return response()->json([
                    'reply' => $reply,
                    'list' => collect($summary['top_projects'])->map(fn ($row) => [
                        'project' => $row['project'],
                        'pi' => $row['pi'],
                        'year_be' => $targetBE,
                        'funding' => $targetFundingType.' / '.($row['source'] ?? 'ไม่ระบุ'),
                        'href' => $row['href'],
                    ])->values(),
                    'table' => [
                        'title' => "{$budgetLabel} ปี {$targetBE} แยกตามแหล่งทุน",
                        'headers' => ['แหล่งทุน', 'จำนวนโครงการ', 'งบประมาณรวม(บาท)'],
                        'fields' => ['source', 'project_count', 'budget_total'],
                        'rows' => $sourceRows->map(fn ($row) => [
                            'source' => $row['source'],
                            'project_count' => $row['project_count'],
                            'budget_total' => $row['budget_total'],
                        ])->values(),
                    ],
                    'chart' => [
                        'type' => $chartRows->count() > 5 ? 'bar' : 'doughnut',
                        'labels' => $chartRows->pluck('source'),
                        'datasets' => [[
                            'label' => 'ล้านบาท',
                            'data' => $chartRows->map(fn ($row) => round(((float)($row['budget_total_raw'] ?? 0)) / 1_000_000, 2))->values(),
                            'backgroundColor' => $targetFundingType === 'ภายใน'
                                ? 'rgba(15, 118, 110, 0.78)'
                                : 'rgba(37, 99, 235, 0.78)',
                            'borderColor' => $targetFundingType === 'ภายใน' ? '#115e59' : '#1d4ed8',
                            'borderWidth' => 1,
                        ]],
                        'options' => [
                            'plugins' => ['legend' => ['display' => $chartRows->count() <= 5, 'position' => 'bottom']],
                            'scales' => $chartRows->count() > 5 ? ['y' => ['beginAtZero' => true]] : (object)[],
                        ],
                    ],
                ]);
            }

            /** 0.3) Co-PI ทั้งหมด */
            if (Str::contains($t, ['co-pi ทั้งหมด', 'copi ทั้งหมด'])) {
                if (!Schema::hasTable('copis')) {
                    return response()->json(['reply' => 'ยังไม่มีข้อมูล Co-PI ในระบบ']);
                }

                $rows = DB::table('copis')
                    ->leftJoin('projects', 'projects.id', '=', 'copis.project_id')
                    ->select(['copis.name', 'copis.affiliation', 'projects.start_year', 'projects.budget'])
                    ->get()
                    ->filter(fn ($row) => trim((string)($row->name ?? '')) !== '')
                    ->filter(fn ($row) => $yearHintBE === null || $this->beIntFromText($row->start_year ?? null) === $yearHintBE)
                    ->groupBy(fn ($row) => trim((string)$row->name))
                    ->map(function ($group, $name) {
                        $affiliations = $group->pluck('affiliation')->filter(fn ($value) => trim((string)$value) !== '')->unique()->take(2)->implode(', ');
                        $budgetTotal = $group->sum(fn ($row) => $this->parseMoney($row->budget ?? 0));
                        return [
                            'name' => $name,
                            'projects' => $group->count(),
                            'budget_total' => $budgetTotal,
                            'meta' => trim($group->count().' โครงการ'
                                .($budgetTotal > 0 ? ' / งบ '.number_format($budgetTotal / 1_000_000, 2).' ล้านบาท' : '')
                                .($affiliations !== '' ? " / {$affiliations}" : '')),
                        ];
                    })
                    ->sortByDesc(fn ($row) => ((int)($row['projects'] ?? 0) * 1000000) + (int)round($row['budget_total'] ?? 0))
                    ->take(200)
                    ->values()
                    ->all();

                if (empty($rows)) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบข้อมูล Co-PI ในปี {$yearHintBE}" : 'ไม่พบข้อมูล Co-PI ในระบบ',
                    ]);
                }

                return response()->json($this->buildNameActionListResponse(
                    $yearHintBE ? "Co-PI ทั้งหมดในปี {$yearHintBE}" : 'Co-PI ทั้งหมดในระบบ',
                    $rows,
                    $yearHintBE
                ));
            }

            /** 0.4) PI ทั้งหมด */
            if (Str::contains($t, ['pi ทั้งหมด', 'principal investigator ทั้งหมด']) && !Str::contains($t, ['co-pi', 'copi'])) {
                $rows = Project::select(['pi_name', 'start_year', 'budget'])
                    ->get()
                    ->filter(fn ($project) => trim((string)($project->pi_name ?? '')) !== '')
                    ->filter(fn ($project) => $yearHintBE === null || $this->beIntFromText($project->start_year ?? null) === $yearHintBE)
                    ->groupBy(fn ($project) => trim((string)$project->pi_name))
                    ->map(function ($group, $name) {
                        $budgetTotal = $group->sum(fn ($project) => $this->parseMoney($project->budget ?? 0));
                        return [
                            'name' => $name,
                            'projects' => $group->count(),
                            'budget_total' => $budgetTotal,
                            'meta' => trim($group->count().' โครงการ'
                                .($budgetTotal > 0 ? ' / งบ '.number_format($budgetTotal / 1_000_000, 2).' ล้านบาท' : '')),
                        ];
                    })
                    ->sortByDesc(fn ($row) => ((int)($row['projects'] ?? 0) * 1000000) + (int)round($row['budget_total'] ?? 0))
                    ->take(200)
                    ->values()
                    ->all();

                if (empty($rows)) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบข้อมูล PI ในปี {$yearHintBE}" : 'ไม่พบข้อมูล PI ในระบบ',
                    ]);
                }

                return response()->json($this->buildNameActionListResponse(
                    $yearHintBE ? "PI ทั้งหมดในปี {$yearHintBE}" : 'PI ทั้งหมดในระบบ',
                    $rows,
                    $yearHintBE
                ));
            }

            /** 0.5) Co-Research ทั้งหมด */
            if (Str::contains($t, ['co-research ทั้งหมด', 'coresearch ทั้งหมด', 'co research ทั้งหมด'])) {
                if (!Schema::hasTable('co_research')) {
                    return response()->json(['reply' => 'ยังไม่มีข้อมูล Co-Research ในระบบ']);
                }

                $rows = DB::table('co_research')
                    ->leftJoin('projects', 'projects.id', '=', 'co_research.project_id')
                    ->select(['co_research.name', 'co_research.unit_status', 'projects.start_year', 'projects.budget'])
                    ->get()
                    ->filter(fn ($row) => trim((string)($row->name ?? '')) !== '')
                    ->filter(fn ($row) => $yearHintBE === null || $this->beIntFromText($row->start_year ?? null) === $yearHintBE)
                    ->groupBy(fn ($row) => trim((string)$row->name))
                    ->map(function ($group, $name) {
                        $units = $group->pluck('unit_status')->filter(fn ($value) => trim((string)$value) !== '')->unique()->take(2)->implode(', ');
                        $budgetTotal = $group->sum(fn ($row) => $this->parseMoney($row->budget ?? 0));
                        return [
                            'name' => $name,
                            'projects' => $group->count(),
                            'budget_total' => $budgetTotal,
                            'meta' => trim($group->count().' โครงการ'
                                .($budgetTotal > 0 ? ' / งบ '.number_format($budgetTotal / 1_000_000, 2).' ล้านบาท' : '')
                                .($units !== '' ? " / {$units}" : '')),
                        ];
                    })
                    ->sortByDesc(fn ($row) => ((int)($row['projects'] ?? 0) * 1000000) + (int)round($row['budget_total'] ?? 0))
                    ->take(200)
                    ->values()
                    ->all();

                if (empty($rows)) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบข้อมูล Co-Research ในปี {$yearHintBE}" : 'ไม่พบข้อมูล Co-Research ในระบบ',
                    ]);
                }

                return response()->json($this->buildNameActionListResponse(
                    $yearHintBE ? "Co-Research ทั้งหมดในปี {$yearHintBE}" : 'Co-Research ทั้งหมดในระบบ',
                    $rows,
                    $yearHintBE
                ));
            }

            /** 0.6) FTE ทั้งหมด */
            if (Str::contains($t, ['fte ทั้งหมด', 'เอฟทีอีทั้งหมด'])) {
                $rows = FteRole::query()
                    ->selectRaw('name, SUM(COALESCE(fte_value,0)) AS total_fte, COUNT(*) AS role_count')
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->when($yearHintBE, function ($query) use ($yearHintBE) {
                        $targetCE = $yearHintBE - 543;
                        $this->scopeFTEActiveInCEYear($query, $targetCE);
                    })
                    ->groupBy('name')
                    ->orderByDesc('total_fte')
                    ->limit(200)
                    ->get();

                if ($rows->isEmpty()) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบข้อมูล FTE ในปี {$yearHintBE}" : 'ไม่พบข้อมูล FTE ในระบบ',
                    ]);
                }

                return response()->json([
                    'reply' => $yearHintBE ? "FTE ทั้งหมดในปี {$yearHintBE}" : 'FTE ทั้งหมดในระบบ',
                    'table' => [
                        'title' => $yearHintBE ? "FTE ทั้งหมด ปี {$yearHintBE}" : 'FTE ทั้งหมด',
                        'headers' => ['ชื่อ', 'FTE รวม', 'จำนวนรายการ'],
                        'fields' => ['name', 'total_fte', 'role_count'],
                        'rows' => $rows->map(fn ($row) => [
                            'name' => $row->name,
                            'total_fte' => number_format((float)$row->total_fte, 4),
                            'role_count' => (int)$row->role_count,
                        ])->values(),
                    ],
                ]);
            }

            /** 0.7) Outputs ทั้งหมด */
            if (Str::contains($t, ['outputs ทั้งหมด', 'output ทั้งหมด', 'ผลงานทั้งหมด', 'publication ทั้งหมด'])) {
                if (!Schema::hasTable('outputs')) {
                    return response()->json(['reply' => 'ยังไม่มีตาราง Outputs ในระบบ']);
                }

                $rows = DB::table('outputs')->select(['type', 'year'])->get()
                    ->filter(function ($row) use ($yearHintBE) {
                        if ($yearHintBE === null) return true;
                        return $this->beIntFromText($row->year ?? null) === $yearHintBE;
                    })
                    ->groupBy(fn ($row) => trim((string)($row->type ?? 'ไม่ระบุ')) ?: 'ไม่ระบุ')
                    ->map(fn ($group, $type) => ['type' => $type, 'count' => $group->count()])
                    ->sortByDesc('count')
                    ->values();

                if ($rows->isEmpty()) {
                    return response()->json([
                        'reply' => $yearHintBE ? "ไม่พบ Outputs ในปี {$yearHintBE}" : 'ไม่พบ Outputs ในระบบ',
                    ]);
                }

                return response()->json([
                    'reply' => $yearHintBE ? "Outputs ทั้งหมดในปี {$yearHintBE}" : 'Outputs ทั้งหมดในระบบ',
                    'table' => [
                        'title' => $yearHintBE ? "Outputs ทั้งหมด ปี {$yearHintBE}" : 'Outputs ทั้งหมด',
                        'headers' => ['ประเภท', 'จำนวน'],
                        'fields' => ['type', 'count'],
                        'rows' => $rows,
                    ],
                    'chart' => [
                        'type' => 'bar',
                        'labels' => $rows->pluck('type'),
                        'datasets' => [[
                            'label' => 'จำนวน',
                            'data' => $rows->pluck('count'),
                        ]],
                        'options' => ['plugins' => ['legend' => ['display' => false]]],
                    ],
                ]);
            }

            /** 0.8) แนะนำคำสั่งทั้งหมดที่รองรับ */
            if ($t === 'ทั้งหมด' || Str::contains($t, ['อะไรทั้งหมด', 'มีอะไรทั้งหมด'])) {
                return response()->json([
                    'reply' => 'คำสั่งแบบ "ทั้งหมด" ที่รองรับ เช่น โครงการทั้งหมด, ทุนทั้งหมด, ประเภททุนทั้งหมด, PI ทั้งหมด, Co-PI ทั้งหมด, Co-Research ทั้งหมด, Outputs ทั้งหมด, FTE ทั้งหมด, Back Office ทั้งหมด',
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
                if (empty($rows)) {
                    $nearestRows = $this->hydrateProjectsWithJoins(
                        $this->approximateProjectTitleCandidates($query, $yearHintBE, 5, 0.30)
                    );
                    if (!empty($nearestRows)) {
                        return response()->json(
                            $wantsPdf
                                ? $this->buildProjectPdfListResponse(
                                    $yearHintBE
                                        ? "ไม่พบโครงการที่ตรงกับ “{$query}” ในปี {$yearHintBE} แต่ใกล้เคียงที่สุดคือ (กดเพื่อเปิด PDF)"
                                        : "ไม่พบโครงการที่ตรงกับ “{$query}” แต่ใกล้เคียงที่สุดคือ (กดเพื่อเปิด PDF)",
                                    $nearestRows
                                )
                                : $this->buildProjectListResponse(
                                    $yearHintBE
                                        ? "ไม่พบโครงการที่ตรงกับ “{$query}” ในปี {$yearHintBE} แต่ใกล้เคียงที่สุดคือ"
                                        : "ไม่พบโครงการที่ตรงกับ “{$query}” แต่ใกล้เคียงที่สุดคือ",
                                    $nearestRows
                                )
                        );
                    }
                    return response()->json([
                        'reply' => $yearHintBE
                            ? "ไม่พบโครงการที่ตรงกับ “{$query}” ในปี {$yearHintBE}"
                            : "ไม่พบโครงการที่ตรงกับ “{$query}”",
                    ]);
                }
                return response()->json(
                    $wantsPdf
                        ? $this->buildProjectPdfListResponse('ผลการค้นหา (คลิกเพื่อเปิด PDF)', $rows)
                        : $this->buildProjectListResponse('ผลการค้นหา (คลิกเพื่อดูรายละเอียด)', $rows)
                );
            }

            /* ===== Hybrid Pack (LLM) — เผื่อคำถามอื่น ๆ ===== */
            $personPrompt = $this->personQueryLabel($prompt);
            $personProjectIds = $this->robustPersonProjectCandidates($personPrompt, $yearHintBE);
            $personTaskIds = $this->robustPersonBackOfficeTaskCandidates($personPrompt, $yearHintBE);
            $isLikelyPersonQuery = !empty($personProjectIds) || !empty($personTaskIds);
            $personSearchTokens = $this->personQueryTokens($personPrompt);
            $projectPrompt = $this->projectQueryLabel($prompt);
            $projectSearchTokens = $this->projectQueryTokens($projectPrompt);
            $hasSpecificProjectTerms = collect($projectSearchTokens)->contains(function ($token) {
                return !preg_match('/^(25\d{2}|20\d{2})$/u', trim((string)$token));
            });
            $likelyProjectTitleIntent = !$isLikelyPersonQuery
                && $hasSpecificProjectTerms
                && count($projectSearchTokens) <= 6
                && mb_strlen($projectPrompt, 'UTF-8') <= 120
                && !Str::contains($t, ['งบ', 'budget', 'fte', 'outputs', 'output', 'irb', 'back office', 'งานนอกโครงการ', 'จำนวน', 'รวม', 'เปรียบเทียบ', 'สถานะ']);
            $projectTitleIds = !$isLikelyPersonQuery
                ? $this->robustProjectTitleCandidates($projectPrompt, $yearHintBE)
                : [];
            $projectApproximateIds = (!$isLikelyPersonQuery && empty($projectTitleIds) && $likelyProjectTitleIntent)
                ? $this->approximateProjectTitleCandidates($projectPrompt, $yearHintBE, 6, 0.33)
                : [];
            $isLikelyProjectTitleQuery = !$isLikelyPersonQuery && !empty($projectTitleIds);
            $hasApproximateProjectTitleMatches = !$isLikelyPersonQuery && empty($projectTitleIds) && !empty($projectApproximateIds);
            $likelyPersonNameIntent = !$isLikelyPersonQuery
                && !$isLikelyProjectTitleQuery
                && !$hasApproximateProjectTitleMatches
                && !empty($personSearchTokens)
                && count($personSearchTokens) <= 4
                && mb_strlen($personPrompt, 'UTF-8') <= 80
                && !Str::startsWith($t, 'ค้นหา:')
                && !Str::startsWith($t, 'ค้นหา :')
                && !Str::contains($t, ['หาโครงการ', 'หาโปรเจค', 'งบ', 'budget', 'fte', 'outputs', 'output', 'irb', 'back office', 'งานนอกโครงการ', 'จำนวน', 'รวม', 'เปรียบเทียบ', 'สถานะ']);
            $approximatePersonMatches = $likelyPersonNameIntent
                ? $this->approximatePersonCandidates($personPrompt, 5, 0.42)
                : [];
            $hasApproximatePersonMatches = !$isLikelyPersonQuery && !empty($approximatePersonMatches);

            if ($isLikelyPersonQuery) {
                $pids = $personProjectIds;
            } elseif ($isLikelyProjectTitleQuery) {
                $pids = $projectTitleIds;
            } elseif ($hasApproximateProjectTitleMatches) {
                $pids = $projectApproximateIds;
            } else {
                $pids = $this->robustProjectCandidates($prompt, $yearHintBE);
            }
            $taskIds = $isLikelyPersonQuery
                ? $personTaskIds
                : $this->robustBackOfficeTaskCandidates($prompt, $yearHintBE);

            // FIX 4: หากการค้นหาแบบ Fuzzy Search (Robust Search) ล้มเหลว 
            // แต่มีการระบุปีในคำถาม ให้ดึง Project ID ทั้งหมดในปีนั้นแทน
            if (
                !$isLikelyPersonQuery &&
                !$hasSpecificProjectTerms &&
                empty($pids) &&
                $yearHintBE !== null
            ) {
                // ดึง Project IDs ทั้งหมดที่ตรงกับปีที่ระบุโดยตรง
                $pids = Project::where('start_year', (string)$yearHintBE)->pluck('id')->all();
            }

            if (
                !$isLikelyPersonQuery &&
                !$likelyPersonNameIntent &&
                empty($taskIds) &&
                $yearHintBE !== null &&
                Schema::hasTable('back_office_tasks')
            ) {
                $taskIds = BackOfficeTask::query()
                    ->orderByDesc('created_at')
                    ->limit(250)
                    ->pluck('id')
                    ->all();
            }

            $backOfficeRows = $isLikelyPersonQuery
                ? $this->hydratePersonBackOfficeContributions($personPrompt, $taskIds, $yearHintBE)
                : $this->hydrateBackOfficeTasks($taskIds, $yearHintBE);

            $hasCustomDateRange = ($dateRange['from'] ?? null) instanceof Carbon && ($dateRange['to'] ?? null) instanceof Carbon;
            $hasFteIntent = Str::contains($t, ['fte', 'เอฟทีอี']);
            if ($isLikelyPersonQuery && ($hasFteIntent || $hasCustomDateRange || $wantsPdf)) {
                $personFteResponse = $this->buildPersonFteWindowResponse($personPrompt, $yearHintBE, $dateRange, $wantsPdf);
                if ($personFteResponse !== null) {
                    return response()->json($personFteResponse);
                }
            }

            // ************************************************
            // ✅ FIX 5: CHECK FOR GENERAL KNOWLEDGE QUESTION
            // ************************************************
            $hasRelevantPIDs = !empty($pids);
            $hasRelevantBackOffice = !empty($backOfficeRows);

            if (!$hasRelevantPIDs && !$hasRelevantBackOffice && $hasApproximatePersonMatches) {
                return response()->json($this->buildApproximatePersonResponse($personPrompt, $approximatePersonMatches, $yearHintBE));
            }
            
            // ถ้าไม่พบโครงการที่เกี่ยวข้อง (และไม่มีปีระบุที่ใช้เป็นตัวกรองได้) 
            // ให้ถือว่าเป็นคำถามความรู้ทั่วไป และข้ามการส่ง Data Pack ไป
            if (!$hasRelevantPIDs && !$hasRelevantBackOffice && $yearHintBE === null) {
                if ($likelyProjectTitleIntent) {
                    return response()->json([
                        'reply' => "ไม่พบโครงการที่ตรงกับ “{$projectPrompt}”",
                    ]);
                }
                if ($likelyPersonNameIntent) {
                    return response()->json([
                        'reply' => "ไม่พบข้อมูลของ “{$personPrompt}” ในระบบ",
                    ]);
                }
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
            if (
                !$isLikelyPersonQuery &&
                !$isLikelyProjectTitleQuery &&
                $yearHintBE !== null &&
                $hasSpecificProjectTerms &&
                empty($rows) &&
                empty($backOfficeRows)
            ) {
                return response()->json([
                    'reply' => "ไม่พบโครงการที่ตรงกับ “{$projectPrompt}” ในปี {$yearHintBE}",
                ]);
            }
            if ($isLikelyProjectTitleQuery && !empty($rows) && empty($backOfficeRows)) {
                $reply = count($rows) === 1
                    ? "พบโครงการที่ตรงกับ “{$projectPrompt}” (คลิกเพื่อดูรายละเอียด)"
                    : "พบโครงการที่ใกล้เคียงกับ “{$projectPrompt}” ".count($rows)." รายการ (คลิกเพื่อดูรายละเอียด)";
                return response()->json(
                    $wantsPdf
                        ? $this->buildProjectPdfListResponse(str_replace('ดูรายละเอียด', 'เปิด PDF', $reply), $rows)
                        : $this->buildProjectListResponse($reply, $rows)
                );
            }
            if ($hasApproximateProjectTitleMatches && !empty($rows) && empty($backOfficeRows)) {
                return response()->json(
                    $wantsPdf
                        ? $this->buildProjectPdfListResponse(
                            $yearHintBE
                                ? "ไม่พบโครงการที่ตรงกับ “{$projectPrompt}” ในปี {$yearHintBE} แต่ใกล้เคียงที่สุดคือ (กดเพื่อเปิด PDF)"
                                : "ไม่พบโครงการที่ตรงกับ “{$projectPrompt}” แต่ใกล้เคียงที่สุดคือ (กดเพื่อเปิด PDF)",
                            $rows
                        )
                        : $this->buildProjectListResponse(
                            $yearHintBE
                                ? "ไม่พบโครงการที่ตรงกับ “{$projectPrompt}” ในปี {$yearHintBE} แต่ใกล้เคียงที่สุดคือ"
                                : "ไม่พบโครงการที่ตรงกับ “{$projectPrompt}” แต่ใกล้เคียงที่สุดคือ",
                            $rows
                        )
                );
            }
            if (
                !$isLikelyPersonQuery &&
                $yearHintBE !== null &&
                $likelyPersonNameIntent &&
                !$hasApproximatePersonMatches &&
                empty($rows) &&
                empty($backOfficeRows)
            ) {
                return response()->json([
                    'reply' => "ไม่พบข้อมูลของ “{$personPrompt}” ในปี {$yearHintBE}",
                ]);
            }
            if ($isLikelyPersonQuery && empty($rows) && empty($backOfficeRows)) {
                return response()->json([
                    'reply' => $yearHintBE
                        ? "ไม่พบข้อมูลของ “{$personPrompt}” ในปี {$yearHintBE}"
                        : "ไม่พบข้อมูลของ “{$personPrompt}” ในระบบ",
                ]);
            }
            if ($isLikelyPersonQuery && !empty($rows) && !empty($backOfficeRows)) {
                return response()->json($this->buildPersonCombinedResponse($personPrompt, $rows, $backOfficeRows));
            }
            if ($isLikelyPersonQuery && !empty($rows) && empty($backOfficeRows)) {
                return response()->json($this->buildPersonProjectResponse($personPrompt, $rows));
            }
            if ($isLikelyPersonQuery && empty($rows) && !empty($backOfficeRows)) {
                return response()->json($this->buildPersonCombinedResponse($personPrompt, [], $backOfficeRows));
            }
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
