<?php

namespace App\Console\Commands;

use App\Models\BackOfficeEntry;
use App\Models\FteRole;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ZipArchive;

class ExportFteSkillRawData extends Command
{
    protected $signature = 'fte:export-skill-raw
        {--year-from= : Buddhist year lower bound, e.g. 2567}
        {--year-to= : Buddhist year upper bound, e.g. 2569}
        {--path= : Output .xlsx path. Default: storage/app/exports/fte-skill-raw-data-YYYYmmdd-His.xlsx}';

    protected $description = 'Export raw FTE skill-map rows used by the visualization section to an Excel file';

    public function handle(): int
    {
        $yearFrom = $this->normalizeYearFilter($this->option('year-from'));
        $yearTo = $this->normalizeYearFilter($this->option('year-to'));
        $path = trim((string) $this->option('path'));

        if ($path === '') {
            $path = storage_path('app/exports/fte-skill-raw-data-' . now('Asia/Bangkok')->format('Ymd-His') . '.xlsx');
        }

        if (! str_ends_with(Str::lower($path), '.xlsx')) {
            $path .= '.xlsx';
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $payload = $this->buildRows($yearFrom, $yearTo);
        $this->writeWorkbook($path, [
            'Summary' => [
                'headers' => [
                    'กลุ่ม',
                    'รายการ',
                    'จำนวนงานที่นับจริง',
                    'จำนวนคน',
                    'จำนวน Project FTE',
                    'จำนวน Back Office FTE',
                    'ชั่วโมงรวม',
                    'FTE รวม',
                ],
                'rows' => $payload['summary_rows'],
            ],
            'Raw Rows' => [
                'headers' => [
                    'เลขแถว',
                    'แหล่งข้อมูล',
                    'ปี พ.ศ.',
                    'ชื่อคน',
                    'กลุ่มทักษะ',
                    'รายการทักษะ',
                    'ภาระงาน',
                    'รายละเอียดงาน',
                    'หมวด/ประเภทต้นทาง',
                    'โครงการ/งาน',
                    'รหัส container',
                    'ชั่วโมง',
                    'FTE',
                    'วันเริ่ม',
                    'วันสิ้นสุด',
                    'รหัส record',
                    'key กลุ่ม',
                    'key รายการ',
                ],
                'rows' => $payload['raw_rows'],
            ],
            'Deduped For Visualization' => [
                'headers' => [
                    'เลขแถว',
                    'จำนวนแถวดิบที่รวม',
                    'แหล่งข้อมูล',
                    'ปี พ.ศ.',
                    'ชื่อคน',
                    'กลุ่มทักษะ',
                    'รายการทักษะ',
                    'ภาระงาน',
                    'รายละเอียดงาน',
                    'หมวด/ประเภทต้นทาง',
                    'โครงการ/งาน',
                    'รหัส container',
                    'ชั่วโมง',
                    'FTE',
                    'วันเริ่ม',
                    'วันสิ้นสุด',
                    'key กลุ่ม',
                    'key รายการ',
                ],
                'rows' => $payload['deduped_rows'],
            ],
        ]);

        $this->info('Exported FTE skill raw data: ' . $path);
        $this->line('raw=' . count($payload['raw_rows']) . ', deduped=' . count($payload['deduped_rows']));

        return self::SUCCESS;
    }

    private function buildRows(?int $yearFrom, ?int $yearTo): array
    {
        $normalizeText = static fn ($value): string => Str::of((string) $value)
            ->lower()
            ->replace(["\r", "\n", "\t"], ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
        $displayText = static fn ($value): string => Str::of((string) $value)
            ->replace(["\r", "\n", "\t"], ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
        $inRange = static function (?int $year) use ($yearFrom, $yearTo): bool {
            if ($yearFrom !== null && ($year === null || $year < $yearFrom)) {
                return false;
            }

            if ($yearTo !== null && ($year === null || $year > $yearTo)) {
                return false;
            }

            return true;
        };

        $skillFor = $this->skillMatcher($normalizeText);

        $researchRows = FteRole::query()
            ->with(['project:id,project_code,project_name_th,start_year,start_date'])
            ->get()
            ->map(function (FteRole $role) use ($displayText, $normalizeText, $skillFor) {
                $task = $displayText($role->task_detail);
                $job = $displayText($role->job_detail);
                $person = $displayText($role->name) ?: '-';
                $skill = $skillFor($task, $job, 'research project');

                return (object) [
                    'record_id' => 'fte_roles:' . (int) $role->id,
                    'source' => 'research',
                    'source_label' => 'Project FTE',
                    'year' => $this->fteYearFor($role),
                    'person' => $person,
                    'person_key' => $normalizeText($person),
                    'container_id' => 'project:' . (int) ($role->project_id ?? 0),
                    'container_name' => $displayText($role->project?->project_name_th) ?: ($role->project?->project_code ?: 'ไม่ระบุโครงการ'),
                    'task' => $task !== '' ? $task : ($job ?: 'ไม่ระบุภาระงาน'),
                    'job' => $job,
                    'source_category' => 'research project',
                    'skill' => $skill,
                    'hours' => (float) ($role->hours ?? 0),
                    'fte' => (float) ($role->fte_value ?? 0),
                    'start_date' => $displayText($role->start_date),
                    'end_date' => $displayText($role->end_date),
                ];
            })
            ->filter(fn ($row) => $row->person_key !== '' && trim((string) $row->task) !== '');

        $backOfficeRows = BackOfficeEntry::query()
            ->with(['task:id,backoffice_id,title,start_date,end_date', 'user:id,name'])
            ->get()
            ->map(function (BackOfficeEntry $entry) use ($displayText, $normalizeText, $skillFor) {
                $task = $displayText($entry->job_detail) ?: $displayText($entry->job_name);
                $job = $displayText($entry->job_name) ?: $displayText($entry->category);
                $person = $displayText($entry->person_name) ?: $displayText($entry->user?->name) ?: '-';
                $skill = $skillFor($task, $job, $displayText($entry->category));

                return (object) [
                    'record_id' => 'back_office_entries:' . (int) $entry->id,
                    'source' => 'backoffice',
                    'source_label' => 'Back Office FTE',
                    'year' => $this->extractBackOfficeEntryFiscalYearBe($entry),
                    'person' => $person,
                    'person_key' => $normalizeText($person),
                    'container_id' => 'backoffice:' . (int) ($entry->task_id ?? 0),
                    'container_name' => $displayText($entry->task?->title) ?: ($entry->task?->backoffice_id ?: 'Back Office'),
                    'task' => $task !== '' ? $task : ($job ?: 'ไม่ระบุภาระงาน'),
                    'job' => $job,
                    'source_category' => $displayText($entry->category),
                    'skill' => $skill,
                    'hours' => (float) ($entry->hours ?? 0),
                    'fte' => (float) ($entry->fte_value ?? 0),
                    'start_date' => $displayText($entry->start_date),
                    'end_date' => $displayText($entry->end_date),
                ];
            })
            ->filter(fn ($row) => $row->person_key !== '' && trim((string) $row->task) !== '');

        $rows = $researchRows
            ->merge($backOfficeRows)
            ->filter(fn ($row) => $inRange($row->year))
            ->values();

        $rawRows = $rows
            ->values()
            ->map(fn ($row, int $index) => [
                $index + 1,
                $row->source_label,
                $row->year,
                $row->person,
                data_get($row->skill, 'thai'),
                data_get($row->skill, 'item_label'),
                $row->task,
                $row->job,
                $row->source_category,
                $row->container_name,
                $row->container_id,
                $row->hours,
                $row->fte,
                $row->start_date,
                $row->end_date,
                $row->record_id,
                data_get($row->skill, 'key'),
                data_get($row->skill, 'item_key'),
            ])
            ->all();

        $dedupedObjects = $rows
            ->groupBy(fn ($row) => implode('|', [$row->source, $row->container_id, $row->person_key, (string) data_get($row->skill, 'key'), $normalizeText($row->task)]))
            ->map(function (Collection $group) {
                $first = clone $group->first();
                $first->hours = (float) $group->max(fn ($row) => (float) ($row->hours ?? 0));
                $first->fte = (float) $group->max(fn ($row) => (float) ($row->fte ?? 0));
                $first->deduped_count = $group->count();

                return $first;
            })
            ->values();

        $dedupedRows = $dedupedObjects
            ->map(fn ($row, int $index) => [
                $index + 1,
                $row->deduped_count,
                $row->source_label,
                $row->year,
                $row->person,
                data_get($row->skill, 'thai'),
                data_get($row->skill, 'item_label'),
                $row->task,
                $row->job,
                $row->source_category,
                $row->container_name,
                $row->container_id,
                $row->hours,
                $row->fte,
                $row->start_date,
                $row->end_date,
                data_get($row->skill, 'key'),
                data_get($row->skill, 'item_key'),
            ])
            ->all();

        $summaryRows = $dedupedObjects
            ->groupBy(fn ($row) => (string) data_get($row->skill, 'item_key'))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    data_get($first->skill, 'thai'),
                    data_get($first->skill, 'item_label'),
                    $group->count(),
                    $group->pluck('person_key')->unique()->count(),
                    $group->where('source', 'research')->pluck('container_id')->unique()->count(),
                    $group->where('source', 'backoffice')->pluck('container_id')->unique()->count(),
                    round((float) $group->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
                    round((float) $group->sum(fn ($row) => (float) ($row->fte ?? 0)), 3),
                ];
            })
            ->sortBy([[0, 'asc'], [1, 'asc']])
            ->values()
            ->all();

        return [
            'raw_rows' => $rawRows,
            'deduped_rows' => $dedupedRows,
            'summary_rows' => $summaryRows,
        ];
    }

    private function skillMatcher(callable $normalizeText): callable
    {
        $taxonomy = $this->fteSkillTaxonomy();

        return static function (string $task, string $job, string $sourceCategory = '') use ($normalizeText, $taxonomy): array {
            $taskText = $normalizeText($task);
            $text = $normalizeText($task . ' ' . $job . ' ' . $sourceCategory);
            $fallback = [
                'key' => 'logistics_admin',
                'label' => 'งานสนับสนุนปฏิบัติการ',
                'thai' => 'งานสนับสนุนปฏิบัติการ',
                'item_key' => 'logistics_admin_support_documents',
                'item_label' => 'งานบริการสิ่งสนับสนุนและเอกสาร',
                'tone' => 'logistics',
                'item_order' => 999,
            ];

            foreach ($taxonomy as $groupKey => $group) {
                foreach ($group['items'] as $index => $item) {
                    if ($taskText !== '' && Str::contains($taskText, $item['keywords'])) {
                        return [
                            'key' => $groupKey,
                            'label' => $group['label'],
                            'thai' => $group['label'],
                            'item_key' => $groupKey . '_' . $item['key'],
                            'item_label' => $item['label'],
                            'item_order' => $index,
                            'tone' => $group['tone'],
                        ];
                    }
                }
            }

            foreach ($taxonomy as $groupKey => $group) {
                foreach ($group['items'] as $index => $item) {
                    if (Str::contains($text, $item['keywords'])) {
                        return [
                            'key' => $groupKey,
                            'label' => $group['label'],
                            'thai' => $group['label'],
                            'item_key' => $groupKey . '_' . $item['key'],
                            'item_label' => $item['label'],
                            'item_order' => $index,
                            'tone' => $group['tone'],
                        ];
                    }
                }
            }

            return $fallback;
        };
    }

    private function fteSkillTaxonomy(): array
    {
        return [
            'scientific' => [
                'label' => 'งานวิชาการและหลักฐาน',
                'tone' => 'scientific',
                'items' => [
                    ['key' => 'review_literature', 'label' => 'review literature', 'keywords' => ['review literature', 'literature review', 'ทบทวนวรรณกรรม', 'systematic', 'meta']],
                    ['key' => 'proposal', 'label' => 'เขียนproposal', 'keywords' => ['proposal', 'โครงร่าง', 'ข้อเสนอโครงการ']],
                    ['key' => 'data_tool', 'label' => 'พัฒนาเครื่องมือและแนวทางในการเก็บข้อมูล', 'keywords' => ['พัฒนาเครื่องมือ', 'เครื่องมือ', 'แบบสอบถาม', 'แนวทางในการเก็บข้อมูล', 'เครื่องมือเก็บข้อมูล']],
                    ['key' => 'capacity_building', 'label' => 'อบรมพัฒนาศักยภาพ', 'keywords' => ['อบรม', 'พัฒนาศักยภาพ', 'training', 'workshop']],
                    ['key' => 'data_collection', 'label' => 'เก็บข้อมูล', 'keywords' => ['เก็บข้อมูล', 'ขอข้อมูล', 'field', 'survey', 'interview']],
                    ['key' => 'data_analysis', 'label' => 'วิเคราะห์ข้อมูล', 'keywords' => ['วิเคราะห์ข้อมูล', 'analysis', 'model', 'decision tree', 'markov', 'ต้นทุน', 'อรรถประโยชน์']],
                    ['key' => 'report_writing', 'label' => 'เขียนเล่มรายงาน', 'keywords' => ['เขียนเล่ม', 'รายงาน', 'เขียนรายงาน']],
                    ['key' => 'manuscript', 'label' => 'เขียนmanuscript', 'keywords' => ['manuscript', 'ต้นฉบับ', 'บทความ']],
                    ['key' => 'submit_journal', 'label' => 'submit journal', 'keywords' => ['submit journal', 'submission', 'journal', 'ตีพิมพ์']],
                    ['key' => 'presentation', 'label' => 'นำเสนอผลงานวิจัย Oral/Poster presentation', 'keywords' => ['presentation', 'oral', 'poster', 'นำเสนอ']],
                    ['key' => 'consultant', 'label' => 'ที่ปรึกษาโครงการ', 'keywords' => ['ที่ปรึกษาโครงการ', 'consultant', 'consultation']],
                    ['key' => 'concept_note', 'label' => 'พัฒนา Scientific concept note / training program', 'keywords' => ['scientific concept', 'concept note', 'training program']],
                    ['key' => 'research_consult_service', 'label' => 'งานบริการวิชาการให้คำปรึกษางานวิจัย', 'keywords' => ['บริการวิชาการ', 'ให้คำปรึกษางานวิจัย']],
                    ['key' => 'curriculum_design', 'label' => 'งานออกแบบหลักสูตรและการเรียนการสอน', 'keywords' => ['ออกแบบหลักสูตร', 'หลักสูตร']],
                    ['key' => 'teaching', 'label' => 'งานจัดการเรียนการสอน', 'keywords' => ['จัดการเรียนการสอน', 'การเรียนการสอน', 'lecture', 'สอน']],
                    ['key' => 'mentorship', 'label' => 'งานพัฒนาศักยภาพและให้คำปรึกษานักศึกษา (Mentorship)', 'keywords' => ['mentorship', 'นักศึกษา', 'ให้คำปรึกษานักศึกษา']],
                    ['key' => 'child_development', 'label' => 'งานด้านการพัฒนาเด็ก', 'keywords' => ['พัฒนาเด็ก']],
                ],
            ],
            'management' => [
                'label' => 'งานบริหารโครงการและกำกับเอกสาร',
                'tone' => 'management',
                'items' => [
                    ['key' => 'coordination', 'label' => 'ติดต่อประสานงาน', 'keywords' => ['ติดต่อประสานงาน', 'ประสานงาน', 'coordinator']],
                    ['key' => 'stakeholder_meeting', 'label' => 'ประชุมผู้มีส่วนได้ส่วนเสีย (Stakeholder meeting)', 'keywords' => ['stakeholder', 'ผู้มีส่วนได้ส่วนเสีย']],
                    ['key' => 'funding_submit', 'label' => 'ดำเนินการเอกสารขอทุน/submit,ทุน', 'keywords' => ['ขอทุน', 'submit ทุน', 'submit,ทุน', 'ทุน']],
                    ['key' => 'irb_submit', 'label' => 'ดำเนินการเอกสารขอirb/submit irb', 'keywords' => ['irb', 'จริยธรรม']],
                    ['key' => 'progress_report', 'label' => 'จัดทำรายงานความก้าวหน้า', 'keywords' => ['รายงานความก้าวหน้า', 'progress report']],
                    ['key' => 'extension', 'label' => 'ยื่นขอขยายระยะเวลาโครงการ', 'keywords' => ['ขยายระยะเวลา']],
                    ['key' => 'revise_funder_report', 'label' => 'ส่งเล่มรายงาน และ แก้ไขตามข้อเสนอแนะจากผู้ให้ทุน', 'keywords' => ['แก้ไขตามข้อเสนอแนะ', 'ผู้ให้ทุน', 'ส่งเล่มรายงาน']],
                    ['key' => 'final_report', 'label' => 'ส่งส่งรายงานฉบับสมบูรณ์', 'keywords' => ['รายงานฉบับสมบูรณ์', 'final report']],
                    ['key' => 'budget_close', 'label' => 'สรุปงบประมาณที่ใช้จริง และปิดโครงการ', 'keywords' => ['สรุปงบประมาณ', 'ปิดโครงการ']],
                    ['key' => 'material_publication_payment', 'label' => 'เบิกใบตรวจวัสดีพิมพ์/ค่าตอบแทนตีพิมพ์', 'keywords' => ['วัสดุพิมพ์', 'ค่าตอบแทนตีพิมพ์']],
                    ['key' => 'grant_disbursement', 'label' => 'เบิกงบประมาณทุน', 'keywords' => ['เบิกงบประมาณทุน', 'เบิกทุน']],
                    ['key' => 'internal_support', 'label' => 'งานบริการ สนับสนุน และประสานงานภายในหน่วย', 'keywords' => ['สนับสนุน', 'ภายในหน่วย']],
                    ['key' => 'admin_coordination', 'label' => 'งานติดต่อประสานงาน', 'keywords' => ['งานติดต่อประสานงาน']],
                    ['key' => 'meeting_minutes', 'label' => 'งานด้านการลงวาระและรายงานการประชุม', 'keywords' => ['วาระ', 'รายงานการประชุม', 'minutes']],
                ],
            ],
            'it_communication' => [
                'label' => 'งานสื่อสาร ระบบข้อมูล และเทคนิค',
                'tone' => 'it',
                'items' => [
                    ['key' => 'public_relations', 'label' => 'ประชาสัมพันธ์', 'keywords' => ['ประชาสัมพันธ์', 'pr']],
                    ['key' => 'communication_assets', 'label' => 'จัดทำสื่อ / Communication Assets', 'keywords' => ['communication assets', 'จัดทำสื่อ', 'สื่อ']],
                    ['key' => 'research_system', 'label' => 'สร้างโปรแกรม ฐานข้อมูล และ โปรแกรมที่เกี่ยวข้องกับงานวิจัย', 'keywords' => ['โปรแกรม', 'ฐานข้อมูล', 'database', 'website', 'ระบบ']],
                    ['key' => 'technical_support', 'label' => 'IT and Technical support', 'keywords' => ['it', 'technical', 'full stack', 'ux/ui', 'support']],
                    ['key' => 'communication_work', 'label' => 'งานด้านการสื่อสาร และประชาสัมพันธ์', 'keywords' => ['การสื่อสาร', 'สื่อสาร']],
                    ['key' => 'media_design', 'label' => 'งานด้านออกแบบ และผลิตสื่อ', 'keywords' => ['ออกแบบ', 'ผลิตสื่อ', 'branding']],
                    ['key' => 'technology_information', 'label' => 'งานด้านการพัฒนาเทคโนโลยีและสารสนเทศ', 'keywords' => ['เทคโนโลยี', 'สารสนเทศ']],
                ],
            ],
            'logistics_admin' => [
                'label' => 'งานสนับสนุนปฏิบัติการ',
                'tone' => 'logistics',
                'items' => [
                    ['key' => 'knowledge_sharing', 'label' => 'จัดกิจกรรมการแลกเปลี่ยนเรียนรู้ (Knowledge Sharing)', 'keywords' => ['knowledge sharing', 'แลกเปลี่ยนเรียนรู้']],
                    ['key' => 'non_research_meeting', 'label' => 'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย', 'keywords' => ['จัดประชุม']],
                    ['key' => 'support_documents', 'label' => 'งานบริการสิ่งสนับสนุนและเอกสาร', 'keywords' => ['สิ่งสนับสนุน', 'เอกสาร']],
                    ['key' => 'document_service', 'label' => 'งานบริการด้านเอกสาร', 'keywords' => ['บริการด้านเอกสาร', 'หนังสือ']],
                    ['key' => 'human_resources', 'label' => 'งานบริการด้านทรัพยากรบุคคล', 'keywords' => ['ทรัพยากรบุคคล', 'hr']],
                    ['key' => 'event_exhibition', 'label' => 'งานจัดงานหรือเข้าร่วมงานนิทรรศการ', 'keywords' => ['นิทรรศการ', 'จัดงาน', 'event']],
                ],
            ],
        ];
    }

    private function fteYearFor(FteRole $role): ?int
    {
        foreach ([$role->start_date, $role->end_date] as $value) {
            $date = $this->parseBackOfficeDate($value);
            if ($date) {
                return $date->year + 543;
            }

            $year = $this->normalizeYearFilter($value);
            if ($year !== null) {
                return $year;
            }
        }

        return $role->project ? $this->extractProjectYearBe($role->project) : null;
    }

    private function normalizeYearFilter($raw): ?int
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/\d{4}/', $raw, $matches)) {
            $year = (int) $matches[0];

            return $year < 2400 ? $year + 543 : $year;
        }

