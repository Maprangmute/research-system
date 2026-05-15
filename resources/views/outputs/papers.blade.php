@extends('layouts.app')

@section('content')
    @php
        $currentSort = $currentSort ?? request('sort', 'created_at');
        $currentDir = $currentDir ?? request('dir', 'desc');
        $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
        $sortUrl = fn($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
        $sortIcon = fn($field) => $currentSort !== $field ? '↕' : ($currentDir === 'asc' ? '↑' : '↓');
        $impactFactorMax = $impactFactorMax ?? 10;
        $selectedImpactMax = $selectedImpactMax ?? $impactFactorMax;
    @endphp

    <style>
        .page-hero {
            position: relative;
            overflow: hidden;
        }
        .page-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 88% 18%, rgba(99, 102, 241, 0.16), transparent 40%);
        }
        .page-hero__chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #312e81;
            background: #e0e7ff;
            border: 1px solid #c7d2fe;
        }
        .papers-filter-panel {
            border-color: #c7d2fe;
            background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
        }
        .papers-submit-btn {
            background: #4338ca;
        }
        .papers-submit-btn:hover {
            background: #3730a3;
        }
        .impact-range {
            position: relative;
            padding-top: 0.75rem;
            padding-bottom: 0.25rem;
        }
        .impact-range input[type='range'] {
            width: 100%;
            appearance: none;
            background: transparent;
        }
        .impact-range input[type='range']::-webkit-slider-thumb {
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 9999px;
            background: #4f46e5;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.25);
            cursor: pointer;
        }
        .impact-range input[type='range']::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border: none;
            border-radius: 9999px;
            background: #4f46e5;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.25);
            cursor: pointer;
        }
        .impact-range input[type='range']::-webkit-slider-runnable-track {
            height: 8px;
            background: #e2e8f0;
            border-radius: 9999px;
        }
        .impact-range input[type='range']::-moz-range-track {
            height: 8px;
            background: #e2e8f0;
            border-radius: 9999px;
        }
        .impact-range-label {
            font-size: 0.75rem;
            color: #475569;
            white-space: nowrap;
        }
        .filter-caption {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #475569;
        }
        .output-export-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            background: #059669;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease;
        }
        .output-export-btn:hover {
            background: #047857;
        }
    </style>

    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="page-hero overflow-hidden rounded-[28px] border border-indigo-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7">
                <div class="max-w-3xl">
                    <span class="page-hero__chip">EA101 Research Papers</span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Research Papers and Journal Articles</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        ค้นหาผลงานตีพิมพ์จากชื่อบทความ, author ภาษาอังกฤษ, journal และย้อนกลับไปยัง project detail ได้ทันที
                    </p>
                </div>
            </div>
        </section>

        <form id="papers-filter-form" method="GET" action="{{ route('outputs.papers') }}"
            class="papers-filter-panel rounded-2xl border bg-white p-5 shadow-sm">
            <input type="hidden" name="dir" value="{{ $currentDir }}">

            <div class="space-y-4">
                <section class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">ตัวกรองผลงานตีพิมพ์</h2>
                        <p class="mt-1 text-sm text-slate-500">ค้นหา title, author, journal หรือ project และปรับเงื่อนไขเสริมได้ทันที</p>
                    </div>
                    @include('outputs.partials.export-dialog', [
                        'id' => 'papersExportModal',
                        'pdfUrl' => route('outputs.papers.pdf'),
                        'excelUrl' => route('outputs.papers.excel'),
                        'formSelector' => '#papers-filter-form',
                        'minDate' => $exportMinDate ?? null,
                    ])
                </section>

                <section>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            data-paper-live-input="true"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50"
                            placeholder="Paper title / author / journal / project">
                    </label>
                </section>

                <section>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Author (English)</span>
                        <select name="author"
                            data-paper-auto-submit="true"
                            data-paper-author-search="true"
                            data-search-placeholder="เลือกผู้เขียนภาษาอังกฤษ"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50">
                            <option value="">ทั้งหมด</option>
                            @foreach ($authorOptions as $authorOption)
                                <option value="{{ $authorOption }}" @selected(request('author') === $authorOption)>{{ $authorOption }}</option>
                            @endforeach
                        </select>
                    </label>
                </section>

                <fieldset class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                    <legend class="sr-only">Paper filters</legend>
                    <section class="space-y-3 md:col-span-2 xl:col-span-2">
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">Role</span>
                            <select name="role" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50">
                                <option value="">ทั้งหมด</option>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">Ranking</span>
                            <select name="quartile" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50">
                                <option value="">ทั้งหมด</option>
                                @foreach (['Q1', 'Q2', 'Q3', 'Q4', 'TCI 1', 'TCI 2'] as $quartile)
                                    <option value="{{ $quartile }}" @selected(request('quartile') === $quartile)>{{ $quartile }}</option>
                                @endforeach
                            </select>
                        </label>
                    </section>

                    <section class="space-y-3 md:col-span-2 xl:col-span-2">
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                            <select name="year" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50">
                                <option value="">ทั้งหมด</option>
                                @foreach ($yearOptions as $year)
                                    <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="px-1 py-1">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold text-slate-600">Impact Factor</span>
                                <span id="impactMaxLabel" class="text-xs font-semibold text-slate-700">{{ number_format((float) $selectedImpactMax, 2) }}</span>
                            </div>
                            <div class="impact-range mt-3">
                                <input id="impactMaxRange" type="range" min="0" max="{{ $impactFactorMax }}" step="0.1"
                                    value="{{ $selectedImpactMax }}">
                            </div>
                            <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                <span>0.00</span>
                                <span>ค่าสูงสุด {{ number_format((float) $impactFactorMax, 2) }}</span>
                            </div>
                        </div>
                        <input type="hidden" name="impact_max" id="impactMaxInput" value="{{ $selectedImpactMax }}">
                        <input type="hidden" name="impact_filter" id="impactFilterInput" value="{{ request()->boolean('impact_filter') ? '1' : '0' }}">
                    </section>

                    <section class="space-y-3 md:col-span-2 xl:col-span-2">
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                            <select name="sort" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50">
                                <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                                <option value="year" @selected($currentSort === 'year')>ปี</option>
                                <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                                <option value="journal" @selected($currentSort === 'journal')>Journal</option>
                                <option value="quartile" @selected($currentSort === 'quartile')>Ranking</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                            <select name="dir" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50">
                                <option value="desc" @selected($currentDir === 'desc')>มาก → น้อย</option>
                                <option value="asc" @selected($currentDir === 'asc')>น้อย → มาก</option>
                            </select>
                        </label>
                    </section>
                </fieldset>

                <div class="flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-slate-500">เลือก dropdown เพื่ออัปเดตทันที หรือพิมพ์ค้นหาแล้วระบบจะค้นให้อัตโนมัติ</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit"
                            class="papers-submit-btn inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-white shadow-sm transition">
                            ค้นหา
                        </button>
                        <a href="{{ route('outputs.papers') }}" data-papers-reset="true"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            ล้างฟิลเตอร์
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div id="papers-loading-indicator" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตรายการ...
        </div>

        @include('outputs.partials.papers-results')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = "{{ route('outputs.papers', [], false) }}";
            let controller = null;
            let requestToken = 0;
            let liveSearchTimer = null;

            const query = (selector, scope = document) => scope.querySelector(selector);
            const queryAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
            const getForm = () => query('#papers-filter-form');
            const getLoading = () => query('#papers-loading-indicator');
            const getResults = () => query('#papers-results');
            const getListSection = () => query('#papers-results section.overflow-hidden.rounded-2xl.border');

            const setLoading = (loading) => {
                const indicator = getLoading();
                const results = getResults();
                if (indicator) indicator.classList.toggle('hidden', !loading);
                if (results) results.classList.toggle('opacity-60', loading);
            };

            const buildUrlFromForm = (form, resetPage = true) => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);
                const hasImpactFilter = formData.get('impact_filter') === '1';
                for (const [key, value] of formData.entries()) {
                    if (value === null || value === '') continue;
                    if ((key === 'impact_max' || key === 'impact_filter') && !hasImpactFilter) continue;
                    url.searchParams.append(key, value);
                }
                if (resetPage) {
                    url.searchParams.set('page', '1');
                }
                return url;
            };

            const parseAndReplaceResults = (html) => {
                const parser = new DOMParser();
                const nextDoc = parser.parseFromString(html, 'text/html');
                const nextResults = nextDoc.querySelector('#papers-results');
                const currentResults = getResults();
                if (!nextResults || !currentResults) return false;
                currentResults.innerHTML = nextResults.innerHTML;
                return true;
            };

            const scrollToListTop = () => {
                const section = getListSection();
                if (!section) return;
                const top = section.getBoundingClientRect().top + window.scrollY - 16;
                window.scrollTo({ top, behavior: 'smooth' });
            };

            const bindImpactRange = () => {
                const maxRange = query('#impactMaxRange');
                const maxInput = query('#impactMaxInput');
                const filterInput = query('#impactFilterInput');
                const maxLabel = query('#impactMaxLabel');
                const form = getForm();
                if (!maxRange || !maxInput || !filterInput || !maxLabel || !form) return;

                const updateRangeUi = () => {
                    const maxVal = Number(maxRange.value);
                    maxInput.value = String(maxVal);
                    maxLabel.textContent = maxVal.toFixed(2);
                };

                const triggerFetch = () => {
                    clearTimeout(liveSearchTimer);
                    liveSearchTimer = setTimeout(() => {
                        fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                    }, 220);
                };

                if (maxRange.dataset.paperRangeBound === 'true') return;
                maxRange.dataset.paperRangeBound = 'true';
                maxRange.addEventListener('input', () => {
                    filterInput.value = '1';
                    updateRangeUi();
                    triggerFetch();
                });
                maxRange.addEventListener('change', () => {
                    filterInput.value = '1';
                    updateRangeUi();
                    triggerFetch();
                });

                updateRangeUi();
            };

            const fetchAndRender = async (url, options = {}) => {
                const { updateUrl = true, scrollToTop = false } = options;
                const token = ++requestToken;
                if (controller) controller.abort();
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
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const html = await res.text();
                    if (token !== requestToken) return;
                    if (!parseAndReplaceResults(html)) {
                        throw new Error('Cannot find papers results in response');
                    }

                    if (updateUrl) {
                        history.replaceState({ ajax: true }, '', `${url.pathname}${url.search}`);
                    }

                    if (scrollToTop) {
                        scrollToListTop();
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

            const bindPapersInteractions = () => {
                const form = getForm();
                if (!form || form.dataset.ajaxBound === 'true') return;
                form.dataset.ajaxBound = 'true';

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                });

                queryAll('[data-paper-auto-submit="true"]', form).forEach((field) => {
                    if (field.dataset.paperAjaxBound === 'true') return;
                    field.dataset.paperAjaxBound = 'true';
                    field.addEventListener('change', () => {
                        clearTimeout(liveSearchTimer);
                        fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                    });
                });

                queryAll('[data-paper-live-input="true"]', form).forEach((field) => {
                    if (field.dataset.paperAjaxBound === 'true') return;
                    field.dataset.paperAjaxBound = 'true';
                    let isComposing = false;

                    field.addEventListener('compositionstart', () => {
                        isComposing = true;
                    });

                    field.addEventListener('compositionend', () => {
                        isComposing = false;
                        clearTimeout(liveSearchTimer);
                        liveSearchTimer = setTimeout(() => {
                            fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                        }, 300);
                    });

                    field.addEventListener('input', () => {
                        if (isComposing) return;
                        clearTimeout(liveSearchTimer);
                        liveSearchTimer = setTimeout(() => {
                            fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                        }, 300);
                    });
                });

                bindImpactRange();
            };

            const bindPaperAuthorSearchSelect = () => {
                const select = query('[data-paper-author-search="true"]');
                if (!(select instanceof HTMLSelectElement) || typeof ensureProjectSearchDropdown !== 'function') {
                    return;
                }

                const dropdown = ensureProjectSearchDropdown(select, {
                    placeholder: 'ทั้งหมด',
                    searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อผู้เขียนภาษาอังกฤษ'
                });

                dropdown?.refreshOptions?.();
            };

            document.addEventListener('click', (event) => {
                const resetLink = event.target.closest('[data-papers-reset="true"]');
                if (resetLink) {
                    event.preventDefault();
                    const form = getForm();
                    if (form) {
                        queryAll('input[name="search"], select[name="author"], select[name="role"], select[name="quartile"], select[name="year"]', form)
                            .forEach((field) => {
                                field.value = '';
                            });
                        const sortSelect = query('select[name="sort"]', form);
                        if (sortSelect) sortSelect.value = 'created_at';
                        queryAll('[name="dir"]', form).forEach((field) => {
                            field.value = 'desc';
                        });
                    }
                    const impactFilterInput = query('#impactFilterInput');
                    const impactMaxRange = query('#impactMaxRange');
                    const impactMaxInput = query('#impactMaxInput');
                    const impactMaxLabel = query('#impactMaxLabel');
                    if (impactFilterInput) impactFilterInput.value = '0';
                    if (impactMaxRange && impactMaxInput && impactMaxLabel) {
                        impactMaxRange.value = impactMaxRange.max;
                        impactMaxInput.value = impactMaxRange.max;
                        impactMaxLabel.textContent = Number(impactMaxRange.max).toFixed(2);
                    }
                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    fetchAndRender(resetUrl, { updateUrl: true });
                    return;
                }

                const link = event.target.closest('#papers-results a[href]');
                if (!link) return;
                if (link.target === '_blank') return;

                const href = link.getAttribute('href');
                if (!href) return;

                const url = new URL(href, window.location.origin);
                const isSamePage = url.pathname === new URL(currentPath, window.location.origin).pathname;
                const isListInteraction = url.searchParams.has('page') || url.searchParams.has('sort') || url.searchParams.has('dir');

                if (!isSamePage || !isListInteraction) return;

                event.preventDefault();
                fetchAndRender(url, {
                    updateUrl: true,
                    scrollToTop: url.searchParams.has('page'),
                });
            });

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname !== new URL(currentPath, window.location.origin).pathname) return;
                fetchAndRender(url, { updateUrl: false });
            });

            bindPapersInteractions();
            bindPaperAuthorSearchSelect();
        });
    </script>
@endsection
