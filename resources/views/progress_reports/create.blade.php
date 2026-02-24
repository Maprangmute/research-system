@extends('layouts.app')

@section('content')
    @include('progress_reports._form', [
        'project' => $project,
        'action' => route('projects.progress_reports.store', $project),
        'httpMethod' => 'POST',
        'submitLabel' => 'บันทึกรายงานความก้าวหน้า',
        'pageTitle' => 'เพิ่มรายงานความก้าวหน้าของการดำเนินงาน',
    ])
@endsection
