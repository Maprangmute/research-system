@extends('layouts.app')

@section('content')
    @php
        $currentSort = $currentSort ?? request('sort', 'created_at');
        $currentDir = $currentDir ?? request('dir', 'desc');
        $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
        $sortUrl = fn($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
        $sortIcon = fn($field) => $currentSort !== $field ? '↕' : ($currentDir === 'asc' ? '↑' : '↓');
    @endphp

    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">📚 Research Papers and Journal Articles</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        ค้นหาผลงานตีพิมพ์จากชื่อบทความ, author ภาษาอังกฤษ, journal และย้อนกลับไปยัง project detail ได้ทันที
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.papers') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                        Research Papers
                    </a>
                    <a href="{{ route('outputs.presentations') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Presentations
                    </a>
                </div>
            </div>
        </section>

        <div id="papers-shell" class="space-y-6">
        <form id="papers-filter-form" method="GET" action="{{ route('outputs.papers') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <input type="hidden" name="dir" value="{{ $currentDir }}">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <label class="block xl:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        data-paper-live-input="true"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Paper title / author / journal / project">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">Author</span>
                    <input type="text" name="person" value="{{ request('person') }}" list="paper-author-list"
                        data-paper-live-input="true"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Search author">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">Role</span>
                    <select name="role" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">Quartile</span>
                    <select name="quartile" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quartile)
                            <option value="{{ $quartile }}" @selected(request('quartile') === $quartile)>{{ $quartile }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block xl:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">Journal</span>
                    <input type="text" name="journal" value="{{ request('journal') }}" list="paper-journal-list"
                        data-paper-live-input="true"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Journal name">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                        <option value="year" @selected($currentSort === 'year')>ปี</option>
                        <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                        <option value="journal" @selected($currentSort === 'journal')>Journal</option>
                        <option value="quartile" @selected($currentSort === 'quartile')>Quartile</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" data-paper-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="desc" @selected($currentDir === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected($currentDir === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        ค้นหา
                    </button>
                    <a href="{{ route('outputs.papers') }}" data-papers-reset="true"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        ล้างฟิลเตอร์
                    </a>
                </div>

                <a href="{{ route('outputs.papers.pdf', request()->query()) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700">
                    Export PDF
                </a>
            </div>
        </form>

        <datalist id="paper-author-list">
            @foreach ($authorOptions as $author)
                <option value="{{ $author }}"></option>
            @endforeach
        </datalist>

        <datalist id="paper-journal-list">
            @foreach ($journalOptions as $journal)
                <option value="{{ $journal }}"></option>
            @endforeach
        </datalist>

        <div id="papers-loading-indicator" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตรายการ...
        </div>

        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Papers</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['total_papers']) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Unique Authors</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['unique_authors']) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Author</div>
                <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_author'] ?? '-' }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Journal</div>
                <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_journal'] ?? '-' }}</div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-4 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-800">รายการบทความ</div>
                    <div class="mt-1 text-sm text-slate-500">
                        แสดงผล {{ $outputs->firstItem() ?? 0 }}–{{ $outputs->lastItem() ?? 0 }} จากทั้งหมด {{ $outputs->total() }} รายการ
                    </div>
                </div>
            </div>

            @if ($outputs->count() > 0)
                <div class="overflow-auto">
                    <table class="min-w-full table-fixed">
                        <colgroup>
                            <col style="width: 24%;">
                            <col style="width: 25%;">
                            <col style="width: 16%;">
                            <col style="width: 8%;">
                            <col style="width: 8%;">
                            <col style="width: 19%;">
                        </colgroup>
                        <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600">
                            <tr>
                                <th class="px-4 py-3"><a href="{{ $sortUrl('title') }}">ชื่อผลงาน <span class="ml-1">{{ $sortIcon('title') }}</span></a></th>
                                <th class="px-4 py-3">Authors</th>
                                <th class="px-4 py-3"><a href="{{ $sortUrl('journal') }}">Journal <span class="ml-1">{{ $sortIcon('journal') }}</span></a></th>
                                <th class="px-4 py-3 text-center"><a href="{{ $sortUrl('quartile') }}">Quartile <span class="ml-1">{{ $sortIcon('quartile') }}</span></a></th>
                                <th class="px-4 py-3 text-center"><a href="{{ $sortUrl('year') }}">ปี <span class="ml-1">{{ $sortIcon('year') }}</span></a></th>
                                <th class="px-4 py-3">Project Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($outputs as $paper)
                                <tr class="align-top hover:bg-indigo-50/40">
                                    <td class="px-4 py-4 text-sm text-slate-900">
                                        <div class="font-semibold leading-6">{{ $paper->title }}</div>
                                        @if ($paper->impact_factor)
                                            <div class="mt-1 text-xs text-slate-500">Impact Factor: {{ $paper->impact_factor }}</div>
                                        @endif
                                        @if (!empty($paper->detail))
                                            <div class="mt-2 text-xs leading-5 text-slate-600 break-words">
                                                รายละเอียด / ลิงก์:
                                                @if (filter_var($paper->detail, FILTER_VALIDATE_URL))
                                                    <a href="{{ $paper->detail }}" target="_blank" rel="noopener"
                                                        class="font-medium text-indigo-700 hover:underline">
                                                        {{ $paper->detail }}
                                                    </a>
                                                @else
                                                    {{ $paper->detail }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        @forelse ($paper->authors_by_role as $role => $names)
                                            <div class="mb-2">
                                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                                    {{ ['first' => 'First Author', 'co' => 'Co Author', 'corresponding' => 'Corresponding Author'][$role] ?? ucfirst($role) }}
                                                </div>
                                                <div class="mt-1 leading-6">{{ $names->implode(', ') }}</div>
                                            </div>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ $paper->journal }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-700">{{ $paper->quartile }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $paper->year_be ?: '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <div class="font-medium">{{ $paper->project_label }}</div>
                                        @if ($paper->project_code)
                                            <div class="mt-1 text-xs text-slate-500">{{ $paper->project_code }}</div>
                                        @endif
                                        @if ($paper->project_link)
                                            <a href="{{ $paper->project_link }}"
                                                class="mt-2 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                                เปิด Project Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                    {{ $outputs->links('vendor.pagination.th') }}
                </div>
            @else
                <div class="px-4 py-10 text-center text-sm text-slate-500">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
            @endif
        </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = "{{ route('outputs.papers', [], false) }}";
            let controller = null;
            let requestToken = 0;
            let liveSearchTimer = null;

            const query = (selector, scope = document) => scope.querySelector(selector);
            const queryAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
            const getShell = () => query('#papers-shell');
            const getForm = () => query('#papers-filter-form');
            const getLoading = () => query('#papers-loading-indicator');

            const setLoading = (loading) => {
                const indicator = getLoading();
                const shell = getShell();
                if (indicator) indicator.classList.toggle('hidden', !loading);
                if (shell) shell.classList.toggle('opacity-60', loading);
            };

            const buildUrlFromForm = (form, resetPage = true) => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (value === null || value === '') continue;
                    url.searchParams.append(key, value);
                }
                if (resetPage) {
                    url.searchParams.set('page', '1');
                }
                return url;
            };

            const parseAndReplaceShell = (html) => {
                const parser = new DOMParser();
                const nextDoc = parser.parseFromString(html, 'text/html');
                const nextShell = nextDoc.querySelector('#papers-shell');
                const currentShell = getShell();
                if (!nextShell || !currentShell) return false;
                currentShell.innerHTML = nextShell.innerHTML;
                return true;
            };

            const fetchAndRender = async (url, options = {}) => {
                const { updateUrl = true } = options;
                const token = ++requestToken;
                const activeField = document.activeElement;
                const activeFieldName = activeField && activeField.form === getForm() ? activeField.getAttribute('name') : null;
                const activeFieldSelectionStart = activeField && typeof activeField.selectionStart === 'number' ? activeField.selectionStart : null;
                const activeFieldSelectionEnd = activeField && typeof activeField.selectionEnd === 'number' ? activeField.selectionEnd : null;

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
                    if (!parseAndReplaceShell(html)) {
                        throw new Error('Cannot find papers shell in response');
                    }

                    if (updateUrl) {
                        history.replaceState({ ajax: true }, '', `${url.pathname}${url.search}`);
                    }

                    bindPapersInteractions();

                    if (activeFieldName) {
                        const nextField = getForm()?.querySelector(`[name="${CSS.escape(activeFieldName)}"]`);
                        if (nextField && typeof nextField.focus === 'function') {
                            nextField.focus({ preventScroll: true });
                            if (typeof nextField.setSelectionRange === 'function' && activeFieldSelectionStart !== null && activeFieldSelectionEnd !== null) {
                                nextField.setSelectionRange(activeFieldSelectionStart, activeFieldSelectionEnd);
                            }
                        }
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
                        }, 450);
                    });

                    field.addEventListener('input', () => {
                        if (isComposing) return;
                        clearTimeout(liveSearchTimer);
                        liveSearchTimer = setTimeout(() => {
                            fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                        }, 450);
                    });
                });
            };

            document.addEventListener('click', (event) => {
                const resetLink = event.target.closest('[data-papers-reset="true"]');
                if (resetLink) {
                    event.preventDefault();
                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    fetchAndRender(resetUrl, { updateUrl: true });
                    return;
                }

                const link = event.target.closest('#papers-shell a[href]');
                if (!link) return;
                if (link.target === '_blank') return;

                const href = link.getAttribute('href');
                if (!href) return;

                const url = new URL(href, window.location.origin);
                const isSamePage = url.pathname === new URL(currentPath, window.location.origin).pathname;
                const isListInteraction = url.searchParams.has('page') || url.searchParams.has('sort') || url.searchParams.has('dir');

                if (!isSamePage || !isListInteraction) return;

                event.preventDefault();
                fetchAndRender(url, { updateUrl: true });
            });

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname !== new URL(currentPath, window.location.origin).pathname) return;
                fetchAndRender(url, { updateUrl: false });
            });

            bindPapersInteractions();
        });
    </script>
@endsection
