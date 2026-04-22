<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
   {{-- คนที่ดาวน์โหลด (user ปัจจุบัน) --}}
<meta name="author" content="{{ $user ? trim(($user->prefix ?? '') . ' ' . $user->name) : '' }}">
{{-- ผู้สร้างโครงการ --}}
<meta name="subject" content="{{ $project->createdBy ? trim(($project->createdBy->prefix ?? '') . ' ' . $project->createdBy->name) : '' }}">

    <title>{{ $project->project_name_th }}</title>
    <style>
        @page {
            header: html_Header;
            footer: html_Footer;
            margin-top: 90px;
            margin-bottom: 70px;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 12.5pt;
            line-height: 1.45;
            color: #222;
        }
        .section-title {
            background: #F8FAFC;
            color: #0F172A;
            padding: 6px 14px 6px 12px;
            margin: 18px 0 8px;
            font-weight: bold;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #dde6f6;
            padding: 7px 10px;
            vertical-align: top;
            font-size: 1em;
        }
        thead {
            background: #F1F5F9;
            color: #0F172A;
        }
        th {
            font-size: 1.02em;
            font-weight: bold;
        }
        ul {
            margin: 0 0 12px 18px;
            padding: 0;
        }
        li {
            margin-bottom: 4px;
        }
        .nowrap { white-space: nowrap; }
        .check {
            text-align: center;
            font-size: 1.2em;
            color: #009900;
        }
        .fte-bar-wrap {
            width: 100%;
            height: 10px;
            background: #E6F3F2;
            border-radius: 6px;
            overflow: hidden;
        }
        .fte-bar {
            height: 10px;
            background: #0F766E;
        }
        .outputs-table {
            table-layout: fixed;
        }
        .outputs-table th,
        .outputs-table td {
            word-break: break-word;
            white-space: normal;
        }
        .outputs-col-index {
            width: 5%;
            text-align: center;
        }
        .outputs-col-title {
            width: 35%;
        }
        .outputs-col-type {
            width: 14%;
        }
        .outputs-col-detail {
            width: 31%;
        }
        .outputs-col-date {
            width: 15%;
        }
        .output-title {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 4px;
        }
        .output-subline {
            margin-top: 4px;
            color: #334155;
            line-height: 1.35;
        }
        .output-subline strong {
            color: #0f172a;
        }
        .output-list {
            margin-top: 6px;
        }
        .output-card {
            border: 1px solid #dbe4f2;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #fff;
            page-break-inside: avoid;
        }
        .output-card-header {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .output-card-index,
        .output-card-date {
            display: table-cell;
            vertical-align: top;
            white-space: nowrap;
        }
        .output-card-index {
            width: 36px;
            font-weight: bold;
            color: #475569;
            padding-right: 10px;
        }
        .output-card-main {
            display: table-cell;
            vertical-align: top;
        }
        .output-card-date {
            width: 118px;
            text-align: right;
            color: #475569;
            font-size: 0.95em;
            padding-left: 10px;
        }
        .output-meta {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #dbe4f2;
        }
        .output-meta-row {
            margin-top: 4px;
            line-height: 1.35;
        }
        .output-type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.95em;
            font-weight: bold;
        }

        .pdf-project-header {
            color: #0f172a;
            line-height: 1.2;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }

        .project-name-adaptive {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }
    </style>
</head>
<body>
@php
    function th_date_or_dash($value) {
        if (!$value) return '-';
        try {
            \Carbon\Carbon::setLocale('th');
            $d = \Carbon\Carbon::parse($value);
            return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
        } catch (\Exception $e) {
            return $value ?: '-';
        }
    }
    function text_or_dash($value) {
        $txt = trim((string)($value ?? ''));
        return $txt !== '' ? $txt : '-';
    }
    if (!function_exists('adaptive_project_font_pt')) {
        function adaptive_project_font_pt($value) {
            $len = mb_strlen(trim((string) ($value ?? '')));
            if ($len >= 170) return 10.5;
            if ($len >= 140) return 11.5;
            if ($len >= 110) return 12.5;
            if ($len >= 85) return 13.5;
            return 16;
        }
    }

    $projectTitle = text_or_dash($project->project_name_th);
    $projectTitleFontPt = adaptive_project_font_pt($projectTitle);
