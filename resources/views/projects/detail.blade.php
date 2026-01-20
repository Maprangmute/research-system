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
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
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
            $formatDateBE = function ($value) {
                if (!$value) {
                    return '-';
                }
                try {
                    $d = \Carbon\Carbon::parse($value)->locale('th');
                    return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
                } catch (\Exception $e) {
                    return $value ?: '-';
                }
            };
        @endphp

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

                {{-- Left: Title --}}
                <div>
                    <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                        ← กลับไปยังรายการโครงการ
                    </a>
                    <h2 class="text-2xl font-semibold text-slate-900 mt-2">
                        {{ $project->project_name_th ?? '-' }}
                    </h2>
                </div>

                {{-- Right: Status + Actions --}}
                <div class="flex flex-col gap-2 w-full md:w-auto">

                    {{-- Status (1 full row) --}}
                    <span
                        class="self-end mt-2 mb-1 w-fit px-3 py-1 rounded-full text-xs font-semibold {{ $bgClass }} text-slate-900 border border-slate-300">
                        สถานะ: {{ $statusText }}
                    </span>



                    {{-- Action buttons (another row) --}}
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('projects.edit', $project->id) }}"
                            class="px-3 py-2 rounded-lg bg-amber-100 text-amber-800 text-sm font-semibold hover:bg-amber-200">
                            ✏️ แก้ไข
                        </a>

                        <a href="{{ route('projects.progress_reports.index', $project->id) }}"
                            class="px-3 py-2 rounded-lg bg-indigo-100 text-indigo-800 text-sm font-semibold hover:bg-indigo-200">
                            📑 Progress Report
                        </a>

                        @php
                            $isOwner = $project->user_id === auth()->id();
                            $isAdmin = auth()->user() && auth()->user()->role === 'admin';
                        @endphp

                        @if ($isOwner || $isAdmin)
                            <a href="{{ route('projects.pdf', $project->id) }}" target="_blank"
                                class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-semibold hover:bg-emerald-200">
                                🖨️ PDF
                            </a>
                        @endif

                        <a href="{{ route('projects.gantt', $project->id) }}"
                            class="px-3 py-2 rounded-lg bg-sky-100 text-sky-800 text-sm font-semibold hover:bg-sky-200">
                            📊 Gantt
                        </a>

                        {{-- Delete --}}
                        <button type="button" id="deleteProjectBtn"
                            class="px-3 py-2 rounded-lg bg-rose-100 text-rose-800 text-sm font-semibold hover:bg-rose-200">
                            🗑️ ลบ
                        </button>

                        <form id="delete-project-{{ $project->id }}"
                            action="{{ route('projects.destroy', $project->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>

            </div>
        </div>


        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-6">
            <!-- 🔵 Section A: ข้อมูลโครงการ -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 border-b border-slate-200 pb-3 mb-4">A: ข้อมูลโครงการ</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-slate-700">
                    <div><span class="font-semibold">ชื่อโครงการ (TH):</span> {{ $project->project_name_th ?? '-' }}</div>
                    <div><span class="font-semibold">ชื่อโครงการ (EN):</span> {{ $project->project_name_en ?? '-' }}</div>
                    <div><span class="font-semibold">ประเภท:</span> {{ $project->project_type ?? '-' }}</div>
                    <div><span class="font-semibold">ลักษณะ:</span> {{ $project->project_list ?? '-' }}</div>
                    <div><span class="font-semibold">Theme:</span> {{ $project->theme ?? '-' }}</div>

                    <div class="md:col-span-2">
                        <span class="font-semibold">Shortcut:</span>
                        @if ($project->shortcut_link)
                            <a href="{{ $project->shortcut_link }}" class="text-blue-600 underline ml-1" target="_blank"
                                rel="noopener">
                                {{ $project->shortcut_link }}
                            </a>
                        @else
                            <span class="text-gray-500 ml-1">-</span>
                        @endif
                    </div>
                    <div><span class="font-semibold">ขนาด:</span> {{ $project->size ?? '-' }}</div>
                    <div><span class="font-semibold">รหัสโครงการ R:</span> {{ $project->r_code ?? '-' }}</div>
                    <div><span class="font-semibold">ปีโปรเจค:</span> {{ $project->start_year ?? '-' }}</div>
                    <div><span class="font-semibold">สถานะ:</span> {{ $project->planning_status ?? '-' }}</div>
                </div>
            </div>

            <!-- 🟣 Section B: ระยะเวลาดำเนินโครงการ -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 border-b border-slate-200 pb-3 mb-4">B:
                    ระยะเวลาดำเนินโครงการ
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-slate-700">
                    <div><span class="font-semibold">วันที่โครงการเริ่มทำงานของทีมวิจัย:</span>
                        {{ $formatDateBE($project->team_start_date ?? null) }}</div>
                    <div><span class="font-semibold">วันที่สิ้นสุดการทำงานของทีมวิจัย:</span>
                        {{ $formatDateBE($project->team_end_date ?? null) }}</div>
                    <div><span class="font-semibold">BA001 - วันที่เริ่มเขียน Proposal:</span>
                        {{ $formatDateBE($project->proposal_date ?? null) }}</div>
                    <div><span class="font-semibold">BC101 - วันที่สัญญาอนุมัติ:</span>
                        {{ $formatDateBE($project->contract_date ?? null) }}</div>
                    <div><span class="font-semibold">BC102 - วันที่โครงการเริ่ม (ตามสัญญา):</span>
                        {{ $formatDateBE($project->start_date ?? null) }}</div>
                    <div><span class="font-semibold">BC103 - วันที่สิ้นสุด (ตามสัญญา):</span>
                        {{ $formatDateBE($project->end_date ?? null) }}</div>
                </div>
            </div>

            <!-- 🟢 Section B: ข้อมูลแหล่งทุน -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 border-b border-slate-200 pb-3 mb-4">C: ข้อมูลแหล่งทุน</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-slate-700">
                    <div><span class="font-semibold">DA102 - ประเภทแหล่งทุน:</span> {{ $project->funding_type ?? '-' }}
                    </div>
                    <div><span class="font-semibold">DA103 - ชื่อแหล่งทุน:</span> {{ $project->funding_source ?? '-' }}
                    </div>
                    <div><span class="font-semibold">DA104 - รัฐบาล / เอกชน / มูลนิธิ:</span>
                        {{ $project->funding_org_type ?? '-' }}</div>
                    <div><span class="font-semibold">DA105 - ชื่อทุน:</span> {{ $project->funding_name ?? '-' }}</div>
                    <div><span class="font-semibold">DA106 - งบประมาณ (บาท):</span>
                        {{ $project->budget ? number_format($project->budget, 2) : '-' }}
                    </div>
                </div>
            </div>
            <!-- 🟢 Section C: ข้อมูลแหล่งทุน -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 mb-2">D: Roles and Responsibilities</h3>
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
                            $hasPI =
                                !empty(trim($project->pi_name ?? '')) || !empty(trim($project->pi_affiliation ?? ''));
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

                {{-- ===== Co-Researchers: จัดกลุ่มเป็น 3 หมวด ===== --}}
                @php
                    $coAll = ($project->coResearches ?? collect())->filter(function ($co) {
                        return !empty(trim($co->name)) || !empty(trim($co->affiliation));
                    });

                    // ตัดช่องว่างทุกชนิด + แปลงเป็นตัวพิมพ์เล็ก
                    $norm = function ($s) {
                        $t = preg_replace('/\s+/u', '', (string) ($s ?? ''));
                        return mb_strtolower($t);
                    };

                    // จัดหมวดจาก unit_status (รองรับ "นโยบาย" และสะกดผิด "นโยบาล")
                    $classify = function ($s) use ($norm) {
                        $u = $norm($s);

                        // 1) ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ (priority สูงสุด)
                        if (
                            str_contains($u, 'ภายในหน่วยวิจัย') &&
                            (str_contains($u, 'นโยบายสุขภาพ') || str_contains($u, 'นโยบาลสุขภาพ'))
                        ) {
                            return 'policy';
                        }

                        // 2) ภายในคณะแพทยศาสตร์ศิริราชพยาบาล (หรือมีคำว่า "ภายใน")
                        if (
                            str_contains($u, 'ภายในคณะแพทยศาสตร์ศิริราชพยาบาล') ||
                            str_contains($u, 'ภายใน') ||
                            str_contains($u, 'internal')
                        ) {
                            return 'internal';
                        }

                        // 3) ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล (หรือมีคำว่า "ภายนอก")
                        if (
                            str_contains($u, 'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล') ||
                            str_contains($u, 'ภายนอก') ||
                            str_contains($u, 'external')
                        ) {
                            return 'external';
                        }

                        return 'unknown';
                    };

                    $groups = $coAll->groupBy(fn($co) => $classify($co->unit_status));
                    $coPolicy = $groups->get('policy', collect())->values();
                    $coInternal = $groups->get('internal', collect())->values();
                    $coExternal = $groups->get('external', collect())->values();
                @endphp

                {{-- ===== 1) ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ ===== --}}
                <h3 class="font-semibold text-base text-slate-800 mb-2">Co-Researcher
                    (ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ)</h3>
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
                        @if ($coPolicy->count())
                            @foreach ($coPolicy as $i => $co)
                                <tr>
                                    <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
                                    <td class="border px-3 py-2">{{ $co->name ?: '-' }}</td>
                                    <td class="border px-3 py-2">{{ $co->affiliation ?: '-' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">ไม่มีนักวิจัยในหมวดนี้</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                {{-- ===== 2) ภายในคณะแพทยศาสตร์ศิริราชพยาบาล ===== --}}
                <h3 class="font-semibold text-base text-slate-800 mb-2">Co-Researcher (ภายในคณะแพทยศาสตร์ศิริราชพยาบาล)</h3>
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
                        @if ($coInternal->count())
                            @foreach ($coInternal as $i => $co)
                                <tr>
                                    <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
                                    <td class="border px-3 py-2">{{ $co->name ?: '-' }}</td>
                                    <td class="border px-3 py-2">{{ $co->affiliation ?: '-' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">ไม่มีนักวิจัยภายในคณะ</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                {{-- ===== 3) ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล ===== --}}
                <h3 class="font-semibold text-base text-slate-800 mb-2">Co-Researcher (ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล)
                </h3>
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
                        @if ($coExternal->count())
                            @foreach ($coExternal as $i => $co)
                                <tr>
                                    <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
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

            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2">
                    <h3 class="font-semibold text-lg text-blue-700">E: รหัส IRB</h3>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <label>ตัวกรอง:
                            <select id="irbFilter" class="ml-1 border rounded px-2 py-1 text-xs">
                                <option value="all">ทั้งหมด</option>
                                <option value="exp_1m">หมดอายุใน 1 เดือน</option>
                                <option value="exp_3m">หมดอายุใน 3 เดือน</option>
                                <option value="active_latest">ยังไม่หมดอายุ (ล่าสุด → เก่าสุด)</option>
                            </select>
                        </label>
                    </div>
                </div>
                <table
                    class="min-w-full bg-white rounded-2xl shadow-mb ring-1 ring-slate-100 border-separate border-spacing-0 mb-0">
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
                                return !empty(trim($irb->code)) ||
                                    !empty(trim($irb->source)) ||
                                    !empty(trim($irb->exp_date));
                            });
                        @endphp
                        @if ($irbs->count())
                            @foreach ($irbs as $i => $irb)
                                @php
                                    $expIso = $irb->exp_date
                                        ? \Carbon\Carbon::parse($irb->exp_date)->format('Y-m-d')
                                        : '';
                                @endphp
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
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 mb-3">F: รายชื่อผู้มีส่วนร่วม (FTE)</h3>

                <div class="overflow-x-auto rounded-xl shadow-sm ring-1 ring-slate-200 mb-6 bg-white">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="border px-3 py-2 text-center font-semibold rounded-tl-xl">#</th>
                                <th class="border px-3 py-2 font-semibold">ชื่อ • กลุ่ม • ภาระงาน</th>
                                <th class="border px-3 py-2 font-semibold">รายละเอียดภารกิจ</th>
                                <th class="border px-3 py-2 text-center font-semibold">ชั่วโมง</th>
                                <th class="border px-3 py-2 text-center font-semibold">FTE</th>
                                <th class="border px-3 py-2 text-center font-semibold rounded-tr-xl">วันที่เริ่ม –
                                    วันที่สิ้นสุด
                                </th>
                            </tr>
                        </thead>

                        <tbody class="text-[15px]">
                            @php
                                $fteReal = ($project->fteRoles ?? collect())->filter(fn($f) => !empty(trim($f->name)));
                            @endphp

                            @if ($fteReal->count())
                                @foreach ($fteReal as $i => $fte)
                                    @php
                                        \Carbon\Carbon::setLocale('th');
                                        $start = $fte->start_date ? \Carbon\Carbon::parse($fte->start_date) : null;
                                        $end = $fte->end_date ? \Carbon\Carbon::parse($fte->end_date) : null;

                                        $startTxt = $start
                                            ? $start->translatedFormat('j F') . ' ' . ($start->year + 543)
                                            : '-';
                                        $endTxt = $end ? $end->translatedFormat('j F') . ' ' . ($end->year + 543) : '-';
                                    @endphp

                                    <tr class="bg-white hover:bg-blue-50 transition">

                                        <!-- ลำดับ -->
                                        <td class="border px-3 py-3 text-center font-medium">
                                            {{ $i + 1 }}
                                        </td>

                                        <!-- ชื่อ + กลุ่ม + ภาระงาน -->
                                        <td class="border px-3 py-3 leading-tight">
                                            <span class="font-semibold text-slate-900">{{ $fte->name }}</span><br>

                                            <span class="text-slate-600 text-sm">
                                                ({{ $fte->role_group ?: '-' }})
                                                — {{ $fte->job_detail ?: '-' }}
                                            </span>
                                        </td>

                                        <!-- รายละเอียดภารกิจ -->
                                        <td class="border px-3 py-3 text-slate-700">
                                            {{ $fte->task_detail ?: '-' }}
                                        </td>

                                        <!-- ชั่วโมง -->
                                        <td class="border px-3 py-3 text-center font-semibold text-slate-700">
                                            {{ $fte->hours ?? '0' }}
                                        </td>

                                        <!-- FTE -->
                                        <td class="border px-3 py-3 text-center font-bold text-blue-700">
                                            {{ number_format($fte->fte_value, 7) }}
                                        </td>

                                        <td class="border px-3 py-3 text-center text-slate-700">
                                            <div class="leading-snug text text-left">
                                                <div class="mb-0.5">
                                                    <span class="font-semibold">เริ่ม:</span> {{ $startTxt }}
                                                </div>
                                                <div>
                                                    <span class="font-semibold">สิ้นสุด:</span> {{ $endTxt }}
                                                </div>
                                            </div>
                                        </td>


                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-500">
                                        ไม่มีข้อมูลผู้มีส่วนร่วม
                                    </td>
                                </tr>
                            @endif
                </tbody>
            </table>
        </div>
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5 mt-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-lg text-blue-700">สรุป FTE รายเดือน</h3>
                    <a href="{{ route('fte.project.detail', $project->id) }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ดูเพิ่มเติม
                    </a>
                </div>

                @if (!empty($fteChartLabels) && count($fteChartLabels))
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 bg-slate-50 rounded-xl border p-3" style="height:300px;">
                            <canvas id="fteProjectStackedChart"></canvas>
                        </div>
                        <div class="bg-slate-50 rounded-xl border p-3" style="height:300px;">
                            <canvas id="fteProjectYearChart"></canvas>
                        </div>
                    </div>
                @else
                    <div class="text-gray-500 text-sm">ยังไม่มีข้อมูล FTE สำหรับทำกราฟ</div>
                @endif
            </div>
            {{-- <div class="pt-4">
                <a href="{{ route('projects.index') }}"
                    class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                    ← กลับไปยังรายการโครงการ
                </a>
            </div> --}}
        </div>

    @if (!empty($fteChartLabels) && count($fteChartLabels))
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

                const labelsRaw = @json($fteChartLabels);
                const labels = labelsRaw.map(monthKey => {
                    const parts = String(monthKey || '').split('-');
                    if (parts.length !== 2) return monthKey;
                    const year = Number(parts[0]);
                    const month = parts[1];
                    if (!Number.isFinite(year)) return monthKey;
                    return `${month}/${year + 543}`;
                });

                const datasets = (@json($fteChartDatasets) || []).map(item => ({
                    label: item.label,
                    backgroundColor: stringToColor(item.label),
                    data: item.data || [],
                    stack: 'stack'
                }));

                new Chart(document.getElementById('fteProjectStackedChart'), {
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

                new Chart(document.getElementById('fteProjectYearChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($fteYearLabels),
                        datasets: [{
                            label: 'FTE รวม',
                            data: @json($fteYearData),
                            backgroundColor: '#0f766e'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endif


            @php
                use Illuminate\Support\Str;

                $outputs = $project->outputs ?? collect();

                // กรองเฉพาะรายการที่มีข้อมูลจริง
                $outputsReal = $outputs->filter(function ($output) {
                    return !empty(trim($output->title ?? '')) || !empty(trim($output->type ?? ''));
                });

                // ฟังก์ชันช่วย: ฟอร์แมตวันที่ (พ.ศ.)
                function th_date_or_raw($value)
                {
                    try {
                        if (!$value) {
                            return '-';
                        }
                        \Carbon\Carbon::setLocale('th');
                        $d = \Carbon\Carbon::parse($value);
                        return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
                    } catch (\Exception $e) {
                        return $value ?: '-';
                    }
                }

                // ฟังก์ชันช่วย: ถ้าเป็น URL ให้ลิงก์, ไม่ใช่ก็แสดงข้อความ
                function linkify_if_url($text)
                {
                    $txt = trim($text ?? '');
                    if (!$txt) {
                        return '-';
                    }
                    return filter_var($txt, FILTER_VALIDATE_URL)
                        ? '<a href="' .
                                e($txt) .
                                '" target="_blank" class="text-blue-600 underline break-words">' .
                                e($txt) .
                                '</a>'
                        : e($txt);
                }
            @endphp
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 mb-2">G: ผลงานวิจัย (Outputs)</h3>

                @if ($outputsReal->isEmpty())
                    <div class="w-full bg-white rounded-2xl shadow-md ring-1 ring-slate-100 p-6 text-center text-gray-500">
                        ไม่มีข้อมูล
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($outputsReal as $i => $output)
                            @php
                                $isEA101 = Str::startsWith((string) ($output->type ?? ''), 'EA101');
                                $isEA103 = Str::startsWith((string) ($output->type ?? ''), 'EA103');
                            @endphp

                            <div class="bg-white rounded-2xl shadow-md ring-1 ring-slate-100 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0 text-sm font-semibold text-gray-600 mt-1">
                                        {{ $i + 1 }}.
                                    </div>

                                    <div class="grow space-y-2">
                                        {{-- ชื่อผลงาน + badge ประเภท --}}
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-base font-semibold text-gray-800 break-words">
                                                {{ $output->title ?: '-' }}
                                            </div>

                                            @if (!empty($output->type))
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $isEA101 ? 'bg-blue-100 text-blue-800' : ($isEA103 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                                                    {{ $output->type }}
                                                </span>
                                            @endif>
                                        </div>

                                        {{-- รายละเอียด / ลิงก์ + วันที่ --}}
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                            <div>
                                                <div class="text-gray-500">รายละเอียด / ลิงก์</div>
                                                <div class="text-gray-800 break-words">{!! linkify_if_url($output->project_output ?? null) !!}</div>
                                            </div>
                                            <div>
                                                <div class="text-gray-500">วันที่เผยแพร่</div>
                                                <div class="text-gray-800">{{ th_date_or_raw($output->year) }}</div>
                                            </div>
                                            <div></div>
                                        </div>

                                        {{-- ===== ส่วนพิเศษ: EA101 ===== --}}
                                        @if ($isEA101)
                                            <div class="mt-3 border rounded-xl p-4 bg-blue-50/50">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <div class="text-gray-500">Journal</div>
                                                        <div class="text-gray-800">{{ $output->journal ?: '-' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-gray-500">Quartile (Q)</div>
                                                        <div class="text-gray-800">
                                                            @if ($output->quartile)
                                                                <span
                                                                    class="inline-flex items-center rounded-md bg-blue-100 text-blue-800 px-2 py-0.5 text-xs font-semibold">
                                                                    {{ $output->quartile }}
                                                                </span>
                                                            @else
                                                                -
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-gray-500">Impact Factor (IF)</div>
                                                        <div class="text-gray-800">
                                                            {{ $output->impact_factor !== null && $output->impact_factor !== '' ? $output->impact_factor : '-' }}
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Authors --}}
                                                @php
                                                    $authors = $output->authors ?? collect();
                                                    $authorsReal = $authors->filter(function ($a) {
                                                        return !empty(trim($a->name ?? '')) ||
                                                            !empty(trim($a->role ?? ''));
                                                    });
                                                @endphp

                                                <div class="mt-3">
                                                    <div class="font-semibold text-blue-800 mb-2">รายชื่อผู้เขียน (Authors)
                                                    </div>

                                                    @if ($authorsReal->isEmpty())
                                                        <div class="text-sm text-gray-500">- ไม่มีรายชื่อผู้เขียน -</div>
                                                    @else
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full text-sm border bg-white">
                                                                <thead class="bg-blue-100">
                                                                    <tr>
                                                                        <th class="px-2 py-2 text-left">#</th>
                                                                        <th class="px-2 py-2 text-left">ชื่อ-นามสกุล</th>
                                                                        <th class="px-2 py-2 text-left">บทบาท</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($authorsReal as $ai => $a)
                                                                        <tr class="border-t">
                                                                            <td class="px-2 py-2">{{ $ai + 1 }}</td>
                                                                            <td class="px-2 py-2 break-words">
                                                                                {{ $a->name ?: '-' }}</td>
                                                                            <td class="px-2 py-2 capitalize">
                                                                                @php
                                                                                    $role = strtolower(
                                                                                        trim($a->role ?? ''),
                                                                                    );
                                                                                    $roleLabel =
                                                                                        $role === 'first'
                                                                                            ? 'First-author'
                                                                                            : ($role === 'co'
                                                                                                ? 'Co-author'
                                                                                                : ($role ===
                                                                                                'corresponding'
                                                                                                    ? 'Corresponding author'
                                                                                                    : ($a->role ?:
                                                                                                    '-')));
                                                                                @endphp
                                                                                {{ $roleLabel }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- ===== ส่วนพิเศษ: EA103 ===== --}}
                                        @if ($isEA103)
                                            @php
                                                $confs = $output->conferences ?? collect();
                                                $confsReal = $confs->filter(function ($c) {
                                                    return !empty(trim($c->name ?? '')) ||
                                                        !empty(trim($c->presentation_type ?? '')) ||
                                                        !empty(trim($c->location ?? '')) ||
                                                        !empty(trim($c->note ?? ''));
                                                });
                                            @endphp

                                            <div class="mt-3 border rounded-xl p-4 bg-amber-50/60">
                                                <div class="font-semibold text-amber-800 mb-2">Conferences</div>

                                                @if ($confsReal->isEmpty())
                                                    <div class="text-sm text-gray-500">- ไม่มีรายการ Conference -</div>
                                                @else
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full text-sm border bg-white">
                                                            <thead class="bg-amber-100">
                                                                <tr>
                                                                    <th class="px-2 py-2 text-left">#</th>
                                                                    <th class="px-2 py-2 text-left">ชื่อเวที/Conference
                                                                    </th>
                                                                    <th class="px-2 py-2 text-left">ประเภทนำเสนอ</th>
                                                                    <th class="px-2 py-2 text-left">สถานที่</th>
                                                                    <th class="px-2 py-2 text-left">หมายเหตุ</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($confsReal as $ci => $c)
                                                                    <tr class="border-t">
                                                                        <td class="px-2 py-2">{{ $ci + 1 }}</td>
                                                                        <td class="px-2 py-2 break-words">
                                                                            {{ $c->name ?: '-' }}
                                                                        </td>
                                                                        <td class="px-2 py-2">
                                                                            {{ $c->presentation_type ?: '-' }}
                                                                        </td>
                                                                        <td class="px-2 py-2 break-words">
                                                                            {{ $c->location ?: '-' }}</td>
                                                                        <td class="px-2 py-2 break-words">
                                                                            {{ $c->note ?: '-' }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="font-semibold text-lg text-blue-700 mb-2">H: ยุทธศาสตร์ Siriraj Health Policy</h3>
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
            </div>
             <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                        ← กลับไปยังรายการโครงการ
                    </a>
        </div>

        @php
            // ---------- ชื่อ "ข้อมูลล่าสุดโดย" ----------
            $updatedUser = $project->updated_by ? $project->updatedBy : $project->createdBy;
            $updatedBy = $updatedUser ? trim(($updatedUser->prefix ?? '') . ' ' . $updatedUser->name) : '-';

            // ---------- เวลา "อัปเดทเมื่อ" ----------
            if ($project->updated_at) {
                $carbonDate = \Carbon\Carbon::parse($project->updated_at)->setTimezone('Asia/Bangkok')->locale('th');
                $dayMonth = $carbonDate->translatedFormat('j F');
                $yearBuddhist = $carbonDate->year + 543;
                $time = $carbonDate->format('H:i');
                $updatedAt = "{$dayMonth} {$yearBuddhist} {$time} น.";
            } else {
                $updatedAt = '';
            }

            // ---------- ผู้สร้าง ----------
            $creator = $project->createdBy;
            $createdBy = $creator ? trim(($creator->prefix ?? '') . ' ' . $creator->name) : '-';

            if ($project->created_at) {
                $d = \Carbon\Carbon::parse($project->created_at)->setTimezone('Asia/Bangkok')->locale('th');
                $createdAt = $d->translatedFormat('j F') . ' ' . ($d->year + 543) . ' ' . $d->format('H:i') . ' น.';
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

    </div>
    <script>
        (function() {
            const filter = document.getElementById('irbFilter');
            if (!filter) return;
            const tbody = filter.closest('div')?.nextElementSibling?.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.dataset.exp !== undefined);
            const today = new Date();

            function parseDate(s) {
                if (!s) return null;
                const d = new Date(s);
                return isNaN(d) ? null : d;
            }

            function updateNumbers(visibleRows) {
                visibleRows.forEach((row, idx) => {
                    const cell = row.querySelector('td');
                    if (cell) cell.textContent = idx + 1;
                });
            }

            function applyFilter() {
                const mode = filter.value;
                let filtered = rows;

                if (mode === 'exp_1m' || mode === 'exp_3m') {
                    const maxDays = mode === 'exp_1m' ? 30 : 90;
                    filtered = rows.filter(r => {
                        const d = parseDate(r.dataset.exp);
                        if (!d) return false;
                        const diff = (d - today) / (1000 * 60 * 60 * 24);
                        return diff >= 0 && diff <= maxDays;
                    });
                }

                if (mode === 'active_latest') {
                    filtered = rows.filter(r => {
                        const d = parseDate(r.dataset.exp);
                        return d && d >= today;
                    }).sort((a, b) => {
                        const da = parseDate(a.dataset.exp) || new Date(0);
                        const db = parseDate(b.dataset.exp) || new Date(0);
                        return db - da;
                    });
                }

                rows.forEach(r => r.classList.add('hidden'));
                filtered.forEach(r => r.classList.remove('hidden'));
                updateNumbers(filtered);
            }

            filter.addEventListener('change', applyFilter);
        })();

        const deleteBtn = document.getElementById('deleteProjectBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const form = document.getElementById('delete-project-{{ $project->id }}');
                if (!form) return;
                Swal.fire({
                    title: 'ยืนยันการลบโครงการ?',
                    text: 'การลบจะไม่สามารถกู้คืนได้',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ลบเลย',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        }
    </script>
@endsection
