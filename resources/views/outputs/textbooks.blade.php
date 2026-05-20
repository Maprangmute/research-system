@extends('layouts.app')

@section('content')
    @php
        $currentSort = $currentSort ?? request('sort', 'created_at');
        $currentDir = $currentDir ?? request('dir', 'desc');
        $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
        $sortUrl = fn($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
        $sortIcon = fn($field) => $currentSort !== $field ? '↕' : ($currentDir === 'asc' ? '↑' : '↓');
    @endphp

    <style>
        .textbook-hero {
            position: relative;
            overflow: hidden;
        }
        .textbook-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 87% 18%, rgba(20, 184, 166, 0.18), transparent 42%);
        }
        .textbook-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #065f46;
            background: #d1fae5;
            border: 1px solid #86efac;
        }
        .textbook-filter-panel {
            border-color: #86efac;
            background: linear-gradient(180deg, #f6fffb 0%, #ffffff 100%);
        }
        .textbook-submit-btn {
            background: #047857;
        }
        .textbook-submit-btn:hover {
            background: #065f46;
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
        <section class="textbook-hero overflow-hidden rounded-[28px] border border-emerald-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7">
                <div class="max-w-3xl">
                    <span class="textbook-chip">EA119 Textbook / Book chapter</span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Textbook / Book chapter</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        ค้นหาจากชื่อผลงาน, หนังสือ, สำนักพิมพ์, ผู้เขียน และย้อนกลับไปยัง project detail ได้ทันที
                    </p>
                </div>
            </div>
        </section>

        <form id="textbooks-filter-form" method="GET" action="{{ route('outputs.textbooks') }}"
            class="textbook-filter-panel rounded-2xl border bg-white p-5 shadow-sm">
            <input type="hidden" name="dir" value="{{ $currentDir }}">
            <section class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">ตัวกรองหนังสือและบทในหนังสือ</h2>
                    <p class="mt-1 text-sm text-slate-500">ค้นหา output, ชื่อหนังสือ, สำนักพิมพ์, ผู้เขียน หรือ project ได้ทันที</p>
                </div>
                @include('outputs.partials.export-dialog', [
                    'id' => 'textbooksExportModal',
                    'pdfUrl' => route('outputs.textbooks.pdf'),
                    'excelUrl' => route('outputs.textbooks.excel'),
                    'formSelector' => '#textbooks-filter-form',
                    'minDate' => $exportMinDate ?? null,
                ])
            </section>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <label class="block xl:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        data-textbook-live-input="true"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Output / textbook / publisher / author / project">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">Author</span>
                    <select name="author"
                        data-textbook-auto-submit="true"
                        data-textbook-author-search="true"
                        data-search-placeholder="เลือกชื่อผู้เขียน"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($authorOptions as $option)
                            <option value="{{ $option }}" @selected(request('author') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" data-textbook-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" data-textbook-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                        <option value="year" @selected($currentSort === 'year')>ปี</option>
                        <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                        <option value="book_title" @selected($currentSort === 'book_title')>ชื่อหนังสือ</option>
                        <option value="publisher" @selected($currentSort === 'publisher')>สำนักพิมพ์</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" data-textbook-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="desc" @selected($currentDir === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected($currentDir === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500">เลือก dropdown เพื่ออัปเดตทันที หรือพิมพ์ค้นหาแล้วระบบจะค้นให้อัตโนมัติ</div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                        class="textbook-submit-btn inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-white shadow-sm transition">
                        ค้นหา
                    </button>
                    <a href="{{ route('outputs.textbooks') }}" data-textbooks-reset="true"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        ล้างฟิลเตอร์
                    </a>
                </div>
            </div>
        </form>

        <div id="textbooks-loading-indicator" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตรายการ...
        </div>

        @include('outputs.partials.textbooks-results')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = "{{ route('outputs.textbooks', [], false) }}";
            let controller = null;
            let requestToken = 0;
            let liveSearchTimer = null;

            const query = (selector, scope = document) => scope.querySelector(selector);
            const queryAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
            const getForm = () => query('#textbooks-filter-form');
            const getLoading = () => query('#textbooks-loading-indicator');
            const getResults = () => query('#textbooks-results');
            const getListSection = () => query('#textbooks-results section.overflow-hidden.rounded-2xl.border');

            const setLoading = (loading) => {
                const indicator = getLoading();
                const results = getResults();
                if (indicator) indicator.classList.toggle('hidden', !loading);
                if (results) results.classList.toggle('opacity-60', loading);
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

            const parseAndReplaceResults = (html) => {
                const parser = new DOMParser();
                const nextDoc = parser.parseFromString(html, 'text/html');
                const nextResults = nextDoc.querySelector('#textbooks-results');
                const currentResults = getResults();
                if (!nextResults || !currentResults) return false;
                currentResults.innerHTML = nextResults.innerHTML;
                syncAuthorOptionsFromDocument(nextDoc);
                return true;
            };

            const syncAuthorOptionsFromDocument = (nextDoc) => {
                const currentSelect = query('select[name="author"][data-textbook-author-search="true"]');
                const nextSelect = nextDoc.querySelector('select[name="author"][data-textbook-author-search="true"]');
                if (!(currentSelect instanceof HTMLSelectElement) || !(nextSelect instanceof HTMLSelectElement)) {
                    return;
                }

                const previousValue = currentSelect.value;
                currentSelect.innerHTML = nextSelect.innerHTML;

                if (Array.from(currentSelect.options).some((option) => option.value === previousValue)) {
                    currentSelect.value = previousValue;
                }

                currentSelect.tomselect?.refreshOptions?.();
            };

            const scrollToListTop = () => {
                const section = getListSection();
                if (!section) return;
                const top = section.getBoundingClientRect().top + window.scrollY - 16;
                window.scrollTo({ top, behavior: 'smooth' });
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
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const html = await res.text();
                    if (token !== requestToken) return;
                    if (!parseAndReplaceResults(html)) {
                        throw new Error('Cannot find textbooks results in response');
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

            const bindInteractions = () => {
                const form = getForm();
                if (!form || form.dataset.ajaxBound === 'true') return;
                form.dataset.ajaxBound = 'true';

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                });

                queryAll('[data-textbook-auto-submit="true"]', form).forEach((field) => {
                    if (field.dataset.textbookAjaxBound === 'true') return;
                    field.dataset.textbookAjaxBound = 'true';
                    field.addEventListener('change', () => {
                        clearTimeout(liveSearchTimer);
                        if (controller) controller.abort();
                        fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                    });
                });

                queryAll('[data-textbook-live-input="true"]', form).forEach((field) => {
                    if (field.dataset.textbookAjaxBound === 'true') return;
                    field.dataset.textbookAjaxBound = 'true';
                    let isComposing = false;

                    field.addEventListener('compositionstart', () => {
                        isComposing = true;
                    });

                    field.addEventListener('compositionend', () => {
                        isComposing = false;
                        clearTimeout(liveSearchTimer);
                        if (controller) controller.abort();
                        liveSearchTimer = setTimeout(() => {
                            fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                        }, 300);
                    });

                    field.addEventListener('input', () => {
                        if (isComposing) return;
                        clearTimeout(liveSearchTimer);
                        if (controller) controller.abort();
                        liveSearchTimer = setTimeout(() => {
                            fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                        }, 300);
                    });
                    field.addEventListener('keydown', (event) => {
                        if (event.key !== 'Enter') return;
                        event.preventDefault();
                        clearTimeout(liveSearchTimer);
                        if (controller) controller.abort();
                        fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                    });
                });
            };

            const bindTextbookAuthorSearchSelect = () => {
                const select = query('[data-textbook-author-search="true"]');
                if (!(select instanceof HTMLSelectElement) || typeof ensureProjectSearchDropdown !== 'function') {
                    return;
                }

                const dropdown = ensureProjectSearchDropdown(select, {
                    placeholder: 'ทั้งหมด',
                    searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อผู้เขียน',
                    ignoreSearchWhitespace: true
                });

                dropdown?.refreshOptions?.();
            };

            document.addEventListener('click', (event) => {
                const resetLink = event.target.closest('[data-textbooks-reset="true"]');
                if (resetLink) {
                    event.preventDefault();
                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    fetchAndRender(resetUrl, { updateUrl: true });
                    return;
                }

                const link = event.target.closest('#textbooks-results a[href]');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#')) return;
                if (link.target === '_blank' || link.hasAttribute('download')) return;
                if (link.hasAttribute('data-no-ajax')) return;

                const url = new URL(href, window.location.origin);
                if (url.origin !== window.location.origin) return;
                if (url.pathname !== currentPath) return;

                event.preventDefault();
                fetchAndRender(url, { updateUrl: true, scrollToTop: true });
            });

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname !== currentPath) return;
                fetchAndRender(url, { updateUrl: false });
            });

            bindInteractions();
            bindTextbookAuthorSearchSelect();
        });
    </script>
@endsection
