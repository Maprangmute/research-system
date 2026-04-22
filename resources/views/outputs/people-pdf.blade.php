<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Personal Output Report</title>
    <style>
        @page {
            margin-top: 82px;
            margin-bottom: 60px;
            header: html_Header;
            footer: html_Footer;
        }
        body {
            font-family: "thsarabun", Tahoma, sans-serif;
            font-size: 13pt;
            line-height: 1.45;
            color: #1e293b;
        }
        h1, h2, h3, p {
            margin: 0;
        }
        .muted {
            color: #64748b;
        }
        .hero {
            border: 1px solid #dbeafe;
            border-radius: 18px;
            padding: 16px 18px;
            background: #f8fbff;
            margin-bottom: 12px;
        }
        .hero-table {
            width: 100%;
            border-collapse: collapse;
        }
        .hero-table td {
            border: none;
            vertical-align: top;
        }
        .avatar {
            width: 92px;
            height: 92px;
            border-radius: 18px;
            overflow: hidden;
            background: #e2e8f0;
            text-align: center;
        }
        .avatar img {
            width: 92px;
            height: 92px;
            object-fit: cover;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary-grid td {
            width: 25%;
            border: 1px solid #dde6f6;
            padding: 8px 10px;
            vertical-align: top;
        }
        .summary-label {
            font-size: 11.5pt;
            color: #475569;
        }
        .summary-value {
            font-size: 18pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 4px;
        }
        .section-title {
            margin: 16px 0 8px;
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 10px;
            font-weight: bold;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #dde6f6;
            padding: 8px 10px;
            vertical-align: top;
        }
        thead {
            background: #f1f5f9;
        }
        .tag {
            display: inline-block;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 11pt;
            color: #334155;
            margin-right: 4px;
        }
        .empty {
            padding: 10px 0;
            color: #64748b;
        }
    </style>
</head>
<body>
    @php
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
    @endphp

    <htmlpageheader name="Header">
        <div style="font-size: 16pt; padding-left: 10px; color: #0f172a;">
            Personal Output Report
        </div>
    </htmlpageheader>

    <htmlpagefooter name="Footer">
        <div style="width: 100%; font-size: 11.5pt; padding: 4px 0;">
            <div style="text-align: right;">
                หน้า {PAGENO} จาก {nbpg}<br>
                โดย {{ $reporter }} : {{ $generatedLabel }}
            </div>
        </div>
    </htmlpagefooter>

    <div class="hero">
        <table class="hero-table">
            <tr>
                <td style="width: 110px;">
                    <div class="avatar">
                        @if ($profilePhotoDataUri)
                            <img src="{{ $profilePhotoDataUri }}" alt="{{ $subject->full_name }}">
                        @endif
                    </div>
                </td>
                <td>
                    <h1 style="font-size: 22pt;">{{ $subject->full_name }}</h1>
                    @if ($subject->research_display_name !== $subject->full_name)
                        <p class="muted">{{ $subject->research_display_name }}</p>
                    @endif
                    @if ($subject->position)
                        <p style="margin-top: 2px;">{{ $subject->position }}</p>
                    @endif
                    <p class="muted" style="margin-top: 4px;">รายงาน ณ {{ $generatedLabel }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-grid">
        <tr>
            <td>
                <div class="summary-label">PI</div>
                <div class="summary-value">{{ number_format($stats['pi_projects']) }}</div>
            </td>
            <td>
                <div class="summary-label">Co-PI</div>
                <div class="summary-value">{{ number_format($stats['co_pi_projects']) }}</div>
            </td>
            <td>
                <div class="summary-label">Co-Researcher</div>
                <div class="summary-value">{{ number_format($stats['co_research_projects']) }}</div>
            </td>
            <td>
                <div class="summary-label">Research</div>
                <div class="summary-value">{{ number_format($stats['research_outputs']) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="summary-label">Presentation</div>
                <div class="summary-value">{{ number_format($stats['presentation_outputs']) }}</div>
            </td>
            <td>
                <div class="summary-label">Textbook</div>
                <div class="summary-value">{{ number_format($stats['textbook_outputs']) }}</div>
            </td>
            <td>
                <div class="summary-label">Award</div>
                <div class="summary-value">{{ number_format($stats['award_outputs']) }}</div>
            </td>
            <td>
                <div class="summary-label">Projects ทั้งหมด</div>
                <div class="summary-value">{{ number_format($stats['total_projects']) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Projects</div>
    @if ($projectRows->isEmpty())
        <div class="empty">ไม่พบโครงการที่เกี่ยวข้อง</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ปี</th>
                    <th style="width: 52%;">โครงการ</th>
                    <th style="width: 20%;">บทบาท</th>
                    <th style="width: 18%;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projectRows as $row)
                    <tr>
                        <td>{{ $row->year_be ?: '-' }}</td>
                        <td>
                            {{ $row->project_name }}
                            @if ($row->project_code)
                                <div class="muted">{{ $row->project_code }}</div>
                            @endif
                        </td>
                        <td>{{ implode(', ', $row->roles) }}</td>
                        <td>{{ $row->execution_status ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Research Papers</div>
    @if ($researchRows->isEmpty())
        <div class="empty">ไม่พบ research paper</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ปี</th>
                    <th style="width: 38%;">ชื่อผลงาน</th>
                    <th style="width: 16%;">บทบาท</th>
                    <th style="width: 10%;">IF</th>
                    <th style="width: 26%;">อ้างอิง</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($researchRows as $row)
                    <tr>
                        <td>{{ $row->year_be ?: '-' }}</td>
                        <td>
                            {{ $row->title }}
                            <div class="muted">{{ $row->journal }} | {{ $row->quartile }}</div>
                        </td>
                        <td>{{ $row->roles->implode(', ') }}</td>
                        <td>{{ $row->impact_factor ?? '-' }}</td>
                        <td>{{ $row->reference_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Presentation</div>
    @if ($presentationRows->isEmpty())
        <div class="empty">ไม่มี presentation</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ปี</th>
                    <th style="width: 34%;">ชื่อผลงาน</th>
                    <th style="width: 24%;">Conference</th>
                    <th style="width: 16%;">ประเภท</th>
                    <th style="width: 16%;">อ้างอิง</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($presentationRows as $row)
                    <tr>
                        <td>{{ $row->year_be ?: '-' }}</td>
                        <td>{{ $row->title }}</td>
                        <td>
                            {{ $row->conference_name }}
                            @if ($row->location)
                                <div class="muted">{{ $row->location }}</div>
                            @endif
                        </td>
                        <td>{{ $row->presentation_type }}</td>
                        <td>{{ $row->reference_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Textbook</div>
    @if ($textbookRows->isEmpty())
        <div class="empty">ไม่มี textbook</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ปี</th>
                    <th style="width: 36%;">Book Title</th>
                    <th style="width: 26%;">ชื่อผลงาน</th>
                    <th style="width: 14%;">Publisher</th>
                    <th style="width: 14%;">อ้างอิง</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($textbookRows as $row)
                    <tr>
                        <td>{{ $row->year_be ?: '-' }}</td>
                        <td>{{ $row->book_title }}</td>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->publisher_name }}</td>
                        <td>{{ $row->reference_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Award</div>
    @if ($awardRows->isEmpty())
        <div class="empty">ไม่มี award</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ปี</th>
                    <th style="width: 36%;">รายการ</th>
                    <th style="width: 22%;">ผู้ได้รับ</th>
                    <th style="width: 32%;">อ้างอิง / หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($awardRows as $row)
                    <tr>
                        <td>{{ $row->year_be ?: '-' }}</td>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->recipient_name ?: '-' }}</td>
                        <td>
                            {{ $row->reference_label }}
                            @if ($row->note)
                                <div class="muted">{{ $row->note }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
