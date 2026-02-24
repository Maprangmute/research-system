@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'error',
                    title: @json(session('error')),
                    showConfirmButton: false,
                    timer: 7000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
    <style>
        .budget-range {
            position: relative;
        }

        .budget-range input[type='range'] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 2rem;
            background: transparent;
            pointer-events: auto;
        }

        .budget-range input[type='range']::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #10b981;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
            pointer-events: auto;
            cursor: pointer;
        }

        .budget-range input[type='range']::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #10b981;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
            pointer-events: auto;
            cursor: pointer;
        }

        .budget-range input[type='range']::-webkit-slider-runnable-track {
            background: transparent;
        }

        .budget-range input[type='range']::-moz-range-track {
            background: transparent;
        }

        .budget-range-label {
            position: absolute;
            top: 28px;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            color: #0f172a;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.12);
            white-space: nowrap;
            z-index: 5;
            pointer-events: none;
        }

        .project-table {
            table-layout: fixed;
            min-width: 1240px;
        }

        .project-table th {
            white-space: nowrap;
            font-size: 12px;
            letter-spacing: 0.01em;
            color: #475569;
        }

        .project-table td {
            vertical-align: top;
        }

        .project-table tbody tr:nth-child(even) {
            background: #fbfdff;
        }

        .project-table tbody tr:hover {
            background: #eef2ff;
        }

        .cell-clamp-2,
        .cell-clamp-3,
        .cell-clamp-4,
        .cell-clamp-5 {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .cell-clamp-2 {
            -webkit-line-clamp: 2;
        }

        .cell-clamp-3 {
            -webkit-line-clamp: 3;
        }

        .cell-clamp-4 {
            -webkit-line-clamp: 4;
        }

        .cell-clamp-5 {
            -webkit-line-clamp: 5;
        }

        .status-box {
            display: block;
            width: 100%;
            padding: 6px 8px;
            border-radius: 0;
            line-height: 1.3;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }
    </style>

    <div class="container mx-auto px-4 mt-11 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">📊 รายการโครงการวิจัย</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('outputs.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-50">
                    📑 รายการ Output
                </a>
                <a href="{{ route('projects.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 shadow-sm hover:shadow transition">
                    ➕ เพิ่มโครงการ
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form id="project-filter-form" method="GET" action="{{ route('projects.index') }}"
            class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-100 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อโครงการ / Project ID</span>
                    <input id="project-search-input" type="text" name="search"
                        placeholder="ชื่อโครงการ หรือ Project ID (รองรับคำใกล้เคียง)" value="{{ request('search') }}"
                        class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
                    <span class="mt-1 block text-[11px] text-slate-500">ค้นหาแบบ realtime ไม่ต้องกดปุ่ม</span>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทโครงการ</span>
                    <select name="type" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($typeOptions as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">PI</span>
                    <select name="pi_name" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($piOptions as $pi)
                            <option value="{{ $pi }}" @selected(request('pi_name') === $pi)>{{ $pi }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">แหล่งทุน</span>
                    <select name="funding_source" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($fundingSources as $src)
                            <option value="{{ $src }}" @selected(request('funding_source') === $src)>{{ $src }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ช่วงงบประมาณ (preset)</span>
                    <select name="budget_range" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="0-500000" @selected(request('budget_range') === '0-500000')>0 - 500,000</option>
                        <option value="500000-1000000" @selected(request('budget_range') === '500000-1000000')>500,000 - 1,000,000</option>
                        <option value="1000000-3000000" @selected(request('budget_range') === '1000000-3000000')>1,000,000 - 3,000,000</option>
                        <option value="3000000-5000000" @selected(request('budget_range') === '3000000-5000000')>3,000,000 - 5,000,000</option>
                        <option value="5000000-999999999" @selected(request('budget_range') === '5000000-999999999')>มากกว่า 5,000,000</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">Project Status</span>
                    <select name="project_status" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="__unspecified__" @selected(request('project_status') === '__unspecified__')>ยังไม่ได้ระบุ</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected(request('project_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                {{-- <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ช่วงงบประมาณ (เลื่อน)</span>
                    <div class="budget-range relative px-1 pt-6 pb-6">
                        <div id="budgetRangeTrack"
                            class="absolute left-0 right-0 top-3 h-2 rounded-full bg-slate-200"></div>
                        <div id="budgetRangeFill"
                            class="absolute top-3 h-2 rounded-full bg-emerald-400"></div>
                        <div id="budgetMinLabel" class="budget-range-label">0</div>
                        <div id="budgetMaxLabel" class="budget-range-label">0</div>
                        <input id="budgetMinRange" type="range" min="{{ $budgetMin }}" max="{{ $budgetMax }}"
                            value="{{ request('budget_min', $budgetMin) }}" step="10000"
                            class="absolute left-0 right-0 w-full appearance-none bg-transparent">
                        <input id="budgetMaxRange" type="range" min="{{ $budgetMin }}" max="{{ $budgetMax }}"
                            value="{{ request('budget_max', $budgetMax) }}" step="10000"
                            class="absolute left-0 right-0 w-full appearance-none bg-transparent">
                    </div>
                    <input type="hidden" name="budget_min" id="budgetMinInput"
                        value="{{ request('budget_min', $budgetMin) }}">
                    <input type="hidden" name="budget_max" id="budgetMaxInput"
                        value="{{ request('budget_max', $budgetMax) }}">
                </label> --}}
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">สถานะ FTE</span>
                    <select name="fte_status" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="missing" @selected(request('fte_status') === 'missing')>ยังกรอกไม่ครบ</option>
                        <option value="complete" @selected(request('fte_status') === 'complete')>กรอกครบแล้ว</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ไม่อัปเดท</span>
                    <select name="last_update" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="1m" @selected(request('last_update') === '1m')>1 เดือน</option>
                        <option value="2m" @selected(request('last_update') === '2m')>2 เดือน</option>
                        <option value="3m" @selected(request('last_update') === '3m')>3 เดือน</option>
                        <option value="gt3" @selected(request('last_update') === 'gt3')>มากกว่า 3 เดือน</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปัญหา</span>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_overdue" value="1" @checked(request('issue_overdue'))>
                            Overdue
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_missing_dates" value="1" @checked(request('issue_missing_dates'))>
                            วันที่ไม่ครบ
                        </label>
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_duplicates" value="1" @checked(request('issue_duplicates'))>
                            โครงการซ้ำ
                        </label>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="updated_at" @selected(request('sort') === 'updated_at')>อัปเดทล่าสุด → เก่าสุด</option>
                        <option value="created_at" @selected(request('sort') === 'created_at')>สร้างล่าสุด → เก่าสุด</option>
                        <option value="budget" @selected(request('sort') === 'budget')>งบประมาณ</option>
                        <option value="start_year" @selected(request('sort') === 'start_year')>ปีโครงการ</option>
                        <option value="planning_status" @selected(request('sort') === 'planning_status')>Project Status</option>
                        <option value="project_name_th" @selected(request('sort') === 'project_name_th')>ชื่อโครงการ</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="desc" @selected(request('dir', 'desc') === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected(request('dir') === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm">ใช้ตัวกรอง</button>
                    <a id="project-filter-reset" href="{{ route('projects.index') }}"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">ล้าง</a>
                </div>
            </div>
        </form>
        <div id="table-loading-indicator" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตตาราง...
        </div>

        {{-- Helpers: sort + color badge --}}
        @php
            $currentSort = request('sort', 'updated_at');
            $currentDir = request('dir', 'desc');
            $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
            $sortUrl = function ($field) use ($toggleDir) {
                return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
            };
            $sortIcon = function ($field) use ($currentSort, $currentDir) {
                if ($currentSort !== $field) {
                    return '↕';
                }
                return $currentDir === 'asc' ? '↑' : '↓';
            };

            // แผนที่สี badge ตามประเภท (ถ้าไม่พบ ใช้สีกลาง)
            function type_badge_classes($type)
            {
                $label = trim((string) $type);
                if ($label === '') {
                    return 'bg-slate-200 text-slate-800 border border-slate-300';
                }

                $palette = [
                    ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'border' => 'border-rose-200'],
                    ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-200'],
                    ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-200'],
                    ['bg' => 'bg-lime-100', 'text' => 'text-lime-800', 'border' => 'border-lime-200'],
                    ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200'],
                    ['bg' => 'bg-teal-100', 'text' => 'text-teal-800', 'border' => 'border-teal-200'],
                    ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'border' => 'border-cyan-200'],
                    ['bg' => 'bg-sky-100', 'text' => 'text-sky-800', 'border' => 'border-sky-200'],
                    ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
                    ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'border' => 'border-indigo-200'],
                    ['bg' => 'bg-violet-100', 'text' => 'text-violet-800', 'border' => 'border-violet-200'],
                    ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-800', 'border' => 'border-fuchsia-200'],
                    ['bg' => 'bg-pink-100', 'text' => 'text-pink-800', 'border' => 'border-pink-200'],
                ];

                $normalized = mb_strtolower($label, 'UTF-8');
                $hash = hexdec(substr(md5($normalized), 0, 8));
                $tone = $palette[$hash % count($palette)];

                return "{$tone['bg']} {$tone['text']} border {$tone['border']}";
            }

            function funding_badge_classes($fundingType)
            {
                $t = trim(mb_strtolower((string) $fundingType, 'UTF-8'));
                if ($t === '') {
                    return 'bg-slate-200 text-slate-800';
                }
                if (str_contains($t, 'ภายใน')) {
                    return 'bg-emerald-200 text-emerald-900';
                }
                if (str_contains($t, 'ภายนอก')) {
                    return 'bg-sky-200 text-sky-900';
                }
                if (str_contains($t, 'ร่วม') || str_contains($t, 'co-') || str_contains($t, 'collab')) {
                    return 'bg-amber-200 text-amber-900';
                }
                return 'bg-slate-200 text-slate-800';
            }

            function project_status_badge_classes($status)
            {
                $s = trim(mb_strtolower((string) $status, 'UTF-8'));
                if ($s === '') {
                    return 'bg-slate-200 text-slate-800';
                }
                if (str_contains($s, 'ยกเลิก')) {
                    return 'bg-rose-200 text-rose-900';
                }
                if (str_contains($s, 'ปิดโปรเจค') || str_contains($s, 'เสร็จ')) {
                    return 'bg-emerald-200 text-emerald-900';
                }
                if (str_contains($s, 'เขียนรายงาน') || str_contains($s, 'publication')) {
                    return 'bg-indigo-200 text-indigo-900';
                }
                if (str_contains($s, 'วิเคราะห์')) {
                    return 'bg-violet-200 text-violet-900';
                }
                if (str_contains($s, 'เก็บข้อมูล')) {
                    return 'bg-sky-200 text-sky-900';
                }
                if (str_contains($s, 'plan') || str_contains($s, 'เริ่มต้น') || str_contains($s, 'submit irb')) {
                    return 'bg-amber-200 text-amber-900';
                }
                return 'bg-slate-200 text-slate-800';
            }
        @endphp


        @if ($missingFTE->count() > 0)
            <div id="fte-alert-box"
                class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl shadow-sm">

                <h3 class="font-semibold text-lg mb-2">⚠️ แจ้งเตือน: กรุณากรอกค่า FTE ของคุณ</h3>

                <p class="text-sm mb-3 font-medium">
                    คุณมีรายชื่อเป็นผู้ร่วมงานในโครงการด้านล่างนี้ แต่ยังไม่ได้กรอกค่า FTE โปรดดำเนินการโดยเร็วที่สุด
                    <span class=""><br>กดที่ชื่อโปรเจคเพื่อดูรายละเอียดได้ทันที</span>

                </p>

                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($missingFTE as $fte)
                        <li>
                            <a href="{{ route('projects.edit', $fte->project ?? $fte->project_id) }}"
                                class="text-indigo-700 font-medium hover:underline">
                                {{ $fte->project->project_name_th ?? 'ไม่พบชื่อโครงการ' }}
                            </a>

                            @php
                                $year = $fte->project->start_year ?? '-';
                                $code = $fte->project->project_code ?? 'null';
                                $id = $fte->project->id ?? '-';
                            @endphp

                            <span class="text-slate-500">
                                ({{ $year }})
                                – {{ $code }} (#{{ $id }})
                            </span>
                        </li>
                    @endforeach

                </ul>

                <div class="mt-3 text-right">
                    <button onclick="document.getElementById('fte-alert-box').classList.add('hidden');"
                        class="inline-block px-3 py-1.5 bg-slate-600 text-white text-xs rounded-md hover:bg-slate-700">
                        ปิดการแจ้งเตือน
                    </button>
                </div>
            </div>
        @endif


        {{-- Table --}}
        <div id="project-table-shell" class="bg-white rounded-2xl shadow-md ring-1 ring-slate-100 overflow-hidden transition-opacity duration-200">
            <div class="px-4 py-3 border-b bg-slate-50 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    แสดงผล {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} จากทั้งหมด
                    {{ $projects->total() }} รายการ
                    <span class="ml-2 text-xs text-slate-500">เรียงลำดับจากข้อมูลที่แก้ไขล่าสุด</span>
                </div>
                <div class="text-xs text-slate-400">ดับเบิลคลิก = ดูรายละเอียด • คลิกขวา/กดค้าง = เมนูด่วน</div>
            </div>

            <div class="overflow-auto">
                <table class="project-table min-w-full">
                    <colgroup>
                        <col style="width: 8%;"> {{-- Project ID --}}
                        <col style="width: 56%;"> {{-- ชื่อโครงการ + Theme + ประเภท --}}
                        <col style="width: 9%;"> {{-- ทุน --}}
                        <col style="width: 8%;"> {{-- Status --}}
                        <col style="width: 4%;"> {{-- Outputs --}}
                        <col style="width: 6%;"> {{-- ปี --}}
                        <col style="width: 6%;"> {{-- อัปเดทล่าสุด --}}
                    </colgroup>
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-left text-xs font-semibold text-slate-600">
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_code') }}" class="hover:text-indigo-700">Project ID <span
                                        class="ml-1">{{ $sortIcon('project_code') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_name_th') }}" class="hover:text-indigo-700">ชื่อโครงการ <span
                                        class="ml-1">{{ $sortIcon('project_name_th') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('funding_type') }}" class="hover:text-indigo-700">ทุน <span
                                        class="ml-1">{{ $sortIcon('funding_type') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('planning_status') }}" class="hover:text-indigo-700">Project Status <span
                                        class="ml-1">{{ $sortIcon('planning_status') }}</span></a>
                            </th>
                            <th class="px-4 py-3 text-right">
                                Outputs
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('start_year') }}" class="hover:text-indigo-700">ปี <span
                                        class="ml-1">{{ $sortIcon('start_year') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('updated_at') }}" class="hover:text-indigo-700">อัปเดทล่าสุด <span
                                        class="ml-1">{{ $sortIcon('updated_at') }}</span></a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($projects as $project)
                            @php
                                $showUrl = route('projects.show', $project);
                                $editUrl = route('projects.edit', $project);
                                $pdfUrl = route('projects.pdf', $project);
                                $progressUrl = route('projects.progress_reports.index', $project);
                                $fteProjectUrl = route('fte.project.detail', $project);
                                $deleteFormId = "delete-form-{$project->id}";
                            @endphp
                            <tr class="hover:bg-indigo-50/40 transition cursor-default" data-show-url="{{ $showUrl }}"
                                data-edit-url="{{ $editUrl }}" data-pdf-url="{{ $pdfUrl }}"
                                data-progress-url="{{ $progressUrl }}" data-fte-project-url="{{ $fteProjectUrl }}"
                                data-delete-form-id="{{ $deleteFormId }}"
                                data-project-name="{{ $project->project_name_th }}"
                                oncontextmenu="return openRowMenu(event, this)" ondblclick="goToDetail(this)"
                                ontouchstart="rowLongPressStart(event, this)" ontouchend="rowLongPressEnd(event)">
                                <td class="px-4 py-3 text-sm font-mono text-slate-700 whitespace-nowrap">
                                    <a href="{{ $showUrl }}" class="hover:underline">{{ $project->project_code }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ $showUrl }}"
                                        class="text-slate-900 hover:text-indigo-700 font-medium cell-clamp-5"
                                        title="{{ $project->project_name_th }}">
                                        {{ $project->project_name_th }}
                                    </a>
                                    <div class="mt-1 text-xs text-slate-500 cell-clamp-2" title="{{ $project->theme ?: 'ไม่ระบุ Theme' }}">
                                        Theme: {{ $project->theme ?: 'ไม่ระบุ' }}
                                    </div>
                                    <span
                                        class="mt-1 inline-flex items-center px-2 py-0.5 rounded-none text-xs font-medium {{ type_badge_classes($project->project_type) }}">
                                        <span class="cell-clamp-2" title="{{ $project->project_type ?: '-' }}">
                                            {{ $project->project_type ?: '-' }}
                                        </span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ funding_badge_classes($project->funding_type) }}">
                                        {{ $project->funding_type ?: 'ยังไม่ได้ระบุ' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-normal text-slate-700">
                                    @php
                                        $projectStatus = trim((string) ($project->planning_status ?? ''));
                                    @endphp
                                    <span
                                        class="status-box text-xs font-semibold {{ project_status_badge_classes($projectStatus) }}">
                                        {{ $projectStatus !== '' ? $projectStatus : 'ยังไม่ได้ระบุ' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                    {{ $project->outputs_count ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 text-center">{{ $project->start_year ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    @php
                                        $updated = $project->updated_at ? \Carbon\Carbon::parse($project->updated_at) : null;
                                    @endphp
                                    {{ $updated ? $updated->locale('th')->translatedFormat('j F') . ' ' . ($updated->year + 543) : '-' }}
                                </td>

                                {{-- hidden form for delete --}}
                                <td class="hidden">
                                    <form id="{{ $deleteFormId }}"
                                        action="{{ route('projects.destroy', $project) }}" method="POST">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-slate-500">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t bg-slate-50">
                {{ $projects->links('vendor.pagination.th') }}
            </div>
        </div>
    </div>

    {{-- Context Menu --}}
    <div id="row-menu"
        class="hidden fixed z-50 w-[188px] max-w-[88vw] max-h-[70vh] overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5">
        <div class="px-3 pt-2 pb-1 border-b border-slate-100 bg-slate-50/80">
            <div class="text-[10px] font-normal text-slate-400">โครงการ</div>
            <div id="row-menu-project-name" class="text-[11px] font-normal text-slate-600 leading-snug cell-clamp-2">-</div>
        </div>
        <div class="py-1 text-sm">
            <button type="button" data-action="show" class="w-full text-left px-3 py-2 hover:bg-indigo-50">🔎
                ดูรายละเอียด</button>
            <button type="button" data-action="show-new-tab" class="w-full text-left px-3 py-2 hover:bg-indigo-50">🗂️
                เปิดข้อมูลในแท็บใหม่</button>
            <button type="button" data-action="copy-project-name" class="w-full text-left px-3 py-2 hover:bg-indigo-50">📋
                คัดลอกชื่อโครงการ</button>
            <button type="button" data-action="edit" class="w-full text-left px-3 py-2 hover:bg-indigo-50">✏️
                แก้ไข</button>
            <button type="button" data-action="pdf"
                class="w-full text-left px-3 py-2 hover:bg-green-50 text-green-700">🖨️ พิมพ์ PDF</button>
            <button type="button" data-action="progress"
                class="w-full text-left px-3 py-2 hover:bg-sky-50 text-sky-700">📑 Progress Report</button>
            <button type="button" data-action="fte-project"
                class="w-full text-left px-3 py-2 hover:bg-emerald-50 text-emerald-700">📊 FTE โปรเจค</button>
            <div class="my-1 border-t border-slate-100"></div>
            <button type="button" data-action="delete"
                class="w-full text-left px-3 py-2 hover:bg-red-50 text-red-600">🗑️ ลบข้อมูล</button>
        </div>
    </div>

    <script>
        function goToDetail(row) {
            const url = row.getAttribute('data-show-url');
            if (url) location.href = url;
        }

        function openRowMenu(e, row) {
            e.preventDefault();
            const menu = document.getElementById('row-menu');
            if (!menu) return;

            if (menu.parentElement !== document.body) {
                document.body.appendChild(menu);
            }

            menu.dataset.showUrl = row.getAttribute('data-show-url') || '';
            menu.dataset.editUrl = row.getAttribute('data-edit-url') || '';
            menu.dataset.pdfUrl = row.getAttribute('data-pdf-url') || '';
            menu.dataset.progressUrl = row.getAttribute('data-progress-url') || '';
            menu.dataset.fteProjectUrl = row.getAttribute('data-fte-project-url') || '';
            menu.dataset.deleteFormId = row.getAttribute('data-delete-form-id') || '';
            menu.dataset.projectName = row.getAttribute('data-project-name') || '';

            const nameLabel = document.getElementById('row-menu-project-name');
            if (nameLabel) {
                nameLabel.textContent = menu.dataset.projectName || '-';
                nameLabel.title = menu.dataset.projectName || '';
            }

            const margin = 8;
            menu.style.left = '0px';
            menu.style.top = '0px';
            menu.style.visibility = 'hidden';
            menu.classList.remove('hidden');

            const measured = menu.getBoundingClientRect();
            let x = e.clientX;
            let y = e.clientY;
            if (x + measured.width + margin > innerWidth) {
                x = innerWidth - measured.width - margin;
            }
            if (y + measured.height + margin > innerHeight) {
                y = innerHeight - measured.height - margin;
            }

            x = Math.max(margin, x);
            y = Math.max(margin, y);
            menu.style.left = `${x}px`;
            menu.style.top = `${y}px`;
            menu.style.visibility = 'visible';

            setTimeout(() => {
                document.addEventListener('click', clickAwayClose, {
                    once: true
                });
                addEventListener('scroll', hideMenu, {
                    once: true
                });
                addEventListener('resize', hideMenu, {
                    once: true
                });
                document.addEventListener('keydown', escClose, {
                    once: true
                });
            }, 0);
            return false;
        }

        function hideMenu() {
            const m = document.getElementById('row-menu');
            if (m) {
                m.classList.add('hidden');
                m.style.visibility = '';
            }
        }

        function clickAwayClose(ev) {
            const m = document.getElementById('row-menu');
            if (m && !m.contains(ev.target)) hideMenu();
        }

        function escClose(ev) {
            if (ev.key === 'Escape') hideMenu();
        }

        async function copyTextToClipboard(text) {
            if (!text) return false;

            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(text);
                    return true;
                } catch (error) {
                    // fallback below
                }
            }

            const temp = document.createElement('textarea');
            temp.value = text;
            temp.setAttribute('readonly', '');
            temp.style.position = 'fixed';
            temp.style.top = '-9999px';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.select();
            temp.setSelectionRange(0, text.length);

            let copied = false;
            try {
                copied = document.execCommand('copy');
            } catch (error) {
                copied = false;
            }
            document.body.removeChild(temp);
            return copied;
        }

        (function bindMenu() {
            const m = document.getElementById('row-menu');
            if (!m) return;
            m.addEventListener('click', async (ev) => {
                const btn = ev.target.closest('button[data-action]');
                if (!btn) return;
                const act = btn.dataset.action,
                    showUrl = m.dataset.showUrl,
                    editUrl = m.dataset.editUrl,
                    pdfUrl = m.dataset.pdfUrl,
                    progressUrl = m.dataset.progressUrl,
                    fteProjectUrl = m.dataset.fteProjectUrl,
                    delId = m.dataset.deleteFormId,
                    projectName = m.dataset.projectName || '';
                hideMenu();
                switch (act) {
                    case 'show':
                        if (showUrl) location.href = showUrl;
                        break;
                    case 'show-new-tab':
                        if (showUrl) window.open(showUrl, '_blank', 'noopener,noreferrer');
                        break;
                    case 'copy-project-name':
                        if (!projectName) return;
                        {
                            const copied = await copyTextToClipboard(projectName);
                            Swal.fire({
                                icon: copied ? 'success' : 'error',
                                title: copied ? 'คัดลอกชื่อโครงการแล้ว' : 'คัดลอกไม่สำเร็จ',
                                text: copied ? projectName : 'เบราว์เซอร์ไม่อนุญาตให้คัดลอกอัตโนมัติ',
                                timer: copied ? 1400 : 2200,
                                showConfirmButton: false
                            });
                        }
                        break;
                    case 'edit':
                        if (editUrl) location.href = editUrl;
                        break;
                    case 'pdf':
                        if (pdfUrl) window.open(pdfUrl, '_blank');
                        break;
                    case 'progress':
                        if (progressUrl) location.href = progressUrl;
                        break;
                    case 'fte-project':
                        if (fteProjectUrl) window.open(fteProjectUrl, '_blank');
                        break;
                    case 'delete':
                        const form = document.getElementById(delId);
                        if (!form) return;
                        Swal.fire({
                            title: 'คุณแน่ใจหรือไม่?',
                            text: 'การลบไม่สามารถย้อนกลับได้',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e3342f',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'ใช่, ลบเลย',
                            cancelButtonText: 'ยกเลิก'
                        }).then(r => {
                            if (r.isConfirmed) form.submit();
                        });
                        break;
                }
            });
        })();

        // mobile long press
        let longPressTimer = null;

        function rowLongPressStart(e, row) {
            if (e.touches && e.touches.length > 1) return;
            longPressTimer = setTimeout(() => {
                const t = e.touches ? e.touches[0] : null;
                openRowMenu({
                    preventDefault: () => {},
                    clientX: t ? t.clientX : 20,
                    clientY: t ? t.clientY : 20
                }, row);
            }, 500);
        }

        function rowLongPressEnd() {
            if (longPressTimer) {
                clearTimeout(longPressTimer);
                longPressTimer = null;
            }
        }

        // Budget range sync
        (function() {
            const minRange = document.getElementById('budgetMinRange');
            const maxRange = document.getElementById('budgetMaxRange');
            const minInput = document.getElementById('budgetMinInput');
            const maxInput = document.getElementById('budgetMaxInput');
            const rangeFill = document.getElementById('budgetRangeFill');
            const minLabel = document.getElementById('budgetMinLabel');
            const maxLabel = document.getElementById('budgetMaxLabel');
            if (!minRange || !maxRange || !minInput || !maxInput || !rangeFill || !minLabel || !maxLabel) return;

            const shortNumber = (value) => {
                const abs = Math.abs(value);
                if (abs >= 1000000) {
                    const v = (value / 1000000).toFixed(1).replace(/\.0$/, '');
                    return `${v}m`;
                }
                if (abs >= 1000) {
                    const v = (value / 1000).toFixed(1).replace(/\.0$/, '');
                    return `${v}k`;
                }
                return `${value}`;
            };
            const minBound = Number(minRange.min);
            const maxBound = Number(maxRange.max);

            const updateUI = () => {
                const minVal = Number(minRange.value);
                const maxVal = Number(maxRange.value);
                const total = maxBound - minBound || 1;
                const minPct = ((minVal - minBound) / total) * 100;
                const maxPct = ((maxVal - minBound) / total) * 100;
                rangeFill.style.left = `${minPct}%`;
                rangeFill.style.right = `${100 - maxPct}%`;
                minLabel.textContent = shortNumber(minVal);
                maxLabel.textContent = shortNumber(maxVal);
                minLabel.style.left = `${minPct}%`;
                maxLabel.style.left = `${maxPct}%`;
                minLabel.style.display = minVal === minBound ? 'none' : 'block';
            };

            const clamp = () => {
                let minVal = Number(minRange.value);
                let maxVal = Number(maxRange.value);
                if (minVal > maxVal) {
                    [minVal, maxVal] = [maxVal, minVal];
                    minRange.value = minVal;
                    maxRange.value = maxVal;
                }
                minInput.value = minVal;
                maxInput.value = maxVal;
                updateUI();
            };

            const clampInputs = () => {
                let minVal = Number(minInput.value);
                let maxVal = Number(maxInput.value);
                if (minVal > maxVal) {
                    [minVal, maxVal] = [maxVal, minVal];
                }
                minRange.value = minVal;
                maxRange.value = maxVal;
                updateUI();
            };

            minRange.addEventListener('input', clamp);
            maxRange.addEventListener('input', clamp);
            minInput.addEventListener('change', clampInputs);
            maxInput.addEventListener('change', clampInputs);
            updateUI();
        })();

        // AJAX filter/search/sort/pagination (no page refresh)
        (function() {
            const form = document.getElementById('project-filter-form');
            const shellSelector = '#project-table-shell';
            const loadingEl = document.getElementById('table-loading-indicator');
            if (!form) return;

            const selectors = [
                'input[name="search"]',
                'select[name="type"]',
                'select[name="year"]',
                'select[name="pi_name"]',
                'select[name="funding_source"]',
                'select[name="project_status"]',
                'select[name="budget_range"]',
                'select[name="fte_status"]',
                'select[name="last_update"]',
                'input[name="issue_overdue"]',
                'input[name="issue_missing_dates"]',
                'input[name="issue_duplicates"]',
                'select[name="sort"]',
                'select[name="dir"]'
            ];

            const fields = Array.from(form.querySelectorAll(selectors.join(',')));
            if (!fields.length) return;

            let timer = null;
            let controller = null;
            let requestToken = 0;

            const setLoading = (isLoading) => {
                if (loadingEl) {
                    loadingEl.classList.toggle('hidden', !isLoading);
                    loadingEl.classList.toggle('flex', isLoading);
                }
                const shell = document.querySelector(shellSelector);
                if (shell) {
                    shell.classList.toggle('opacity-60', isLoading);
                }
            };

            const parseAndReplaceShell = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextShell = doc.querySelector(shellSelector);
                const currentShell = document.querySelector(shellSelector);
                if (!nextShell || !currentShell) {
                    return false;
                }
                currentShell.replaceWith(nextShell);
                return true;
            };

            const syncFormWithUrl = (url) => {
                form.querySelectorAll('input[name], select[name]').forEach((field) => {
                    const name = field.name;
                    if (!name) return;

                    if (field.type === 'checkbox') {
                        const values = url.searchParams.getAll(name);
                        const fieldValue = field.value || '1';
                        field.checked = values.includes(fieldValue);
                        return;
                    }

                    field.value = url.searchParams.get(name) ?? '';
                });
            };

            const buildUrlFromForm = () => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (value !== null && String(value).trim() !== '') {
                        url.searchParams.append(key, String(value));
                    }
                }
                url.searchParams.delete('page');
                return url;
            };

            const fetchAndRender = async (url, options = {}) => {
                const {
                    updateUrl = true
                } = options;
                const token = ++requestToken;

                if (controller) {
                    controller.abort();
                }
                controller = new AbortController();
                setLoading(true);

                try {
                    const res = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: controller.signal
                    });
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }

                    const html = await res.text();
                    if (token !== requestToken) {
                        return;
                    }

                    if (!parseAndReplaceShell(html)) {
                        throw new Error('Cannot find table shell in response');
                    }

                    if (updateUrl) {
                        history.replaceState({
                            ajax: true
                        }, '', `${url.pathname}${url.search}`);
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    if (token === requestToken) {
                        setLoading(false);
                    }
                }
            };

            const submitDebounced = (delay = 220) => {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    fetchAndRender(buildUrlFromForm(), {
                        updateUrl: true
                    });
                }, delay);
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchAndRender(buildUrlFromForm(), {
                    updateUrl: true
                });
            });

            fields.forEach((field) => {
                const isTypingField = field.matches('input[type="text"], input[type="search"]');
                if (isTypingField) {
                    field.addEventListener('input', () => submitDebounced(260));
                    field.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            submitDebounced(0);
                        }
                    });
                    return;
                }

                field.addEventListener('change', () => submitDebounced(120));
            });

            document.addEventListener('click', (event) => {
                const link = event.target.closest(`${shellSelector} a[href]`);
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href) return;

                const url = new URL(href, window.location.origin);
                const isSamePage = url.pathname === window.location.pathname;
                const isListInteraction = url.searchParams.has('page') || url.searchParams.has('sort') || url.searchParams.has('dir');

                if (!isSamePage || !isListInteraction) {
                    return;
                }

                event.preventDefault();
                syncFormWithUrl(url);
                fetchAndRender(url, {
                    updateUrl: true
                });
            });

            const resetLink = document.getElementById('project-filter-reset');
            if (resetLink) {
                resetLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    form.querySelectorAll('input[name], select[name]').forEach((field) => {
                        if (field.type === 'checkbox') {
                            field.checked = false;
                            return;
                        }

                        if (field.name === 'sort') {
                            field.value = 'updated_at';
                            return;
                        }
                        if (field.name === 'dir') {
                            field.value = 'desc';
                            return;
                        }
                        field.value = '';
                    });

                    fetchAndRender(new URL(resetLink.href, window.location.origin), {
                        updateUrl: true
                    });
                });
            }
        })();
    </script>
@endsection
