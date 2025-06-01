@extends('layouts.app')


{{-- @if (session('success'))
    <div class="text-green-600 font-semibold mb-4">
        ✅ {{ session('success') }}
    </div>
@endif --}}

@section('content')
    <div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
        <div class="mb-4">
            <a href="javascript:history.back()" class="text-blue-600 hover:underline text-base font-semibold">
                <<< ย้อนกลับ </a>
        </div>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">🔍 รายละเอียดโครงการ</h2>
            <div class="space-x-2">
                <a href="{{ route('projects.edit', $project->id) }}"
                    class="bg-yellow-200 hover:bg-yellow-300 text-yellow-800 px-4 py-2 rounded shadow-sm">✏️
                    แก้ไข</a>
                <a href="{{ route('projects.pdf', $project->id) }}" target="_blank"
                    class="bg-green-200 hover:bg-green-300 text-green-800 px-4 py-2 rounded shadow-sm">🖨️ พิมพ์ PDF</a>
                <!-- 📊 Gantt Chart -->
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

        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
        <table class="w-full table-auto border mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-center w-12">ลำดับ</th>
                    <th class="border px-2 py-1">ชื่อ</th>
                    <th class="border px-2 py-1">สังกัด</th>
                    <th class="border px-2 py-1 text-center">บทบาท</th>
                </tr>
            </thead>
            <tbody>
                {{-- PI --}}
                <tr>
                    <td class="border px-2 py-1 text-center">1</td>
                    <td class="border px-2 py-1">{{ $project->pi_name ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ $project->pi_affiliation ?? '-' }}</td>
                    <td class="border px-2 py-1 text-center">PI</td>
                </tr>

                {{-- COPI --}}
                @forelse ($project->copis as $i => $copi)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i + 2 }}</td>
                        <td class="border px-2 py-1">{{ $copi->name }}</td>
                        <td class="border px-2 py-1">{{ $copi->affiliation }}</td>
                        <td class="border px-2 py-1 text-center">Co-PI</td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="3" class="text-center py-2">ไม่มีข้อมูล</td>
                    </tr>
                @endforelse
                @if (empty($project->pi_name) && $project->copis->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center py-2 border">ไม่มีข้อมูล</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <h3 class="font-semibold text-lg text-blue-700 mb-2">Co-Researcher (ภายใน)</h3>
        <table class="w-full table-auto border mb-6 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 text-center">#</th>
                    <th class="border px-2 py-1">ชื่อ</th>
                    <th class="border px-2 py-1">สังกัด</th>
                </tr>
            </thead>
            <tbody>
                @php $inIndex = 1; @endphp
                @forelse ($project->coResearches->where('unit_status', 'ภายใน') as $co)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $inIndex++ }}</td>
                        <td class="border px-2 py-1">{{ $co->name }}</td>
                        <td class="border px-2 py-1">{{ $co->affiliation }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-2">ไม่มีนักวิจัยภายใน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <h3 class="font-semibold text-lg text-blue-700 mb-2">Co-Researcher (ภายนอก)</h3>
        <table class="w-full table-auto border mb-6 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 text-center">#</th>
                    <th class="border px-2 py-1">ชื่อ</th>
                    <th class="border px-2 py-1">สังกัด</th>
                </tr>
            </thead>
            <tbody>
                @php $inIndex = 1; @endphp
                @forelse ($project->coResearches->where('unit_status', 'ภายนอก') as $co)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $inIndex++ }}</td>
                        <td class="border px-2 py-1">{{ $co->name }}</td>
                        <td class="border px-2 py-1">{{ $co->affiliation }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-2">ไม่มีนักวิจัยภายนอก</td>
                    </tr>
                @endforelse
            </tbody>
        </table>




        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รหัส IRB</h3>
        <table class="w-full table-auto border mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-center w-12">ลำดับ</th>
                    <th class="border px-2 py-1">รหัส IRB</th>
                    <th class="border px-2 py-1">จากหน่วยงาน</th>
                    <th class="border px-2 py-1 text-center">วันหมดอายุ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($project->irbs as $i => $irb)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $irb->code }}</td>
                        <td class="border px-2 py-1">{{ $irb->source }}</td>
                        <td class="border px-2 py-1 text-center">
                            @php
                                \Carbon\Carbon::setLocale('th');
                                $date = \Carbon\Carbon::parse($irb->exp_date);
                                $thaiMonth = $date->translatedFormat('j F') . ' ' . ($date->year + 543);
                            @endphp
                            {{ $thaiMonth }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-2 border">ไม่มีข้อมูล</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <table class="w-full table-auto border mb-6 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 text-center">#</th>
                    <th class="border px-2 py-1">ชื่อ</th>
                    <th class="border px-2 py-1">กลุ่ม</th>
                    <th class="border px-2 py-1">ภาระงาน</th>
                    <th class="border px-2 py-1">วันเริ่ม</th>
                    <th class="border px-2 py-1">วันสิ้นสุด</th>
                    <th class="border px-2 py-1 text-center">FTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($project->fteRoles as $i => $fte)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $fte->name }}</td>
                        <td class="border px-2 py-1">{{ $fte->role_group }}</td>
                        <td class="border px-2 py-1">{{ $fte->job_detail }}</td>
                        <td class="border px-2 py-1">
                            {{ $fte->start_date ? \Carbon\Carbon::parse($fte->start_date)->format('d/m/Y') : '-' }}</td>
                        <td class="border px-2 py-1">
                            {{ $fte->end_date ? \Carbon\Carbon::parse($fte->end_date)->format('d/m/Y') : '-' }}</td>
                        <td class="border px-2 py-1 text-center">{{ $fte->fte_value }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-2">ไม่มีข้อมูล</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <h3 class="font-semibold text-lg text-blue-700 mb-2">F: ผลงานวิจัย (Outputs)</h3>
        <table class="w-full table-auto border mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-center w-12">ลำดับ</th>
                    <th class="border px-2 py-1">ชื่อผลงาน</th>
                    <th class="border px-2 py-1">ประเภท</th>
                    <th class="border px-2 py-1">รายละเอียด</th>
                    <th class="border px-2 py-1 text-center w-24">ปี</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($project->outputs as $i => $output)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $output->title }}</td>
                        <td class="border px-2 py-1">{{ $output->type }}</td>
                        <td class="border px-2 py-1">{{ $output->output ?? '-' }}</td>
                        <td class="border px-2 py-1 text-center">
                            {{ is_numeric($output->year) ? $output->year : $output->year }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-2 border">ไม่มีข้อมูล</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">G: ยุทธศาสตร์ Siriraj Health Policy</h3>
        <table class="w-full table-auto text-sm mb-6 border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-3 py-2 text-left w-52">หมวด</th>
                    <th class="border px-3 py-2 text-left">รายการ</th>
                    <th class="border px-3 py-2 text-center w-12">มี</th>
                </tr>
            </thead>
            <tbody>
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
                        <td class="border px-3 py-2 font-bold" rowspan="{{ count($strategy['items']) }}">
                            {{ $strategy['label'] }}
                        </td>
                        @foreach ($strategy['items'] as $iKey => $desc)
                            @php $field = "strategy_{$sKey}_{$iKey}"; @endphp
                            @if ($loop->first == false)
                    <tr>
                @endif
                <td class="border px-3 py-2">{{ $desc }}</td>
                <td class="border px-3 py-2 text-center">
                    {!! !empty($project->$field) ? '✓' : '' !!}
                </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
