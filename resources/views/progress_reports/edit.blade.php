@extends('layouts.app')

@section('content')
    @include('progress_reports._form', [
        'project' => $project,
        'report' => $report,
        'action' => route('projects.progress_reports.update', [$project, $report]),
        'httpMethod' => 'PUT',
        'submitLabel' => 'บันทึกการแก้ไข',
        'pageTitle' => 'แก้ไขรายงานความก้าวหน้าของการดำเนินงาน',
    ])
@endsection
