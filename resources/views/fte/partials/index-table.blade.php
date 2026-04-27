<div class="mb-3 flex items-center justify-between">
    <div class="text-sm text-slate-600">
        พบ <span id="fte-visible-count" class="font-semibold text-slate-900" data-total-count="{{ $fteSummary->count() }}">{{ $fteSummary->count() }}</span> รายชื่อ
    </div>
</div>

<div class="fte-table-wrap overflow-x-auto bg-white rounded-xl shadow border border-slate-200">
    <table class="fte-index-table min-w-[1120px] xl:min-w-full table-auto text-sm">
        <thead class="bg-slate-100 text-slate-700">
            <tr>
                <th class="px-4 py-3 text-left w-[50%]">ชื่อ</th>
                <th class="px-4 py-3 text-right w-[28%]">{{ $selectedFteLabel ?? 'FTE ตามช่วงที่เลือก' }}</th>
                <th class="px-4 py-3 text-center w-[22%]">รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fteSummary as $row)
                <tr class="fte-main-row border-t group hover:bg-blue-50 transition"
                    data-person-name="{{ $row->name }}"
                    data-total-fte="{{ (float) ($row->total_fte ?? 0) }}"
                    data-month-fte="{{ (float) ($row->fte_selected ?? 0) }}">
                    <td class="px-4 py-2 font-medium flex items-center gap-2" data-label="ชื่อ">
                        @php
                            $profileImage = $profiles[$row->name] ?? null;
                        @endphp
                        @if ($profileImage)
                            <img src="{{ asset('storage/' . $profileImage) }}?v={{ time() }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                class="w-8 h-8 rounded-full border shadow object-cover" alt="avatar">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($row->name) }}&background=random&size=64"
                                class="w-8 h-8 rounded-full border shadow object-cover" alt="avatar">
                        @endif

                        <a href="{{ route('fte.detail', array_merge(['person' => $row->name], request()->only(['mode', 'year', 'start', 'end']))) }}"
                            class="text-blue-800 hover:underline font-bold">
                            {{ $row->name }}
                        </a>
                    </td>
                    <td class="px-4 py-2 text-right font-semibold text-green-600" data-label="{{ $selectedFteLabel ?? 'FTE ตามช่วงที่เลือก' }}">
                        {{ number_format($row->fte_selected ?? 0, 7) }}
                    </td>
                    <td class="px-4 py-2 text-center" data-label="รายละเอียด">
                        <button type="button"
                            class="px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 font-bold"
                            onclick="toggleDetail('{{ $loop->index }}')"
                            data-detail="{{ $loop->index }}">ดูรายละเอียด</button>
                    </td>
                </tr>

                <tr id="detail-row-{{ $loop->index }}" class="fte-detail-row bg-blue-50 animate-fade-in">
                    <td colspan="3" class="px-6 py-4">
                        @if (isset($monthlyFTE[$row->name]))
                            @foreach ($monthlyFTE[$row->name] as $month => $items)
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

                                <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50">
                                    <div class="flex items-center gap-2 px-4 py-3 border-b border-blue-100 bg-blue-100 rounded-t-xl font-semibold text-base md:text-lg">
                                        {{ $thaiMonth }} {{ $thaiYear }}
                                        <span class="ml-auto text-green-600 font-bold">
                                            FTE: {{ number_format($sum, 7) }}
                                        </span>
                                    </div>
                                    <div class="p-2 fte-detail-table-wrap">
                                        <table class="fte-detail-table min-w-[1320px] w-full table-auto text-sm border border-slate-200 rounded-lg overflow-hidden">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="p-3 text-left font-semibold text-slate-700 min-w-[340px]">ชื่อโปรเจค / งานนอกโครงการ</th>
                                                    <th class="p-3 text-left font-semibold text-slate-700 min-w-[180px]">กลุ่ม</th>
                                                    <th class="p-3 text-left font-semibold text-slate-700 min-w-[340px]">รายละเอียดงาน</th>
                                                    <th class="p-3 text-center font-semibold text-slate-700 min-w-[150px]">ชั่วโมงการทำงาน</th>
                                                    <th class="p-3 text-center font-semibold text-slate-700 min-w-[150px]">จำนวนวันทำงาน</th>
                                                    <th class="p-3 text-right font-semibold text-slate-700 min-w-[140px]">FTE</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($items as $item)
                                                    @php
                                                        $hoursInMonth = $item->hours_in_month_exact
                                                            ?? $item->hours_in_month
                                                            ?? $item->hours
                                                            ?? 0;
                                                    @endphp
                                                    <tr class="{{ $loop->even ? 'bg-white' : 'bg-gray-50' }}">
                                                        <td class="p-3 break-words whitespace-normal align-top" data-label="โปรเจค/งาน">
                                                            @php
                                                                $workloadName = trim((string) ($item->job_name ?? ''));
                                                                if ($workloadName === '') {
                                                                    $workloadName = trim((string) ($item->task_detail ?? ''));
                                                                }
                                                            @endphp
                                                            @if ($item->project?->id)
                                                                <a href="{{ route('projects.show', $item->project) }}" class="text-blue-700 underline">
                                                                    {{ $item->project->project_name_th ?? '-' }}
                                                                </a>
                                                            @else
                                                                @php
                                                                    $backOfficeTitle = $item->task_detail ?? '-';
                                                                    $backOfficeTask = property_exists($item, 'task') ? $item->task : null;
                                                                    $backOfficeId = $backOfficeTask?->getRouteKey()
                                                                        ?? (!empty($item->task_id) ? \App\Models\BackOfficeTask::formatBackOfficeId((int) $item->task_id) : null);
                                                                @endphp
                                                                @if (!is_null($backOfficeId))
                                                                    <a href="{{ route('back_office.show', $backOfficeId) }}"
                                                                        class="text-blue-700 underline relative z-20 pointer-events-auto"
                                                                        onclick="event.stopPropagation();">
                                                                        {{ $backOfficeTitle }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-slate-800">{{ $backOfficeTitle }}</span>
                                                                @endif
                                                                @if ($workloadName !== '' && $workloadName !== trim((string) $backOfficeTitle))
                                                                    <div class="mt-1 text-sm leading-6">{{ $workloadName }}</div>
                                                                @endif
                                                                <div class="text-gray-400 text-xs">งานนอกโครงการ</div>
                                                            @endif
                                                        </td>

                                                        <td class="p-3 break-words whitespace-normal align-top" data-label="กลุ่ม">
                                                            {{ trim((string) ($item->role_group ?? '')) !== '' ? $item->role_group : '-' }}
                                                        </td>

                                                        <td class="p-3 break-words whitespace-normal align-top" data-label="รายละเอียดงาน">
                                                            @php
                                                                $isBackOffice = !$item->project?->id;
                                                                if ($isBackOffice) {
                                                                    $workloadDetail = trim((string) ($item->job_detail ?? ''));
                                                                    if ($workloadDetail === '') {
                                                                        $workloadDetail = trim((string) ($item->task_detail ?? ''));
                                                                    }
                                                                } else {
                                                                    $workloadDetail = trim((string) ($item->task_detail ?? ''));
                                                                    if ($workloadDetail === '') {
                                                                        $workloadDetail = trim((string) ($item->job_detail ?? ''));
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="text-slate-700">{{ $workloadDetail !== '' ? $workloadDetail : '-' }}</div>
                                                        </td>

                                                        <td class="p-3 text-center font-semibold text-slate-700 align-top" data-label="ชั่วโมงการทำงาน">
                                                            {{ number_format((float) $hoursInMonth, 2) }} ชั่วโมง
                                                        </td>

                                                        <td class="p-3 text-center font-semibold text-slate-700 align-top" data-label="จำนวนวันทำงาน">
                                                            {{ $item->days_in_month }} วัน
                                                        </td>

                                                        <td class="p-3 text-right font-semibold align-top" data-label="FTE">
                                                            {{ number_format($item->fte_month, 7) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-gray-400 italic text-center py-6">ไม่มีข้อมูล FTE สำหรับบุคคลนี้</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="p-8 text-center text-gray-400">ไม่พบข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
