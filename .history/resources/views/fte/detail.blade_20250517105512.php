@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        📋 รายละเอียด FTE: {{ $name }}
        @if($month && $year) – เดือน {{ $month }} ปี {{ $year }} @endif
    </h2>

    <div class="mb-4">
        <strong>รวม FTE ทั้งหมด:</strong> {{ number_format($totalFTE, 2) }}
    </div>

    <table class="w-full bg-white shadow rounded table-auto text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-4 py-2">โครงการ</th>
                <th class="px-4 py-2">บทบาท</th>
                <th class="px-4 py-2">FTE</th>
                <th class="px-4 py-2">เดือน</th>
                <th class="px-4 py-2">ปี</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2">{{ $row->project->project_name_th ?? '-' }}</td>
                <td class="px-4 py-2">{{ $row->role_group }}</td>
                <td class="px-4 py-2">{{ $row->fte_value }}</td>
                <td class="px-4 py-2">{{ $row->month }}</td>
                <td class="px-4 py-2">{{ $row->year }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
