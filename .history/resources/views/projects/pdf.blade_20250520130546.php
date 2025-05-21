<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดโครงการ (PDF)</title>
    <style>
        @font-face {
            font-family: 'thsarabun';
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        body {
            font-family: 'thsarabun', DejaVu Sans, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        h2 {
            margin-top: 16px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px;
        }
        th {
            background-color: #eee;
        }
        .section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h2>A: ข้อมูลโครงการ</h2>
<ul>
    <li><strong>ชื่อโครงการ (TH):</strong> {{ $project->project_name_th }}</li>
    <li><strong>ชื่อโครงการ (EN):</strong> {{ $project->project_name_en }}</li>
    <li><strong>ประเภท:</strong> {{ $project->project_type }}</li>
    <li><strong>Theme:</strong> {{ $project->theme }}</li>
    <li><strong>Shortcut:</strong> {{ $project->shortcut_link }}</li>
    <li><strong>ขนาด:</strong> {{ $project->size }}</li>
    <li><strong>รหัสโครงการ R:</strong> {{ $project->r_code }}</li>
    <li><strong>ปีโปรเจค:</strong> {{ $project->start_year }}</li>
</ul>

<h2>B: ข้อมูลแหล่งทุน</h2>
<table>
    <tr><th>ประเภทแหล่งทุน</th><td>{{ $project->funding_type }}</td></tr>
    <tr><th>ชื่อแหล่งทุน</th><td>{{ $project->funding_source }}</td></tr>
    <tr><th>ประเภทองค์กร</th><td>{{ $project->funding_org_type }}</td></tr>
    <tr><th>ชื่อทุน</th><td>{{ $project->funding_name }}</td></tr>
    <tr><th>งบประมาณ (บาท)</th><td>{{ number_format($project->budget, 2) }}</td></tr>
</table>

<h2>C: รายชื่อผู้ร่วมวิจัย (COPI)</h2>
<table>
    <thead><tr><th>#</th><th>ชื่อ</th><th>สังกัด</th></tr></thead>
    <tbody>
        @foreach ($project->copis as $i => $copi)
            <tr><td>{{ $i+1 }}</td><td>{{ $copi->name }}</td><td>{{ $copi->affiliation }}</td></tr>
        @endforeach
    </tbody>
</table>

<h2>D: รหัส IRB</h2>
<table>
    <thead><tr><th>#</th><th>รหัส</th><th>หน่วยงาน</th><th>วันหมดอายุ</th></tr></thead>
    <tbody>
        @foreach ($project->irbs as $i => $irb)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $irb->code }}</td>
                <td>{{ $irb->source }}</td>
                <td>{{ $irb->exp_date }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>E: ผู้มีส่วนร่วม (FTE)</h2>
<table>
    <thead><tr><th>#</th><th>ชื่อ</th><th>กลุ่ม</th><th>FTE</th></tr></thead>
    <tbody>
        @foreach ($project->fteRoles as $i => $fte)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $fte->name }}</td>
                <td>{{ $fte->role_group }}</td>
                <td>{{ $fte->fte_value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>F: ผลงานวิจัย (Outputs)</h2>
<table>
    <thead><tr><th>#</th><th>ชื่อผลงาน</th><th>ประเภท</th><th>รายละเอียด</th><th>ปี</th></tr></thead>
    <tbody>
        @foreach ($project->outputs as $i => $output)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $output->title }}</td>
                <td>{{ $output->type }}</td>
                <td>{{ $output->note }}</td>
                <td>{{ $output->year }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>G: ยุทธศาสตร์</h2>
<ul>
    @for ($i = 1; $i <= 5; $i++)
        @for ($j = 1; $j <= 5; $j++)
            @php $field = "strategy_{$i}_{$j}"; @endphp
            @if (!empty($project->$field))
                <li>{{ $field }}</li>
            @endif
        @endfor
    @endfor
</ul>

</body>
</html>
