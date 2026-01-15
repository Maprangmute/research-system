@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-4">

        {{-- Back --}}
        <div class="mb-5 flex items-center gap-3">
            <a href="{{ route('fte.index') }}" class="text-blue-600 hover:underline flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                ย้อนกลับ
            </a>
        </div>

        {{-- Header --}}

        {{-- Profile --}}
        <div class="flex items-center gap-4 mb-6">

            @if ($profile && $profile->profile_photo_path)
                <img src="{{ asset('storage/' . $profile->profile_photo_path) }}?v={{ time() }}"
                    class="w-40 h-40 rounded-lg border shadow object-cover">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($person) }}&background=random&size=128"
                    class="w-20 h-20 rounded-lg border shadow object-cover">
            @endif

            <div class="text-2xl mx-4 font-bold mb-4">รายละเอียด FTE : {{ $person }}</div>
        </div>


        {{-- ============================ --}}
        {{-- STACKED CHART --}}
        {{-- ============================ --}}
        <h3 class="text-lg mt-6 font-semibold">📊 สรุป FTE รายเดือน (Stacked)</h3>

        <div id="chart-container" class="mt-3 mb-6 bg-white p-4 rounded-xl border shadow" style="height:300px;">
            <canvas id="fteStackedChart"></canvas>
        </div>

        {{-- ============================ --}}
        {{-- รายเดือน --}}
        {{-- ============================ --}}
        <div class="bg-white rounded-xl shadow border p-4">
            <h3 class="text-lg font-semibold mb-4">📅 รายการ FTE รายเดือน</h3>

            @forelse($monthlyFTE as $monthKey => $rows)
                @php
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
                    $monthName = $carbon->locale('th')->translatedFormat('F');
                    $year = $carbon->year + 543;
                    $sumFTE = $rows->sum('fte_value');
                @endphp

                <div class="border rounded-lg mb-6 overflow-hidden">
                    <div class="bg-blue-50 px-4 py-2 font-semibold flex items-center">
                        {{ $monthName }} {{ $year }}
                        <span class="ml-auto text-green-700">
                            รวม: {{ number_format($sumFTE, 4) }} FTE
                        </span>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="p-2 text-left">โครงการ</th>
                                <th class="p-2 text-left">กลุ่ม</th>
                                <th class="p-2 text-left">ภาระงาน</th>
                                <th class="p-2 text-center">ช่วงเวลา</th>
                                <th class="p-2 text-center">ชั่วโมงทำงาน</th>
                                <th class="p-2 text-center">จำนวนวัน</th>
                                <th class="p-2 text-center">FTE/วัน</th>
                                <th class="p-2 text-center">FTE เดือนนี้</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rows as $item)
                                @php
                                    // Convert overlap to Thai language
                                    $os = \Carbon\Carbon::parse($item->overlap_start)
                                        ->locale('th')
                                        ->translatedFormat('j F Y');

                                    $oe = \Carbon\Carbon::parse($item->overlap_end)
                                        ->locale('th')
                                        ->translatedFormat('j F Y');
                                @endphp

                                <tr class="border-b">
                                    <td class="p-2 text-blue-600 underline break-words max-w-[600px]">
                                        {{ $item->project->project_name_th }}
                                    </td>

                                    <td class="p-2 break-words whitespace-normal">
                                        {{ $item->role_group ?? '-' }}
                                    </td>

                                    <td class="p-2 break-words whitespace-normal">
                                        <div>{{ $item->job_detail ?? '-' }}</div>
                                        <div class="text-gray-400 text-xs">
                                            {{ $item->role_group ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- ช่วงทับซ้อนภาษาไทย --}}
                                    <td class="p-2 text-center">
                                        {{ $os }} - {{ $oe }}
                                    </td>

                                    <td class="p-2 text-center">
                                        {{ number_format($item->hours_in_month, 2) }}
                                    </td>

                                    {{-- วันในเดือน เป็นจำนวนเต็ม --}}
                                    <td class="p-2 text-center">
                                        {{ intval($item->days_in_month) }}
                                    </td>

                                    <td class="p-2 text-center">
                                        {{ number_format($item->fte_per_day, 6) }}
                                    </td>

                                    <td class="p-2 text-center font-semibold">
                                        {{ number_format($item->fte_value, 6) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            @empty
                <div class="text-gray-400 text-center py-8">ไม่มีข้อมูล</div>
            @endforelse
        </div>

    </div>

    {{-- ============================ --}}
    {{-- STACKED CHART SCRIPT --}}
    {{-- ============================ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            function stringToColor(str) {
                let hash = 0;
                for (let i = 0; i < str.length; i++) {
                    hash = str.charCodeAt(i) + ((hash << 5) - hash);
                }
                const r = (hash & 0xFF0000) >> 16;
                const g = (hash & 0x00FF00) >> 8;
                const b = hash & 0x0000FF;
                return `rgba(${Math.abs(r)}, ${Math.abs(g)}, ${Math.abs(b)}, 0.8)`;
            }

            const labels = @json($chartLabels);

            const projectNames = [];
            @foreach ($monthlyFTE as $month => $items)
                @foreach ($items as $item)
                    projectNames.push("{{ $item->project->project_name_th }}");
                @endforeach
            @endforeach

            const uniqueProjects = [...new Set(projectNames)];

            const datasets = uniqueProjects.map(projectName => ({
                label: projectName,
                backgroundColor: stringToColor(projectName),
                data: labels.map(monthKey => {
                    let total = 0;

                    @foreach ($monthlyFTE as $m => $items)
                        @foreach ($items as $item)
                            if (monthKey === "{{ $m }}" && projectName ===
                                "{{ $item->project->project_name_th }}") {
                                total = {{ $item->fte_value }};
                            }
                        @endforeach
                    @endforeach

                    return total;
                }),
                stack: 'stack'
            }));

            new Chart(document.getElementById('fteStackedChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

        });
    </script>

@endsection
