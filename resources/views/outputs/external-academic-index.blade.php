@extends('layouts.app')

@php
    $typeBaseColorMap = [
        'EA101' => '#7aa2f7',
        'EA103' => '#f7b267',
        'EA118' => '#a78bfa',
        'EA119' => '#f4b183',
        'EA120' => '#e8a5a5',
    ];
    $withAlpha = static function (string $hex, string $alpha): string {
        $base = ltrim(trim($hex), '#');
        if (strlen($base) !== 6) {
            return '#cbd5e1';
        }
        return '#' . $base . $alpha;
    };
    $typeBadge = function ($type) use ($typeBaseColorMap, $withAlpha) {
        $baseColor = $typeBaseColorMap[$type] ?? '#94a3b8';
        return sprintf(
            'background-color:%s; border-color:%s; color:%s;',
            $withAlpha($baseColor, '1A'),
            $withAlpha($baseColor, '66'),
            $baseColor
        );
    };
    $typeLabel = fn ($type) => match ($type) {
        'EA101' => 'Academic Paper',
        'EA103' => 'Presentation',
        'EA118' => 'Award and Recognition',
        'EA119' => 'Textbook / Book chapter',
        'EA120' => 'Manual / Instruction / Guideline',
        default => $type ?: '-',
    };
    $thaiDate = function ($date) {
        if (!$date) return '-';
        $months = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        $day = $date->day;
        $month = $months[$date->month] ?? $date->format('m');
        $year = $date->year + 543;
        return "{$day} {$month} {$year}";
    };
    $hasValue = static function ($value): bool {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return count($value) > 0;
        }
        return true;
    };
