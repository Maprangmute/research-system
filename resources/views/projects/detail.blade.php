@extends('layouts.app')


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '{{ session('success') }}',
                confirmButtonText: 'ตกลง',
                timer: 2000
            });
        });
    </script>
@endif

@section('content')
    <div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
        @php
            $status = $project->planning_status ?? '-';
            // Mapping สีพื้นหลังแต่ละสถานะ
            $statusBg = [
                'Project Planing (เริ่มต้น - submit IRB)' => 'bg-[#FFE599]',
                'เก็บข้อมูล' => 'bg-[#B6E2D3]',
                'วิเคราะห์ข้อมูล' => 'bg-[#B3D8F7]',
                'เขียนรายงานและpublication' => 'bg-[#D6C1E6]',
                'ปิดโปรเจค' => 'bg-[#C6F1E7]',
                'ยกเลิกโปรเจค' => 'bg-[#F7A9A8]',
            ];
            $statusText = $status !== '-' ? $status : 'ไม่ระบุสถานะ';
            $bgClass = $statusBg[$status] ?? 'bg-gray-200';
        @endphp

        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('projects.index') }}" class="text-blue-600 hover:underline">
                ← กลับไปยังรายการโครงการ
            </a>
            <span
                class="px-4 py-1 rounded-lg font-semibold {{ $bgClass }} text-black shadow border border-gray-300 text-sm">
                  <span> สถานะ : </span>{{ $statusText }}
            </span>
        </div>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">🔍 รายละเอียดโครงการ</h2>
            <div class="space-x-2">
                <a href="{{ route('projects.edit', $project->id) }}"
                    class="bg-yellow-200 hover:bg-yellow-300 text-yellow-800 px-4 py-2 rounded shadow-sm">✏️
                    แก้ไข</a>
                <a href="{{ route('projects.progress_reports.index', $project->id) }}"
                    class="bg-indigo-200 hover:bg-indigo-300 text-indigo-800 px-4 py-2 rounded shadow-sm">
                    📑 Progress Report
                </a>

               @php
    $isOwner = $project->user_id === auth()->id();
    $isAdmin = auth()->user() && auth()->user()->role === 'admin';
@endphp

@if($isOwner || $isAdmin)
    <a href="{{ route('projects.pdf', $project->id) }}" target="_blank"
        class="bg-green-200 hover:bg-green-300 text-green-800 px-4 py-2 rounded shadow-sm">
        🖨️ พิมพ์ PDF
    </a>
