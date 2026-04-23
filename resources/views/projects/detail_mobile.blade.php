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
        .mobile-detail {
            font-size: 15px;
            color: #0f172a;
        }

        #pageTop.mobile-detail {
            max-width: 36rem;
        }

        .mobile-detail,
        .mobile-detail * {
            min-width: 0;
        }

        .mobile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
        }

        .mobile-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        }

        .field-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .field-row:last-child {
            border-bottom: none;
        }

        .field-label {
            font-weight: 600;
            color: #475569;
            font-size: 13px;
        }

        .field-value {
            color: #0f172a;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .list-card {
            display: flex;
            gap: 12px;
            padding: 12px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .list-index {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
        }

        .list-meta {
            font-size: 13px;
            color: #64748b;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .output-block {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        }

        .sub-list {
            display: grid;
            gap: 8px;
        }

        .sub-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            background: #ffffff;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .mobile-detail a,
        .mobile-detail .list-card,
        .mobile-detail .output-block {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .mobile-detail .list-card > :last-child {
            min-width: 0;
            flex: 1 1 auto;
        }

        .mobile-project-title {
            line-height: 1.24;
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }

        .mobile-project-title.mobile-project-title--normal {
            font-size: 1.25rem;
        }

        .mobile-project-title.mobile-project-title--medium {
            font-size: 1.12rem;
        }

        .mobile-project-title.mobile-project-title--long {
            font-size: 1.02rem;
        }

        .mobile-project-title.mobile-project-title--xlong {
            font-size: 0.95rem;
        }

        @media (max-width: 420px) {
            .field-row {
                grid-template-columns: 92px 1fr;
            }
        }

        @media (min-width: 768px) {
            #pageTop.mobile-detail {
                max-width: 96rem;
            }

            .mobile-detail {
                font-size: 1rem;
            }
        }

        @media (max-width: 360px) {
            .field-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }

        .detail-fab-wrap {
            position: fixed;
            left: 14px;
            bottom: 14px;
            z-index: 70;
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
            width: min(88vw, 270px);
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
            min-width: 108px;
            height: 44px;
            border-radius: 999px;
            border: 1px solid #bfdbfe;
            background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            font-weight: 700;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
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

    <div id="pageTop" class="mobile-detail mx-auto px-4 py-4 space-y-4">
        @php
            $status = $project->planning_status ?? '-';
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
            $mobileProjectTitleClass = match (true) {
                $projectTitleLength >= 140 => 'mobile-project-title--xlong',
                $projectTitleLength >= 100 => 'mobile-project-title--long',
                $projectTitleLength >= 70 => 'mobile-project-title--medium',
                default => 'mobile-project-title--normal',
            };
        @endphp

        <div id="detailHeaderCard" class="mobile-card space-y-3">
            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                ← กลับไปยังรายการโครงการ
            </a>
            <h2 class="mobile-project-title {{ $mobileProjectTitleClass }} font-semibold text-slate-900">{{ $projectTitle }}</h2>
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-600">
                <span class="font-medium text-slate-500">รหัสโครงการ</span>
                <span class="font-semibold text-slate-700">{{ $project->project_code ?: '-' }}</span>
            </div>
            <span class="tag {{ $bgClass }} border border-slate-300 text-slate-900">
                สถานะ: {{ $statusText }}
            </span>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('projects.edit', $project) }}"
                    class="px-3 py-2 rounded-lg bg-amber-100 text-amber-800 text-sm font-semibold hover:bg-amber-200">
                    ✏️ แก้ไข
                </a>
                <a href="{{ route('projects.progress_reports.index', $project) }}"
                    class="px-3 py-2 rounded-lg bg-indigo-100 text-indigo-800 text-sm font-semibold hover:bg-indigo-200">
                    📑 Progress Report
                </a>

                <a href="{{ route('fte.project.detail', $project) }}" target="_blank"
                    class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-semibold hover:bg-emerald-200">
                        🔥 FTE project
                </a>
                       
                <a href="{{ route('projects.pdf', $project) }}" target="_blank"
                    class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-semibold hover:bg-emerald-200">
                    🖨️ PDF
                </a>
                <a href="{{ route('projects.gantt', $project) }}"
                    class="px-3 py-2 rounded-lg bg-sky-100 text-sky-800 text-sm font-semibold hover:bg-sky-200">
                    📊 Gantt
                </a>
                <button type="button" id="deleteProjectBtn"
                    class="px-3 py-2 rounded-lg bg-rose-100 text-rose-800 text-sm font-semibold hover:bg-rose-200">
                    🗑️ ลบ
                </button>
            </div>
            <form id="delete-project-{{ $project->id }}"
                action="{{ route('projects.destroy', $project) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">A: ข้อมูลโครงการ</h3>
            <div class="field-row">
                <div class="field-label">ชื่อโครงการ (TH)</div>
                <div class="field-value">{{ $project->project_name_th ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ชื่อโครงการ (EN)</div>
                <div class="field-value">{{ $project->project_name_en ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">กลุ่มเนื้อหาหลักของงาน</div>
                <div class="field-value">{{ $project->project_type ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ประเภทของโครงการ/กิจกรรม</div>
                <div class="field-value">{{ $project->theme ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ลักษณะโครงการ</div>
                <div class="field-value">{{ $project->project_list ?? '-' }}</div>
            </div>

            <div class="field-row">
                <div class="field-label">เอกสารโครงการ (proposal , IRB) : </div>
                <div class="field-value">
                    @if ($project->shortcut_link)
                        <a href="{{ $project->shortcut_link }}"
                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 underline-offset-2 hover:bg-blue-100 hover:underline"
                            target="_blank" rel="noopener">
                            {{ $project->project_code ?: 'รหัสโครงการ' }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="field-row">
                <div class="field-label">ขนาด</div>
                <div class="field-value">{{ $project->size ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">รหัสโครงการ R</div>
                <div class="field-value">{{ $project->r_code ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ปีโปรเจค</div>
                <div class="field-value">{{ $project->start_year ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">สถานะ</div>
                <div class="field-value">{{ $project->planning_status ?? '-' }}</div>
            </div>
        </div>

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">B: ระยะเวลาดำเนินโครงการ</h3>
            <div class="field-row">
                <div class="field-label">เริ่มทำงานของทีมวิจัย</div>
                <div class="field-value">{{ $formatDateBE($project->team_start_date ?? null) }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">สิ้นสุดทีมวิจัย</div>
                <div class="field-value">{{ $formatDateBE($project->team_end_date ?? null) }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">BA001 Proposal</div>
                <div class="field-value">{{ $formatDateBE($project->proposal_date ?? null) }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">BC101 สัญญาอนุมัติ</div>
                <div class="field-value">{{ $formatDateBE($project->contract_date ?? null) }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">BC102 เริ่มโครงการ</div>
                <div class="field-value">{{ $formatDateBE($project->start_date ?? null) }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">BC103 - วันที่สิ้นสุด (ตามสัญญา / หากมีการขอขยายเวลาให้ใส่วันที่ใหม่ที่ได้รับการอนุมัติ)</div>
                <div class="field-value">{{ $formatDateBE($project->end_date ?? null) }}</div>
            </div>
        </div>

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">C: ข้อมูลแหล่งทุน</h3>
            <div class="field-row">
                <div class="field-label">ประเภทแหล่งทุน</div>
                <div class="field-value">{{ $project->funding_type ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ชื่อแหล่งทุน</div>
                <div class="field-value">{{ $project->funding_source ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">รัฐบาล/เอกชน/มูลนิธิ</div>
                <div class="field-value">{{ $project->funding_org_type ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ชื่อทุน</div>
                <div class="field-value">{{ $project->funding_name ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">งบประมาณ</div>
                <div class="field-value">{{ $project->budget ? number_format($project->budget, 2) : '-' }}</div>
            </div>
        </div>

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">D: Roles and Responsibilities</h3>
            @php
                $hasPI = !empty(trim($project->pi_name ?? '')) || !empty(trim($project->pi_affiliation ?? ''));
                $copiList = $project->copis ?? collect();
                $copiReal = $copiList->filter(
                    fn($copi) => !empty(trim($copi->name ?? '')) || !empty(trim($copi->affiliation ?? '')),
                );
                $roles = collect();
                if ($hasPI) {
                    $roles->push((object) [
                        'name' => $project->pi_name ?: '-',
                        'affiliation' => $project->pi_affiliation ?: '-',
                        'role' => 'PI',
                    ]);
                }
                foreach ($copiReal as $copi) {
                    $roles->push((object) [
                        'name' => $copi->name ?: '-',
                        'affiliation' => $copi->affiliation ?: '-',
                        'role' => 'Co-PI',
                    ]);
                }
            @endphp

            @if ($roles->isEmpty())
                <div class="text-sm text-gray-500 text-center">ไม่มีข้อมูล</div>
            @else
                <div class="space-y-2">
                    @foreach ($roles as $i => $item)
                        <div class="list-card">
                            <div class="list-index">{{ $i + 1 }}</div>
                            <div>
                                <div class="font-semibold">{{ $item->name }}</div>
                                <div class="list-meta">{{ $item->affiliation }}</div>
                                <div class="tag bg-slate-100 text-slate-700 mt-2">{{ $item->role }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @php
                $coAll = ($project->coResearches ?? collect())->filter(function ($co) {
                    return !empty(trim($co->name)) || !empty(trim($co->affiliation));
                });
                $norm = function ($s) {
                    $t = preg_replace('/\s+/u', '', (string) ($s ?? ''));
                    return mb_strtolower($t);
                };
                $classify = function ($s) use ($norm) {
                    $u = $norm($s);
                    if (
                        str_contains($u, 'ภายในหน่วยวิจัย') &&
                        (str_contains($u, 'นโยบายสุขภาพ') || str_contains($u, 'นโยบาลสุขภาพ'))
                    ) {
                        return 'policy';
                    }
                    if (
                        str_contains($u, 'ภายในคณะแพทยศาสตร์ศิริราชพยาบาล') ||
                        str_contains($u, 'ภายใน') ||
                        str_contains($u, 'internal')
                    ) {
                        return 'internal';
                    }
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
                $coSections = [
                    [
                        'title' => 'Co-Researcher (ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ)',
                        'items' => $coPolicy,
                        'empty' => 'ไม่มีนักวิจัยในหมวดนี้',
                    ],
                    [
                        'title' => 'Co-Researcher (ภายในคณะแพทยศาสตร์ศิริราชพยาบาล)',
                        'items' => $coInternal,
                        'empty' => 'ไม่มีนักวิจัยภายในคณะ',
                    ],
                    [
                        'title' => 'Co-Researcher (ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล)',
                        'items' => $coExternal,
                        'empty' => 'ไม่มีนักวิจัยภายนอก',
                    ],
                ];
            @endphp

            <div class="space-y-3">
                @foreach ($coSections as $section)
                    <div class="mobile-card space-y-2">
                        <div class="font-semibold text-slate-800 text-sm">{{ $section['title'] }}</div>
                        @if ($section['items']->isEmpty())
                            <div class="text-sm text-gray-500">{{ $section['empty'] }}</div>
                        @else
                            <div class="space-y-2">
                                @foreach ($section['items'] as $i => $co)
                                    <div class="list-card">
                                        <div class="list-index">{{ $i + 1 }}</div>
                                        <div>
                                            <div class="font-semibold">{{ $co->name ?: '-' }}</div>
                                            <div class="list-meta">{{ $co->affiliation ?: '-' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if (false)
        @php
            $latestProgressReport = ($project->progressReports ?? collect())->first();
            $alignmentBadgeClass = function ($text) {
                $normalized = mb_strtolower(trim((string) $text));
                if ($normalized === '') {
                    return 'bg-slate-100 text-slate-700';
                }
                if (str_contains($normalized, 'ช้ากว่าแผน') && str_contains($normalized, 'ไม่กระทบ')) {
                    return 'bg-amber-100 text-amber-800';
                }
                if (str_contains($normalized, 'ช้ากว่าแผน') || str_contains($normalized, 'ล่าช้า') || str_contains($normalized, 'ช้า')) {
                    return 'bg-orange-100 text-orange-800';
                }
                if (str_contains($normalized, 'เป็นไปตามแผน')) {
                    return 'bg-emerald-100 text-emerald-800';
                }
                return 'bg-slate-100 text-slate-700';
            };
        @endphp
        <div class="mobile-section space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold text-lg text-blue-700">E: ความก้าวหน้าของการดำเนินงาน</h3>
                <a href="{{ route('projects.progress_reports.index', $project) }}"
                    class="text-xs px-3 py-1.5 rounded-lg bg-blue-600 text-white">ทั้งหมด</a>
            </div>

            @if ($latestProgressReport)
                @php
                    $latestSubmitDate = $latestProgressReport->submit_date
                        ? \Carbon\Carbon::parse($latestProgressReport->submit_date)->locale('th')->translatedFormat('j F') .
                            ' ' .
                            (\Carbon\Carbon::parse($latestProgressReport->submit_date)->year + 543)
                        : '-';
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
                @endphp

                <div class="mobile-card space-y-2">
                    <div class="font-semibold text-slate-900">{{ $progressTitle }}</div>
                    @if ($progressSubtitle !== '')
                        <div class="text-sm text-slate-600">{{ $progressSubtitle }}</div>
                    @endif
                    <div class="text-xs text-slate-500">รายงานล่าสุด: {{ $latestSubmitDate }} โดย {{ $latestProgressReport->submitter_name ?: '-' }}</div>
                    @if ($latestProgressReport->file_info)
                        <div class="text-xs text-slate-600 break-words">ไฟล์/ลิงก์: {{ $latestProgressReport->file_info }}</div>
                    @endif
                </div>

                <div class="mobile-card space-y-2">
                    <div class="font-semibold text-slate-800 text-sm">กิจกรรมตามแผนการดำเนินงาน</div>
                    @forelse ($latestProgressRows as $i => $row)
                        @php $alignmentText = trim((string) ($row['alignment'] ?? '')); @endphp
                        <div class="list-card">
                            <div class="list-index">{{ $i + 1 }}</div>
                            <div class="space-y-1">
                                <div class="text-xs text-slate-500">แผน</div>
                                <div class="text-sm text-slate-800">{{ trim((string) ($row['plan'] ?? '')) ?: '-' }}</div>
                                <div class="text-xs text-slate-500 mt-1">ดำเนินการจริง</div>
                                <div class="text-sm text-slate-800">{{ trim((string) ($row['actual'] ?? '')) ?: '-' }}</div>
                                <div class="mt-2">
                                    <span class="tag {{ $alignmentBadgeClass($alignmentText) }}">{{ $alignmentText !== '' ? $alignmentText : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">ไม่มีข้อมูลกิจกรรม</div>
                    @endforelse
                </div>

                <div class="mobile-card space-y-2">
                    <div class="font-semibold text-slate-800 text-sm">ปัญหาอุปสรรค</div>
                    @forelse ($latestIssueRows as $i => $row)
                        <div class="list-card">
                            <div class="list-index">{{ $i + 1 }}</div>
                            <div class="space-y-1">
                                <div class="text-xs text-slate-500">ปัญหา</div>
                                <div class="text-sm text-slate-800">{{ trim((string) ($row['problem'] ?? '')) ?: '-' }}</div>
                                <div class="text-xs text-slate-500 mt-1">แนวทางแก้ไข</div>
                                <div class="text-sm text-slate-800">{{ trim((string) ($row['solution'] ?? '')) ?: '-' }}</div>
                                <div class="text-xs text-slate-500 mt-1">ประเด็นปรึกษา</div>
                                <div class="text-sm text-slate-800">{{ trim((string) ($row['consultation'] ?? '')) ?: '-' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">ไม่มีข้อมูลปัญหาอุปสรรค</div>
                    @endforelse
                </div>
            @else
                <div class="mobile-card text-sm text-slate-500">
                    ยังไม่มีรายงานความก้าวหน้า
                    <a href="{{ route('projects.progress_reports.create', $project) }}" class="text-blue-600 underline ml-1">เพิ่มรายงาน</a>
                </div>
            @endif
        </div>
        @endif

        <div class="mobile-section space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold text-lg text-blue-700">F: รหัส IRB</h3>
                <label class="text-xs text-gray-600">
                    ตัวกรอง:
                    <select id="irbFilter" class="ml-1 border rounded px-2 py-1 text-xs">
                        <option value="all">ทั้งหมด</option>
                        <option value="exp_1m">หมดอายุใน 1 เดือน</option>
                        <option value="exp_3m">หมดอายุใน 3 เดือน</option>
                        <option value="active_latest">ยังไม่หมดอายุ (ล่าสุด → เก่าสุด)</option>
                    </select>
                </label>
            </div>
            @php
                $irbs = $project->irbs->filter(function ($irb) {
                    return !empty(trim($irb->code)) || !empty(trim($irb->source)) || !empty(trim($irb->exp_date));
                });
            @endphp
            @if ($irbs->count())
                <div class="space-y-2" id="irbCards">
                    @foreach ($irbs as $i => $irb)
                        @php
                            $expIso = $irb->exp_date ? \Carbon\Carbon::parse($irb->exp_date)->format('Y-m-d') : '';
                            \Carbon\Carbon::setLocale('th');
                            $date = $irb->exp_date ? \Carbon\Carbon::parse($irb->exp_date) : null;
                            $thaiMonth = $date ? $date->translatedFormat('j F') . ' ' . ($date->year + 543) : '-';
                        @endphp
                        <div class="list-card irb-card" data-exp="{{ $expIso }}">
                            <div class="list-index irb-index">{{ $i + 1 }}</div>
                            <div>
                                <div class="font-semibold">{{ $irb->code ?: '-' }}</div>
                                <div class="list-meta">{{ $irb->source ?: '-' }}</div>
                                <div class="text-sm text-slate-700 mt-1">หมดอายุ: {{ $thaiMonth }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500 text-center">ไม่มีข้อมูล</div>
            @endif
        </div>

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">G: รายชื่อผู้มีส่วนร่วมในการทำงาน</h3>
            @php
                $fteReal = ($project->fteRoles ?? collect())->filter(fn($f) => !empty(trim($f->name)));
            @endphp
            @if ($fteReal->count())
                <div class="space-y-2">
                    @foreach ($fteReal as $i => $fte)
                        @php
                            \Carbon\Carbon::setLocale('th');
                            $start = $fte->start_date ? \Carbon\Carbon::parse($fte->start_date) : null;
                            $end = $fte->end_date ? \Carbon\Carbon::parse($fte->end_date) : null;
                            $startTxt = $start ? $start->translatedFormat('j F') . ' ' . ($start->year + 543) : '-';
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
                        <div class="list-card">
                            <div class="list-index">{{ $i + 1 }}</div>
                            <div>
                                <div class="font-semibold">{{ $fte->name }}</div>
                                <div class="list-meta">({{ $fte->role_group ?: '-' }}) — {{ $jobDetail }}</div>
                                <div class="text-sm text-slate-700 mt-1">ภารกิจ: {{ $taskDetail }}</div>
                                <div class="text-sm text-slate-700 mt-1">ชั่วโมง: {{ $fte->hours ?? '0' }}</div>
                                <div class="text-sm text-blue-700 font-semibold mt-1">FTE: {{ number_format($fte->fte_value, 7) }}</div>
                                <div class="text-sm text-slate-700 mt-1">เริ่ม {{ $startTxt }} • สิ้นสุด {{ $endTxt }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500 text-center">ไม่มีข้อมูลผู้มีส่วนร่วม</div>
            @endif
        </div>

        <div class="mobile-section space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold text-lg text-blue-700">สรุป FTE รายเดือน</h3>
                <a href="{{ route('fte.project.detail', $project) }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ดูเพิ่มเติม
                </a>
            </div>
            @if (!empty($fteChartLabels) && count($fteChartLabels))
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-slate-50 rounded-xl border p-3" style="height:260px;">
                        <canvas id="fteProjectStackedChart"></canvas>
                    </div>
                    <div class="bg-slate-50 rounded-xl border p-3" style="height:260px;">
                        <canvas id="fteProjectYearChart"></canvas>
                    </div>
                </div>
            @else
                <div class="text-gray-500 text-sm">ยังไม่มีข้อมูล FTE สำหรับทำกราฟ</div>
            @endif
        </div>

        @php
            use Illuminate\Support\Str;
            $outputs = $project->outputs ?? collect();
            $outputsReal = $outputs->filter(function ($output) {
                return !empty(trim($output->title ?? '')) || !empty(trim($output->type ?? ''));
            });

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

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">H: ผลงานวิจัย (Outputs)</h3>
            @if ($outputsReal->isEmpty())
                <div class="text-sm text-gray-500 text-center">ไม่มีข้อมูล</div>
            @else
                <div class="space-y-3">
                    @foreach ($outputsReal as $i => $output)
                        @php
                            $isEA101 = Str::startsWith((string) ($output->type ?? ''), 'EA101');
                            $isEA103 = Str::startsWith((string) ($output->type ?? ''), 'EA103');
                            $isEA118 = Str::startsWith((string) ($output->type ?? ''), 'EA118');
                            $isEA119 = Str::startsWith((string) ($output->type ?? ''), 'EA119');
                            $typeBadgeStyle = $outputTypeStyle($output->type ?? '');
                        @endphp
                        <div id="output-{{ $output->id }}" class="output-block">
                            <div class="flex items-start gap-2">
                                <div class="list-index">{{ $i + 1 }}</div>
                                <div class="space-y-2 min-w-0 flex-1">
                                    @if (!empty($output->output_code))
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">
                                            {{ $output->output_code }}
                                        </div>
                                    @endif
                                    <div class="font-semibold leading-snug">{{ $output->title ?: '-' }}</div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        @if (!empty($output->type))
                                            <span class="tag border rounded-lg" style="{{ $typeBadgeStyle }}">
                                                {{ $output->type }}
                                            </span>
                                        @endif
                                        <span class="text-slate-400">|</span>
                                        <span class="text-slate-500">วันที่เผยแพร่</span>
                                        <span class="font-medium text-slate-700">{{ th_date_or_raw($output->year) }}</span>
                                    </div>
                                    <div class="text-sm text-slate-700">
                                        <div class="text-slate-500">รายละเอียด/ลิงก์</div>
                                        <div>{!! linkify_if_url($output->project_output ?? null) !!}</div>
                                    </div>

                                    @if ($isEA101)
                                        <div class="mobile-card bg-blue-50/45">
                                            <div class="space-y-3">
                                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Journal</div>
                                                    <div class="mt-2 font-semibold leading-6">{{ $output->journal ?: '-' }}</div>
                                                </div>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Quartile</div>
                                                        <div class="mt-2 font-semibold leading-6">{{ $output->quartile ?: '-' }}</div>
                                                    </div>
                                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Impact Factor</div>
                                                        <div class="mt-2 font-semibold leading-6">{{ $output->impact_factor !== null && $output->impact_factor !== '' ? $output->impact_factor : '-' }}</div>
                                                    </div>
                                                </div>

                                                @php
                                                    $authors = $output->authors ?? collect();
                                                    $authorsReal = $authors->filter(function ($a) {
                                                        return !empty(trim($a->name ?? '')) || !empty(trim($a->role ?? ''));
                                                    });
                                                @endphp
                                                <div>
                                                    <div class="font-semibold text-blue-800 text-sm">รายชื่อผู้เขียน (Authors)</div>
                                                    <div class="mt-1 text-[11px] text-slate-500">{{ $authorsReal->count() ?: 0 }} คน</div>
                                                </div>
                                                @if ($authorsReal->isEmpty())
                                                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-gray-500">- ไม่มีรายชื่อผู้เขียน -</div>
                                                @else
                                                    <div class="space-y-3">
                                                        @foreach ($authorsReal as $ai => $a)
                                                            @php
                                                                $role = strtolower(trim($a->role ?? ''));
                                                                $roleLabel =
                                                                    $role === 'first'
                                                                        ? 'First-author'
                                                                        : ($role === 'co'
                                                                            ? 'Co-author'
                                                                            : ($role === 'corresponding'
                                                                                ? 'Corresponding author'
                                                                                : ($a->role ?: '-')));
                                                            @endphp
                                                            <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-blue-700 ring-1 ring-slate-200">
                                                                    {{ $ai + 1 }}
                                                                </div>
                                                                <div class="min-w-0">
                                                                    <div class="break-words font-semibold text-slate-800">{{ $a->name ?: '-' }}</div>
                                                                    <div class="mt-1 text-sm text-slate-600">{{ $roleLabel }}</div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($isEA118)
                                        @php
                                            $awardees = ($output->awardRecipients ?? collect())->filter(function ($a) {
                                                return !empty(trim($a->name ?? ''));
                                            });
                                        @endphp
                                        <div class="mobile-card bg-violet-50/45">
                                            <div>
                                                <div class="font-semibold text-violet-800 text-sm">ผู้ได้รับรางวัล (Award Recipients)</div>
                                                <div class="mt-1 text-[11px] text-slate-500">{{ $awardees->count() ?: 0 }} รายการ</div>
                                            </div>
                                            @if ($awardees->isEmpty())
                                                <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-gray-500">- ไม่มีรายชื่อผู้ได้รับรางวัล -</div>
                                            @else
                                                <div class="mt-3 space-y-3">
                                                    @foreach ($awardees as $ai => $awardee)
                                                        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-violet-700 ring-1 ring-slate-200">
                                                                {{ $ai + 1 }}
                                                            </div>
                                                            <div class="min-w-0 break-words font-semibold text-slate-800">{{ $awardee->name }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($isEA103)
                                        @php
                                            $confs = $output->conferences ?? collect();
                                            $confsReal = $confs->filter(function ($c) {
                                                return !empty(trim($c->name ?? '')) ||
                                                    !empty(trim($c->presentation_type ?? '')) ||
                                                    !empty(trim($c->location ?? '')) ||
                                                    !empty(trim($c->presenterDisplay() ?? ''));
                                            });
                                        @endphp
                                        <div class="mobile-card bg-amber-50/45">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-800">
                                                    Conference Presentation
                                                </div>
                                                <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                                    {{ $confsReal->count() ?: 0 }} รายการ
                                                </div>
                                            </div>
                                            @if ($confsReal->isEmpty())
                                                <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-gray-500">- ไม่มีรายการ Conference -</div>
                                            @else
                                                <div class="mt-3 space-y-3">
                                                    @foreach ($confsReal as $ci => $c)
                                                        <div id="output-{{ $output->id }}-conference-{{ $c->id }}" class="rounded-xl border border-amber-100 bg-white px-4 py-4">
                                                            <div class="flex items-start gap-3">
                                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-xs font-bold text-amber-700 ring-1 ring-amber-100">
                                                                    {{ $ci + 1 }}
                                                                </div>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="font-semibold leading-6 text-slate-800">{{ $c->name ?: '-' }}</div>
                                                                    <div class="mt-3 space-y-2">
                                                                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                                            <span class="font-medium text-slate-500">ประเภทนำเสนอ:</span> {{ $c->presentation_type ?: '-' }}
                                                                        </div>
                                                                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                                            <span class="font-medium text-slate-500">สถานที่:</span> {{ $c->location ?: '-' }}
                                                                        </div>
                                                                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                                            <span class="font-medium text-slate-500">ผู้นำเสนอ:</span> {{ $c->presenterDisplay() ?: '-' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
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
                                        <div class="mobile-card overflow-hidden bg-emerald-50/45 p-0">
                                            <div class="border-b border-emerald-100 bg-white px-4 py-4">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">
                                                        หนังสือ / บทในหนังสือ
                                                    </div>
                                                    <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                                        ผู้เขียน {{ $bookAuthorsReal->count() ?: 0 }} คน
                                                    </div>
                                                </div>

                                                <div class="mt-4 rounded-2xl bg-emerald-50/70 px-4 py-4">
                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">ชื่อหนังสือ</div>
                                                    <div class="mt-2 text-base font-bold leading-6 text-slate-900">{{ $output->book_title ?: 'ไม่ระบุชื่อหนังสือ' }}</div>
                                                </div>
                                            </div>

                                            <div class="space-y-3 px-4 py-4">
                                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ชื่อสำนักพิมพ์</div>
                                                    <div class="mt-2 font-semibold leading-6">{{ $output->publisher_name ?: '-' }}</div>
                                                </div>
                                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ชื่อบท / เลขหน้า</div>
                                                    <div class="mt-2 font-semibold leading-6">{{ $output->chapter_pages ?: '-' }}</div>
                                                </div>
                                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">เลข ISBN</div>
                                                    <div class="mt-2 font-semibold tracking-wide">{{ $output->isbn ?: '-' }}</div>
                                                </div>
                                            </div>

                                            <div class="border-t border-emerald-100 bg-white px-4 py-4">
                                                <div>
                                                    <div class="text-sm font-semibold text-emerald-800">รายชื่อผู้เขียน</div>
                                                    <div class="mt-1 text-[11px] text-slate-500">{{ $bookAuthorsReal->count() ?: 0 }} คน</div>
                                                </div>
                                            @if ($bookAuthorsReal->isEmpty())
                                                    <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-gray-500">- ไม่มีรายชื่อผู้เขียน -</div>
                                            @else
                                                    <div class="mt-3 space-y-3">
                                                    @foreach ($bookAuthorsReal as $ai => $a)
                                                            <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-emerald-700 ring-1 ring-slate-200">
                                                                    {{ $ai + 1 }}
                                                                </div>
                                                                <div class="min-w-0">
                                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Author {{ $ai + 1 }}</div>
                                                                    <div class="mt-1 break-words font-semibold leading-6 text-slate-800">{{ $a->name ?: '-' }}</div>
                                                                </div>
                                                            </div>
                                                    @endforeach
                                                </div>
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

        @include('projects.partials.progress_section_mobile', ['project' => $project])

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">J: ยุทธศาสตร์ Siriraj Health Policy</h3>
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
            <div class="space-y-3">
                @foreach ($strategies as $sKey => $strategy)
                    <div class="mobile-card space-y-2">
                        <div class="font-semibold text-slate-800 text-sm">{{ $strategy['label'] }}</div>
                        <div class="space-y-2">
                            @foreach ($strategy['items'] as $iKey => $desc)
                                @php $field = "strategy_{$sKey}_{$iKey}"; @endphp
                                <div class="list-card">
                                    <div class="list-index">{{ $iKey }}</div>
                                    <div class="text-sm text-slate-700">{{ $desc }}</div>
                                    @if (!empty($project->$field))
                                        <div class="tag bg-emerald-100 text-emerald-800">✓ </div>
                                    @else <div class="tag bg-rose-100 text-rose-800">✗ </div>
                                    @endif
                                   
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @php
            $updatedBy = '-';
            $updatedAt = '';
            $createdBy = '-';
            $createdAt = '';

            $upd = $project->updatedBy ?? null;
            $creator = $project->createdBy ?? null;

            $updatedBy = $upd ? trim(($upd->prefix ?? '') . ' ' . $upd->name) : '-';
            $createdBy = $creator ? trim(($creator->prefix ?? '') . ' ' . $creator->name) : '-';

            if ($project->updated_at) {
                $d = \Carbon\Carbon::parse($project->updated_at)->setTimezone('Asia/Bangkok')->locale('th');
                $updatedAt = $d->translatedFormat('j F') . ' ' . ($d->year + 543) . ' ' . $d->format('H:i') . ' น.';
            }
            if ($project->created_at) {
                $d = \Carbon\Carbon::parse($project->created_at)->setTimezone('Asia/Bangkok')->locale('th');
                $createdAt = $d->translatedFormat('j F') . ' ' . ($d->year + 543) . ' ' . $d->format('H:i') . ' น.';
            }
        @endphp

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

    <script>
        (function() {
            const filter = document.getElementById('irbFilter');
            if (!filter) return;
            const cards = Array.from(document.querySelectorAll('.irb-card'));
            const today = new Date();

            function parseDate(s) {
                if (!s) return null;
                const d = new Date(s);
                return isNaN(d) ? null : d;
            }

            function updateNumbers(visibleCards) {
                visibleCards.forEach((card, idx) => {
                    const cell = card.querySelector('.irb-index');
                    if (cell) cell.textContent = idx + 1;
                });
            }

            function applyFilter() {
                const mode = filter.value;
                let filtered = cards;

                if (mode === 'exp_1m' || mode === 'exp_3m') {
                    const maxDays = mode === 'exp_1m' ? 30 : 90;
                    filtered = cards.filter(card => {
                        const d = parseDate(card.dataset.exp);
                        if (!d) return false;
                        const diff = (d - today) / (1000 * 60 * 60 * 24);
                        return diff >= 0 && diff <= maxDays;
                    });
                }

                if (mode === 'active_latest') {
                    filtered = cards.filter(card => {
                        const d = parseDate(card.dataset.exp);
                        return d && d >= today;
                    }).sort((a, b) => {
                        const da = parseDate(a.dataset.exp) || new Date(0);
                        const db = parseDate(b.dataset.exp) || new Date(0);
                        return db - da;
                    });
                }

                cards.forEach(card => card.classList.add('hidden'));
                filtered.forEach(card => card.classList.remove('hidden'));
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
                headerOutOfView = window.scrollY > 180;
            }

            const onScroll = () => {
                if (!('IntersectionObserver' in window)) {
                    headerOutOfView = window.scrollY > 180;
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
