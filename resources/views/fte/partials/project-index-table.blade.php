<div class="bg-white rounded-xl shadow border border-green-100 mb-4">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3">
        <div class="text-sm text-slate-600">
            พบ <span id="fte-project-visible-count" class="font-semibold text-slate-900" data-total-count="{{ $fteSummary->count() }}">{{ $fteSummary->count() }}</span> โปรเจค
        </div>
        <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-800">
            <span class="px-3 py-1 rounded-full bg-green-50 text-green-700">
                FTE ทั้งหมด: {{ number_format($summaryTotalFte ?? 0, 7) }}
            </span>
            <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">
                FTE ตามตัวกรอง: {{ number_format($summaryFilteredFte ?? 0, 7) }}
            </span>
            <span class="px-3 py-1 rounded-full bg-lime-50 text-lime-700">
                FTE เดือนนี้: {{ number_format($summaryMonthFte ?? 0, 7) }}
            </span>
        </div>
    </div>
</div>

<div class="fte-table-wrap overflow-x-auto bg-white rounded-xl shadow border border-slate-200">
    <table class="fte-index-table min-w-[1220px] xl:min-w-full table-auto text-sm">
        <thead class="bg-slate-100 text-slate-700">
            <tr>
                <th class="px-4 py-3 text-left w-[30%]">โปรเจค</th>
                <th class="px-4 py-3 text-center w-[12%]">ปีโปรเจค (พ.ศ.)</th>
                <th class="px-4 py-3 text-right w-[18%]">FTE ทั้งหมด</th>
                <th class="px-4 py-3 text-right w-[18%]">FTE ตามตัวกรอง</th>
                <th class="px-4 py-3 text-right w-[14%]">FTE เดือนนี้</th>
                <th class="px-4 py-3 text-center w-[8%]">รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fteSummary as $row)
                <tr class="fte-project-main-row border-t group hover:bg-green-50 transition"
                    data-project-name="{{ $row->project_name }}"
                    data-project-year="{{ (float) ($row->project_year ?? 0) }}"
                    data-total-fte="{{ (float) ($row->total_fte ?? 0) }}"
                    data-filtered-fte="{{ (float) ($row->filtered_fte ?? 0) }}"
                    data-month-fte="{{ (float) ($row->fte_this_month ?? 0) }}">
                    <td class="px-4 py-2 font-medium" data-label="โปรเจค">
                        <a href="{{ route('fte.project.detail', $row->project_id) }}"
                            class="text-slate-900 hover:underline font-bold">
                            {{ $row->project_name }}
                        </a>
                    </td>
                    <td class="px-4 py-2 text-center text-slate-700" data-label="ปีโปรเจค">
                        {{ $row->project_year ?? '-' }}
                    </td>
                    <td class="px-4 py-2 text-right font-semibold text-green-700" data-label="FTE ทั้งหมด">
                        {{ number_format($row->total_fte ?? 0, 7) }}
                    </td>
                    <td class="px-4 py-2 text-right font-semibold {{ (float) ($row->filtered_fte ?? 0) > 0 ? 'text-slate-700' : 'text-slate-400' }}" data-label="FTE ตามตัวกรอง">
                        {{ number_format($row->filtered_fte ?? 0, 7) }}
                    </td>
                    <td class="px-4 py-2 text-right text-slate-700" data-label="FTE เดือนนี้">
                        {{ number_format($row->fte_this_month ?? 0, 7) }}
                    </td>
                    <td class="px-4 py-2 text-center" data-label="รายละเอียด">
                        <button type="button"
                            class="px-3 py-1 rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold"
                            onclick="toggleProjectDetail('{{ $loop->index }}')"
                            data-project-detail="{{ $loop->index }}">ดูรายละเอียด</button>
                    </td>
                </tr>

                <tr id="project-detail-row-{{ $loop->index }}" class="fte-project-detail-row bg-green-50 animate-fade-in">
                    <td colspan="6" class="px-6 py-4">
                        @if (isset($monthlyFTE[$row->project_id]))
                            @foreach ($monthlyFTE[$row->project_id] as $month => $items)
                                @php
                                    $thaiMonth = '-';
                                    $thaiYear = '-';
                                    if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                                        $carbon = \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01');
                                        $thaiYear = $carbon->year + 543;
                                        $thaiMonth = $carbon->locale('th')->translatedFormat('F');
                                    }
                                    $sum = collect($items)->sum('fte_month');
                                @endphp

                                <div class="mb-4 rounded-xl border border-green-100 bg-green-50">
                                    <div class="flex items-center gap-2 px-4 py-3 border-b border-green-100 bg-green-100 rounded-t-xl font-semibold text-base text-green-800 md:text-lg">
                                        {{ $thaiMonth }} {{ $thaiYear }}
                                        <span class="ml-auto text-green-700 font-bold">
                                            FTE: {{ number_format($sum, 7) }}
                                        </span>
                                    </div>
                                    <div class="p-2 fte-detail-table-wrap">
                                        <table class="fte-detail-table min-w-[1180px] w-full table-auto text-sm border border-slate-200 rounded-lg overflow-hidden">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="p-3 text-left font-semibold text-slate-700 min-w-[180px]">ชื่อบุคคล</th>
                                                    <th class="p-3 text-left font-semibold text-slate-700 min-w-[130px]">กลุ่ม</th>
                                                    <th class="p-3 text-left font-semibold text-slate-700 min-w-[260px]">ภาระงาน</th>
                                                    <th class="p-3 text-center font-semibold text-slate-700 min-w-[130px]">จำนวนวันทำงาน</th>
                                                    <th class="p-3 text-right font-semibold text-slate-700 min-w-[110px]">FTE</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($items as $item)
                                                    <tr class="{{ $loop->even ? 'bg-white' : 'bg-gray-50' }}">
                                                        <td class="p-3 break-words whitespace-normal align-top" data-label="ชื่อบุคคล">
                                                            @if (!empty($item->name))
                                                                <a href="{{ route('fte.detail', ['person' => $item->name]) }}"
                                                                    class="text-green-700 underline hover:text-green-800">
                                                                    {{ $item->name }}
                                                                </a>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="p-3 break-words whitespace-normal align-top" data-label="กลุ่ม">
                                                            {{ $item->role_group ?? '-' }}
                                                        </td>
                                                        <td class="p-3 break-words whitespace-normal align-top" data-label="ภาระงาน">
                                                            <div>{{ $item->job_detail ?? '-' }}</div>
                                                            <div class="text-gray-500 text-xs mt-1">
                                                                {{ $item->task_detail ?? '-' }}
                                                            </div>
                                                        </td>
                                                        <td class="p-3 text-center font-semibold text-gray-700 align-top" data-label="วันทำงาน">
                                                            {{ $item->days_in_month }} วัน
                                                        </td>
                                                        <td class="p-3 text-right font-semibold relative group align-top" data-label="FTE">
                                                            {{ number_format($item->fte_month, 7) }}
                                                            <div class="text-xs text-gray-400">
                                                                FTE/วัน: {{ number_format($item->fte_per_day, 10) }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-gray-400 italic text-center py-6">ไม่มีข้อมูล FTE สำหรับโปรเจคนี้</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400">ไม่พบข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
