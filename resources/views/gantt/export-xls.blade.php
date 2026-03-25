<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Gantt Export Excel</title>
    <style>
        body { font-family: "TH Sarabun New", "DejaVu Sans", sans-serif; font-size: 14px; color: #0f172a; }
        h1 { margin: 0 0 6px; font-size: 24px; }
        .header-card { margin-bottom: 12px; border: 1px solid #dbe4f2; background: #f8fafc; }
        .meta { margin-bottom: 12px; color: #475569; padding: 10px 12px 0; }
        .meta div { margin-bottom: 4px; }
        .badge-row { margin: 0; padding: 0 12px 10px; }
        .badge { display: inline-block; margin: 0 6px 6px 0; padding: 3px 8px; border: 1px solid #99f6e4; background: #ccfbf1; color: #115e59; font-weight: 700; }
        .gantt-export-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: {{ $timeline['font_size'] }}; }
        .gantt-export-table th, .gantt-export-table td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle; }
        .gantt-export-table th { background: #dbeafe; font-weight: 700; text-align: center; color: #1e3a8a; }
        .col-no { width: 34px; }
        .col-task { width: 320px; }
        .col-owner { width: 130px; }
        .col-status { width: 90px; }
        .col-date { width: 78px; }
        .col-period { width: {{ $timeline['cell_width'] }}; }
        .cell-no { text-align: center; }
        .cell-task { padding: 0; }
        .task-title { padding: 6px 8px; line-height: 1.35; }
        .level-1 { padding-left: 20px; }
        .level-2 { padding-left: 34px; }
        .level-3, .level-4, .level-5, .level-6 { padding-left: 46px; }
        .row-summary td { background: #f8fafc; font-weight: 700; }
        .timeline-cell { background: #ffffff; }
        .timeline-cell.is-mid { background: #99f6e4; }
        .timeline-cell.is-start { background: #14b8a6; }
        .timeline-cell.is-end { background: #0f766e; }
        .timeline-cell.is-single { background: #0d9488; }
        .empty-cell { text-align: center; padding: 22px; color: #64748b; }
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