        return null;
    }

    private function extractProjectYearBe(Project $project): ?int
    {
        $startYear = $this->normalizeYearFilter($project->start_year);
        if ($startYear !== null) {
            return $startYear;
        }

        $startDate = trim((string) ($project->start_date ?? ''));
        if ($startDate === '') {
            return null;
        }

        try {
            $year = Carbon::parse($startDate)->year;

            return $year < 2400 ? $year + 543 : $year;
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractBackOfficeEntryFiscalYearBe(BackOfficeEntry $entry): ?int
    {
        $date = $this->parseBackOfficeDate($entry->start_date)
            ?: $this->parseBackOfficeDate($entry->end_date)
            ?: $this->parseBackOfficeDate($entry->task?->start_date)
            ?: $this->parseBackOfficeDate($entry->task?->end_date);

        if (! $date) {
            return null;
        }

        $year = (int) $date->format('Y');
        if ((int) $date->format('n') >= 10) {
            $year++;
        }

        return $year + 543;
    }

    private function parseBackOfficeDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (\Throwable) {
                // Try the next supported date format.
            }
        }

        return null;
    }

    private function writeWorkbook(string $path, array $sheets): void
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'fte_skill_xlsx_');
        $zip = new ZipArchive();
        $zip->open($tmpPath, ZipArchive::OVERWRITE);

        $sheetCount = count($sheets);
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $contentTypes .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        $contentTypes .= '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');

        $workbookSheets = '';
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        $index = 1;
        foreach ($sheets as $name => $sheet) {
            $safeName = $this->xlsxEscapeAttribute(Str::limit((string) $name, 31, ''));
            $workbookSheets .= '<sheet name="' . $safeName . '" sheetId="' . $index . '" r:id="rId' . $index . '"/>';
            $workbookRels .= '<Relationship Id="rId' . $index . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $index . '.xml"/>';
            $zip->addFromString('xl/worksheets/sheet' . $index . '.xml', $this->buildWorksheet($sheet['headers'], collect($sheet['rows'])));
            $index++;
        }

        $workbookRels .= '<Relationship Id="rId' . $index . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' . $workbookSheets . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDDEBFF"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf></cellXfs></styleSheet>');
        $zip->close();

        rename($tmpPath, $path);
    }

    private function buildWorksheet(array $headers, Collection $rows): string
    {
        $rowIndex = 1;
        $xmlRows = [$this->xlsxRow($rowIndex++, $headers, 1)];

        foreach ($rows as $row) {
            $xmlRows[] = $this->xlsxRow($rowIndex++, array_values((array) $row), 2);
        }

        $columnCount = max(count($headers), 1);
        $lastColumn = $this->xlsxColumnName($columnCount);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . collect(range(1, $columnCount))->map(fn ($i) => '<col min="' . $i . '" max="' . $i . '" width="' . ($i <= 2 ? 18 : 28) . '" customWidth="1"/>')->implode('') . '</cols>'
            . '<sheetData>' . implode('', $xmlRows) . '</sheetData>'
            . '<autoFilter ref="A1:' . $lastColumn . max(1, $rowIndex - 1) . '"/>'
            . '</worksheet>';
    }

    private function xlsxRow(int $rowIndex, array $values, int $style = 0): string
    {
        $cells = [];
        foreach (array_values($values) as $columnIndex => $value) {
            $cell = $this->xlsxColumnName($columnIndex + 1) . $rowIndex;
            $styleAttribute = $style > 0 ? ' s="' . $style . '"' : '';
            $cells[] = '<c r="' . $cell . '" t="inlineStr"' . $styleAttribute . '><is><t xml:space="preserve">' . $this->xlsxEscapeText($value) . '</t></is></c>';
        }

        return '<row r="' . $rowIndex . '">' . implode('', $cells) . '</row>';
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function xlsxEscapeText($value): string
    {
        $text = trim((string) ($value ?? ''));
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xlsxEscapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
