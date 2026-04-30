@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @php
        $activeFilterCount = collect(request()->query())
            ->reject(fn($value, $key) => in_array($key, ['page', 'sort', 'dir', 'view'], true))
            ->filter(fn($value) => filled($value))
            ->count();
    @endphp
    <style>
        .lux-mobile-page {
            max-width: 100%;
            margin: 0.75rem;
            padding-bottom: calc(96px + env(safe-area-inset-bottom, 0px));
        }

        .lux-mobile-card {
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 12px;
        }

        .lux-mobile-badge {
            border-radius: 9999px;
            padding: 2px 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .lux-mobile-actions a,
        .lux-mobile-actions button {
            border-radius: 10px;
        }

        .lux-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-mobile-filter-anchor {
            scroll-margin-top: 80px;
        }

        .project-mobile-filter-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 46;
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(6px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
        }

        .project-mobile-filter-drawer-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .project-mobile-filter-drawer {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 47;
            width: min(92vw, 400px);
            transform: translateX(-100%);
            transition: transform 0.22s ease;
        }

        .project-mobile-filter-drawer.is-open {
            transform: translateX(0);
        }

        .project-mobile-filter-fab {
            position: fixed;
            left: 16px;
            bottom: calc(16px + env(safe-area-inset-bottom, 0px));
            z-index: 35;
            width: 62px;
            min-height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 18px;
            border: 1px solid #dbeafe;
            background: rgba(255, 255, 255, 0.94);
            color: #0f172a;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(12px);
            opacity: 0;
            transform: translateY(18px);
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .project-mobile-filter-fab.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .project-mobile-filter-fab__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0f172a 0%, #312e81 100%);
            color: #ffffff;
        }

        .project-mobile-filter-fab__badge {
            position: absolute;
            top: -0.28rem;
            right: -0.28rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.28rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            border: 2px solid #fff;
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        .project-mobile-filter-fab__badge.hidden {
            display: none;
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .project-mobile-filter-fab {
                width: 70px;
                min-height: 78px;
                flex-direction: column;
                gap: 0.3rem;
                padding: 0.72rem 0.45rem;
                border-radius: 20px;
            }

            .project-mobile-filter-fab::after {
                content: 'ฟิลเตอร์';
                font-size: 0.68rem;
                font-weight: 700;
                line-height: 1;
            }
        }

        .project-filter-dropdown {
            position: relative;
        }

        .project-filter-dropdown__menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 55;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            padding: 0.5rem;
        }

        .project-filter-dropdown__menu.hidden {
            display: none;
        }

        .project-filter-dropdown__options {
            max-height: 14rem;
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }

        .project-filter-dropdown__options::-webkit-scrollbar {
            width: 10px;
        }

        .project-filter-dropdown__options::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 999px;
        }

        .project-filter-dropdown__options::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
            border: 2px solid #f1f5f9;
        }

        .project-role-hit {
            display: inline-flex;
            align-items: center;
            margin-top: 0.35rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            border: 1px solid #fca5a5;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.25;
        }

    </style>

    <div class="lux-mobile-page space-y-4">
        {{-- Header --}}
        <div class="flex flex-col gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">📊 Research Project</h2>
                <p id="project-mobile-count" class="text-xs text-slate-500">แสดงผล {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} จากทั้งหมด
                    {{ $projects->total() }} รายการ</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('outputs.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 border border-slate-200 text-slate-700 text-sm rounded-lg bg-white">
                    📑 รายการ Output
                </a>
                <a href="{{ route('projects.create') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-purple-600 text-white text-sm rounded-lg shadow-sm">
                    ➕ เพิ่มโครงการ
                </a>
            </div>
        </div>

        {{-- Search --}}
        <form id="project-mobile-search-form" method="GET" action="{{ route('projects.index') }}"
            class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <input type="hidden" name="type" value="{{ request('type') }}">
            <input type="hidden" name="year" value="{{ request('year') }}">
            <input type="hidden" name="pi_name" value="{{ request('pi_name') }}">
            <input type="hidden" name="person_name" value="{{ request('person_name') }}">
            <input type="hidden" name="funding_source" value="{{ request('funding_source') }}">
            <input type="hidden" name="project_status" value="{{ request('project_status') }}">
            <input type="hidden" name="budget_range" value="{{ request('budget_range') }}">
            <input type="hidden" name="fte_status" value="{{ request('fte_status') }}">
            <input type="hidden" name="last_update" value="{{ request('last_update') }}">
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">
            @if (request('issue_overdue'))
                <input type="hidden" name="issue_overdue" value="1">
            @endif
            @if (request('issue_missing_dates'))
                <input type="hidden" name="issue_missing_dates" value="1">
            @endif
            @if (request('issue_duplicates'))
                <input type="hidden" name="issue_duplicates" value="1">
            @endif
            @if (request('issue_no_output'))
                <input type="hidden" name="issue_no_output" value="1">
            @endif
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อโครงการ / Project ID</span>
                <input id="project-mobile-search-input" type="text" name="search"
                    placeholder="ชื่อโครงการ หรือ Project ID (รองรับคำใกล้เคียง)" value="{{ request('search') }}"
                    class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
                <span class="mt-1 block text-[11px] text-slate-500">พิมพ์แล้วค้นหาทันทีโดยไม่ reload</span>
            </label>
        </form>

        {{-- Filters --}}
        <details id="project-mobile-filter-anchor" class="project-mobile-filter-anchor bg-white rounded-2xl border border-slate-100 shadow-sm">
            <summary class="flex items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-slate-700 cursor-pointer">
                <span>ตัวกรอง</span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600">
                    <span>ใช้อยู่</span>
                    <span id="project-mobile-active-filter-pill" class="font-semibold text-slate-900">{{ $activeFilterCount }}</span>
                </span>
            </summary>
            <form id="project-mobile-filter-form" method="GET" action="{{ route('projects.index') }}"
                class="px-4 pb-4 space-y-3">
                <input type="hidden" name="search" value="{{ request('search') }}">
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
                    <div class="project-filter-dropdown" data-project-filter-dropdown>
                        <input type="hidden" name="pi_name" value="{{ request('pi_name') }}" data-project-live-mobile="true" data-project-dropdown-value>
                        <button type="button" data-project-dropdown-toggle
                            class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                            <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                            <span class="text-slate-400">▾</span>
                        </button>
                        <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                            <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                            <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                <button type="button" data-project-dropdown-option data-value=""
                                    class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                @foreach ($piOptions as $pi)
                                    <button type="button" data-project-dropdown-option data-value="{{ $pi }}"
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $pi }}</button>
                                @endforeach
                                <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">บุคคลในโครงการ</span>
                    <div class="project-filter-dropdown" data-project-filter-dropdown>
                        <input type="hidden" name="person_name" value="{{ $selectedPersonName ?? '' }}" data-project-live-mobile="true" data-project-dropdown-value>
                        <button type="button" data-project-dropdown-toggle
                            class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                            <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                            <span class="text-slate-400">▾</span>
                        </button>
                        <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                            <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                            <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                <button type="button" data-project-dropdown-option data-value=""
                                    class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                @foreach (($personOptions ?? []) as $person)
                                    <button type="button" data-project-dropdown-option data-value="{{ $person['value'] }}"
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $person['label'] }}</button>
                                @endforeach
                                <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">แหล่งทุน</span>
                    <div class="project-filter-dropdown" data-project-filter-dropdown>
                        <input type="hidden" name="funding_source" value="{{ request('funding_source') }}" data-project-live-mobile="true" data-project-dropdown-value>
                        <button type="button" data-project-dropdown-toggle
                            class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                            <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                            <span class="text-slate-400">▾</span>
                        </button>
                        <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                            <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                            <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                <button type="button" data-project-dropdown-option data-value=""
                                    class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                @foreach ($fundingSources as $src)
                                    <button type="button" data-project-dropdown-option data-value="{{ $src }}"
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $src }}</button>
                                @endforeach
                                <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="block">
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
                    <span class="text-xs font-semibold text-slate-600">สถานะโครงการ (Project Status)</span>
                    <select name="project_status" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        <option value="__unspecified__" @selected(request('project_status') === '__unspecified__')>ยังไม่ได้ระบุ</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected(request('project_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
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
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="issue_no_output" value="1" @checked(request('issue_no_output'))>
                            ไม่มี output
                        </label>
                    </div>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="updated_at" @selected(request('sort') === 'updated_at')>อัปเดทล่าสุด → เก่าสุด</option>
                        <option value="created_at" @selected(request('sort') === 'created_at')>สร้างล่าสุด → เก่าสุด</option>
                        <option value="budget" @selected(request('sort') === 'budget')>งบประมาณ</option>
                        <option value="start_year" @selected(request('sort') === 'start_year')>ปีโครงการ</option>
                        <option value="planning_status" @selected(request('sort') === 'planning_status')>สถานะโครงการ</option>
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
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm">ใช้ตัวกรอง</button>
                    <a id="project-mobile-reset" data-project-mobile-reset href="{{ route('projects.index') }}"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white">ล้าง</a>
                </div>
            </form>
        </details>
        <div id="project-mobile-loading" class="hidden items-center gap-2 text-xs text-slate-500 px-1">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตรายการ...
        </div>

        {{-- Helpers: sort + color badge --}}
        @php
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
                    return 'bg-slate-100 text-slate-700';
                }
                if (str_contains($t, 'ภายใน')) {
                    return 'bg-emerald-100 text-emerald-800';
                }
                if (str_contains($t, 'ภายนอก')) {
                    return 'bg-sky-100 text-sky-800';
                }
                if (str_contains($t, 'ร่วม') || str_contains($t, 'co-') || str_contains($t, 'collab')) {
                    return 'bg-amber-100 text-amber-800';
                }
                return 'bg-slate-100 text-slate-700';
            }

            function project_status_badge_classes($status)
            {
                $s = trim(mb_strtolower((string) $status, 'UTF-8'));
                if ($s === '') {
                    return 'bg-slate-100 text-slate-700';
                }
                if (str_contains($s, 'ยกเลิก')) {
                    return 'bg-rose-100 text-rose-800';
                }
                if (str_contains($s, 'ปิดโปรเจค') || str_contains($s, 'เสร็จ')) {
                    return 'bg-emerald-100 text-emerald-800';
                }
                if (str_contains($s, 'เขียนรายงาน') || str_contains($s, 'publication')) {
                    return 'bg-indigo-100 text-indigo-800';
                }
                if (str_contains($s, 'วิเคราะห์')) {
                    return 'bg-violet-100 text-violet-800';
                }
                if (str_contains($s, 'เก็บข้อมูล')) {
                    return 'bg-sky-100 text-sky-800';
                }
                if (str_contains($s, 'plan') || str_contains($s, 'เริ่มต้น') || str_contains($s, 'submit irb')) {
                    return 'bg-amber-100 text-amber-800';
                }
                return 'bg-slate-100 text-slate-700';
            }
        @endphp

        <div id="project-mobile-results-shell" class="space-y-4 transition-opacity duration-200">
            @if(collect($personRoleSummary ?? [])->isNotEmpty())
                <div class="rounded-xl border border-sky-100 bg-sky-50 px-3 py-2">
                    <div class="text-xs font-semibold text-sky-700">
                        บทบาทของ <span class="text-sky-900">{{ $selectedPersonName }}</span> ในผลลัพธ์หน้านี้
                    </div>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @forelse(($personRoleSummary ?? []) as $item)
                            <span class="inline-flex items-center rounded-full bg-white border border-sky-200 px-2 py-0.5 text-xs text-sky-800">
                                {{ $item['role'] }}: {{ number_format((int) $item['count']) }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-500">ไม่พบบทบาทที่ตรงเงื่อนไข</span>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($missingFTE->count() > 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl shadow-sm">
                    <h3 class="font-semibold text-base mb-2">⚠️ แจ้งเตือน: กรุณากรอกค่า FTE ของคุณ</h3>
                    <p class="text-sm mb-3 font-medium">
                        คุณมีรายชื่อเป็นผู้ร่วมงานในโครงการด้านล่างนี้ แต่ยังไม่ได้กรอกค่า FTE โปรดดำเนินการโดยเร็วที่สุด
                        <span class="block">กดที่ชื่อโครงการเพื่อดูรายละเอียดได้ทันที</span>
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
                                <span class="text-slate-500">({{ $year }}) – {{ $code }} (#{{ $id }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Cards --}}
            <div class="space-y-3">
                @forelse ($projects as $project)
                    @php
                        $relatedPersonNames = collect()
                            ->merge(($project->fteRoles ?? collect())->pluck('name'))
                            ->merge(($project->copis ?? collect())->pluck('name'))
                            ->merge(($project->coResearches ?? collect())->pluck('name'))
                            ->filter()
                            ->map(fn($name) => trim((string) $name))
                            ->filter()
                            ->unique()
                            ->implode(' ');
                        $selectedPersonRoles = collect();
                        $selectedPersonRaw = trim((string) ($selectedPersonName ?? ''));
                        if ($selectedPersonRaw !== '') {
                            $selectedPersonLower = mb_strtolower($selectedPersonRaw, 'UTF-8');
                            $selectedPersonCompact = preg_replace('/\s+/u', '', $selectedPersonLower) ?: $selectedPersonLower;
                            $matchesSelectedPerson = function ($candidate) use ($selectedPersonLower, $selectedPersonCompact) {
                                $candidateLower = mb_strtolower(trim((string) $candidate), 'UTF-8');
                                if ($candidateLower === '') {
                                    return false;
                                }
                                if ($candidateLower === $selectedPersonLower) {
                                    return true;
                                }
                                $candidateCompact = preg_replace('/\s+/u', '', $candidateLower) ?: $candidateLower;
                                return $candidateCompact === $selectedPersonCompact;
                            };

                            if ($matchesSelectedPerson($project->pi_name ?? '')) {
                                $selectedPersonRoles->push('PI');
                            }
                            if (($project->copis ?? collect())->contains(fn ($item) => $matchesSelectedPerson($item->name ?? ''))) {
                                $selectedPersonRoles->push('Co-PI');
                            }
                            if (($project->coResearches ?? collect())->contains(fn ($item) => $matchesSelectedPerson($item->name ?? ''))) {
                                $selectedPersonRoles->push('Co-Research');
                            }
                            if (($project->fteRoles ?? collect())->contains(fn ($item) => $matchesSelectedPerson($item->name ?? ''))) {
                                $selectedPersonRoles->push('FTE');
                            }
                            $selectedPersonRoles = $selectedPersonRoles->unique()->values();
                        }
                        $showUrl = route('projects.show', $project);
                        $editUrl = route('projects.edit', $project);
                        $pdfUrl = route('projects.pdf', $project);
                        $progressUrl = route('projects.progress_reports.index', $project);
                        $fteProjectUrl = route('fte.project.detail', $project);
                        $deleteFormId = "delete-form-{$project->id}";
                        $updated = $project->updated_at ? \Carbon\Carbon::parse($project->updated_at) : null;
                    @endphp
                    <div class="lux-mobile-card space-y-2"
                        data-mobile-search-text="{{ trim(implode(' ', array_filter([$project->project_code, $project->project_name_th, $project->theme, $project->project_type, $project->funding_type, $project->planning_status, $project->start_year, $relatedPersonNames]))) }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">{{ $project->project_code }}</div>
                                <a href="{{ $showUrl }}" class="block text-slate-900 font-semibold break-words">
                                    {{ $project->project_name_th }}
                                </a>
                                @if(!empty($selectedPersonName) && $selectedPersonRoles->isNotEmpty())
                                    <div class="project-role-hit">
                                        (มีรายชื่อใน {{ $selectedPersonRoles->implode(', ') }})
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">ประเภทโครงการ</div>
                        <div class="text-sm leading-snug lux-clamp-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ type_badge_classes($project->project_type) }}">
                                {{ $project->project_type ?: '-' }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-700">
                            ทุน:
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ funding_badge_classes($project->funding_type) }}">
                                {{ $project->funding_type ?: 'ยังไม่ได้ระบุ' }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-700">
                            Theme: <span class="text-slate-900">{{ $project->theme ?: '-' }}</span>
                        </div>
                        <div class="text-sm text-slate-700">
                            สถานะโครงการ:
                            @php
                                $projectStatus = trim((string) ($project->planning_status ?? ''));
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ project_status_badge_classes($projectStatus) }}">
                                {{ $projectStatus !== '' ? $projectStatus : 'ยังไม่ได้ระบุ' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-3 text-xs text-slate-600">
                            <span>
                                Outputs:
                                @if (($project->outputs_count ?? 0) > 0)
                                    {{ $project->outputs_count }}
                                @else
                                    <span class="inline-flex items-center rounded-none border border-amber-200 bg-amber-50 px-2 py-0.5 font-semibold text-amber-700">
                                        ไม่มี output
                                    </span>
                                @endif
                            </span>
                            <span>ปี: {{ $project->start_year ?: '-' }}</span>
                            <span>อัปเดท: {{ $updated ? $updated->locale('th')->translatedFormat('j F') . ' ' . ($updated->year + 543) : '-' }}</span>
                        </div>
                        <div class="lux-mobile-actions flex flex-wrap gap-2 pt-1">
                            <a href="{{ $showUrl }}"
                                class="px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium">ดูรายละเอียด</a>
                            <a href="{{ $editUrl }}"
                                class="px-3 py-1.5 rounded-md bg-slate-100 text-slate-700 text-xs font-medium">แก้ไข</a>
                            <a href="{{ $progressUrl }}"
                                class="px-3 py-1.5 rounded-md bg-sky-50 text-sky-700 text-xs font-medium">Progress</a>
                            <a href="{{ $fteProjectUrl }}" target="_blank" rel="noopener"
                                class="px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 text-xs font-medium">FTE</a>
                            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                                class="px-3 py-1.5 rounded-md bg-green-50 text-green-700 text-xs font-medium">PDF</a>
                            <button type="button" onclick="confirmDelete('{{ $deleteFormId }}')"
                                class="px-3 py-1.5 rounded-md bg-red-50 text-red-700 text-xs font-medium">ลบ</button>
                        </div>
                        <form id="{{ $deleteFormId }}" action="{{ route('projects.destroy', $project) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-500">ไม่มีข้อมูล</div>
                @endforelse
                <div id="project-mobile-empty-search" class="hidden text-center py-10 text-slate-500">ไม่มีข้อมูลตรงกับคำค้นหาในหน้านี้</div>
            </div>

            <div id="project-mobile-pagination-wrap" class="py-3">
                {{ $projects->links('vendor.pagination.th') }}
            </div>
        </div>
    </div>

    <div id="project-mobile-filter-drawer-overlay" class="project-mobile-filter-drawer-overlay"></div>
    <aside id="project-mobile-filter-drawer" class="project-mobile-filter-drawer" aria-hidden="true">
        <div class="flex h-full flex-col border-r border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">ตัวกรองโครงการ</h3>
                    <p class="mt-1 text-xs text-slate-500">ค่าที่เลือกจะ sync กับฟิลเตอร์ด้านบนและอัปเดตผลลัพธ์ทันที</p>
                </div>
                <button id="project-mobile-filter-drawer-close" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="sr-only">ปิดฟิลเตอร์</span>
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>

            <form id="project-mobile-filter-drawer-form" method="GET" action="{{ route('projects.index') }}" class="flex h-full flex-col">
                <div class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อโครงการ / Project ID</span>
                        <input id="project-mobile-drawer-search-input" type="text" name="search"
                            placeholder="ชื่อโครงการ หรือ Project ID (รองรับคำใกล้เคียง)" value="{{ request('search') }}"
                            class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
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
                        <div class="project-filter-dropdown" data-project-filter-dropdown>
                            <input type="hidden" name="pi_name" value="{{ request('pi_name') }}" data-project-live-mobile="true" data-project-dropdown-value>
                            <button type="button" data-project-dropdown-toggle
                                class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                                <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                                <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                    <button type="button" data-project-dropdown-option data-value=""
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                    @foreach ($piOptions as $pi)
                                        <button type="button" data-project-dropdown-option data-value="{{ $pi }}"
                                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $pi }}</button>
                                    @endforeach
                                    <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">บุคคลในโครงการ</span>
                        <div class="project-filter-dropdown" data-project-filter-dropdown>
                            <input type="hidden" name="person_name" value="{{ $selectedPersonName ?? '' }}" data-project-live-mobile="true" data-project-dropdown-value>
                            <button type="button" data-project-dropdown-toggle
                                class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                                <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                                <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                    <button type="button" data-project-dropdown-option data-value=""
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                    @foreach (($personOptions ?? []) as $person)
                                        <button type="button" data-project-dropdown-option data-value="{{ $person['value'] }}"
                                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $person['label'] }}</button>
                                    @endforeach
                                    <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">แหล่งทุน</span>
                        <div class="project-filter-dropdown" data-project-filter-dropdown>
                            <input type="hidden" name="funding_source" value="{{ request('funding_source') }}" data-project-live-mobile="true" data-project-dropdown-value>
                            <button type="button" data-project-dropdown-toggle
                                class="w-full border border-slate-200 bg-white text-left p-2 rounded-lg flex items-center justify-between gap-2 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2">
                                <span class="truncate" data-project-dropdown-label>ทั้งหมด</span>
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div class="project-filter-dropdown__menu hidden" data-project-dropdown-menu>
                                <input type="text" data-project-dropdown-search placeholder="พิมพ์ค้นหา..."
                                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <div class="project-filter-dropdown__options mt-2 space-y-1" data-project-dropdown-options>
                                    <button type="button" data-project-dropdown-option data-value=""
                                        class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">ทั้งหมด</button>
                                    @foreach ($fundingSources as $src)
                                        <button type="button" data-project-dropdown-option data-value="{{ $src }}"
                                            class="w-full text-left rounded-md px-2 py-1.5 text-sm hover:bg-slate-100">{{ $src }}</button>
                                    @endforeach
                                    <div class="hidden px-2 py-1.5 text-xs text-slate-500" data-project-dropdown-empty>ไม่พบข้อมูล</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="block">
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
                        <span class="text-xs font-semibold text-slate-600">สถานะโครงการ (Project Status)</span>
                        <select name="project_status" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            <option value="__unspecified__" @selected(request('project_status') === '__unspecified__')>ยังไม่ได้ระบุ</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" @selected(request('project_status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
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
                            <label class="inline-flex items-center gap-1">
                                <input type="checkbox" name="issue_no_output" value="1" @checked(request('issue_no_output'))>
                                ไม่มี output
                            </label>
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="updated_at" @selected(request('sort') === 'updated_at')>อัปเดทล่าสุด → เก่าสุด</option>
                            <option value="created_at" @selected(request('sort') === 'created_at')>สร้างล่าสุด → เก่าสุด</option>
                            <option value="budget" @selected(request('sort') === 'budget')>งบประมาณ</option>
                            <option value="start_year" @selected(request('sort') === 'start_year')>ปีโครงการ</option>
                            <option value="planning_status" @selected(request('sort') === 'planning_status')>สถานะโครงการ</option>
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
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm">
                            ใช้ตัวกรอง
                        </button>
                        <a data-project-mobile-reset href="{{ route('projects.index') }}"
                            class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white">ล้าง</a>
                    </div>
                </div>
            </form>
        </div>
    </aside>

    <button id="project-mobile-filter-fab" type="button" class="project-mobile-filter-fab" aria-label="เปิดฟิลเตอร์">
        <span class="project-mobile-filter-fab__icon" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </span>
        <span id="project-mobile-filter-fab-badge" class="project-mobile-filter-fab__badge @if ($activeFilterCount === 0) hidden @endif">{{ $activeFilterCount }}</span>
    </button>

    <script>
        function confirmDelete(formId) {
            const form = document.getElementById(formId);
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
        }

        // AJAX filter/search/pagination (mobile + tablet, no page refresh)
        (function() {
            const searchForm = document.getElementById('project-mobile-search-form');
            const filterForm = document.getElementById('project-mobile-filter-form');
            const drawerForm = document.getElementById('project-mobile-filter-drawer-form');
            const searchInput = document.getElementById('project-mobile-search-input');
            const drawerSearchInput = document.getElementById('project-mobile-drawer-search-input');
            const resetLinks = Array.from(document.querySelectorAll('[data-project-mobile-reset]'));
            const loadingEl = document.getElementById('project-mobile-loading');
            const filterAnchor = document.getElementById('project-mobile-filter-anchor');
            const filterFab = document.getElementById('project-mobile-filter-fab');
            const filterFabBadge = document.getElementById('project-mobile-filter-fab-badge');
            const activeFilterPill = document.getElementById('project-mobile-active-filter-pill');
            const drawer = document.getElementById('project-mobile-filter-drawer');
            const drawerOverlay = document.getElementById('project-mobile-filter-drawer-overlay');
            const drawerClose = document.getElementById('project-mobile-filter-drawer-close');
            const paginationSelector = '#project-mobile-pagination-wrap';
            const shellSelector = '#project-mobile-results-shell';
            const countSelector = '#project-mobile-count';

            if (!searchForm || !filterForm || !searchInput || !drawerForm) return;

            const trackedNames = [
                'type',
                'year',
                'pi_name',
                'person_name',
                'funding_source',
                'project_status',
                'budget_range',
                'fte_status',
                'last_update',
                'sort',
                'dir',
            ];
            const checkboxNames = ['issue_overdue', 'issue_missing_dates', 'issue_duplicates', 'issue_no_output'];

            let timer = null;
            let controller = null;
            let requestToken = 0;
            let drawerOpen = false;
            let previousBodyOverflow = '';
            let isSearchComposing = false;
            const countEl = document.querySelector(countSelector);
            const paginationWrap = document.querySelector(paginationSelector);
            const emptySearchEl = document.getElementById('project-mobile-empty-search');
            const baseMobileCountText = countEl ? countEl.textContent.trim() : '';

            const currentUrl = new URL(window.location.href);
            const forcedView = currentUrl.searchParams.get('view');
            const queryAll = (selector, root = document) => Array.from(root.querySelectorAll(selector));
            const closeAllFilterDropdowns = (except = null) => {
                queryAll('[data-project-dropdown-menu]').forEach((menu) => {
                    if (except && menu === except) return;
                    menu.classList.add('hidden');
                });
            };
            const syncFilterDropdownLabels = (root = document) => {
                queryAll('[data-project-filter-dropdown]', root).forEach((dropdown) => {
                    const valueField = dropdown.querySelector('[data-project-dropdown-value]');
                    const labelEl = dropdown.querySelector('[data-project-dropdown-label]');
                    if (!valueField || !labelEl) return;
                    const selected = queryAll('[data-project-dropdown-option]', dropdown)
                        .find((option) => (option.dataset.value ?? '') === (valueField.value ?? ''));
                    labelEl.textContent = selected ? selected.textContent.trim() : (valueField.value || 'ทั้งหมด');
                });
            };
            const initFilterDropdowns = (root = document) => {
                queryAll('[data-project-filter-dropdown]', root).forEach((dropdown) => {
                    if (dropdown.dataset.dropdownBound === 'true') return;
                    dropdown.dataset.dropdownBound = 'true';

                    const valueField = dropdown.querySelector('[data-project-dropdown-value]');
                    const toggle = dropdown.querySelector('[data-project-dropdown-toggle]');
                    const menu = dropdown.querySelector('[data-project-dropdown-menu]');
                    const searchInput = dropdown.querySelector('[data-project-dropdown-search]');
                    const emptyEl = dropdown.querySelector('[data-project-dropdown-empty]');
                    const options = queryAll('[data-project-dropdown-option]', dropdown);
                    if (!valueField || !toggle || !menu || !searchInput || !options.length) return;

                    const filterOptions = () => {
                        const term = (searchInput.value || '').toLowerCase().trim();
                        let visible = 0;
                        options.forEach((option) => {
                            const text = (option.textContent || '').toLowerCase();
                            const show = term === '' || text.includes(term);
                            option.classList.toggle('hidden', !show);
                            if (show) visible += 1;
                        });
                        if (emptyEl) {
                            emptyEl.classList.toggle('hidden', visible > 0);
                        }
                    };

                    toggle.addEventListener('click', () => {
                        const willOpen = menu.classList.contains('hidden');
                        closeAllFilterDropdowns(willOpen ? menu : null);
                        menu.classList.toggle('hidden', !willOpen);
                        if (willOpen) {
                            searchInput.value = '';
                            filterOptions();
                            searchInput.focus();
                        }
                    });

                    searchInput.addEventListener('input', filterOptions);

                    options.forEach((option) => {
                        option.addEventListener('click', () => {
                            valueField.value = option.dataset.value ?? '';
                            syncFilterDropdownLabels(dropdown);
                            menu.classList.add('hidden');
                            valueField.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    });
                });

                syncFilterDropdownLabels(root);
            };
            const normalizeSearchTerm = (value) => String(value || '')
                .toLowerCase()
                .replace(/\s+/g, ' ')
                .trim();

            const setFieldValue = (field, value) => {
                if (!field) return;
                if (field.type === 'checkbox') {
                    const values = Array.isArray(value) ? value : [value];
                    field.checked = values.includes(field.value || '1');
                    return;
                }
                field.value = value ?? '';
            };

            const applySearchValue = (value, sourceInput = null) => {
                if (searchInput && searchInput !== sourceInput) {
                    searchInput.value = value;
                }
                if (drawerSearchInput && drawerSearchInput !== sourceInput) {
                    drawerSearchInput.value = value;
                }
                const primarySearchField = filterForm.querySelector('input[name="search"]');
                if (primarySearchField) {
                    primarySearchField.value = value;
                }
            };

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

            const applyLocalSearchPreview = (rawValue) => {
                const shell = document.querySelector(shellSelector);
                if (!shell) return;

                const cards = queryAll('.lux-mobile-card', shell);
                if (!cards.length) return;

                const term = normalizeSearchTerm(rawValue);
                let visibleCount = 0;
                cards.forEach((card) => {
                    const haystack = card.dataset.mobileSearchText || normalizeSearchTerm(card.textContent);
                    card.dataset.mobileSearchText = haystack;
                    const matched = term === '' || haystack.includes(term);
                    card.classList.toggle('hidden', !matched);
                    if (matched) visibleCount += 1;
                });

                if (emptySearchEl) {
                    emptySearchEl.classList.toggle('hidden', !(term !== '' && visibleCount === 0));
                }

                if (paginationWrap) {
                    paginationWrap.classList.toggle('hidden', term !== '');
                }

                if (countEl) {
                    countEl.textContent = term === '' ? baseMobileCountText : `พบ ${visibleCount} รายการในหน้านี้`;
                }
            };

            const countActiveFilters = () => {
                let count = 0;
                if (String(searchInput.value || '').trim() !== '') {
                    count += 1;
                }

                trackedNames.forEach((name) => {
                    if (['sort', 'dir'].includes(name)) return;
                    const field = filterForm.querySelector(`[name="${name}"]`);
                    if (field && String(field.value || '').trim() !== '') {
                        count += 1;
                    }
                });

                checkboxNames.forEach((name) => {
                    const field = filterForm.querySelector(`input[name="${name}"][type="checkbox"]`);
                    if (field && field.checked) {
                        count += 1;
                    }
                });

                return count;
            };

            const updateFilterBadges = () => {
                const count = countActiveFilters();
                if (activeFilterPill) {
                    activeFilterPill.textContent = String(count);
                }
                if (filterFabBadge) {
                    filterFabBadge.textContent = String(count);
                    filterFabBadge.classList.toggle('hidden', count === 0);
                }
            };

            const syncFabVisibility = () => {
                if (!filterFab || !filterAnchor) return;
                const rect = filterAnchor.getBoundingClientRect();
                const pagination = document.querySelector(paginationSelector);
                const paginationRect = pagination?.getBoundingClientRect();
                const paginationVisible = Boolean(
                    paginationRect &&
                    paginationRect.top < window.innerHeight - 120 &&
                    paginationRect.bottom > 0
                );
                filterFab.classList.toggle('is-visible', rect.bottom < 36 && !paginationVisible);
            };

            const scrollToResultsTop = () => {
                const shell = document.querySelector(shellSelector);
                if (!shell) return;

                window.requestAnimationFrame(() => {
                    const top = shell.getBoundingClientRect().top + window.scrollY - 84;
                    window.scrollTo({
                        top: Math.max(top, 0),
                        behavior: 'smooth',
                    });
                });
            };

            const openDrawer = () => {
                if (!drawer || !drawerOverlay || drawerOpen) return;
                syncFilterFormsFromSource(filterForm);
                drawer.classList.add('is-open');
                drawerOverlay.classList.add('is-open');
                drawer.setAttribute('aria-hidden', 'false');
                drawerOpen = true;
                previousBodyOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                window.setTimeout(() => {
                    drawerSearchInput?.focus({ preventScroll: true });
                }, 120);
            };

            const closeDrawer = () => {
                if (!drawer || !drawerOverlay || !drawerOpen) return;
                drawer.classList.remove('is-open');
                drawerOverlay.classList.remove('is-open');
                drawer.setAttribute('aria-hidden', 'true');
                drawerOpen = false;
                document.body.style.overflow = previousBodyOverflow;
            };

            const ensureSearchHidden = (name, value, enabled = true) => {
                let input = searchForm.querySelector(`input[type="hidden"][name="${name}"]`);
                if (!enabled) {
                    if (input) input.remove();
                    return;
                }

                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    searchForm.appendChild(input);
                }
                input.value = value;
            };

            const syncSearchFormState = () => {
                applySearchValue(searchInput.value || '', searchInput);

                trackedNames.forEach((name) => {
                    const field = filterForm.querySelector(`[name="${name}"]`);
                    ensureSearchHidden(name, field ? field.value : '');
                });

                checkboxNames.forEach((name) => {
                    const field = filterForm.querySelector(`input[name="${name}"][type="checkbox"]`);
                    ensureSearchHidden(name, '1', Boolean(field && field.checked));
                });
            };

            const syncFilterFormsFromSource = (sourceForm) => {
                [filterForm, drawerForm].forEach((targetForm) => {
                    if (!targetForm || targetForm === sourceForm) return;

                    queryAll('[name]', sourceForm).forEach((sourceField) => {
                        if (!sourceField.name) return;
                        const targetFields = queryAll(`[name="${sourceField.name}"]`, targetForm);
                        if (!targetFields.length) return;

                        if (sourceField.type === 'checkbox') {
                            const checkedValues = queryAll(`[name="${sourceField.name}"]`, sourceForm)
                                .filter((field) => field.checked)
                                .map((field) => field.value || '1');
                            targetFields.forEach((field) => setFieldValue(field, checkedValues));
                            return;
                        }

                        targetFields.forEach((field) => setFieldValue(field, sourceField.value));
                    });
                });

                const sourceSearch = sourceForm.querySelector('[name="search"]');
                if (sourceSearch) {
                    applySearchValue(sourceSearch.value || '', sourceForm === drawerForm ? drawerSearchInput : searchInput);
                }
                syncSearchFormState();
                updateFilterBadges();
                syncFilterDropdownLabels();
            };

            const buildUrlFromForms = (resetPage = true) => {
                syncSearchFormState();
                const url = new URL(filterForm.action, window.location.origin);
                if (forcedView) {
                    url.searchParams.set('view', forcedView);
                }

                const searchValue = (searchInput.value || '').trim();
                if (searchValue !== '') {
                    url.searchParams.set('search', searchValue);
                }

                trackedNames.forEach((name) => {
                    const field = filterForm.querySelector(`[name="${name}"]`);
                    const value = field ? String(field.value || '').trim() : '';
                    if (value !== '') {
                        url.searchParams.set(name, value);
                    }
                });

                checkboxNames.forEach((name) => {
                    const field = filterForm.querySelector(`input[name="${name}"][type="checkbox"]`);
                    if (field && field.checked) {
                        url.searchParams.set(name, '1');
                    }
                });

                if (resetPage) {
                    url.searchParams.delete('page');
                }

                return url;
            };

            const applyUrlToForms = (url) => {
                applySearchValue(url.searchParams.get('search') ?? '');

                trackedNames.forEach((name) => {
                    [filterForm, drawerForm].forEach((form) => {
                        const field = form.querySelector(`[name="${name}"]`);
                        if (!field) return;
                        setFieldValue(field, url.searchParams.get(name) ?? '');
                    });
                });

                checkboxNames.forEach((name) => {
                    [filterForm, drawerForm].forEach((form) => {
                        const field = form.querySelector(`input[name="${name}"][type="checkbox"]`);
                        if (!field) return;
                        field.checked = url.searchParams.get(name) === '1';
                    });
                });

                syncSearchFormState();
                updateFilterBadges();
                syncFilterDropdownLabels();
            };

            const replaceMobileShell = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextShell = doc.querySelector(shellSelector);
                const currentShell = document.querySelector(shellSelector);
                if (!nextShell || !currentShell) {
                    return false;
                }
                currentShell.replaceWith(nextShell);

                const nextCount = doc.querySelector(countSelector);
                const currentCount = document.querySelector(countSelector);
                if (nextCount && currentCount) {
                    currentCount.textContent = nextCount.textContent;
                }

                return true;
            };

            const fetchAndRender = async (url, options = {}) => {
                const {
                    updateUrl = true,
                    scrollToTop = false,
                    silent = false
                } = options;
                const token = ++requestToken;

                if (controller) {
                    controller.abort();
                }
                controller = new AbortController();
                if (!silent) {
                    setLoading(true);
                }

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

                    if (!replaceMobileShell(html)) {
                        throw new Error('Cannot replace mobile results shell');
                    }

                    if (updateUrl) {
                        history.replaceState({
                            ajax: true
                        }, '', `${url.pathname}${url.search}`);
                    }

                    applyUrlToForms(url);
                    syncFabVisibility();

                    if (scrollToTop) {
                        scrollToResultsTop();
                    }

                    applyLocalSearchPreview(searchInput?.value || '');
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    if (!silent && token === requestToken) {
                        setLoading(false);
                    }
                }
            };

            const fetchDebounced = (delay = 220, resetPage = true, options = {}) => {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    fetchAndRender(buildUrlFromForms(resetPage), {
                        updateUrl: true,
                        ...options
                    });
                }, delay);
            };

            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                syncSearchFormState();
                syncFilterFormsFromSource(filterForm);
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });

            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                syncFilterFormsFromSource(filterForm);
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });

            drawerForm.addEventListener('submit', (event) => {
                event.preventDefault();
                syncFilterFormsFromSource(drawerForm);
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });

            searchInput.addEventListener('input', () => {
                if (isSearchComposing) return;
                applySearchValue(searchInput.value || '', searchInput);
                applyLocalSearchPreview(searchInput.value || '');
                syncSearchFormState();
                syncFilterFormsFromSource(filterForm);
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });
            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applySearchValue(searchInput.value || '', searchInput);
                    applyLocalSearchPreview(searchInput.value || '');
                    syncSearchFormState();
                    syncFilterFormsFromSource(filterForm);
                    fetchAndRender(buildUrlFromForms(true), {
                        updateUrl: true
                    });
                }
            });
            searchInput.addEventListener('compositionstart', () => {
                isSearchComposing = true;
            });
            searchInput.addEventListener('compositionend', () => {
                isSearchComposing = false;
                applySearchValue(searchInput.value || '', searchInput);
                applyLocalSearchPreview(searchInput.value || '');
                syncSearchFormState();
                syncFilterFormsFromSource(filterForm);
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });

            if (drawerSearchInput) {
                drawerSearchInput.addEventListener('input', () => {
                    if (isSearchComposing) return;
                    applySearchValue(drawerSearchInput.value || '', drawerSearchInput);
                    applyLocalSearchPreview(drawerSearchInput.value || '');
                    syncFilterFormsFromSource(drawerForm);
                    fetchAndRender(buildUrlFromForms(true), {
                        updateUrl: true
                    });
                });
                drawerSearchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        applySearchValue(drawerSearchInput.value || '', drawerSearchInput);
                        applyLocalSearchPreview(drawerSearchInput.value || '');
                        syncFilterFormsFromSource(drawerForm);
                        fetchAndRender(buildUrlFromForms(true), {
                            updateUrl: true
                        });
                    }
                });
                drawerSearchInput.addEventListener('compositionstart', () => {
                    isSearchComposing = true;
                });
                drawerSearchInput.addEventListener('compositionend', () => {
                    isSearchComposing = false;
                    applySearchValue(drawerSearchInput.value || '', drawerSearchInput);
                    applyLocalSearchPreview(drawerSearchInput.value || '');
                    syncFilterFormsFromSource(drawerForm);
                    fetchAndRender(buildUrlFromForms(true), {
                        updateUrl: true
                    });
                });
            }

            const bindFilterFields = (form) => {
                form.querySelectorAll('select, input[type="checkbox"], input[data-project-live-mobile]').forEach((field) => {
                    field.addEventListener('change', () => {
                        syncFilterFormsFromSource(form);
                        fetchDebounced(120, true);
                    });
                });
            };
            bindFilterFields(filterForm);
            bindFilterFields(drawerForm);

            resetLinks.forEach((resetLink) => {
                resetLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    applySearchValue('');

                    trackedNames.forEach((name) => {
                        [filterForm, drawerForm].forEach((form) => {
                            const field = form.querySelector(`[name="${name}"]`);
                            if (!field) return;
                            if (name === 'sort') {
                                setFieldValue(field, 'updated_at');
                                return;
                            }
                            if (name === 'dir') {
                                setFieldValue(field, 'desc');
                                return;
                            }
                            setFieldValue(field, '');
                        });
                    });

                    checkboxNames.forEach((name) => {
                        [filterForm, drawerForm].forEach((form) => {
                            const field = form.querySelector(`input[name="${name}"][type="checkbox"]`);
                            if (field) field.checked = false;
                        });
                    });

                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    if (forcedView) {
                        resetUrl.searchParams.set('view', forcedView);
                    }
                    syncSearchFormState();
                    updateFilterBadges();
                    fetchAndRender(resetUrl, {
                        updateUrl: true
                    });
                });
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-project-filter-dropdown]')) {
                    closeAllFilterDropdowns();
                }

                if (event.target.closest('#project-mobile-filter-fab')) {
                    event.preventDefault();
                    openDrawer();
                    return;
                }

                if (event.target.closest('#project-mobile-filter-drawer-overlay')) {
                    closeDrawer();
                    return;
                }

                if (event.target.closest('#project-mobile-filter-drawer-close')) {
                    event.preventDefault();
                    closeDrawer();
                    return;
                }
            });

            filterFab?.addEventListener('click', openDrawer);
            drawerOverlay?.addEventListener('click', closeDrawer);
            drawerClose?.addEventListener('click', closeDrawer);

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && drawerOpen) {
                    closeDrawer();
                }
                if (event.key === 'Escape') {
                    closeAllFilterDropdowns();
                }
            });

            document.addEventListener('click', (event) => {
                const link = event.target.closest(`${shellSelector} a[href]`);
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href) return;

                const url = new URL(href, window.location.origin);
                const isSamePage = url.pathname === window.location.pathname;
                const isPagination = url.searchParams.has('page');
                if (!isSamePage || !isPagination) {
                    return;
                }

                event.preventDefault();
                applyUrlToForms(url);
                fetchAndRender(url, {
                    updateUrl: true,
                    scrollToTop: true
                });
            });

            window.addEventListener('scroll', syncFabVisibility, { passive: true });
            window.addEventListener('resize', syncFabVisibility);

            initFilterDropdowns();
            applyUrlToForms(currentUrl);
            applyLocalSearchPreview(searchInput?.value || '');
            syncFabVisibility();
        })();
    </script>
@endsection
