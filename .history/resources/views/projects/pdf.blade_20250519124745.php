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
    </style>
</head>
<body>
    <h1>รายละเอียดโครงการ: {{ $project->project_name_th }}</h1>
    <p><strong>ชื่ออังกฤษ:</strong> {{ $project->project_name_en }}</p>
    <p><strong>ประเภท:</strong> {{ $project->project_type }}</p>
    <p><strong>Theme:</strong> {{ $project->theme }}</p>

    <h2>COPI</h2>
    <table>
        <thead>
            <tr><th>ชื่อ</th><th>สังกัด</th></tr>
        </thead>
        <tbody>
            @foreach($project->copis as $copi)
                <tr><td>{{ $copi->name }}</td><td>{{ $copi->affiliation }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>IRB</h2>
    <table>
        <thead>
            <tr><th>รหัส</th><th>หน่วยงาน</th></tr>
        </thead>
        <tbody>
            @foreach($project->irbs as $irb)
                <tr><td>{{ $irb->code }}</td><td>{{ $irb->source }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