@endif
                <a href="{{ route('projects.gantt', $project->id) }}"
                    class="bg-blue-200 hover:bg-blue-300 text-blue-800 px-4 py-2 rounded shadow-sm">
                    📊 Gantt Chart
                </a>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                    class="inline-flex items-center">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโครงการนี้?')"
                        class="bg-red-200 hover:bg-red-300 text-red-800 px-4 py-2 rounded shadow-sm">
                        🗑️ ลบ
                    </button>
                </form>

            </div>
        </div>

        <!-- 🔵 Section A: ข้อมูลโครงการ -->
        <div class="mb-8 bg-white rounded-lg shadow-sm">
            <h3 class="font-semibold text-xl text-blue-700 border-b pb-2 mb-4">A: ข้อมูลโครงการ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-700">
                <div><span class="font-semibold">ชื่อโครงการ (TH):</span> {{ $project->project_name_th }}</div>
                <div><span class="font-semibold">ชื่อโครงการ (EN):</span> {{ $project->project_name_en }}</div>
                <div><span class="font-semibold">ประเภท:</span> {{ $project->project_type }}</div>
                <div><span class="font-semibold">ลักษณะ</span> {{ $project->project_list }}</div>
                <div><span class="font-semibold">Theme:</span> {{ $project->theme }}</div>
                
                <div class="md:col-span-2">
                    <span class="font-semibold">Shortcut:</span>
                    @if ($project->shortcut_link)
                        <a href="{{ $project->shortcut_link }}" class="text-blue-600 underline ml-1" target="_blank">
                            {{ $project->shortcut_link }}
                        </a>
                    @else
                        <span class="text-gray-500 ml-1">-</span>
                    @endif
                </div>
                <div><span class="font-semibold">ขนาด:</span> {{ $project->size ?? '-' }}</div>
                <div><span class="font-semibold">รหัสโครงการ R:</span> {{ $project->r_code ?? '-' }}</div>
                <div><span class="font-semibold">ปีโปรเจค:</span> {{ $project->start_year ?? '-' }}</div>
                <div><span class="font-semibold">สถานะ: </span> {{ $project->planning_status ?? '-' }}</div>
            </div>
        </div>

        <!-- 🟢 Section B: ข้อมูลแหล่งทุน -->
        <div class="mb-8 bg-white rounded-lg shadow-sm ">
            <h3 class="font-semibold text-xl text-green-700 border-b pb-2 mb-4">B: ข้อมูลแหล่งทุน</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-700">
                <div><span class="font-semibold">DA102 - ประเภทแหล่งทุน:</span> {{ $project->funding_type ?? '-' }}</div>
                <div><span class="font-semibold">DA103 - ชื่อแหล่งทุน:</span> {{ $project->funding_source ?? '-' }}</div>
                <div><span class="font-semibold">DA104 - รัฐบาล / เอกชน / มูลนิธิ:</span>
                    {{ $project->funding_org_type ?? '-' }}</div>
                <div><span class="font-semibold">DA105 - ชื่อทุน:</span> {{ $project->funding_name ?? '-' }}</div>
                <div><span class="font-semibold">DA106 - งบประมาณ (บาท):</span>
                    {{ $project->budget ? number_format($project->budget, 2) : '-' }}
                </div>
            </div>
        </div>
        <!-- 🟢 Section C: ข้อมูลแหล่งทุน -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-lg ring-1 ring-slate-100 border-separate border-spacing-0 mb-6">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tl-2xl w-12">ลำดับ</th>
                    <th class="border px-3 py-2 text-left font-semibold">ชื่อ</th>
                    <th class="border px-3 py-2 text-left font-semibold">สังกัด</th>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tr-2xl">บทบาท</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    $hasPI = !empty(trim($project->pi_name ?? '')) || !empty(trim($project->pi_affiliation ?? ''));
                    $copiList = $project->copis ?? collect();
                    $copiReal = $copiList->filter(
                        fn($copi) => !empty(trim($copi->name ?? '')) || !empty(trim($copi->affiliation ?? '')),
                    );
                    $hasCOPI = $copiReal->count() > 0;
                @endphp
                @if (!$hasPI && !$hasCOPI)
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500">ไม่มีข้อมูล</td>
                    </tr>
                @else
                    @if ($hasPI)
                        <tr>
                            <td class="border px-3 py-2 text-center">1</td>
                            <td class="border px-3 py-2">{{ $project->pi_name ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $project->pi_affiliation ?: '-' }}</td>
                            <td class="border px-3 py-2 text-center">PI</td>
                        </tr>
                    @endif
                    @foreach ($copiReal as $i => $copi)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $hasPI ? $i + 2 : $i + 1 }}</td>
                            <td class="border px-3 py-2">{{ $copi->name ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $copi->affiliation ?: '-' }}</td>
                            <td class="border px-3 py-2 text-center">Co-PI</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <h3 class="font-semibold text-lg text-blue-700 mb-2">Co-Researcher (ภายใน)</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-mb ring-1 ring-slate-100 border-separate border-spacing-0 mb-6 text-sm">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tl-2xl">#</th>
                    <th class="border px-3 py-2 font-semibold">ชื่อ</th>
                    <th class="border px-3 py-2 font-semibold rounded-tr-2xl">สังกัด</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    $coInList = $project->coResearches->where('unit_status', 'ภายใน')->filter(function ($co) {
                        return !empty(trim($co->name)) || !empty(trim($co->affiliation));
                    });
                    $hasData = $coInList->count() > 0;
                    $inIndex = 1;
                @endphp
                @if ($hasData)
                    @foreach ($coInList as $co)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $inIndex++ }}</td>
                            <td class="border px-3 py-2">{{ $co->name ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $co->affiliation ?: '-' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-500">ไม่มีนักวิจัยภายใน</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <h3 class="font-semibold text-lg text-blue-700 mb-2">Co-Researcher (ภายนอก)</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-mb ring-1 ring-slate-100 border-separate border-spacing-0 mb-6 text-sm">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tl-2xl">#</th>
                    <th class="border px-3 py-2 font-semibold">ชื่อ</th>
                    <th class="border px-3 py-2 font-semibold rounded-tr-2xl">สังกัด</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    $coOutList = $project->coResearches->where('unit_status', 'ภายนอก')->filter(function ($co) {
                        return !empty(trim($co->name)) || !empty(trim($co->affiliation));
                    });
                    $hasDataOut = $coOutList->count() > 0;
                    $outIndex = 1;
                @endphp
                @if ($hasDataOut)
                    @foreach ($coOutList as $co)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $outIndex++ }}</td>
                            <td class="border px-3 py-2">{{ $co->name ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $co->affiliation ?: '-' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-500">ไม่มีนักวิจัยภายนอก</td>
                    </tr>
                @endif
            </tbody>
        </table>



        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รหัส IRB</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-mb ring-1 ring-slate-100 border-separate border-spacing-0 mb-6">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tl-2xl w-12">ลำดับ</th>
                    <th class="border px-3 py-2 font-semibold">รหัส IRB</th>
                    <th class="border px-3 py-2 font-semibold">จากหน่วยงาน</th>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tr-2xl">วันหมดอายุ</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    // ถ้าอยากให้แถวว่างจริง ๆ ไม่แสดง ให้ filter ข้อมูลที่ว่างออกก่อน
                    $irbs = $project->irbs->filter(function ($irb) {
                        return !empty(trim($irb->code)) || !empty(trim($irb->source)) || !empty(trim($irb->exp_date));
                    });
                @endphp
                @if ($irbs->count())
                    @foreach ($irbs as $i => $irb)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
                            <td class="border px-3 py-2">{{ $irb->code ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $irb->source ?: '-' }}</td>
                            <td class="border px-3 py-2 text-center">
                                @php
                                    \Carbon\Carbon::setLocale('th');
                                    $date = $irb->exp_date ? \Carbon\Carbon::parse($irb->exp_date) : null;
                                    $thaiMonth = $date
                                        ? $date->translatedFormat('j F') . ' ' . ($date->year + 543)
                                        : '-';
                                @endphp
                                {{ $thaiMonth }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500">ไม่มีข้อมูล</td>
                    </tr>
                @endif
            </tbody>
        </table>



        <h3 class="font-semibold text-lg text-blue-700 mb-2">E: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-md ring-1 ring-slate-100 border-separate border-spacing-0 mb-6 text-sm">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tl-2xl">#</th>
                    <th class="border px-3 py-2 font-semibold">ชื่อ</th>
                    <th class="border px-3 py-2 font-semibold">กลุ่ม</th>
                    <th class="border px-3 py-2 font-semibold">ภาระงาน</th>
                    <th class="border px-3 py-2 font-semibold">รายละเอียดภารกิจ</th>
                    <th class="border px-3 py-2 font-semibold">วันเริ่ม</th>
                    <th class="border px-3 py-2 font-semibold">วันสิ้นสุด</th>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tr-2xl">FTE</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    $fteList = $project->fteRoles ?? collect();
                    // กรองเฉพาะแถวที่มีข้อมูลจริง
                    $fteReal = $fteList->filter(function ($fte) {
                        return !empty(trim($fte->name)) || !empty(trim($fte->role_group));
                    });
                @endphp
                @if ($fteReal->count())
                    @foreach ($fteReal as $i => $fte)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
                            <td class="border px-3 py-2">{{ $fte->name ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $fte->role_group ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $fte->job_detail ?: '-' }}</td>
                            <td class="border px-3 py-2">{{ $fte->task_detail ?? '-' }}</td>
                            <td class="border px-3 py-2 text-center">
                                @php
                                    \Carbon\Carbon::setLocale('th');
                                    $date = $fte->start_date ? \Carbon\Carbon::parse($fte->start_date) : null;
                                    $thaiDate = $date
                                        ? $date->translatedFormat('j F') . ' ' . ($date->year + 543)
                                        : '-';
                                @endphp
                                {{ $thaiDate }}
                            </td>
                            <td class="border px-3 py-2 text-center">
                                @php
                                    \Carbon\Carbon::setLocale('th');
                                    $date = $fte->end_date ? \Carbon\Carbon::parse($fte->end_date) : null;
                                    $thaiDate = $date
                                        ? $date->translatedFormat('j F') . ' ' . ($date->year + 543)
                                        : '-';
                                @endphp
                                {{ $thaiDate }}
                            </td>
                            <td class="border px-3 py-2 text-center">{{ $fte->fte_value }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="py-6 text-center text-gray-500">ไม่มีข้อมูล</td>
                    </tr>
                @endif
            </tbody>
        </table>




        <h3 class="font-semibold text-lg text-blue-700 mb-2">F: ผลงานวิจัย (Outputs)</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-md ring-1 ring-slate-100 border-separate border-spacing-0 mb-6 table-fixed">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-2 py-2 text-center font-semibold rounded-tl-2xl w-[5%]">ลำดับ</th>
                    <th class="border px-2 py-2 font-semibold w-[35%]">ชื่อผลงาน</th>
                    <th class="border px-2 py-2 font-semibold w-[20%]">ประเภท</th>
                    <th class="border px-2 py-2 font-semibold w-[20%]">รายละเอียด</th>
                    <th class="border px-2 py-2 text-center font-semibold rounded-tr-2xl w-[20%]">ปี</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    $outputs = $project->outputs ?? collect();
                    $outputsReal = $outputs->filter(function ($output) {
                        return !empty(trim($output->title)) || !empty(trim($output->type));
                    });
                @endphp
                @if ($outputsReal->count())
                    @foreach ($outputsReal as $i => $output)
                        <tr>
                            <td class="border px-2 py-2 text-center w-[5%]">{{ $i + 1 }}</td>
                            <td class="border px-2 py-2 w-[35%] break-words">{{ $output->title ?: '-' }}</td>
                            <td class="border px-2 py-2 w-[20%] break-words">{{ $output->type ?: '-' }}</td>
                            <td class="border px-2 py-2 w-[20%] break-words">{{ $output->output ?? '-' }}</td>
                            <td class="border px-2 py-2 text-center w-[20%] break-words">
                                @php
                                    \Carbon\Carbon::setLocale('th');
                                    $dateValue = $output->year;
                                    $formatted = '-';
                                    try {
                                        if (!empty($dateValue)) {
                                            $date = \Carbon\Carbon::parse($dateValue);
                                            $formatted = $date->translatedFormat('j F') . ' ' . ($date->year + 543);
                                        }
                                    } catch (\Exception $e) {
                                        $formatted = $dateValue;
                                    }
                                @endphp
                                {{ $formatted }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">ไม่มีข้อมูล</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">G: ยุทธศาสตร์ Siriraj Health Policy</h3>
        <table
            class="min-w-full bg-white rounded-2xl shadow-s ring-1 ring-slate-100 border-separate border-spacing-0 mb-6 table-fixed text-sm">
            <thead class="bg-blue-50">
                <tr>
                    <th class="border px-3 py-2 text-left font-semibold rounded-tl-2xl w-[25%]">หมวด</th>
                    <th class="border px-3 py-2 text-left font-semibold w-[65%]">รายการ</th>
                    <th class="border px-3 py-2 text-center font-semibold rounded-tr-2xl w-[10%]">มี</th>
                </tr>
            </thead>
            <tbody class="text-[15px]">
                @php
                    $strategies = [
                        '1' => [
                            'label' => 'AA501: Capacity Building',
                            'items' => [
                                '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                                '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                                '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                                '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                                '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                            ],
                        ],
                        '2' => [
                            'label' => 'AA502: Excellence Research',
                            'items' => [
                                '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                                '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                                '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                            ],
                        ],
                        '3' => [
                            'label' => 'AA503: Health Policy Linkage',
                            'items' => [
                                '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                                '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                                '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                                '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                                '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                            ],
                        ],
                        '4' => [
                            'label' => 'AA504: Partnership Engagement',
                            'items' => [
                                '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                                '2' => '4.2 การสร้างความร่วมมือฯ',
                            ],
                        ],
                        '5' => [
                            'label' => 'AA505: System Support',
                            'items' => [
                                '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                                '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                            ],
                        ],
                    ];
                @endphp

                @foreach ($strategies as $sKey => $strategy)
                    <tr>
                        <td class="border px-3 py-2 font-bold w-[25%]" rowspan="{{ count($strategy['items']) }}">
                            {{ $strategy['label'] }}
                        </td>
                        @foreach ($strategy['items'] as $iKey => $desc)
                            @php $field = "strategy_{$sKey}_{$iKey}"; @endphp
                            @if (!$loop->first)
                    <tr>
                @endif
                <td class="border px-3 py-2 w-[65%]">{{ $desc }}</td>
                <td class="border px-3 py-2 text-center w-[10%]">
                    {!! !empty($project->$field) ? '<span class="text-green-600 text-lg font-bold">✓</span>' : '' !!}
                </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>

        @php
            // ข้อมูลผู้แก้ไขล่าสุด
            $user = $project->updatedBy;
            $updatedBy = $user ? trim(($user->prefix ?? '') . ' ' . $user->name) : '-';

            if ($project->updated_at) {
                $carbonDate = \Carbon\Carbon::parse($project->updated_at)->setTimezone('Asia/Bangkok')->locale('th');
                $dayMonth = $carbonDate->translatedFormat('j F');
                $yearBuddhist = $carbonDate->year + 543;
                $time = $carbonDate->format('H:i');
                $updatedAt = "{$dayMonth} {$yearBuddhist} {$time} น.";
            } else {
                $updatedAt = '';
            }

            // ข้อมูลผู้สร้าง (createdBy)
            $creator = $project->createdBy ?? null;
            $createdBy = $creator ? trim(($creator->prefix ?? '') . ' ' . $creator->name) : '-';

            if ($project->created_at) {
                $createdAtDate = \Carbon\Carbon::parse($project->created_at)->setTimezone('Asia/Bangkok')->locale('th');
                $createdDayMonth = $createdAtDate->translatedFormat('j F');
                $createdYearBuddhist = $createdAtDate->year + 543;
                $createdTime = $createdAtDate->format('H:i');
                $createdAt = "{$createdDayMonth} {$createdYearBuddhist} {$createdTime} น.";
            } else {
                $createdAt = '';
            }
        @endphp

        <div class="mt-8 flex flex-col space-y-1">
            <div class="text-xs text-gray-500 italic">
                ข้อมูลล่าสุดโดย {{ $updatedBy }}
                @if ($updatedAt)
                    | อัปเดทเมื่อ {{ $updatedAt }}
                @endif
            </div>
            <div class="text-xs text-gray-400 italic">
                สร้างโดย {{ $createdBy }}
                @if ($createdAt)
                    | สร้างเมื่อ {{ $createdAt }}
                @endif
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('projects.index') }}" class="text-blue-600 hover:underline">
                ← กลับไปยังรายการโครงการ
            </a>
        </div>
    </div>
@endsection
