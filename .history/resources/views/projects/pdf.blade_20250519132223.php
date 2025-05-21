@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดโครงการ</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        h1 { font-size: 18px; }
        h2 { margin-top: 20px; font-size: 16px; }
    </style>
</head>
<body>
    <h1>รายละเอียดโครงการ: {{ $project->project_name_th }}</h1>
    <p><strong>ชื่ออังกฤษ:</strong> {{ $project->project_name_en }}</p>
    <p><strong>ประเภท:</strong> {{ $project->project_type }}</p>
    <p><strong>Theme:</strong> {{ $project->theme }}</p>
    <p><strong>ขนาด:</strong> {{ $project->size }}</p>
    <p><strong>ลิงก์:</strong> <a href="{{ $project->shortcut_link }}">{{ $project->shortcut_link }}</a></p>

    <h2>แหล่งทุน (Funding)</h2>
    <table>
        <tbody>
            <tr><th>DA102 - ประเภทแหล่งทุน</th><td>{{ $project->funding_type }}</td></tr>
            <tr><th>DA103 - ชื่อแหล่งทุน</th><td>{{ $project->funding_source }}</td></tr>
            <tr><th>DA104 - รัฐบาล / เอกชน / มูลนิธิ</th><td>{{ $project->funding_org_type }}</td></tr>
            <tr><th>DA105 - ชื่อทุน</th><td>{{ $project->funding_name }}</td></tr>
            <tr><th>DA106 - งบประมาณ (บาท)</th><td>{{ number_format($project->budget, 2) }}</td></tr>
        </tbody>
    </table>

    <h2>COPI</h2>
    <table>
        <thead>
            <tr><th>ชื่อ</th><th>สังกัด</th></tr>
        </thead>
        <tbody>
            @forelse($project->copis as $copi)
                <tr><td>{{ $copi->name }}</td><td>{{ $copi->affiliation }}</td></tr>
            @empty
                <tr><td colspan="2">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>IRB</h2>
    <table>
        <thead>
            <tr><th>รหัส</th><th>หน่วยงาน</th></tr>
        </thead>
        <tbody>
            @forelse($project->irbs as $irb)
                <tr><td>{{ $irb->code }}</td><td>{{ $irb->source }}</td></tr>
            @empty
                <tr><td colspan="2">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>FTE</h2>
    <table>
        <thead>
            <tr><th>กลุ่ม</th><th>ชื่อ</th><th>FTE</th></tr>
        </thead>
        <tbody>
            @forelse($project->fteRoles as $fte)
                <tr>
                    <td>{{ $fte->role_group }}</td>
                    <td>{{ $fte->name }}</td>
                    <td>{{ $fte->fte_value }}</td>
                </tr>
            @empty
                <tr><td colspan="3">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>ผลงานวิจัย (Outputs)</h2>
    <table>
        <thead>
            <tr><th>ประเภท</th><th>ชื่อผลงาน</th><th>ปี</th></tr>
        </thead>
        <tbody>
            @forelse($project->outputs as $output)
                <tr>
                    <td>{{ $output->type }}</td>
                    <td>{{ $output->title }}</td>
                    <td>{{ $output->year }}</td>
                </tr>
            @empty
                <tr><td colspan="3">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>ยุทธศาสตร์ (Strategy)</h2>
</body>
</html>
@endsection
