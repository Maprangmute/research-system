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
    <style>
        .project-detail-safe,
        .project-detail-safe * {
            min-width: 0;
        }

        .project-detail-safe {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .project-detail-safe a,
        .project-detail-safe .break-words {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .project-title-adaptive {
            line-height: 1.22;
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }

        .project-title-adaptive.project-title--normal {
            font-size: 1.58rem;
        }

        .project-title-adaptive.project-title--medium {
            font-size: 1.34rem;
        }

        .project-title-adaptive.project-title--long {
            font-size: 1.16rem;
        }

        .project-title-adaptive.project-title--xlong {
            font-size: 0.98rem;
        }

        .project-header-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 380px);
            gap: 1rem 1.25rem;
            align-items: start;
        }

        .project-header-main {
            min-width: 0;
        }

        .project-header-side {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.65rem;
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 0.75rem;
        }

        .project-status-chip {
            align-self: flex-end;
            border-color: #cbd5e1;
            padding: 0.35rem 0.72rem;
        }

        .project-header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
        }

        .project-header-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            width: 100%;
            min-height: 2.5rem;
            border-radius: 11px;
            border: 1px solid #dbe5f0;
            background: #f8fafc;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }

        .project-header-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
        }

        .project-header-action--edit {
            background: #fff8e6;
            border-color: #f5d58b;
            color: #9a5b12;
        }

        .project-header-action--progress {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .project-header-action--pdf {
            background: #eafbf3;
            border-color: #a7f3d0;
            color: #0f766e;
        }

        .project-header-action--fte {
            background: #ecfeff;
            border-color: #a5f3fc;
            color: #0f766e;
        }

        .project-header-action--gantt {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .project-header-action--delete {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #be123c;
        }

        .project-detail-safe table {
            width: 100%;
            table-layout: fixed;
        }

        .project-detail-safe th,
        .project-detail-safe td {
            overflow-wrap: anywhere;
            word-break: break-word;
            vertical-align: top;
        }

        .project-detail-safe .overflow-x-auto > table {
            min-width: 720px;
            table-layout: auto;
        }

        .project-detail-safe .detail-index-col {
            width: 3.5rem;
            min-width: 3.5rem;
            white-space: nowrap;
        }

        .project-detail-safe .irb-code-col {
            width: 30%;
        }

        .project-detail-safe .irb-source-col {
            width: 40%;
        }

        .project-detail-safe .irb-exp-col {
            width: 10rem;
            min-width: 10rem;
            white-space: nowrap;
        }

        .edit-section-nav {
            position: fixed;
            left: 4px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 55;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.45rem;
            padding: 0.55rem;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(8px);
        }

        .edit-section-nav::before {
            content: '';
            position: absolute;
            top: 0.85rem;
            bottom: 0.85rem;
            left: 1.3rem;
            width: 2px;
            background: linear-gradient(180deg, #bfdbfe 0%, #cbd5e1 100%);
            border-radius: 999px;
            z-index: 0;
        }

        .edit-section-dot {
            position: relative;
            z-index: 1;
            width: auto;
            min-width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 800;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 0.46rem;
            overflow: hidden;
            white-space: nowrap;
            max-width: 38px;
            transition: max-width 0.2s ease, padding 0.2s ease, transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease, box-shadow 0.16s ease, color 0.16s ease;
        }

        .edit-section-dot:hover,
        .edit-section-dot:focus-visible {
            outline: none;
            transform: translateY(-1px);
            border-color: #60a5fa;
            background: #eff6ff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.18);
        }

        .edit-section-dot.is-active {
            border-color: #2563eb;
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.28);
        }

        .edit-section-dot__short {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            border-radius: 9px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.65);
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
        }

        .edit-section-dot__full {
            margin-left: 0.5rem;
            font-size: 0.78rem;
            font-weight: 700;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 0.14s ease, transform 0.14s ease;
        }

        .edit-section-nav.is-expanded .edit-section-dot {
            width: 270px;
            max-width: 270px;
            padding: 0 0.62rem;
        }

        .edit-section-nav.is-expanded .edit-section-dot__full {
            opacity: 1;
            transform: translateX(0);
        }

        .edit-section-dot.is-active .edit-section-dot__short {
            border-color: rgba(191, 219, 254, 0.9);
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        .edit-section-target {
            scroll-margin-top: 88px;
        }

        #pageTop .project-zone-card {
            border: 1px solid #dbe5f0 !important;
            border-radius: 1rem !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%) !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05) !important;
        }

        #pageTop .project-zone-card.project-zone-card--alt {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        }

        #pageTop .project-zone-title {
            margin: 0 0 0.9rem !important;
            padding: 0.55rem 0.75rem;
            border: 1px solid #dbeafe;
            border-radius: 0.8rem;
            background: #eff6ff;
            color: #1d4ed8 !important;
            font-weight: 800;
            line-height: 1.25;
        }

        @media (max-width: 640px) {
            .project-detail-safe .overflow-x-auto > table {
                min-width: 620px;
            }
        }

        @media (max-width: 1279px) {
            .edit-section-nav {
                display: none;
            }
        }

        @media (min-width: 768px) {
            #pageTop.project-detail-safe {
                max-width: 96rem;
            }

            #pageTop.project-detail-safe,
            #pageTop.project-detail-safe input,
            #pageTop.project-detail-safe select,
            #pageTop.project-detail-safe textarea,
            #pageTop.project-detail-safe button,
            #pageTop.project-detail-safe table {
                font-size: 1rem;
            }

            #pageTop.project-detail-safe h2 {
                font-size: 1.75rem;
            }

            .project-title-adaptive.project-title--normal {
                font-size: 1.68rem;
            }

            .project-title-adaptive.project-title--medium {
                font-size: 1.42rem;
            }

            .project-title-adaptive.project-title--long {
                font-size: 1.22rem;
            }

            .project-title-adaptive.project-title--xlong {
                font-size: 1.02rem;
            }

            #pageTop.project-detail-safe h3 {
                font-size: 1.35rem;
            }
        }

        @media (max-width: 1240px) {
            .project-header-layout {
                grid-template-columns: minmax(0, 1fr) minmax(300px, 340px);
            }
        }

        @media (max-width: 980px) {
            .project-header-layout {
                grid-template-columns: 1fr;
            }

            .project-header-side {
                align-items: flex-start;
            }

            .project-status-chip {
                align-self: flex-start;
            }

            .project-header-actions {
                grid-template-columns: 1fr 1fr;
            }
        }

        .detail-fab-wrap {
            position: fixed;
            left: 16px;
            bottom: 16px;
            z-index: 60;
            opacity: 0;
            transform: translateY(8px) scale(0.96);
            pointer-events: none;
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .detail-fab-wrap.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .detail-fab-menu {
            width: min(84vw, 280px);
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px) scale(0.98);
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
        }

        .detail-fab-menu.is-open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .detail-fab-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            opacity: 0;
            transform: translateY(5px);
            transition: transform 0.18s ease, opacity 0.18s ease, filter 0.15s ease;
        }

        .detail-fab-menu.is-open .detail-fab-item {
            opacity: 1;
            transform: translateY(0);
        }

        .detail-fab-menu.is-open .detail-fab-item:nth-child(1) {
            transition-delay: 0.02s;
        }

        .detail-fab-menu.is-open .detail-fab-item:nth-child(2) {
            transition-delay: 0.05s;
        }

        .detail-fab-menu.is-open .detail-fab-item:nth-child(3) {
            transition-delay: 0.08s;
        }

        .detail-fab-menu.is-open .detail-fab-item:nth-child(4) {
            transition-delay: 0.11s;
        }

        .detail-fab-menu.is-open .detail-fab-item:nth-child(5) {
            transition-delay: 0.14s;
        }

        .detail-fab-menu.is-open .detail-fab-item:nth-child(6) {
            transition-delay: 0.17s;
        }

        .detail-fab-item:hover {
            filter: brightness(0.98);
        }

        .detail-fab-item--edit {
            background: #fef3c7;
            color: #92400e;
        }

        .detail-fab-item--progress {
            background: #e0e7ff;
            color: #3730a3;
        }

        .detail-fab-item--pdf {
            background: #d1fae5;
            color: #065f46;
        }

        .detail-fab-item--fte {
            background: #ecfeff;
            color: #0f766e;
        }

        .detail-fab-item--gantt {
            background: #dbeafe;
            color: #1e3a8a;
        }

        .detail-fab-item--delete {
            background: #ffe4e6;
            color: #9f1239;
        }

        .detail-fab-btn {
            min-width: 118px;
            height: 44px;
            border-radius: 999px;
            border: 1px solid #bfdbfe;
            background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            font-weight: 700;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 24px rgba(29, 78, 216, 0.22);
            transition: transform 0.16s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .detail-fab-btn:hover {
            background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .detail-fab-btn:active {
            transform: translateY(1px) scale(0.98);
        }

        .detail-fab-btn span[aria-hidden="true"] {
            transition: transform 0.2s ease;
        }

        .detail-fab-btn[aria-expanded="true"] span[aria-hidden="true"] {
            transform: rotate(90deg);
        }

        #appAiChatFabWrap.is-hidden-at-bottom {
            opacity: 0;
            transform: translateY(12px);
            pointer-events: none;
        }
    </style>

    <div id="pageTop" class="project-detail-safe mx-auto px-4 py-6 space-y-6">
        <aside class="edit-section-nav" aria-label="เมนูลัดหัวข้อ">
            <button type="button" class="edit-section-dot" data-nav-target="section-a" aria-label="A: ข้อมูลโครงการ"><span class="edit-section-dot__short">A</span><span class="edit-section-dot__full">A: ข้อมูลโครงการ</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-b" aria-label="B: ระยะเวลาดำเนินโครงการ"><span class="edit-section-dot__short">B</span><span class="edit-section-dot__full">B: ระยะเวลาดำเนินโครงการ</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-c" aria-label="C: ข้อมูลแหล่งทุน"><span class="edit-section-dot__short">C</span><span class="edit-section-dot__full">C: ข้อมูลแหล่งทุน</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-d" aria-label="D: Roles and Responsibilities"><span class="edit-section-dot__short">D</span><span class="edit-section-dot__full">D: Roles and Responsibilities</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-e" aria-label="E: รหัส IRB"><span class="edit-section-dot__short">E</span><span class="edit-section-dot__full">E: รหัส IRB</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-f" aria-label="F: รายชื่อผู้มีส่วนร่วมในการทำงาน"><span class="edit-section-dot__short">F</span><span class="edit-section-dot__full">F: รายชื่อผู้มีส่วนร่วมในการทำงาน</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-g" aria-label="G: ผลงานวิจัย (Outputs)"><span class="edit-section-dot__short">G</span><span class="edit-section-dot__full">G: ผลงานวิจัย (Outputs)</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-i" aria-label="H: ความก้าวหน้าของการดำเนินงาน"><span class="edit-section-dot__short">H</span><span class="edit-section-dot__full">H: ความก้าวหน้าของการดำเนินงาน</span></button>
            <button type="button" class="edit-section-dot" data-nav-target="section-j" aria-label="I: ยุทธศาสตร์ Siriraj Health Policy"><span class="edit-section-dot__short">I</span><span class="edit-section-dot__full">I: ยุทธศาสตร์ Siriraj Health Policy</span></button>
        </aside>
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
                $raw = trim((string) ($value ?? ''));
                if ($raw === '') {
                    return '-';
                }

                $raw = preg_replace_callback('/[๐-๙]/u', static fn ($m) => (string) mb_strpos('๐๑๒๓๔๕๖๗๘๙', $m[0]), $raw);
                $raw = preg_replace('/\s+/u', ' ', $raw);

                $formatCarbon = static function (\Carbon\Carbon $date) {
                    $date = $date->copy()->locale('th');
                    return $date->translatedFormat('j F') . ' ' . ($date->year + 543);
                };

                foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'] as $format) {
                    try {
                        return $formatCarbon(\Carbon\Carbon::createFromFormat($format, $raw));
                    } catch (\Throwable $e) {
                    }
                }

                if (preg_match('/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/u', $raw, $m)) {
                    $monthMap = [
                        'มกราคม' => 1, 'ม.ค.' => 1, 'ม.ค' => 1,
                        'กุมภาพันธ์' => 2, 'ก.พ.' => 2, 'ก.พ' => 2,
                        'มีนาคม' => 3, 'มี.ค.' => 3, 'มี.ค' => 3,
                        'เมษายน' => 4, 'เม.ย.' => 4, 'เม.ย' => 4,
                        'พฤษภาคม' => 5, 'พ.ค.' => 5, 'พ.ค' => 5,
                        'มิถุนายน' => 6, 'มิ.ย.' => 6, 'มิ.ย' => 6,
                        'กรกฎาคม' => 7, 'ก.ค.' => 7, 'ก.ค' => 7,
                        'สิงหาคม' => 8, 'ส.ค.' => 8, 'ส.ค' => 8,
                        'กันยายน' => 9, 'ก.ย.' => 9, 'ก.ย' => 9,
                        'ตุลาคม' => 10, 'ต.ค.' => 10, 'ต.ค' => 10,
                        'พฤศจิกายน' => 11, 'พ.ย.' => 11, 'พ.ย' => 11,
                        'ธันวาคม' => 12, 'ธ.ค.' => 12, 'ธ.ค' => 12,
                    ];
                    $day = (int) $m[1];
                    $month = $monthMap[$m[2]] ?? null;
                    $year = (int) $m[3];
                    if ($year > 2400) {
                        $year -= 543;
                    }
                    if ($month) {
                        try {
                            return $formatCarbon(\Carbon\Carbon::create($year, $month, $day, 0, 0, 0));
                        } catch (\Throwable $e) {
                        }
                    }
                }

                try {
                    return $formatCarbon(\Carbon\Carbon::parse($raw));
                } catch (\Throwable $e) {
                    return $raw;
                }
            };
            $projectTitle = trim((string) ($project->project_name_th ?? '-'));
            $projectTitleLength = mb_strlen($projectTitle);
            $projectTitleClass = match (true) {
                $projectTitleLength >= 120 => 'project-title--xlong',
                $projectTitleLength >= 90 => 'project-title--long',
                $projectTitleLength >= 60 => 'project-title--medium',
                default => 'project-title--normal',
            };
        @endphp

        <div id="detailHeaderCard" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <div class="project-header-layout">

                {{-- Left: Title --}}
                <div class="project-header-main">
                    <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                        ← กลับไปยังรายการโครงการ
                    </a>
                    <h2 class="project-title-adaptive {{ $projectTitleClass }} font-semibold text-slate-900 mt-2">
                        {{ $projectTitle }}
                    </h2>
                    <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-600">
                        <span class="font-medium text-slate-500">รหัสโครงการ</span>
                        <span class="font-semibold text-slate-700">{{ $project->project_code ?: '-' }}</span>
                    </div>
                </div>

                {{-- Right: Status + Actions --}}
                <div class="project-header-side">

                    {{-- Status (1 full row) --}}
                    <span
                        class="project-status-chip mt-1 mb-1 w-fit rounded-full text-xs font-semibold {{ $bgClass }} text-slate-900 border">
                        สถานะ: {{ $statusText }}
                    </span>



                    {{-- Action buttons (another row) --}}
                    <div class="project-header-actions">
                        <a href="{{ route('projects.edit', $project) }}"
                            class="project-header-action project-header-action--edit">
                            ✏️ แก้ไข
                        </a>

                        <a href="{{ route('projects.progress_reports.index', $project) }}"
                            class="project-header-action project-header-action--progress">
                            📑 Progress Report
                        </a>

                        <a href="{{ route('projects.pdf', $project) }}" target="_blank"
                            class="project-header-action project-header-action--pdf">
                            🖨️ PDF
                        </a>

                        <a href="{{ route('fte.project.detail', $project) }}" target="_blank"
                            class="project-header-action project-header-action--fte">
                            🔥 FTE project
                        </a>

                        

                        <a href="{{ route('projects.gantt', $project) }}"
                            class="project-header-action project-header-action--gantt">
                            📊 Gantt
                        </a>

                        {{-- Delete --}}
                        <button type="button" id="deleteProjectBtn"
                            class="project-header-action project-header-action--delete">
                            🗑️ ลบ
                        </button>

                        <form id="delete-project-{{ $project->id }}"
                            action="{{ route('projects.destroy', $project) }}" method="POST" class="hidden">
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
                <h3 id="section-a" class="edit-section-target font-semibold text-lg text-blue-700 border-b border-slate-200 pb-3 mb-4">A: ข้อมูลโครงการ</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-slate-700">
                    <div><span class="font-semibold">ชื่อโครงการ (TH):</span> {{ $project->project_name_th ?? '-' }}</div>
                    <div><span class="font-semibold">ชื่อโครงการ (EN):</span> {{ $project->project_name_en ?? '-' }}</div>
                    <div><span class="font-semibold">กลุ่มเนื้อหาหลักของงาน (Content/Theme):</span> {{ $project->project_type ?? '-' }}</div>
                    <div><span class="font-semibold">ลักษณะโครงการ:</span> {{ $project->project_list ?? '-' }}</div>
                    <div><span class="font-semibold">ประเภทของโครงการ/กิจกรรม (TYPE):</span> {{ $project->theme ?? '-' }}</div>

                    <div class="md:col-span-2">
                        <span class="font-semibold">เอกสารโครงการ (proposal , IRB) : </span>
                        @if ($project->shortcut_link)
                            <a href="{{ $project->shortcut_link }}"
                                class="ml-1 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 underline-offset-2 hover:bg-blue-100 hover:underline"
                                target="_blank" rel="noopener">
                                {{ $project->project_code ?: 'รหัสโครงการ' }}
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
                <h3 id="section-b" class="edit-section-target font-semibold text-lg text-blue-700 border-b border-slate-200 pb-3 mb-4">B:
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
                    <div><span class="font-semibold">BC103 - วันที่สิ้นสุด <strong>(ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)</strong>:</span>
                        {{ $formatDateBE($project->end_date ?? null) }}</div>
                </div>
            </div>

            <!-- 🟢 Section B: ข้อมูลแหล่งทุน -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 id="section-c" class="edit-section-target font-semibold text-lg text-blue-700 border-b border-slate-200 pb-3 mb-4">C: ข้อมูลแหล่งทุน</h3>
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
                <h3 id="section-d" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">D: Roles and Responsibilities</h3>
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
                            <th class="detail-index-col border px-2 py-2 text-center font-semibold rounded-tl-2xl">#</th>
                            <th class="border px-3 py-2 font-semibold">ชื่อ</th>
                            <th class="border px-3 py-2 font-semibold rounded-tr-2xl">สังกัด</th>
                        </tr>
                    </thead>
                    <tbody class="text-[15px]">
                        @if ($coPolicy->count())
                            @foreach ($coPolicy as $i => $co)
                                <tr>
                                    <td class="detail-index-col border px-2 py-2 text-center">{{ $i + 1 }}</td>
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
                            <th class="detail-index-col border px-2 py-2 text-center font-semibold rounded-tl-2xl">#</th>
                            <th class="border px-3 py-2 font-semibold">ชื่อ</th>
                            <th class="border px-3 py-2 font-semibold rounded-tr-2xl">สังกัด</th>
                        </tr>
                    </thead>
                    <tbody class="text-[15px]">
                        @if ($coInternal->count())
                            @foreach ($coInternal as $i => $co)
                                <tr>
                                    <td class="detail-index-col border px-2 py-2 text-center">{{ $i + 1 }}</td>
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
                            <th class="detail-index-col border px-2 py-2 text-center font-semibold rounded-tl-2xl">#</th>
                            <th class="border px-3 py-2 font-semibold">ชื่อ</th>
                            <th class="border px-3 py-2 font-semibold rounded-tr-2xl">สังกัด</th>
                        </tr>
                    </thead>
                    <tbody class="text-[15px]">
                        @if ($coExternal->count())
                            @foreach ($coExternal as $i => $co)
                                <tr>
                                    <td class="detail-index-col border px-2 py-2 text-center">{{ $i + 1 }}</td>
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

            @if (false)
            @php
                $latestProgressReport = ($project->progressReports ?? collect())->first();
            @endphp
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-3">
                    <div>
                        <h3 class="font-semibold text-lg text-blue-700">E: ความก้าวหน้าของการดำเนินงาน</h3>
                        @if ($latestProgressReport)
                            @php
                                $latestSubmitDate = $latestProgressReport->submit_date
                                    ? \Carbon\Carbon::parse($latestProgressReport->submit_date)->locale('th')->translatedFormat('j F') .
                                        ' ' .
                                        (\Carbon\Carbon::parse($latestProgressReport->submit_date)->year + 543)
                                    : '-';
                            @endphp
                            <p class="text-sm text-slate-600 mt-1">
                                อ้างอิงรายงานล่าสุดวันที่ {{ $latestSubmitDate }}
                                โดย {{ $latestProgressReport->submitter_name ?: '-' }}
                            </p>
                        @else
                            <p class="text-sm text-slate-500 mt-1">ยังไม่มีรายงานความก้าวหน้า</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('projects.progress_reports.index', $project) }}"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            รายงานทั้งหมด
                        </a>
                        <a href="{{ route('projects.progress_reports.create', $project) }}"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-white border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50">
                            เพิ่มรายงาน
                        </a>
                    </div>
                </div>

                @if ($latestProgressReport)
                    @php
                        $progressTitle = trim((string) ($latestProgressReport->progress_title ?? '')) ?: 'ความก้าวหน้าของการดำเนินงาน';
                        $progressSubtitle = trim((string) ($latestProgressReport->progress_subtitle ?? ''));
                        $latestProgressRows = collect($latestProgressReport->progress_rows ?? [])
                            ->filter(function ($row) {
                                return !empty(trim((string) ($row['plan'] ?? ''))) ||
                                    !empty(trim((string) ($row['actual'] ?? ''))) ||
                                    !empty(trim((string) ($row['alignment'] ?? '')));
                            })
                            ->values();
                        $latestIssueRows = collect($latestProgressReport->issue_rows ?? [])
                            ->filter(function ($row) {
                                return !empty(trim((string) ($row['problem'] ?? ''))) ||
                                    !empty(trim((string) ($row['solution'] ?? ''))) ||
                                    !empty(trim((string) ($row['consultation'] ?? '')));
                            })
                            ->values();
                        $alignmentClass = function ($text) {
                            $normalized = mb_strtolower(trim((string) $text));
                            if ($normalized === '') {
                                return 'text-slate-500';
                            }
                            if (str_contains($normalized, 'ช้ากว่าแผน') && str_contains($normalized, 'ไม่กระทบ')) {
                                return 'text-amber-700';
                            }
                            if (str_contains($normalized, 'ช้ากว่าแผน') || str_contains($normalized, 'ล่าช้า') || str_contains($normalized, 'ช้า')) {
                                return 'text-orange-700';
                            }
                            if (str_contains($normalized, 'เป็นไปตามแผน')) {
                                return 'text-emerald-700';
                            }
                            return 'text-slate-700';
                        };
                    @endphp

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 mb-4">
                        <div class="font-semibold text-slate-900">{{ $progressTitle }}</div>
                        @if ($progressSubtitle !== '')
                            <div class="text-sm text-slate-600 mt-1">{{ $progressSubtitle }}</div>
                        @endif
                        @if ($latestProgressReport->file_info)
                            <div class="text-sm text-slate-600 mt-1">
                                ไฟล์/ลิงก์: {{ $latestProgressReport->file_info }}
                            </div>
                        @endif
                    </div>

                    <h4 class="font-semibold text-slate-800 mb-2">กิจกรรมตามแผนการดำเนินงาน</h4>
                    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200 bg-white">
                        <table class="min-w-full text-sm border-separate border-spacing-0">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border px-3 py-2 text-left font-semibold">แผนการดำเนินงาน</th>
                                    <th class="border px-3 py-2 text-left font-semibold">การดำเนินงานจริง</th>
                                    <th class="border px-3 py-2 text-left font-semibold">ความสอดคล้องของการดำเนินงานจริงกับแผนการดำเนินงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestProgressRows as $row)
                                    @php $alignmentText = trim((string) ($row['alignment'] ?? '')); @endphp
                                    <tr class="odd:bg-[#eef5fa]">
                                        <td class="border px-3 py-2 align-top">{{ trim((string) ($row['plan'] ?? '')) ?: '-' }}</td>
                                        <td class="border px-3 py-2 align-top">{{ trim((string) ($row['actual'] ?? '')) ?: '-' }}</td>
                                        <td class="border px-3 py-2 align-top font-semibold {{ $alignmentClass($alignmentText) }}">
                                            {{ $alignmentText !== '' ? $alignmentText : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="border px-3 py-5 text-center text-slate-500">ไม่มีข้อมูลกิจกรรม</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h4 class="font-semibold text-slate-800 mt-5 mb-2">ปัญหาอุปสรรค</h4>
                    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200 bg-white">
                        <table class="min-w-full text-sm border-separate border-spacing-0">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border px-3 py-2 text-left font-semibold">ปัญหาอุปสรรค</th>
                                    <th class="border px-3 py-2 text-left font-semibold">แนวทางการแก้ปัญหา</th>
                                    <th class="border px-3 py-2 text-left font-semibold">ประเด็นขอคำปรึกษาจากที่ประชุม</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestIssueRows as $row)
                                    <tr class="odd:bg-[#eef5fa]">
                                        <td class="border px-3 py-2 align-top">{{ trim((string) ($row['problem'] ?? '')) ?: '-' }}</td>
                                        <td class="border px-3 py-2 align-top">{{ trim((string) ($row['solution'] ?? '')) ?: '-' }}</td>
                                        <td class="border px-3 py-2 align-top">{{ trim((string) ($row['consultation'] ?? '')) ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="border px-3 py-5 text-center text-slate-500">ไม่มีข้อมูลปัญหาอุปสรรค</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2">
                    <h3 id="section-e" class="edit-section-target font-semibold text-lg text-blue-700">E: รหัส IRB</h3>
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
                <div class="overflow-x-auto rounded-2xl">
                    <table
                        class="min-w-full bg-white rounded-2xl shadow-mb ring-1 ring-slate-100 border-separate border-spacing-0 mb-0">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="detail-index-col border px-2 py-3 text-center font-semibold rounded-tl-2xl">#</th>
                                <th class="irb-code-col border px-4 py-3 font-semibold">รหัส IRB</th>
                                <th class="irb-source-col border px-4 py-3 font-semibold">จากหน่วยงาน</th>
                                <th class="irb-exp-col border px-4 py-3 text-center font-semibold rounded-tr-2xl">วันหมดอายุ</th>
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
                                        <td class="detail-index-col border px-2 py-3 text-center align-middle">{{ $i + 1 }}</td>
                                        <td class="border px-4 py-3">{{ $irb->code ?: '-' }}</td>
                                        <td class="border px-4 py-3">{{ $irb->source ?: '-' }}</td>
                                        <td class="irb-exp-col border px-4 py-3 text-center">
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
                                    <td colspan="4" class="py-10 text-center text-gray-500">
                                        ไม่มีข้อมูลรหัส IRB
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 id="section-f" class="edit-section-target font-semibold text-lg text-blue-700 mb-3">F: รายชื่อผู้มีส่วนร่วมในการทำงาน</h3>

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
                                        $jobDetailRaw = trim((string) ($fte->job_detail ?? ''));
                                        $taskDetailRaw = trim((string) ($fte->task_detail ?? ''));
                                        $jobDetail = $jobDetailRaw !== ''
                                            ? $jobDetailRaw
                                            : ($taskDetailRaw !== '' ? $taskDetailRaw : '-');
                                        $taskDetail = ($taskDetailRaw !== '' && $taskDetailRaw !== $jobDetail)
                                            ? $taskDetailRaw
                                            : '-';
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
                                                — {{ $jobDetail }}
                                            </span>
                                        </td>

                                        <!-- รายละเอียดภารกิจ -->
                                        <td class="border px-3 py-3 text-slate-700">
                                            {{ $taskDetail }}
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
                    <a href="{{ route('fte.project.detail', $project) }}"
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

                $typeUniverseSource = collect($outputTypeOptions ?? [])
                    ->filter(fn($type) => trim((string) $type) !== '')
                    ->map(fn($type) => trim((string) $type))
                    ->unique()
                    ->sort()
                    ->values();
                $typeUniverse = $typeUniverseSource->isNotEmpty()
                    ? $typeUniverseSource
                    : $outputsReal
                        ->pluck('type')
                        ->filter(fn($type) => trim((string) $type) !== '')
                        ->map(fn($type) => trim((string) $type))
                        ->unique()
                        ->sort()
                        ->values();
                $typePalette = [
                    '#7aa2f7', '#7dcfb6', '#f7b267', '#f79d9d', '#b39ddb', '#7ec8e3', '#9ad29a', '#9fa8da',
                    '#f6b26b', '#f4a6c1', '#9bb8ff', '#8fd3c8', '#c5d86d', '#c3a6e8', '#d9b08c', '#8ecae6',
                    '#9ccc9c', '#a5b4fc', '#f4b183', '#e8a5a5', '#82c7b2', '#aab2bd', '#d7c97a', '#b0bec5',
                ];
                $withAlpha = static function (string $hex, string $alpha): string {
                    $base = ltrim(trim($hex), '#');
                    if (strlen($base) !== 6) {
                        return '#cbd5e1';
                    }
                    return '#' . $base . $alpha;
                };
                $typeBaseColorMap = $typeUniverse
                    ->values()
                    ->mapWithKeys(fn($type, $index) => [(string) $type => $typePalette[$index % count($typePalette)]])
                    ->all();
                $typeStyleMap = collect($typeBaseColorMap)->mapWithKeys(function ($baseColor, $type) use ($withAlpha) {
                    return [
                        (string) $type => sprintf(
                            'background-color:%s; border-color:%s; color:%s;',
                            $withAlpha($baseColor, '1A'),
                            $withAlpha($baseColor, '66'),
                            $baseColor
                        ),
                    ];
                })->all();
                $defaultTypeStyle = 'background-color:#f8fafc; border-color:#cbd5e1; color:#334155;';
                $outputTypeStyle = function ($type) use ($typeStyleMap, $defaultTypeStyle) {
                    $label = trim((string) $type);
                    return $typeStyleMap[$label] ?? $defaultTypeStyle;
                };
            @endphp
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 id="section-g" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">G: ผลงานวิจัย (Outputs)</h3>

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
                                $isEA119 = Str::startsWith((string) ($output->type ?? ''), 'EA119');
                                $typeBadgeStyle = $outputTypeStyle($output->type ?? '');
                            @endphp

                            <div id="output-{{ $output->id }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0 mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700">
                                        {{ $i + 1 }}.
                                    </div>

                                    <div class="grow space-y-2.5">
                                        {{-- ชื่อผลงาน + badge ประเภท --}}
                                        @if (!empty($output->output_code))
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">
                                                {{ $output->output_code }}
                                            </div>
                                        @endif
                                        <div class="text-base font-semibold leading-snug text-gray-800 break-words">
                                                {{ $output->title ?: '-' }}
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 text-sm">
                                            @if (!empty($output->type))
                                                <span
                                                    class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold"
                                                    style="{{ $typeBadgeStyle }}">
                                                    {{ $output->type }}
                                                </span>
                                            @endif
                                            <span class="text-slate-400">|</span>
                                            <span class="text-slate-500">วันที่เผยแพร่</span>
                                            <span class="font-medium text-slate-700">{{ th_date_or_raw($output->year) }}</span>
                                        </div>

                                        <div class="text-sm">
                                            <div class="text-slate-500">รายละเอียด / ลิงก์</div>
                                            <div class="mt-0.5 text-slate-800 break-words">
                                                {!! linkify_if_url($output->project_output ?? null) !!}
                                            </div>
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
                                                                    <th class="px-2 py-2 text-left">ชื่อผู้นำเสนอ</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($confsReal as $ci => $c)
                                                                    <tr id="output-{{ $output->id }}-conference-{{ $c->id }}" class="border-t">
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

                                        @if ($isEA119)
                                            @php
                                                $bookAuthors = $output->bookAuthors ?? collect();
                                                $bookAuthorsReal = $bookAuthors->filter(function ($a) {
                                                    return !empty(trim($a->name ?? ''));
                                                });
                                            @endphp

                                            <div class="mt-3 border rounded-xl p-4 bg-emerald-50/60">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <div class="text-gray-500">ชื่อหนังสือ</div>
                                                        <div class="text-gray-800">{{ $output->book_title ?: '-' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-gray-500">ชื่อสำนักพิมพ์</div>
                                                        <div class="text-gray-800">{{ $output->publisher_name ?: '-' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-gray-500">ชื่อบท / เลขหน้า</div>
                                                        <div class="text-gray-800">{{ $output->chapter_pages ?: '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="font-semibold text-emerald-800 mb-2">รายชื่อผู้เขียน</div>
                                                    @if ($bookAuthorsReal->isEmpty())
                                                        <div class="text-sm text-gray-500">- ไม่มีรายชื่อผู้เขียน -</div>
                                                    @else
                                                        <ul class="list-disc list-inside text-sm text-slate-700">
                                                            @foreach ($bookAuthorsReal as $a)
                                                                <li>{{ $a->name ?: '-' }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @include('projects.partials.progress_section_detail', ['project' => $project])

            <div id="progressReportModal" class="fixed inset-0 z-[90] hidden">
                <div class="absolute inset-0 bg-slate-900/50" data-progress-modal-close></div>
                <div class="relative mx-auto h-[100dvh] w-full rounded-none bg-white border border-slate-200 shadow-xl md:mt-3 md:h-[94vh] md:w-[98vw] md:max-w-[1700px] md:rounded-2xl">
                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-slate-200">
                        <h4 id="progressReportModalTitle" class="text-sm md:text-base font-semibold text-slate-900">ความก้าวหน้าของการดำเนินงาน</h4>
                        <div class="flex items-center gap-2">
                            <button type="button" data-progress-modal-close
                                class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-sm hover:bg-slate-900">
                                ปิด
                            </button>
                        </div>
                    </div>
                    <iframe id="progressReportModalFrame" src="about:blank" class="w-full h-[calc(100dvh-57px)] md:h-[calc(94vh-57px)] rounded-none md:rounded-b-2xl"></iframe>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 id="section-j" class="edit-section-target font-semibold text-lg text-blue-700 mb-2">I: ยุทธศาสตร์ Siriraj Health Policy</h3>
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

        <div class="detail-fab-wrap">
            <div id="detailFabMenu" class="detail-fab-menu">
                <a href="{{ route('projects.edit', $project) }}" class="detail-fab-item detail-fab-item--edit">
                    ✏️ แก้ไขโครงการ
                </a>
                <a href="{{ route('projects.progress_reports.index', $project) }}" class="detail-fab-item detail-fab-item--progress">
                    📑 จัดการ Progress Report
                </a>
                <a href="{{ route('projects.pdf', $project) }}" target="_blank" class="detail-fab-item detail-fab-item--pdf">
                    🖨️ PDF
                </a>
                <a href="{{ route('fte.project.detail', $project) }}" target="_blank" class="detail-fab-item detail-fab-item--fte">
                    🔥 FTE project
                </a>
                <a href="{{ route('projects.gantt', $project) }}" class="detail-fab-item detail-fab-item--gantt">
                    📊 Gantt
                </a>
                <button type="button" id="detailFabDeleteBtn" class="detail-fab-item detail-fab-item--delete">
                    🗑️ ลบ
                </button>
            </div>
            <button id="detailFabBtn" type="button" class="detail-fab-btn" aria-expanded="false" aria-controls="detailFabMenu">
                <span aria-hidden="true">☰</span>
                <span>เมนูลัด</span>
            </button>
        </div>

    </div>
    <script>
        (() => {
            const headings = Array.from(document.querySelectorAll('#pageTop .edit-section-target'));
            if (!headings.length) return;

            headings.forEach((heading, index) => {
                heading.classList.add('project-zone-title');
                const sectionCard = heading.closest('.rounded-2xl');
                if (!sectionCard) return;
                sectionCard.classList.add('project-zone-card');
                if (index % 2 === 1) {
                    sectionCard.classList.add('project-zone-card--alt');
                }
            });
        })();
    </script>
    <script>
        (function() {
            const navDots = Array.from(document.querySelectorAll('.edit-section-dot[data-nav-target]'));
            if (!navDots.length) return;
            const quickNav = document.querySelector('.edit-section-nav');
            const expandQuickNav = () => quickNav?.classList.add('is-expanded');
            const collapseQuickNav = () => quickNav?.classList.remove('is-expanded');

            quickNav?.addEventListener('mouseenter', expandQuickNav);
            quickNav?.addEventListener('mouseleave', collapseQuickNav);
            quickNav?.addEventListener('focusin', expandQuickNav);
            quickNav?.addEventListener('focusout', (event) => {
                if (!quickNav?.contains(event.relatedTarget)) collapseQuickNav();
            });

            const sectionById = new Map();
            navDots.forEach((dot) => {
                const id = dot.dataset.navTarget;
                const section = id ? document.getElementById(id) : null;
                if (section) sectionById.set(id, section);
            });

            if (!sectionById.size) return;

            const getOffset = () => Math.max(78, Math.round(window.innerHeight * 0.14));

            navDots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    const targetId = dot.dataset.navTarget;
                    const targetEl = targetId ? sectionById.get(targetId) : null;
                    if (!targetEl) return;

                    const y = targetEl.getBoundingClientRect().top + window.scrollY - getOffset();
                    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
                    dot.blur();
                    collapseQuickNav();
                });
            });

            const updateActive = () => {
                const offset = getOffset();
                const entries = Array.from(sectionById.entries());
                let activeId = '';

                for (let i = entries.length - 1; i >= 0; i -= 1) {
                    const [id, section] = entries[i];
                    const top = section.getBoundingClientRect().top;
                    if (top <= offset + 14) {
                        activeId = id;
                        break;
                    }
                }

                if (!activeId) {
                    const firstVisible = entries.find(([, section]) => section.getBoundingClientRect().bottom > 0);
                    activeId = firstVisible ? firstVisible[0] : entries[0][0];
                }

                navDots.forEach((dot) => {
                    dot.classList.toggle('is-active', dot.dataset.navTarget === activeId);
                });
            };

            updateActive();
            window.addEventListener('scroll', updateActive, { passive: true });
            window.addEventListener('resize', updateActive);
        })();

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

        (function() {
            const modal = document.getElementById('progressReportModal');
            const frame = document.getElementById('progressReportModalFrame');
            const title = document.getElementById('progressReportModalTitle');
            const currentProjectId = Number(@json($project->id));
            if (!modal || !frame || !title) return;

            const openModal = (url, modalTitle) => {
                if (!url) return;
                title.textContent = modalTitle || 'ความก้าวหน้าของการดำเนินงาน';
                frame.src = url;
                modal.classList.remove('hidden');
                document.documentElement.classList.add('overflow-hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                frame.src = 'about:blank';
                document.documentElement.classList.remove('overflow-hidden');
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-progress-modal-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    openModal(button.dataset.url || '', button.dataset.title || '');
                });
            });

            modal.querySelectorAll('[data-progress-modal-close]').forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin) return;

                const payload = event.data;
                if (!payload || Number(payload.projectId) !== currentProjectId) return;

                if (payload.type === 'progress-report:close') {
                    closeModal();
                    return;
                }

                if (payload.type !== 'progress-report:updated') return;

                closeModal();

                if (payload.message && window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: String(payload.message),
                        confirmButtonText: 'ตกลง',
                        timer: 1800
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }

                window.location.reload();
            });
        })();

        (function() {
            const fabBtn = document.getElementById('detailFabBtn');
            const fabMenu = document.getElementById('detailFabMenu');
            const fabWrap = document.querySelector('.detail-fab-wrap');
            const headerCard = document.getElementById('detailHeaderCard');
            const aiFabWrap = document.getElementById('appAiChatFabWrap');
            if (!fabBtn || !fabMenu || !fabWrap || !headerCard) return;
            const bottomThreshold = 40;
            let headerOutOfView = false;

            const setOpen = (open) => {
                fabMenu.classList.toggle('is-open', open);
                fabBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            };

            const setVisible = (visible) => {
                fabWrap.classList.toggle('is-visible', visible);
                if (!visible) setOpen(false);
            };

            const isNearBottom = () => {
                const doc = document.documentElement;
                return window.innerHeight + window.scrollY >= doc.scrollHeight - bottomThreshold;
            };

            const refreshFabVisibility = () => {
                const nearBottom = isNearBottom();
                setVisible(headerOutOfView && !nearBottom);
                if (aiFabWrap) {
                    aiFabWrap.classList.toggle('is-hidden-at-bottom', nearBottom);
                }
            };

            fabBtn.addEventListener('click', () => {
                const isOpen = fabMenu.classList.contains('is-open');
                setOpen(!isOpen);
            });

            document.addEventListener('click', (event) => {
                if (!fabMenu.contains(event.target) && !fabBtn.contains(event.target)) {
                    setOpen(false);
                }
            });

            setOpen(false);

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    const entry = entries[0];
                    headerOutOfView = !entry.isIntersecting;
                    refreshFabVisibility();
                }, {
                    root: null,
                    threshold: 0.02,
                });
                observer.observe(headerCard);
            } else {
                headerOutOfView = window.scrollY > 220;
            }

            const onScroll = () => {
                if (!('IntersectionObserver' in window)) {
                    headerOutOfView = window.scrollY > 220;
                }
                refreshFabVisibility();
            };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            fabMenu.querySelectorAll('a').forEach((item) => {
                item.addEventListener('click', () => {
                    setOpen(false);
                });
            });

            const fabDeleteBtn = document.getElementById('detailFabDeleteBtn');
            if (fabDeleteBtn && deleteBtn) {
                fabDeleteBtn.addEventListener('click', () => {
                    setOpen(false);
                    deleteBtn.click();
                });
            }
        })();
    </script>
@endsection