@endphp

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container mx-auto mt-8 space-y-4 px-4 pb-8">
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-4 px-5 py-5 md:px-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 md:text-2xl">Academic Paper / Presentation / Textbook / Manual นอกเหนือโครงการวิจัยในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</h1>
                    <p class="mt-1.5 text-sm leading-6 text-slate-600">
                        รายการผลงานเพิ่มเติมแบบไม่ผูกกับโครงการ พร้อมค้นหา เปิดดูรายละเอียด และย้อนกลับมาเพิ่มข้อมูลต่อได้ใน flow เดียว
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.external-academic.create') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm">
                        เพิ่มรายการใหม่
                    </a>
                </div>
            </div>
        </section>

        <section id="external-output-stats" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">ทั้งหมด</div>
                <div class="mt-1.5 text-2xl font-bold text-slate-900">{{ number_format($allCount) }}</div>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Academic Paper</div>
                <div class="mt-1.5 text-2xl font-bold" style="color:#7aa2f7;">{{ number_format($paperCount) }}</div>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Presentation</div>
                <div class="mt-1.5 text-2xl font-bold" style="color:#f7b267;">{{ number_format($presentationCount) }}</div>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Award and Recognition</div>
                <div class="mt-1.5 text-2xl font-bold" style="color:#a78bfa;">{{ number_format($awardCount) }}</div>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Textbook / Book chapter</div>
                <div class="mt-1.5 text-2xl font-bold" style="color:#f4b183;">{{ number_format($textbookCount) }}</div>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Manual / Instruction / Guideline</div>
                <div class="mt-1.5 text-2xl font-bold" style="color:#e8a5a5;">{{ number_format($manualCount) }}</div>
            </article>
        </section>

        <form id="external-output-filter-form" method="GET" action="{{ route('outputs.external-academic') }}"
            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input type="text" name="search" value="{{ $search }}" data-external-live-search="true"
                        class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"
                        placeholder="Extra ID / ชื่อผลงาน / journal / author / presenter / conference">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภท</span>
                    <select name="type" data-external-auto-search="true" class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="EA101" @selected($type === 'EA101')>Academic Paper</option>
                        <option value="EA103" @selected($type === 'EA103')>Presentation</option>
                        <option value="EA118" @selected($type === 'EA118')>Award and Recognition</option>
                        <option value="EA119" @selected($type === 'EA119')>Textbook / Book chapter</option>
                        <option value="EA120" @selected($type === 'EA120')>Manual / Instruction / Guideline</option>
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                        ค้นหา
                    </button>
                    <a href="{{ route('outputs.external-academic') }}"
                        data-external-reset="true"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700">
                        ล้าง
                    </a>
                </div>
            </div>
        </form>

        <div id="external-output-loading" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 animate-spin rounded-full border-2 border-indigo-200 border-t-indigo-600"></span>
            กำลังอัปเดตรายการ...
        </div>

        <section id="external-output-list" class="space-y-4">
            @forelse ($outputs as $output)
                @php
                    $extraCode = trim((string) ($output->output_code ?? '')) ?: \App\Models\ExternalOutput::formatOutputCode((int) $output->id);
                    $creatorName = trim(((optional($output->creator)->prefix ?? '') . ' ' . (optional($output->creator)->name ?? '')));
                    $summaryItems = collect([
                        ['label' => 'Theme', 'value' => $output->job_category],
                        ['label' => 'Journal', 'value' => $output->type === 'EA101' ? $output->journal : null],
                        [
                            'label' => 'Author',
                            'value' => in_array($output->type, ['EA101', 'EA119'], true)
                                ? ($output->type === 'EA119'
                                    ? $output->bookAuthors->pluck('name')->filter()->implode(', ')
                                    : $output->authors->pluck('name')->filter()->implode(', '))
                                : null,
                        ],
                        [
                            'label' => 'Award Recipient',
                            'value' => $output->type === 'EA118'
                                ? $output->awardRecipients->pluck('name')->filter()->implode(', ')
                                : null,
                        ],
                        [
                            'label' => 'Ranking / IF',
                            'value' => $output->type === 'EA101' && ($hasValue($output->quartile) || $hasValue($output->impact_factor))
                                ? collect([$output->quartile, $output->impact_factor])->filter($hasValue(...))->implode(' / ')
                                : null,
                        ],
                        [
                            'label' => 'Conference',
                            'value' => $output->type === 'EA103'
                                ? $output->conferences->pluck('name')->filter()->implode(', ')
                                : null,
                        ],
                        [
                            'label' => 'Presentation Type',
                            'value' => $output->type === 'EA103'
                                ? $output->conferences->pluck('presentation_type')->filter()->implode(', ')
                                : null,
                        ],
                        [
                            'label' => 'Presenter',
                            'value' => $output->type === 'EA103'
                                ? $output->conferences->map(fn ($conference) => $conference->presenterDisplay())->filter()->implode(', ')
                                : null,
                        ],
                        [
                            'label' => 'Location',
                            'value' => $output->type === 'EA103'
                                ? $output->conferences->pluck('location')->filter()->implode(', ')
                                : null,
                        ],
                        ['label' => 'ชื่อหนังสือ', 'value' => $output->type === 'EA119' ? $output->book_title : null],
                        ['label' => 'ชื่อสำนักพิมพ์', 'value' => $output->type === 'EA119' ? $output->publisher_name : null],
                        ['label' => 'ชื่อบท / เลขหน้า', 'value' => $output->type === 'EA119' ? $output->chapter_pages : null],
                        ['label' => 'เลข ISBN', 'value' => $output->type === 'EA119' ? $output->isbn : null],
                        ['label' => 'บันทึกโดย', 'value' => $creatorName ?: null],
                    ])->filter(fn ($item) => $hasValue($item['value'] ?? null))->values();
                    $moveSummary = [
                        'type' => $typeLabel($output->type),
                        'title' => $output->title,
                        'publish_date' => $output->publish_date ? $thaiDate($output->publish_date) : null,
                        'note' => $output->note,
                        'fields' => $summaryItems->map(fn ($item) => [
                            'label' => $item['label'],
                            'value' => $item['value'],
                        ])->values()->all(),
                    ];
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex items-baseline gap-2 text-sm text-slate-900">
                                <span class="font-semibold uppercase tracking-wide">Extra ID</span>
                                <span class="font-mono text-base font-bold">{{ $extraCode }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold" style="{{ $typeBadge($output->type) }}">
                                    {{ $typeLabel($output->type) }}
                                </span>
                                @if ($output->publish_date)
                                    <span class="text-[11px] font-medium text-slate-500">เผยแพร่ {{ $thaiDate($output->publish_date) }}</span>
                                @endif
                            </div>
                            <h2 class="mt-2 text-base font-bold leading-6 text-slate-900">{{ $output->title }}</h2>

                            @if ($output->note)
                                <p class="mt-1.5 line-clamp-2 text-sm leading-6 text-slate-600">{{ $output->note }}</p>
                            @endif

                            @if ($summaryItems->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($summaryItems as $item)
                                        <div class="min-w-[180px] flex-1 rounded-lg border border-slate-200 bg-slate-50/70 px-3 py-2">
                                            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</div>
                                            <div class="mt-0.5 text-sm font-medium leading-5 text-slate-800">{{ $item['value'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-start">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('outputs.external-academic.show', $output) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    ดูรายละเอียด
                                </a>
                                <a href="{{ route('outputs.external-academic.edit', $output) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    แก้ไข
                                </a>
                                <button type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-100"
                                    data-open-move-modal="true"
                                    data-move-action="{{ route('outputs.external-academic.move-to-project', $output) }}"
                                    data-output-base64="{{ base64_encode(json_encode($moveSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}">
                                    ย้าย output ไปโครงการ
                                </button>
                                <form method="POST" action="{{ route('outputs.external-academic.destroy', $output) }}"
                                    onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                                        ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center shadow-sm">
                    <div class="text-lg font-semibold text-slate-800">ยังไม่มีรายการ</div>
                    <p class="mt-2 text-sm text-slate-500">เริ่มต้นบันทึกรายการเพิ่มเติมได้จากปุ่มเพิ่มรายการใหม่</p>
                </div>
            @endforelse
        </section>

        <div id="external-output-pagination">
            @if ($outputs->hasPages())
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                {{ $outputs->links() }}
            </section>
            @endif
        </div>
    </div>

    <form id="moveExternalOutputForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="project_id" id="moveExternalOutputProjectId">
    </form>

    <script id="moveProjectOptionsData" type="application/json">
        @php
            echo json_encode(
                $projectOptions->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'project_code' => $project->project_code,
                        'project_name_th' => $project->project_name_th,
                        'search_text' => mb_strtolower(trim(implode(' ', array_filter([$project->id, $project->project_code, $project->project_name_th])))),
                    ];
                })->values(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        @endphp
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = "{{ route('outputs.external-academic', [], false) }}";
            let externalSearchTimer = null;
            let externalRequestController = null;
            let externalRequestToken = 0;

            const externalQuery = (selector, scope = document) => scope.querySelector(selector);
            const externalQueryAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
            const externalForm = externalQuery('#external-output-filter-form');
            const externalLoading = externalQuery('#external-output-loading');
            const buildExternalUrl = (resetPage = true) => {
                const url = new URL(externalForm.action, window.location.origin);
                const formData = new FormData(externalForm);

                for (const [key, value] of formData.entries()) {
                    if (value === null || value === '') continue;
                    url.searchParams.set(key, value);
                }

                if (resetPage) {
                    url.searchParams.set('page', '1');
                }

                return url;
            };
            const setExternalLoading = (loading) => {
                externalLoading?.classList.toggle('hidden', !loading);
                externalLoading?.classList.toggle('flex', loading);
                externalQuery('#external-output-list')?.classList.toggle('opacity-60', loading);
            };
            const replaceExternalSection = (nextDoc, selector) => {
                const current = externalQuery(selector);
                const next = nextDoc.querySelector(selector);
                if (!current || !next) return false;
                current.innerHTML = next.innerHTML;
                return true;
            };
            const fetchExternalResults = async (url, options = {}) => {
                const { updateUrl = true } = options;
                const token = ++externalRequestToken;
                externalRequestController?.abort();
                externalRequestController = new AbortController();
                setExternalLoading(true);

                try {
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: externalRequestController.signal,
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const html = await response.text();
                    if (token !== externalRequestToken) return;

                    const nextDoc = new DOMParser().parseFromString(html, 'text/html');
                    const replaced = [
                        replaceExternalSection(nextDoc, '#external-output-stats'),
                        replaceExternalSection(nextDoc, '#external-output-list'),
                        replaceExternalSection(nextDoc, '#external-output-pagination'),
                    ].every(Boolean);

                    if (!replaced) {
                        throw new Error('Cannot replace external output sections');
                    }

                    if (updateUrl) {
                        history.replaceState({ ajax: true }, '', `${url.pathname}${url.search}`);
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    if (token === externalRequestToken) {
                        setExternalLoading(false);
                    }
                }
            };
            const scheduleExternalSearch = () => {
                clearTimeout(externalSearchTimer);
                externalSearchTimer = setTimeout(() => {
                    fetchExternalResults(buildExternalUrl(true), { updateUrl: true });
                }, 300);
            };

            if (externalForm) {
                externalForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    fetchExternalResults(buildExternalUrl(true), { updateUrl: true });
                });

                externalQueryAll('[data-external-live-search="true"]', externalForm).forEach((field) => {
                    let isComposing = false;
                    field.addEventListener('compositionstart', () => {
                        isComposing = true;
                    });
                    field.addEventListener('compositionend', () => {
                        isComposing = false;
                        scheduleExternalSearch();
                    });
                    field.addEventListener('input', () => {
                        if (!isComposing) {
                            scheduleExternalSearch();
                        }
                    });
                });

                externalQueryAll('[data-external-auto-search="true"]', externalForm).forEach((field) => {
                    field.addEventListener('change', () => {
                        clearTimeout(externalSearchTimer);
                        fetchExternalResults(buildExternalUrl(true), { updateUrl: true });
                    });
                });
            }

            const moveForm = document.getElementById('moveExternalOutputForm');
            const moveProjectIdInput = document.getElementById('moveExternalOutputProjectId');
            const projectOptionsData = document.getElementById('moveProjectOptionsData');
            const projectOptions = projectOptionsData ? JSON.parse(projectOptionsData.textContent || '[]') : [];
            const decodeBase64Json = (encoded) => {
                if (!encoded) {
                    return {};
                }

                const binary = atob(encoded);

                if (typeof TextDecoder !== 'undefined') {
                    const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
                    return JSON.parse(new TextDecoder('utf-8').decode(bytes));
                }

                return JSON.parse(decodeURIComponent(escape(binary)));
            };

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const renderProjectOptions = (items) => {
                if (!items.length) {
                    return '<div style="padding:12px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;">ไม่พบโครงการ</div>';
                }

                return items.map((project) => `
                    <button type="button"
                        class="move-project-choice"
                        data-project-id="${escapeHtml(project.id)}"
                        data-project-code="${escapeHtml(project.project_code || '')}"
                        data-project-name="${escapeHtml(project.project_name_th || '')}"
                        style="width:100%;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;text-align:left;">
                        <span>
                            <span style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;">Project #${escapeHtml(project.id)}</span>
                            <span style="display:block;margin-top:4px;font-size:14px;font-weight:700;color:#0f172a;">${escapeHtml(project.project_name_th || '-')}</span>
                            ${project.project_code ? `<span style="display:block;margin-top:4px;font-size:12px;color:#64748b;">${escapeHtml(project.project_code)}</span>` : ''}
                        </span>
                        <span style="display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;flex:0 0 auto;min-width:56px;border-radius:10px;background:#e0e7ff;padding:4px 10px;font-size:12px;font-weight:700;color:#4338ca;">เลือก</span>
                    </button>
                `).join('');
            };

            const openProjectPicker = async (action, output) => {
                let filteredProjects = [...projectOptions];

                const picker = await Swal.fire({
                    title: 'เลือกโครงการปลายทาง',
                    width: 860,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'ปิด',
                    html: `
                        <div style="display:grid;gap:12px;text-align:left;">
                            <div style="font-size:13px;color:#475569;">เลือกโครงการจากรายการด้านล่าง แล้วระบบจะขอให้ยืนยันอีกครั้งก่อนย้าย</div>
                            <input id="swalMoveProjectSearch" type="text" placeholder="ค้นหาจากรหัสโครงการ / ชื่อโครงการ"
                                style="width:100%;border:1px solid #cbd5e1;border-radius:12px;padding:10px 12px;font-size:14px;">
                            <div id="swalMoveProjectList" style="max-height:50vh;overflow-y:auto;display:grid;gap:10px;">
                                ${renderProjectOptions(filteredProjects)}
                            </div>
                        </div>
                    `,
                    didOpen: () => {
                        const popup = Swal.getPopup();
                        const searchInput = popup?.querySelector('#swalMoveProjectSearch');
                        const listWrap = popup?.querySelector('#swalMoveProjectList');
                        const bindProjectButtons = () => {
                            listWrap?.querySelectorAll('.move-project-choice').forEach((button) => {
                                button.addEventListener('click', () => {
                                    Swal.close();
                                    confirmProjectMove(
                                        action,
                                        output,
                                        {
                                            id: button.getAttribute('data-project-id') || '',
                                            project_code: button.getAttribute('data-project-code') || '',
                                            project_name_th: button.getAttribute('data-project-name') || '',
                                        }
                                    );
                                });
                            });
                        };

                        bindProjectButtons();

                        searchInput?.addEventListener('input', () => {
                            const term = (searchInput.value || '').trim().toLowerCase();
                            filteredProjects = projectOptions.filter((project) => {
                                return term === '' || String(project.search_text || '').includes(term);
                            });
                            if (listWrap) {
                                listWrap.innerHTML = renderProjectOptions(filteredProjects);
                                bindProjectButtons();
                            }
                        });
                    },
                });

                return picker;
            };

            const confirmProjectMove = async (action, activeOutput, project) => {
                const fields = Array.isArray(activeOutput.fields) ? activeOutput.fields : [];
                const detailHtml = fields.map((field) => `
                    <div style="padding:10px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;text-align:left;">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;">${escapeHtml(field.label)}</div>
                        <div style="margin-top:4px;font-size:13px;line-height:1.55;color:#0f172a;word-break:break-word;">${escapeHtml(field.value || '-')}</div>
                    </div>
                `).join('');

                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'ย้าย output ไปยังโครงการนี้?',
                    width: 760,
                    html: `
                        <div style="display:grid;gap:12px;text-align:left;">
                            <div style="padding:12px 14px;border:1px solid #fde68a;border-radius:14px;background:#fffbeb;">
                                <div style="font-size:12px;font-weight:700;color:#92400e;">โครงการปลายทาง</div>
                                <div style="margin-top:4px;font-size:14px;font-weight:700;color:#111827;">Project #${escapeHtml(project.id)} ${escapeHtml(project.project_name_th)}</div>
                                ${project.project_code ? `<div style="margin-top:4px;font-size:12px;color:#475569;">${escapeHtml(project.project_code)}</div>` : ''}
                            </div>
                            <div style="padding:12px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#ffffff;">
                                <div style="font-size:12px;font-weight:700;color:#475569;">ข้อมูลผลงานที่จะย้าย</div>
                                <div style="margin-top:4px;font-size:14px;font-weight:700;color:#111827;">${escapeHtml(activeOutput.title || '-')}</div>
                                <div style="margin-top:6px;font-size:12px;color:#475569;">ประเภท: ${escapeHtml(activeOutput.type || '-')}</div>
                                ${activeOutput.publish_date ? `<div style="margin-top:4px;font-size:12px;color:#475569;">เผยแพร่: ${escapeHtml(activeOutput.publish_date)}</div>` : ''}
                                ${activeOutput.note ? `<div style="margin-top:8px;font-size:12px;line-height:1.6;color:#334155;word-break:break-word;">${escapeHtml(activeOutput.note)}</div>` : ''}
                            </div>
                            ${detailHtml ? `<div style="display:grid;gap:10px;">${detailHtml}</div>` : ''}
                            <div style="font-size:12px;font-weight:700;color:#b91c1c;">* ย้ายแล้วไม่สามารถย้อนกลับได้</div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันการย้าย',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                });

                if (!result.isConfirmed) {
                    return;
                }

                moveForm.setAttribute('action', action);
                moveProjectIdInput.value = project.id;
                moveForm.submit();
            };

            document.addEventListener('click', async (event) => {
                const resetLink = event.target.closest('[data-external-reset="true"]');
                if (resetLink) {
                    event.preventDefault();
                    if (externalForm) {
                        externalQueryAll('input[name="search"], select[name="type"]', externalForm).forEach((field) => {
                            field.value = '';
                        });
                    }
                    fetchExternalResults(new URL(resetLink.href, window.location.origin), { updateUrl: true });
                    return;
                }

                const paginationLink = event.target.closest('#external-output-pagination a[href]');
                if (paginationLink) {
                    const url = new URL(paginationLink.href, window.location.origin);
                    if (url.pathname === new URL(currentPath, window.location.origin).pathname) {
                        event.preventDefault();
                        fetchExternalResults(url, { updateUrl: true });
                        return;
                    }
                }

                const openButton = event.target.closest('[data-open-move-modal="true"]');
                if (openButton) {
                    const encoded = openButton.getAttribute('data-output-base64') || '';
                    let output = {};
                    try {
                        output = decodeBase64Json(encoded);
                    } catch (error) {
                        console.error('Cannot parse move output payload', error);
                        await Swal.fire({
                            icon: 'error',
                            title: 'เปิดข้อมูลสำหรับย้ายไม่สำเร็จ',
                            text: 'กรุณารีเฟรชหน้าแล้วลองอีกครั้ง',
                            confirmButtonText: 'ตกลง',
                        });
                        return;
                    }
                    await openProjectPicker(openButton.getAttribute('data-move-action') || '', output);
                    return;
                }
            });

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname === new URL(currentPath, window.location.origin).pathname) {
                    fetchExternalResults(url, { updateUrl: false });
                }
            });
        });
    </script>
@endsection
