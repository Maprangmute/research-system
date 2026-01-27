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
        }
    </style>

    <div class="mobile-detail max-w-xl mx-auto px-4 py-4 space-y-4">
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

        <div class="mobile-card space-y-3">
            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                ← กลับไปยังรายการโครงการ
            </a>
            <h2 class="text-xl font-semibold text-slate-900">{{ $project->project_name_th ?? '-' }}</h2>
            <span class="tag {{ $bgClass }} border border-slate-300 text-slate-900">
                สถานะ: {{ $statusText }}
            </span>
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
                <button type="button" id="deleteProjectBtn"
                    class="px-3 py-2 rounded-lg bg-rose-100 text-rose-800 text-sm font-semibold hover:bg-rose-200">
                    🗑️ ลบ
                </button>
            </div>
            <form id="delete-project-{{ $project->id }}"
                action="{{ route('projects.destroy', $project->id) }}" method="POST" class="hidden">
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
                <div class="field-label">ประเภท</div>
                <div class="field-value">{{ $project->project_type ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">ลักษณะ</div>
                <div class="field-value">{{ $project->project_list ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">Theme</div>
                <div class="field-value">{{ $project->theme ?? '-' }}</div>
            </div>
            <div class="field-row">
                <div class="field-label">Shortcut</div>
                <div class="field-value">
                    @if ($project->shortcut_link)
                        <a href="{{ $project->shortcut_link }}" class="text-blue-600 underline" target="_blank"
                            rel="noopener">
                            {{ $project->shortcut_link }}
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
                <div class="field-label">BC103 สิ้นสุด</div>
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

        <div class="mobile-section space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold text-lg text-blue-700">E: รหัส IRB</h3>
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
            <h3 class="font-semibold text-lg text-blue-700">F: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
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
                <a href="{{ route('fte.project.detail', $project->id) }}"
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
        @endphp

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">G: ผลงานวิจัย (Outputs)</h3>
            @if ($outputsReal->isEmpty())
                <div class="text-sm text-gray-500 text-center">ไม่มีข้อมูล</div>
            @else
                <div class="space-y-3">
                    @foreach ($outputsReal as $i => $output)
                        @php
                            $isEA101 = Str::startsWith((string) ($output->type ?? ''), 'EA101');
                            $isEA103 = Str::startsWith((string) ($output->type ?? ''), 'EA103');
                        @endphp
                        <div class="output-block">
                            <div class="flex items-start gap-2">
                                <div class="list-index">{{ $i + 1 }}</div>
                                <div class="space-y-2">
                                    <div class="font-semibold">{{ $output->title ?: '-' }}</div>
                                    @if (!empty($output->type))
                                        <span
                                            class="tag {{ $isEA101 ? 'bg-blue-100 text-blue-800' : ($isEA103 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                                            {{ $output->type }}
                                        </span>
                                    @endif
                                    <div class="text-sm text-slate-700">
                                        รายละเอียด/ลิงก์: {!! linkify_if_url($output->project_output ?? null) !!}
                                    </div>
                                    <div class="text-sm text-slate-700">วันที่เผยแพร่: {{ th_date_or_raw($output->year) }}</div>

                                    @if ($isEA101)
                                        <div class="mobile-card space-y-2 bg-blue-50/60">
                                            <div class="text-sm text-slate-700">Journal: {{ $output->journal ?: '-' }}</div>
                                            <div class="text-sm text-slate-700">Quartile: {{ $output->quartile ?: '-' }}</div>
                                            <div class="text-sm text-slate-700">Impact Factor: {{ $output->impact_factor !== null && $output->impact_factor !== '' ? $output->impact_factor : '-' }}</div>

                                            @php
                                                $authors = $output->authors ?? collect();
                                                $authorsReal = $authors->filter(function ($a) {
                                                    return !empty(trim($a->name ?? '')) || !empty(trim($a->role ?? ''));
                                                });
                                            @endphp
                                            <div class="font-semibold text-blue-800 text-sm">รายชื่อผู้เขียน (Authors)</div>
                                            @if ($authorsReal->isEmpty())
                                                <div class="text-sm text-gray-500">- ไม่มีรายชื่อผู้เขียน -</div>
                                            @else
                                                <div class="sub-list">
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
                                                        <div class="sub-card">
                                                            <div class="text-xs text-slate-500">#{{ $ai + 1 }}</div>
                                                            <div class="font-semibold">{{ $a->name ?: '-' }}</div>
                                                            <div class="text-sm text-slate-700">{{ $roleLabel }}</div>
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
                                                    !empty(trim($c->note ?? ''));
                                            });
                                        @endphp
                                        <div class="mobile-card space-y-2 bg-amber-50/60">
                                            <div class="font-semibold text-amber-800 text-sm">Conferences</div>
                                            @if ($confsReal->isEmpty())
                                                <div class="text-sm text-gray-500">- ไม่มีรายการ Conference -</div>
                                            @else
                                                <div class="sub-list">
                                                    @foreach ($confsReal as $ci => $c)
                                                        <div class="sub-card">
                                                            <div class="text-xs text-slate-500">#{{ $ci + 1 }}</div>
                                                            <div class="font-semibold">{{ $c->name ?: '-' }}</div>
                                                            <div class="text-sm text-slate-700">ประเภท: {{ $c->presentation_type ?: '-' }}</div>
                                                            <div class="text-sm text-slate-700">สถานที่: {{ $c->location ?: '-' }}</div>
                                                            <div class="text-sm text-slate-700">หมายเหตุ: {{ $c->note ?: '-' }}</div>
                                                        </div>
                                                    @endforeach
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

        <div class="mobile-section space-y-3">
            <h3 class="font-semibold text-lg text-blue-700">H: ยุทธศาสตร์ Siriraj Health Policy</h3>
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
    </script>
@endsection
