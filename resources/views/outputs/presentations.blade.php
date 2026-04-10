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
        .presentation-hero {
            position: relative;
            overflow: hidden;
        }
        .presentation-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 86% 16%, rgba(251, 146, 60, 0.18), transparent 40%);
        }
        .presentation-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #9a3412;
            background: #ffedd5;
            border: 1px solid #fdba74;
        }
        .presentation-filter-panel {
            border-color: #fdba74;
            background: linear-gradient(180deg, #fffaf3 0%, #ffffff 100%);
        }
        .presentation-submit-btn {
            background: #ea580c;
        }
        .presentation-submit-btn:hover {
            background: #c2410c;
        }
    </style>

    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="presentation-hero overflow-hidden rounded-[28px] border border-orange-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7">
                <div class="max-w-3xl">
                    <span class="presentation-chip">EA103 Presentations</span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Presentations in Academic Conferences</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        ค้นหาจากชื่อผลงาน, ชื่อผู้นำเสนอ, ประเภทการนำเสนอ, conference และย้อนกลับไปยัง project detail ได้ทันที
                    </p>
                </div>
            </div>
        </section>

        <form id="presentations-filter-form" method="GET" action="{{ route('outputs.presentations') }}"
            class="presentation-filter-panel rounded-2xl border bg-white p-5 shadow-sm">
            <input type="hidden" name="dir" value="{{ $currentDir }}">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <label class="block xl:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        data-presentation-live-input="true"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Output / presenter / conference / project">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">กิจกรรม</span>
                    <select name="activity" data-presentation-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($activityOptions as $option)
                            <option value="{{ $option }}" @selected(request('activity') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" data-presentation-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" data-presentation-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                        <option value="year" @selected($currentSort === 'year')>ปี</option>
                        <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                        <option value="conference" @selected($currentSort === 'conference')>Conference</option>
                        <option value="activity" @selected($currentSort === 'activity')>กิจกรรม</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" data-presentation-auto-submit="true" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="desc" @selected($currentDir === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected($currentDir === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                        class="presentation-submit-btn inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-white shadow-sm transition">
                        ค้นหา
                    </button>
                    <a href="{{ route('outputs.presentations') }}" data-presentations-reset="true"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        ล้างฟิลเตอร์
                    </a>
                </div>

                <a href="{{ route('outputs.presentations.pdf', request()->query()) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700">
                    Export รายการ เป็น PDF
                </a>
            </div>
        </form>

        <div id="presentations-loading-indicator" class="hidden items-center gap-2 text-xs text-slate-500">
            <span class="inline-block h-3 w-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            กำลังอัปเดตรายการ...
        </div>

        @include('outputs.partials.presentations-results')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = "{{ route('outputs.presentations', [], false) }}";
            let controller = null;
            let requestToken = 0;
            let liveSearchTimer = null;

            const query = (selector, scope = document) => scope.querySelector(selector);
            const queryAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
            const getForm = () => query('#presentations-filter-form');
            const getLoading = () => query('#presentations-loading-indicator');
            const getResults = () => query('#presentations-results');
            const getListSection = () => query('#presentations-results section.overflow-hidden.rounded-2xl.border');

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
                const nextResults = nextDoc.querySelector('#presentations-results');
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
                        throw new Error('Cannot find presentations results in response');
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

            const bindPresentationInteractions = () => {
                const form = getForm();
                if (!form || form.dataset.ajaxBound === 'true') return;
                form.dataset.ajaxBound = 'true';

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                });

                queryAll('[data-presentation-auto-submit="true"]', form).forEach((field) => {
                    if (field.dataset.presentationAjaxBound === 'true') return;
                    field.dataset.presentationAjaxBound = 'true';
                    field.addEventListener('change', () => {
                        clearTimeout(liveSearchTimer);
                        fetchAndRender(buildUrlFromForm(form, true), { updateUrl: true });
                    });
                });

                queryAll('[data-presentation-live-input="true"]', form).forEach((field) => {
                    if (field.dataset.presentationAjaxBound === 'true') return;
                    field.dataset.presentationAjaxBound = 'true';
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
                const resetLink = event.target.closest('[data-presentations-reset="true"]');
                if (resetLink) {
                    event.preventDefault();
                    const resetUrl = new URL(resetLink.href, window.location.origin);
                    fetchAndRender(resetUrl, { updateUrl: true });
                    return;
                }

                const link = event.target.closest('#presentations-results a[href]');
                if (!link) return;
                if (link.target === '_blank') return;

                const href = link.getAttribute('href');
                if (!href) return;

                const url = new URL(href, window.location.origin);
                if (url.pathname !== currentPath) return;

                event.preventDefault();
                fetchAndRender(url, { updateUrl: true, scrollToTop: true });
            });

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                if (url.pathname !== currentPath) return;
                fetchAndRender(url, { updateUrl: false });
            });

            bindPresentationInteractions();
        });
    </script>
@endsection
