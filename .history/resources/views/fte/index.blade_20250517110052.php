@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 สรุป FTE รายบุคคล</h2>

    <!-- Filter by year -->
    <form method="GET" action="{{ route('fte.index') }}" class="mb-4 flex gap-2">
        <select name="year" class="border border-gray-300 p-2 rounded">
            <option value="">-- ทุกปี --</option>
            @foreach($years as $year)
                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                    ปี {{ $year }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">🔍 ค้นหา</button>
    </form>

    <div class="bg-white shadow rounded overflow-hidden">
        <table class="min-w-full table-auto text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-2">ชื่อ</th>
                    <th class="px-4 py-2">กลุ่มบทบาท</th>
                    <th class="px-4 py-2 text-right">FTE รวม</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fteSummary as $row)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">
                        <a href="{{ route('fte.detail', ['name' => $row->name]) }}" class="text-blue-600 hover:underline">
                            {{ $row->name }}
                        </a>
                    </td>
                    <td class="px-4 py-2">{{ $row->role_group }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($row->total_fte, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-4 text-center">ไม่พบข้อมูล</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