@endphp
{{-- HEADER --}}
<htmlpageheader name="Header">
    <div class="pdf-project-header" style="font-size: {{ $projectTitleFontPt }}pt; padding-left: 10px;">
        {{ $projectTitle }}
    </div>
</htmlpageheader>

{{-- FOOTER --}}
<htmlpagefooter name="Footer">
    @php
        use Carbon\Carbon;
        $carbon = Carbon::now()->setTimezone('Asia/Bangkok')->locale('th');
        $carbon->settings(['formatFunction' => 'translatedFormat']);
        $thaiDateTime = $carbon->format('j F Y เวลา H:i');
    @endphp

    <div style="width: 100%; font-size: 12pt; border: none; display: flex; justify-content: space-between; align-items: flex-end; padding: 4px 0;">
        <div style="">
           
        </div>
        <div style="text-align: right;">
            หน้า {PAGENO} จาก {nbpg} <br>
            โดย {{ $user->prefix}} {{ $user->name ?? 'ผู้ใช้ระบบ' }} : {{ $thaiDateTime }}<br>
        </div>
    </div>
</htmlpagefooter>



    {{-- SECTION A --}}
    <div class="section-title">A: ข้อมูลโครงการ</div>
    <ul>
        <li><strong>ชื่อโครงการ (TH):</strong> <span class="project-name-adaptive">{{ text_or_dash($project->project_name_th) }}</span></li>
        <li><strong>ชื่อโครงการ (EN):</strong> <span class="project-name-adaptive">{{ text_or_dash($project->project_name_en) }}</span></li>
        <li><strong>ประเภท:</strong> {{ text_or_dash($project->project_type) }}</li>
        <li><strong>ลักษณะ:</strong> {{ text_or_dash($project->project_list) }}</li>
        <li><strong>Theme:</strong> {{ text_or_dash($project->theme) }}</li>
        <li><strong>เอกสารโครงการ (proposal , IRB) :</strong> {{ text_or_dash($project->shortcut_link) }}</li>
        <li><strong>ขนาด:</strong> {{ text_or_dash($project->size) }}</li>
        <li><strong>รหัสโครงการ R:</strong> {{ text_or_dash($project->r_code) }}</li>
        <li><strong>ปีโปรเจค:</strong> {{ text_or_dash($project->start_year) }}</li>
        <li><strong>สถานะ:</strong> {{ text_or_dash($project->planning_status) }}</li>
    </ul>

    {{-- SECTION B --}}
    <div class="section-title">B: ระยะเวลาดำเนินโครงการ</div>
    <table>
        <thead>
            <tr>
                <th>วันที่โครงการเริ่มทำงานของทีมวิจัย</th>
                <th>วันที่สิ้นสุดการทำงานของทีมวิจัย</th>
                <th>BA001 - วันที่เริ่มเขียน Proposal</th>
                <th>BC101 - วันที่สัญญาอนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ th_date_or_dash($project->team_start_date) }}</td>
                <td>{{ th_date_or_dash($project->team_end_date) }}</td>
                <td>{{ th_date_or_dash($project->proposal_date) }}</td>
                <td>{{ th_date_or_dash($project->contract_date) }}</td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th>BC102 - วันที่โครงการเริ่ม (ตามสัญญา)</th>
                <th>BC103 - วันที่สิ้นสุด (ตามสัญญา)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ th_date_or_dash($project->start_date) }}</td>
                <td>{{ th_date_or_dash($project->end_date) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SECTION C --}}
    <div class="section-title">C: ข้อมูลแหล่งทุน</div>
    <table>
        <thead>
            <tr>
                <th>DA102 - ประเภทแหล่งทุน</th>
                <th>DA103 - ชื่อแหล่งทุน</th>
                <th>DA104 - รัฐบาล / เอกชน / มูลนิธิ</th>
                <th>DA105 - ชื่อทุน</th>
                <th>DA106 - งบประมาณ (บาท)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ text_or_dash($project->funding_type) }}</td>
                <td>{{ text_or_dash($project->funding_source) }}</td>
                <td>{{ text_or_dash($project->funding_org_type) }}</td>
                <td>{{ text_or_dash($project->funding_name) }}</td>
                <td>{{ $project->budget !== null ? number_format($project->budget, 2) : '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- SECTION D: Roles and COPI --}}
    <div class="section-title">D: รายชื่อผู้ร่วมวิจัย</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ลำดับ</th>
                <th>ชื่อ</th>
                <th>สังกัด</th>
                <th>บทบาท</th>
            </tr>
        </thead>
        <tbody>
            {{-- PI --}}
            <tr>
                <td class="nowrap">1</td>
                <td>{{ text_or_dash($project->pi_name) }}</td>
                <td>{{ text_or_dash($project->pi_affiliation) }}</td>
                <td>PI</td>
            </tr>
            {{-- COPI --}}
            @forelse ($project->copis as $i => $copi)
                <tr>
                    <td class="nowrap">{{ $i+2 }}</td>
                    <td>{{ text_or_dash($copi->name) }}</td>
                    <td>{{ text_or_dash($copi->affiliation) }}</td>
                    <td>Co-PI</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มี Co-PI</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION D2: Co-Researcher --}}
    <div class="section-title">D2: Co-Researcher</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ลำดับ</th>
                <th>ชื่อ</th>
                <th>สังกัด</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @forelse ($project->coResearches as $co)
                <tr>
                    <td class="nowrap">{{ $i++ }}</td>
                    <td>{{ text_or_dash($co->name) }}</td>
                    <td>{{ text_or_dash($co->affiliation) }}</td>
                    <td>{{ text_or_dash($co->unit_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if (false)
    @php
        $latestProgressReport = ($project->progressReports ?? collect())->first();
    @endphp
    <div class="section-title">E: ความก้าวหน้าของการดำเนินงาน</div>
    @if ($latestProgressReport)
        @php
            $progressTitle = text_or_dash($latestProgressReport->progress_title ?: 'ความก้าวหน้าของการดำเนินงาน');
            $progressSubtitle = text_or_dash($latestProgressReport->progress_subtitle);
            $progressRows = collect($latestProgressReport->progress_rows ?? [])
                ->filter(function ($row) {
                    return !empty(trim((string) ($row['plan'] ?? ''))) ||
                        !empty(trim((string) ($row['actual'] ?? ''))) ||
                        !empty(trim((string) ($row['alignment'] ?? '')));
                })
                ->values();
            $issueRows = collect($latestProgressReport->issue_rows ?? [])
                ->filter(function ($row) {
                    return !empty(trim((string) ($row['problem'] ?? ''))) ||
                        !empty(trim((string) ($row['solution'] ?? ''))) ||
                        !empty(trim((string) ($row['consultation'] ?? '')));
                })
                ->values();
        @endphp

        <table>
            <tbody>
                <tr>
                    <td style="width: 50%;">
                        <strong>ผู้รายงาน:</strong> {{ text_or_dash($latestProgressReport->submitter_name) }}
                    </td>
                    <td style="width: 50%;">
                        <strong>วันที่รายงาน:</strong> {{ th_date_or_dash($latestProgressReport->submit_date) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>หัวข้อ:</strong> {{ $progressTitle }}
                        @if ($progressSubtitle !== '-')
                            <br><strong>คำอธิบาย:</strong> {{ $progressSubtitle }}
                        @endif
                        @if (!empty($latestProgressReport->file_info))
                            <br><strong>ไฟล์/ลิงก์:</strong> {{ text_or_dash($latestProgressReport->file_info) }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>แผนการดำเนินงาน</th>
                    <th>การดำเนินงานจริง</th>
                    <th>ความสอดคล้องของการดำเนินงานจริงกับแผนการดำเนินงาน</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($progressRows as $row)
                    <tr>
                        <td>{{ text_or_dash($row['plan'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['actual'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['alignment'] ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="check">ไม่มีข้อมูลกิจกรรม</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>ปัญหาอุปสรรค</th>
                    <th>แนวทางการแก้ปัญหา</th>
                    <th>ประเด็นขอคำปรึกษาจากที่ประชุม</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($issueRows as $row)
                    <tr>
                        <td>{{ text_or_dash($row['problem'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['solution'] ?? '') }}</td>
                        <td>{{ text_or_dash($row['consultation'] ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="check">ไม่มีข้อมูลปัญหาอุปสรรค</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <tbody>
                <tr>
                    <td class="check">ไม่มีข้อมูลความก้าวหน้าของการดำเนินงาน</td>
                </tr>
            </tbody>
        </table>
    @endif
    @endif

    {{-- SECTION F: FTE --}}
    <div class="section-title">F: รายชื่อผู้มีส่วนร่วม (FTE)</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>ชื่อ</th>
                <th>กลุ่ม</th>
                <th>ภาระงาน</th>
                <th>รายละเอียดภารกิจ</th>
                <th>วันเริ่ม</th>
                <th>วันสิ้นสุด</th>
                <th class="nowrap">FTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->fteRoles as $i => $fte)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($fte->name) }}</td>
                    <td>{{ text_or_dash($fte->role_group) }}</td>
                    <td>{{ text_or_dash($fte->job_detail) }}</td>
                    <td>{{ text_or_dash($fte->task_detail) }}</td>
                    <td>
                        {{ th_date_or_dash($fte->start_date) }}
                    </td>
                    <td>
                        {{ th_date_or_dash($fte->end_date) }}
                    </td>
                    <td class="nowrap">{{ text_or_dash($fte->fte_value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">F.1: สรุป FTE รายเดือน</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">เดือน</th>
                <th class="nowrap">FTE รวม</th>
                <th>กราฟสรุป</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($fteMonthlySummary ?? [] as $row)
                @php
                    $monthLabel = $row['month'] ?? '-';
                    if (preg_match('/^\d{4}-\d{2}$/', $monthLabel)) {
                        $d = \Carbon\Carbon::createFromFormat('Y-m', $monthLabel)->locale('th');
                        $monthLabel = $d->translatedFormat('F') . ' ' . ($d->year + 543);
                    }
                    $total = (float) ($row['total'] ?? 0);
                    $percent = ($fteMonthlyMax ?? 0) > 0 ? ($total / $fteMonthlyMax) * 100 : 0;
                @endphp
                <tr>
                    <td class="nowrap">{{ $monthLabel }}</td>
                    <td class="nowrap">{{ number_format($total, 6) }}</td>
                    <td>
                        <div class="fte-bar-wrap">
                            <div class="fte-bar" style="width: {{ round($percent, 2) }}%;"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">F.2: FTE รวม 3 ปีล่าสุด</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">ปี (พ.ศ.)</th>
                <th class="nowrap">FTE รวม</th>
            </tr>
        </thead>
        <tbody>
            @php
                $yearLabels = $fteYearLabels ?? [];
                $yearData = $fteYearData ?? [];
            @endphp
            @forelse ($yearLabels as $i => $label)
                <tr>
                    <td class="nowrap">{{ $label }}</td>
                    <td class="nowrap">{{ number_format($yearData[$i] ?? 0, 6) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION G: IRB --}}
    <div class="section-title">G: รหัส IRB</div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">#</th>
                <th>รหัส</th>
                <th>หน่วยงาน</th>
                <th>วันหมดอายุ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->irbs as $i => $irb)
                <tr>
                    <td class="nowrap">{{ $i+1 }}</td>
                    <td>{{ text_or_dash($irb->code) }}</td>
                    <td>{{ text_or_dash($irb->source) }}</td>
                    <td>
                        {{ th_date_or_dash($irb->exp_date) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="check">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECTION H: Outputs --}}
    <div class="section-title">H: ผลงานวิจัย (Outputs)</div>
    <div class="output-list">
        @forelse ($project->outputs as $i => $output)
            <div class="output-card">
                <div class="output-card-header">
                    <div class="output-card-index">{{ $i + 1 }}.</div>
                    <div class="output-card-main">
                        <div class="output-title">{{ text_or_dash($output->title) }}</div>
                        <div class="output-type-badge">{{ text_or_dash($output->type) }}</div>
                    </div>
                    <div class="output-card-date">{{ th_date_or_dash($output->year) }}</div>
                </div>

                <div class="output-meta">
                    <div class="output-meta-row">
                        <strong>รายละเอียด/ลิงก์:</strong>
                        {{ text_or_dash($output->project_output ?? $output->output ?? '') }}
                    </div>

                    @if (!empty($output->journal) || !empty($output->quartile) || $output->impact_factor !== null)
                        <div class="output-meta-row">
                            <strong>Journal:</strong> {{ text_or_dash($output->journal) }}
                        </div>
                        <div class="output-meta-row">
                            <strong>Quartile:</strong> {{ text_or_dash($output->quartile) }}
                            |
                            <strong>Impact Factor:</strong> {{ text_or_dash($output->impact_factor) }}
                        </div>
                    @endif

                    @if (!empty($output->authors) && $output->authors->count())
                        <div class="output-meta-row">
                            <strong>{{ str_starts_with((string) ($output->type ?? ''), 'EA118') ? 'Award Recipients' : 'Authors' }}:</strong>
                            {{ $output->authors->pluck('name')->filter()->implode(', ') }}
                        </div>
                    @endif

                    @if (!empty($output->conferences) && $output->conferences->count())
                        <div class="output-meta-row">
                            <strong>Conferences:</strong>
                            {{ $output->conferences->pluck('name')->filter()->implode(', ') }}
                        </div>
                    @endif

                    @if (!empty($output->book_title) || !empty($output->publisher_name) || !empty($output->chapter_pages))
                        <div class="output-meta-row">
                            <strong>ชื่อหนังสือ:</strong> {{ text_or_dash($output->book_title) }}
                        </div>
                        <div class="output-meta-row">
                            <strong>ชื่อสำนักพิมพ์:</strong> {{ text_or_dash($output->publisher_name) }}
                            |
                            <strong>ชื่อบท / เลขหน้า:</strong> {{ text_or_dash($output->chapter_pages) }}
                        </div>
                    @endif

                    @if (!empty($output->bookAuthors) && $output->bookAuthors->count())
                        <div class="output-meta-row">
                            <strong>Book Authors:</strong>
                            {{ $output->bookAuthors->pluck('name')->filter()->implode(', ') }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <table>
                <tbody>
                    <tr>
                        <td class="check">ไม่มีข้อมูล</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
    </div>

    @include('projects.partials.progress_section_pdf', ['project' => $project])

    {{-- SECTION J: ยุทธศาสตร์ --}}
    <div class="section-title">J: ยุทธศาสตร์ Siriraj Health Policy</div>
    <table>
        <thead>
            <tr>
                <th>หมวด</th>
                <th>รายการ</th>
                <th>มี</th>
            </tr>
        </thead>
        <tbody>
            @php
                $strategies = [
                    '1' => [
                        'label' => 'AA501: Capacity Building',
                        'items' => [
                            '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                            '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                            '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                            '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                            '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                        ],
                    ],
                    '2' => [
                        'label' => 'AA502: Excellence Research',
                        'items' => [
                            '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                            '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                            '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                        ],
                    ],
                    '3' => [
                        'label' => 'AA503: Health Policy Linkage',
                        'items' => [
                            '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                            '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                            '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                            '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                            '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                        ],
                    ],
                    '4' => [
                        'label' => 'AA504: Partnership Engagement',
                        'items' => [
                            '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                            '2' => '4.2 การสร้างความร่วมมือฯ',
                        ],
                    ],
                    '5' => [
                        'label' => 'AA505: System Support',
                        'items' => [
                            '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                            '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                        ],
                    ],
                ];
            @endphp
            @foreach ($strategies as $sKey => $strategy)
                @foreach ($strategy['items'] as $iKey => $desc)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ count($strategy['items']) }}" style="font-weight:bold;">{{ $strategy['label'] }}</td>
                    @endif
                    <td>{{ $desc }}</td>
              <td class="check font-weight-bold" style="color: red;">
    {{-- Check if the project has this strategy item --}}
    {!! !empty($project->{'strategy_'.$sKey.'_'.$iKey}) ? '<strong style="color:red;">X</strong>' : '' !!}
</td>

                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- PAGE NUMBER (for wkhtmltopdf auto replace) --}}
    <script type="text/javascript">
        (function() {
            function subst() {
                var vars = {};
                var x = window.location.search.substring(1).split('&');
                for (var i in x) {
                    var z = x[i].split('=', 2);
                    vars[z[0]] = unescape(z[1]);
                }
                var y = ['page', 'topage', 'frompage', 'topage'];
                for (var i in y) {
                    var els = document.getElementsByClassName(y[i]);
                    for (var j = 0; j < els.length; ++j) {
                        if (y[i] === 'page') {
                            els[j].textContent = vars['page'];
                        }
                        if (y[i] === 'topage') {
                            els[j].textContent = vars['topage'];
                        }
                    }
                }
            }
            window.onload = subst;
        })();
    </script>
</body>
</html>
