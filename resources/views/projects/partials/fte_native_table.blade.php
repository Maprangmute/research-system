<div class="fte-tools mb-3">
    <input id="fte-search-input" type="search" class="fte-tools__search"
        placeholder="ค้นหา FTE: ชื่อ / กลุ่ม / ภาระงาน"
        oninput="window.applyFteRowSearchFilter?.()">
    <div class="fte-tools__actions">
        <button id="fte-search-clear-btn" type="button" class="fte-tools__clear-btn"
            onclick="const input=document.getElementById('fte-search-input'); if(input){input.value=''; window.applyFteRowSearchFilter?.(); input.focus();}">
            ล้างคำค้นหา
        </button>
    </div>
</div>
<div id="fte-table-wrap" class="fte-native-scroll is-active mb-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="fte-table-grid fte-native-grid w-full border-collapse text-sm">
        <colgroup>
            <col style="width:3%"><col style="width:13%"><col style="width:13%"><col style="width:13%">
            <col style="width:18%"><col style="width:10%"><col style="width:10%"><col style="width:7%">
            <col style="width:8%"><col style="width:5%">
        </colgroup>
        <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-600">
            <tr class="border-b border-slate-200">
                <th class="w-12 px-2 py-3 text-center">#</th>
                <th class="min-w-40 px-2 py-3">ชื่อ</th>
                <th class="min-w-40 px-2 py-3">กลุ่ม</th>
                <th class="min-w-40 px-2 py-3">ภาระงาน</th>
                <th class="min-w-52 px-2 py-3">รายละเอียดงาน</th>
                <th class="min-w-32 px-2 py-3">เริ่ม</th>
                <th class="min-w-32 px-2 py-3">สิ้นสุด</th>
                <th class="w-24 px-2 py-3">ชั่วโมง</th>
                <th class="w-28 px-2 py-3">FTE ที่ได้รับ</th>
                <th class="w-16 px-2 py-3 text-center">ลบ</th>
            </tr>
        </thead>
        <tbody id="fte-container" class="divide-y divide-slate-200">
            @foreach ($fteRows as $i => $fte)
                @php
                    $selectedTask = data_get($fte, 'task_detail');
                    $knownTask = in_array($selectedTask, $taskDetails, true);
                @endphp
                <tr id="fte-{{ $i }}" class="fte-row bg-white hover:bg-slate-50/70" data-deleted="0">
                    <td class="fte-table-index px-2 py-2 text-center font-bold text-blue-700">
                        <span class="fte-index">{{ $i + 1 }}</span>
                        <input type="hidden" name="fte_roles[{{ $i }}][id]" value="{{ data_get($fte, 'id') }}">
                        <input type="hidden" name="fte_roles[{{ $i }}][fte_value_research]" value="{{ data_get($fte, 'fte_value_research') }}">
                    </td>
                    <td class="px-2 py-2"><input name="fte_roles[{{ $i }}][name]" value="{{ data_get($fte, 'name') }}" placeholder="พิมพ์หรือเลือกชื่อ" class="fte-person-picker w-full border p-2 rounded" required></td>
                    <td class="px-2 py-2"><select name="fte_roles[{{ $i }}][role_group]" class="w-full border p-2 rounded" required><option value="" disabled>เลือกกลุ่ม</option>@foreach($roleGroups as $value)<option value="{{ $value }}" @selected(data_get($fte, 'role_group') === $value)>{{ $value }}</option>@endforeach</select></td>
                    <td class="px-2 py-2"><select name="fte_roles[{{ $i }}][job_detail]" class="w-full border p-2 rounded" required><option value="" disabled>เลือกภาระงาน</option>@foreach($jobDetails as $value)<option value="{{ $value }}" @selected(data_get($fte, 'job_detail') === $value)>{{ $value }}</option>@endforeach</select></td>
                    <td class="px-2 py-2"><select name="fte_roles[{{ $i }}][task_detail]" class="w-full border p-2 rounded" required><option value="" disabled @selected(blank($selectedTask))>เลือกรายละเอียดงาน</option>@if(filled($selectedTask) && !$knownTask)<option value="{{ $selectedTask }}" selected>{{ $selectedTask }}</option>@endif @foreach($taskDetails as $value)<option value="{{ $value }}" @selected($selectedTask === $value)>{{ $value }}</option>@endforeach</select></td>
                    <td class="px-2 py-2"><input name="fte_roles[{{ $i }}][start_date]" type="text" value="{{ data_get($fte, 'start_date') }}" placeholder="เลือกวันที่เริ่ม" class="fte-shared-datepicker w-full border p-2 rounded bg-white cursor-pointer" required readonly data-date-role="start"></td>
                    <td class="px-2 py-2"><input name="fte_roles[{{ $i }}][end_date]" type="text" value="{{ data_get($fte, 'end_date') }}" placeholder="เลือกวันที่สิ้นสุด" class="fte-shared-datepicker w-full border p-2 rounded bg-white cursor-pointer" required readonly data-date-role="end"></td>
                    <td class="px-2 py-2"><input type="number" step="0.1" min="0" name="fte_roles[{{ $i }}][hours]" value="{{ data_get($fte, 'hours') }}" placeholder="0" class="fte-hours w-full border p-2 rounded text-right" required></td>
                    <td class="px-2 py-2 text-right font-semibold text-slate-700">
                        <input type="hidden" name="fte_roles[{{ $i }}][fte_value]" value="{{ data_get($fte, 'fte_value') }}" class="fte-value">
                        <span class="fte-value-display fte-table-display-value">{{ rtrim(rtrim(number_format((float) data_get($fte, 'fte_value', 0), 8, '.', ''), '0'), '.') ?: '0' }}</span>
                    </td>
                    <td class="px-2 py-2 text-center"><button type="button" onclick="removeFteRow('fte-{{ $i }}')" class="fte-table-remove-btn">ลบ</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
