<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Gantt Export PDF</title>
    <style>
        @page { margin: 18mm 10mm 14mm 10mm; }
        body { font-family: "thsarabun", Tahoma, sans-serif; font-size: 14pt; color: #0f172a; }
        h1 { margin: 0 0 4px; font-size: 24pt; }
        .meta { margin-bottom: 10px; color: #475569; font-size: 12pt; }
        .meta div { margin-bottom: 3px; }
        .header-card { background: #f8fafc; border: 1px solid #dbe4f2; padding: 10px 12px; margin-bottom: 10px; }
        .badge-row { margin: 6px 0 10px; }
        .badge { display: inline-block; margin: 0 6px 6px 0; padding: 2px 8px; border: 1px solid #cbd5e1; background: #e2f7f3; border-radius: 999px; font-size: 11pt; color: #0f766e; font-weight: 700; }
        .gantt-export-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: {{ $timeline['font_size'] }}; }
        .gantt-export-table th, .gantt-export-table td { border: 1px solid #cbd5e1; padding: 7px 6px; vertical-align: middle; word-wrap: break-word; white-space: normal; }
        .gantt-export-table th { background: #e2e8f0; font-weight: 700; text-align: center; }
        .col-no { width: 22px; }
        .col-task { width: 220px; }
        .col-owner { width: 128px; }
        .col-status { width: 94px; }
        .col-date { width: 78px; }
        .col-period { width: 58px; font-size: 9.5px; line-height: 1.15; }
        .cell-no { text-align: center; }
        .cell-task { padding: 0; }
        .task-title { padding: 8px 8px; line-height: 1.45; font-size: 13pt; }
        .level-1 { padding-left: 16px; }
        .level-2 { padding-left: 28px; }
        .level-3, .level-4, .level-5, .level-6 { padding-left: 40px; }
        .row-summary td { background: #f8fafc; font-weight: 700; }
        .gantt-export-table tbody td:nth-child(3),
        .gantt-export-table tbody td:nth-child(4),
        .gantt-export-table tbody td:nth-child(5),
        .gantt-export-table tbody td:nth-child(6) {
            font-size: 12pt;
            line-height: 1.35;
        }
        .timeline-cell { background: #ffffff; min-height: 18px; height: 18px; }
        .timeline-cell.is-mid { background: #99f6e4; }
        .timeline-cell.is-start { background: #14b8a6; }
        .timeline-cell.is-end { background: #0f766e; }
        .timeline-cell.is-single { background: #0d9488; }
        .empty-cell { text-align: center; padding: 18px; color: #64748b; }
    </style>
</head>
<body>
    <h1>Gantt Export: {{ $project->project_name_th }}</h1>
    <div class="header-card">
        <div class="meta">
            <div>โครงการ: {{ $project->project_code ?: $project->id }}</div>
            <div>รูปแบบ: {{ $timelineModeLabel }}</div>
            <div>จำนวนรายการ: {{ count($rows) }}</div>
            <div>สร้างเมื่อ {{ $generatedAtThai }} โดย {{ $generatedBy }}</div>
        </div>

        @if(!empty($filterSummary))
            <div class="badge-row">
                @foreach($filterSummary as $item)
                    <span class="badge">{{ $item }}</span>
                @endforeach
            </div>
        @endif
    </div>

    @include('gantt.partials.export-table', ['rows' => $rows, 'timeline' => $timeline])
</body>
</html>
