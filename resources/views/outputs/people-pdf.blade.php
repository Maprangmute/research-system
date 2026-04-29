<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Personal Output Report</title>
    <style>
        @page {
            margin-top: 92px;
            margin-bottom: 58px;
            header: html_Header;
            footer: html_Footer;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 13pt;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
        }
        h1, h2, h3, h4, p {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .muted {
            color: #64748b;
        }
        .report-shell {
            padding-top: 4px;
        }
        .hero-card {
            border: 1px solid #dbe7f5;
            border-radius: 22px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 18px 20px;
            margin-bottom: 14px;
        }
        .hero-table td {
            vertical-align: top;
            border: none;
        }
        .hero-kicker {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #64748b;
        }
        .hero-name {
            margin-top: 4px;
            font-size: 24pt;
            font-weight: bold;
            line-height: 1.08;
            color: #0f172a;
        }
        .hero-sub {
            margin-top: 3px;
            font-size: 13pt;
            color: #475569;
        }
        .hero-meta {
            margin-top: 8px;
            font-size: 11.5pt;
            color: #64748b;
        }
        .avatar-wrap {
            width: 108px;
        }
        .avatar {
            width: 92px;
            height: 92px;
            text-align: center;
        }
        .avatar img {
            display: block;
            width: 92px;
            height: auto;
            max-height: 92px;
            border-radius: 20px;
            border: 1px solid #dbe7f5;
        }
        .summary-panel {
            margin-bottom: 14px;
        }
        .summary-box {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .summary-box td {
            width: 25%;
            vertical-align: top;
            border: 1px solid #dde6f6;
            border-radius: 16px;
            background: #ffffff;
            padding: 10px 12px;
            min-height: 74px;
        }
        .summary-box td.metric-card--project {
            background: #f8fbff;
            border-color: #d8e7fb;
        }
        .summary-box td.metric-card--research {
            background: #f7f5ff;
            border-color: #ddd6fe;
        }
        .summary-box td.metric-card--presentation {
            background: #f0fdfa;
            border-color: #b8f1e5;
        }
        .summary-box td.metric-card--textbook {
            background: #fff7ed;
            border-color: #fed7aa;
        }
        .summary-box td.metric-card--award {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .metric-label {
            font-size: 11pt;
            color: #475569;
        }
        .metric-value {
            margin-top: 2px;
            font-size: 21pt;
            font-weight: bold;
            line-height: 1.05;
            color: #0f172a;
        }
        .metric-note {
            margin-top: 2px;
            font-size: 10.5pt;
            color: #64748b;
        }
        .section {
            margin-top: 16px;
        }
        .section-head {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .section-head--projects {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        .section-head--research {
            background: #f5f3ff;
            border-color: #ddd6fe;
        }
        .section-head--presentation {
            background: #ecfeff;
            border-color: #bae6fd;
        }
        .section-head--textbook {
            background: #fff7ed;
            border-color: #fed7aa;
        }
        .section-head--award {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .section-kicker {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
        }
        .section-title {
            margin-top: 1px;
            font-size: 17pt;
            font-weight: bold;
            color: #0f172a;
        }
        .section-subtitle {
            margin-top: 1px;
            font-size: 11pt;
            color: #64748b;
        }
        .data-table {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            vertical-align: top;
        }
        .data-table thead th {
            font-size: 11pt;
            font-weight: bold;
            color: #334155;
            text-align: left;
        }
        .data-table tbody tr:nth-child(even) td {
            background: #fbfdff;
        }
        .table-projects thead th {
            background: #eff6ff;
        }
        .table-research thead th {
            background: #f5f3ff;
        }
        .table-presentation thead th {
            background: #ecfeff;
        }
        .table-textbook thead th {
            background: #fff7ed;
        }
        .table-award thead th {
            background: #fffbeb;
        }
        .title-main {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.22;
        }
        .title-sub {
            margin-top: 2px;
            font-size: 10.75pt;
            color: #64748b;
            line-height: 1.3;
        }
        .chip {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10.5pt;
            font-weight: bold;
            margin: 0 4px 4px 0;
            border: 1px solid #cbd5e1;
            color: #334155;
            background: #f8fafc;
        }
        .chip--project {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .chip--back-office {
            border-color: #fde68a;
            background: #fffbeb;
            color: #b45309;
        }
        .chip--extra {
            border-color: #fbcfe8;
            background: #fdf2f8;
            color: #be185d;
        }
        .chip--pi {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .chip--co-pi {
            border-color: #fde68a;
            background: #fffbeb;
            color: #b45309;
        }
        .chip--co-researcher {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #15803d;
        }
        .chip--first-author {
            border-color: #c7d2fe;
            background: #eef2ff;
            color: #4338ca;
        }
        .chip--co-author {
            border-color: #fed7aa;
            background: #fff7ed;
            color: #c2410c;
        }
        .chip--corresponding-author {
            border-color: #a7f3d0;
            background: #ecfdf5;
            color: #047857;
        }
        .empty-card {
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 12px;
            color: #64748b;
            background: #fafcff;
        }
        .small {
            font-size: 10.75pt;
        }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $originChipClass = static function (?string $source): string {
            return match (trim((string) $source)) {
                'Project' => 'chip chip--project',
                'Mission' => 'chip chip--back-office',
                'Back Office' => 'chip chip--back-office',
                'Extra' => 'chip chip--extra',
                default => 'chip',
            };
        };
        $roleChipClass = static function (?string $role): string {
            $key = mb_strtolower(trim((string) $role), 'UTF-8');
            return match ($key) {
                'pi' => 'chip chip--pi',
                'co-pi' => 'chip chip--co-pi',
                'co-researcher' => 'chip chip--co-researcher',
                'first author' => 'chip chip--first-author',
                'co author' => 'chip chip--co-author',
                'corresponding author' => 'chip chip--corresponding-author',
                default => 'chip',
            };
        };
        $headerLogoPath = public_path('images/sihp-logo.png');
        $headerLogo = file_exists($headerLogoPath) ? 'file://' . $headerLogoPath : null;
    @endphp

    <htmlpageheader name="Header">
        <table style="width:100%; border-bottom: 1px solid #dbe7f5; padding-bottom: 6px; border-collapse:collapse;">
            <tr>
                <td style="width:58%; text-align:left; vertical-align:middle;">
                    <table style="border-collapse:collapse;">
                        <tr>
                            @if ($headerLogo)
                                <td style="vertical-align:middle; padding-right:7px;">
                                    <img src="{{ $headerLogo }}" alt="SiHP Logo" style="height:18px; width:auto; vertical-align:middle;">
                                </td>
                            @endif
                            <td style="vertical-align:middle; font-size:13.2pt; color:#0f172a; font-weight:bold;">
                                SiHP Research Database Management System
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:42%; text-align:right; vertical-align:middle; font-size:13.6pt; font-weight:bold; color:#0f172a;">
                    Personal Output Report
                </td>
            </tr>
        </table>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <table style="width:100%; border-top: 1px solid #e2e8f0; padding-top: 5px; font-size: 10.5pt; color: #64748b;">
            <tr>
                <td style="width: 60%;">
                    สร้างโดย {{ $reporter }} · {{ $generatedLabel }}
                </td>
                <td style="width: 40%; text-align: right;">
                    หน้า {PAGENO} / {nbpg}
                </td>
            </tr>
        </table>
    </htmlpagefooter>

    <div class="report-shell">
        <div class="hero-card">
            <table class="hero-table">
                <tr>
                    <td class="avatar-wrap">
                        <div class="avatar">
                            @if ($profilePhotoDataUri)
                                <img src="{{ $profilePhotoDataUri }}" alt="{{ $subject->full_name }}" width="92">
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="hero-kicker">Personal Overview</div>
                        <div class="hero-name">{{ $subject->full_name }}</div>
                        @if ($subject->research_display_name !== $subject->full_name)
                            <div class="hero-sub">{{ $subject->research_display_name }}</div>
                        @endif
                        @if ($subject->position)
                            <div class="hero-sub">{{ $subject->position }}</div>
                        @endif
                        <div class="hero-meta">สรุปข้อมูลโครงการและผลงานทั้งหมด ณ {{ $generatedLabel }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="summary-panel">
            <table class="summary-box">
                <tr>
                    <td class="metric-card--project">
                        <div class="metric-label">PI</div>
                        <div class="metric-value">{{ number_format($stats['pi_projects']) }}</div>
                        <div class="metric-note">บทบาทหัวหน้าโครงการ</div>
                    </td>
                    <td class="metric-card--project">
                        <div class="metric-label">Co-PI</div>
                        <div class="metric-value">{{ number_format($stats['co_pi_projects']) }}</div>
                        <div class="metric-note">บทบาทผู้ร่วมวิจัยหลัก</div>
                    </td>
                    <td class="metric-card--project">
                        <div class="metric-label">Co-Researcher</div>
                        <div class="metric-value">{{ number_format($stats['co_research_projects']) }}</div>
                        <div class="metric-note">บทบาทผู้ร่วมวิจัย</div>
                    </td>
                    <td class="metric-card--project">
                        <div class="metric-label">Projects ทั้งหมด</div>
                        <div class="metric-value">{{ number_format($stats['total_projects']) }}</div>
                        <div class="metric-note">รวมโครงการและ Mission</div>
                    </td>
                </tr>
            </table>
            <table class="summary-box" style="margin-top: 10px;">
                <tr>
                    <td class="metric-card--research">
                        <div class="metric-label">Research Papers</div>
                        <div class="metric-value">{{ number_format($stats['research_outputs']) }}</div>
                        <div class="metric-note">ผลงานตีพิมพ์และบทความวิจัย</div>
                    </td>
                    <td class="metric-card--presentation">
                        <div class="metric-label">Presentations</div>
                        <div class="metric-value">{{ number_format($stats['presentation_outputs']) }}</div>
                        <div class="metric-note">ผลงานการนำเสนอวิชาการ</div>
                    </td>
                    <td class="metric-card--textbook">
                        <div class="metric-label">Textbooks</div>
                        <div class="metric-value">{{ number_format($stats['textbook_outputs']) }}</div>
                        <div class="metric-note">หนังสือและบทในหนังสือ</div>
                    </td>
                    <td class="metric-card--award">
                        <div class="metric-label">Awards</div>
                        <div class="metric-value">{{ number_format($stats['award_outputs']) }}</div>
                        <div class="metric-note">รางวัลและการยกย่อง</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-head section-head--projects">
                <div class="section-kicker">Projects</div>
                <div class="section-title">รายการโครงการที่เกี่ยวข้อง</div>
                <div class="section-subtitle">แสดงทั้งโครงการวิจัยหลักและ Mission ที่เชื่อมโยงกับบุคคลนี้</div>
            </div>
            @if ($projectRows->isEmpty())
                <div class="empty-card">ไม่พบโครงการที่เกี่ยวข้อง</div>
            @else
                <table class="data-table table-projects">
                    <thead>
                        <tr>
                            <th style="width: 9%;">ปี</th>
                            <th style="width: 43%;">โครงการ / งาน</th>
                            <th style="width: 24%;">บทบาท</th>
                            <th style="width: 24%;">ประเภท / Theme</th>
                            <th style="width: 12%;">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectRows as $row)
                            <tr>
                                <td>{{ $row->year_be ?: '-' }}</td>
                                <td>
                                    <div class="title-main">{{ $row->project_name }}</div>
                                    @if ($row->project_code)
                                        <div class="title-sub">{{ $row->project_code }}</div>
                                    @endif
                                </td>
                                <td>
                                    @foreach (($row->roles ?? []) as $role)
                                        <span class="{{ $roleChipClass($role) }}">{{ $role }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div><strong>TYPE:</strong> {{ $row->project_type_label ?? '-' }}</div>
                                    <div class="title-sub"><strong>Theme:</strong> {{ $row->content_theme ?? '-' }}</div>
                                </td>
                                <td><span class="{{ $originChipClass($row->source_label ?? 'Project') }}">{{ $row->source_label ?? 'Project' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <div class="section-head section-head--research">
                <div class="section-kicker">Research</div>
                <div class="section-title">Research Papers</div>
                <div class="section-subtitle">เรียงตามรายการผลงานวิจัย พร้อมบทบาทและแหล่งอ้างอิงของแต่ละผลงาน</div>
            </div>
            @if ($researchRows->isEmpty())
                <div class="empty-card">ไม่พบ research paper</div>
            @else
                <table class="data-table table-research">
                    <thead>
                        <tr>
                            <th style="width: 8%;">ปี</th>
                            <th style="width: 34%;">ชื่อผลงาน</th>
                            <th style="width: 15%;">บทบาท</th>
                            <th style="width: 11%;">IF / Q</th>
                            <th style="width: 22%;">อ้างอิง</th>
                            <th style="width: 10%;">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($researchRows as $row)
                            <tr>
                                <td>{{ $row->year_be ?: '-' }}</td>
                                <td>
                                    <div class="title-main">{{ $row->title }}</div>
                                    <div class="title-sub">{{ $row->journal ?: '-' }} · {{ $row->author_line ?: 'ไม่ระบุผู้เขียน' }}</div>
                                </td>
                                <td>
                                    @forelse (($row->roles ?? collect()) as $role)
                                        <span class="{{ $roleChipClass($role) }}">{{ $role }}</span>
                                    @empty
                                        <span class="muted">-</span>
                                    @endforelse
                                </td>
                                <td>{{ $row->impact_factor ?? '-' }} / {{ $row->quartile ?: '-' }}</td>
                                <td>{{ $row->reference_label ?: '-' }}</td>
                                <td><span class="{{ $originChipClass($row->source_label ?? null) }}">{{ $row->source_label ?: '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <div class="section-head section-head--presentation">
                <div class="section-kicker">Presentation</div>
                <div class="section-title">Presentations</div>
                <div class="section-subtitle">สรุปเวทีการนำเสนอ ประเภทการนำเสนอ และโครงการหรือแหล่งอ้างอิงที่เกี่ยวข้อง</div>
            </div>
            @if ($presentationRows->isEmpty())
                <div class="empty-card">ไม่มี presentation</div>
            @else
                <table class="data-table table-presentation">
                    <thead>
                        <tr>
                            <th style="width: 8%;">ปี</th>
                            <th style="width: 30%;">ชื่อผลงาน</th>
                            <th style="width: 24%;">Conference</th>
                            <th style="width: 14%;">ประเภท</th>
                            <th style="width: 14%;">อ้างอิง</th>
                            <th style="width: 10%;">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($presentationRows as $row)
                            <tr>
                                <td>{{ $row->year_be ?: '-' }}</td>
                                <td>
                                    <div class="title-main">{{ $row->title }}</div>
                                    @if ($row->presenter_name)
                                        <div class="title-sub">ผู้นำเสนอ: {{ $row->presenter_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="title-main">{{ $row->conference_name ?: '-' }}</div>
                                    @if ($row->location)
                                        <div class="title-sub">{{ $row->location }}</div>
                                    @endif
                                </td>
                                <td>{{ $row->presentation_type ?: '-' }}</td>
                                <td>{{ $row->reference_label ?: '-' }}</td>
                                <td><span class="{{ $originChipClass($row->source_label ?? null) }}">{{ $row->source_label ?: '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <div class="section-head section-head--textbook">
                <div class="section-kicker">Textbook</div>
                <div class="section-title">Textbooks / Book Chapters</div>
                <div class="section-subtitle">จัดรูปแบบให้อ่านง่ายโดยแยกชื่อหนังสือ สำนักพิมพ์ และแหล่งอ้างอิงอย่างชัดเจน</div>
            </div>
            @if ($textbookRows->isEmpty())
                <div class="empty-card">ไม่มี textbook</div>
            @else
                <table class="data-table table-textbook">
                    <thead>
                        <tr>
                            <th style="width: 8%;">ปี</th>
                            <th style="width: 27%;">ชื่อผลงาน</th>
                            <th style="width: 25%;">หนังสือ / สำนักพิมพ์</th>
                            <th style="width: 30%;">รายละเอียด</th>
                            <th style="width: 10%;">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($textbookRows as $row)
                            <tr>
                                <td>{{ $row->year_be ?: '-' }}</td>
                                <td>
                                    <div class="title-main">{{ $row->title }}</div>
                                </td>
                                <td>
                                    <div class="title-main">{{ $row->book_title ?: '-' }}</div>
                                    <div class="title-sub">{{ $row->publisher_name ?: 'ไม่ระบุสำนักพิมพ์' }}</div>
                                </td>
                                <td>
                                    <div><strong>อ้างอิง:</strong> {{ $row->reference_label ?: '-' }}</div>
                                    @if ($row->chapter_pages)
                                        <div class="small muted">Chapter / Pages: {{ $row->chapter_pages }}</div>
                                    @endif
                                </td>
                                <td><span class="{{ $originChipClass($row->source_label ?? null) }}">{{ $row->source_label ?: '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <div class="section-head section-head--award">
                <div class="section-kicker">Award</div>
                <div class="section-title">Awards and Recognition</div>
                <div class="section-subtitle">สรุปรางวัล ผู้ได้รับ และหมายเหตุประกอบที่เกี่ยวข้อง</div>
            </div>
            @if ($awardRows->isEmpty())
                <div class="empty-card">ไม่มี award</div>
            @else
                <table class="data-table table-award">
                    <thead>
                        <tr>
                            <th style="width: 8%;">ปี</th>
                            <th style="width: 32%;">รายการ</th>
                            <th style="width: 20%;">ผู้ได้รับ</th>
                            <th style="width: 30%;">อ้างอิง / หมายเหตุ</th>
                            <th style="width: 10%;">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($awardRows as $row)
                            <tr>
                                <td>{{ $row->year_be ?: '-' }}</td>
                                <td><div class="title-main">{{ $row->title }}</div></td>
                                <td>{{ $row->recipient_name ?: '-' }}</td>
                                <td>
                                    <div>{{ $row->reference_label ?: '-' }}</div>
                                    @if ($row->note)
                                        <div class="title-sub">{{ $row->note }}</div>
                                    @endif
                                </td>
                                <td><span class="{{ $originChipClass($row->source_label ?? null) }}">{{ $row->source_label ?: '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</body>
</html>
