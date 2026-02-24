@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .lux-mobile-page {
            max-width: 100%;
            margin: 0.75rem;
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
    </style>

    <div class="lux-mobile-page space-y-4">
        {{-- Header --}}
        <div class="flex flex-col gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">📊 รายการโครงการวิจัย</h2>
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
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อโครงการ / Project ID</span>
                <input id="project-mobile-search-input" type="text" name="search"
                    placeholder="ชื่อโครงการ หรือ Project ID (รองรับคำใกล้เคียง)" value="{{ request('search') }}"
                    class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
                <span class="mt-1 block text-[11px] text-slate-500">ค้นหาแบบ realtime ไม่ต้องกดปุ่ม</span>
            </label>
        </form>

        {{-- Filters --}}
        <details class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <summary class="px-4 py-3 text-sm font-semibold text-slate-700 cursor-pointer">ตัวกรอง</summary>
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
                    <span class="text-xs font-semibold text-slate-600">Project Status</span>
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
                    </div>
                </label>
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
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-sm">ใช้ตัวกรอง</button>
                    <a id="project-mobile-reset" href="{{ route('projects.index') }}"
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
            @if ($missingFTE->count() > 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl shadow-sm">
                    <h3 class="font-semibold text-base mb-2">⚠️ แจ้งเตือน: กรุณากรอกค่า FTE ของคุณ</h3>
                    <p class="text-sm mb-3 font-medium">
                        คุณมีรายชื่อเป็นผู้ร่วมงานในโครงการด้านล่างนี้ แต่ยังไม่ได้กรอกค่า FTE โปรดดำเนินการโดยเร็วที่สุด
                        <span class="block">กดที่ชื่อโปรเจคเพื่อดูรายละเอียดได้ทันที</span>
                    </p>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($missingFTE as $fte)
                            <li>
                                <a href="{{ route('projects.edit', $fte->project_id) }}"
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
                        $showUrl = route('projects.show', $project->id);
                        $editUrl = route('projects.edit', $project->id);
                        $pdfUrl = route('projects.pdf', $project->id);
                        $progressUrl = route('projects.progress_reports.index', $project->id);
                        $fteProjectUrl = route('fte.project.detail', $project->id);
                        $deleteFormId = "delete-form-{$project->id}";
                        $updated = $project->updated_at ? \Carbon\Carbon::parse($project->updated_at) : null;
                    @endphp
                    <div class="lux-mobile-card space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-500">{{ $project->project_code }}</div>
                                <a href="{{ $showUrl }}" class="block text-slate-900 font-semibold break-words">
                                    {{ $project->project_name_th }}
                                </a>
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
                            Project Status:
                            @php
                                $projectStatus = trim((string) ($project->planning_status ?? ''));
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ project_status_badge_classes($projectStatus) }}">
                                {{ $projectStatus !== '' ? $projectStatus : 'ยังไม่ได้ระบุ' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-3 text-xs text-slate-600">
                            <span>Outputs: {{ $project->outputs_count ?? 0 }}</span>
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
                        <form id="{{ $deleteFormId }}" action="{{ route('projects.destroy', $project->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-500">ไม่มีข้อมูล</div>
                @endforelse
            </div>

            <div class="py-3">
                {{ $projects->links('vendor.pagination.th') }}
            </div>
        </div>
    </div>

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

        // AJAX filter/search/pagination (mobile, no page refresh)
        (function() {
            const searchForm = document.getElementById('project-mobile-search-form');
            const filterForm = document.getElementById('project-mobile-filter-form');
            const searchInput = document.getElementById('project-mobile-search-input');
            const resetLink = document.getElementById('project-mobile-reset');
            const loadingEl = document.getElementById('project-mobile-loading');
            const shellSelector = '#project-mobile-results-shell';
            const countSelector = '#project-mobile-count';

            if (!searchForm || !filterForm || !searchInput) return;

            const trackedNames = [
                'type',
                'year',
                'pi_name',
                'funding_source',
                'project_status',
                'budget_range',
                'fte_status',
                'last_update',
                'sort',
                'dir',
            ];
            const checkboxNames = ['issue_overdue', 'issue_missing_dates', 'issue_duplicates'];

            let timer = null;
            let controller = null;
            let requestToken = 0;

            const currentUrl = new URL(window.location.href);
            const forcedView = currentUrl.searchParams.get('view');

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

            const syncForms = () => {
                const searchHidden = filterForm.querySelector('input[name="search"]');
                if (searchHidden) {
                    searchHidden.value = searchInput.value || '';
                }

                trackedNames.forEach((name) => {
                    const field = filterForm.querySelector(`[name="${name}"]`);
                    ensureSearchHidden(name, field ? field.value : '');
                });

                checkboxNames.forEach((name) => {
                    const field = filterForm.querySelector(`input[name="${name}"][type="checkbox"]`);
                    ensureSearchHidden(name, '1', Boolean(field && field.checked));
                });
            };

            const buildUrlFromForms = (resetPage = true) => {
                syncForms();
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
                searchInput.value = url.searchParams.get('search') ?? '';

                trackedNames.forEach((name) => {
                    const field = filterForm.querySelector(`[name="${name}"]`);
                    if (!field) return;
                    field.value = url.searchParams.get(name) ?? '';
                });

                checkboxNames.forEach((name) => {
                    const field = filterForm.querySelector(`input[name="${name}"][type="checkbox"]`);
                    if (!field) return;
                    field.checked = url.searchParams.get(name) === '1';
                });

                syncForms();
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

                    if (!replaceMobileShell(html)) {
                        throw new Error('Cannot replace mobile results shell');
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

            const fetchDebounced = (delay = 220, resetPage = true) => {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    fetchAndRender(buildUrlFromForms(resetPage), {
                        updateUrl: true
                    });
                }, delay);
            };

            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });

            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchAndRender(buildUrlFromForms(true), {
                    updateUrl: true
                });
            });

            searchInput.addEventListener('input', () => fetchDebounced(260, true));
            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    fetchDebounced(0, true);
                }
            });

            const filterFields = filterForm.querySelectorAll('select, input[type="checkbox"]');
            filterFields.forEach((field) => {
                field.addEventListener('change', () => fetchDebounced(120, true));
            });

            if (resetLink) {
                resetLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    searchInput.value = '';

                    trackedNames.forEach((name) => {
                        const field = filterForm.querySelector(`[name="${name}"]`);
                        if (!field) return;
                        if (name === 'sort') {
                            field.value = 'updated_at';
                            return;
                        }
                        if (name === 'dir') {
                            field.value = 'desc';
                            return;
                        }
                        field.value = '';
                    });

                    checkboxNames.forEach((name) => {
                        const field = filterForm.querySelector(`input[name="${name}"][type="checkbox"]`);
                        if (field) field.checked = false;
                    });

                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    if (forcedView) {
                        resetUrl.searchParams.set('view', forcedView);
                    }
                    syncForms();
                    fetchAndRender(resetUrl, {
                        updateUrl: true
                    });
                });
            }

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
                    updateUrl: true
                });
            });

            syncForms();
        })();
    </script>
@endsection
