<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Research Management</title>
    <link rel="icon" type="image/png" href="{{ asset('images/sihp-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/sihp-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/sihp-logo.png') }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles
    <script src="//unpkg.com/alpinejs" defer></script>



    <!-- ✅ jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ jQuery Gantt Plugin (Full) -->
    <link href="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/css/style.css" rel="stylesheet" />
    <script src="https://rawcdn.githack.com/taitems/jQuery.Gantt/master/js/jquery.fn.gantt.js"></script>

    <!-- CSS + JS DHTMLX Gantt -->
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>



</head>

@php
    $isModalView = request()->boolean('modal');
@endphp

<body class="{{ $isModalView ? 'bg-white text-gray-800' : 'bg-gray-100 text-gray-800' }}">
    @if (!$isModalView)
        <style>
            .mobile-nav {
                border-radius: 16px;
                padding: 8px 12px;
            }

            .mobile-nav a.brand {
                font-weight: 700;
                color: #0f172a;
                text-decoration: none;
            }

            .mobile-nav-toggle {
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                background: #ffffff;
                width: 42px;
                height: 42px;
                align-items: center;
                justify-content: center;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }

            .mobile-nav-toggle:active {
                transform: scale(0.96);
            }

            .mobile-nav-toggle:hover {
                box-shadow: 0 3px 10px rgba(15, 23, 42, 0.08);
            }

            .mobile-hamburger {
                width: 18px;
                height: 14px;
                display: inline-flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .mobile-hamburger span {
                display: block;
                width: 100%;
                height: 2px;
                border-radius: 999px;
                background: #334155;
                transition: transform 0.2s ease, opacity 0.2s ease;
                transform-origin: center;
            }

            .mobile-nav-toggle.is-open .mobile-hamburger span:nth-child(1) {
                transform: translateY(6px) rotate(45deg);
            }

            .mobile-nav-toggle.is-open .mobile-hamburger span:nth-child(2) {
                opacity: 0;
            }

            .mobile-nav-toggle.is-open .mobile-hamburger span:nth-child(3) {
                transform: translateY(-6px) rotate(-45deg);
            }

            .mobile-nav-links a {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 10px;
                color: #0f172a;
                border: 1px solid #e5e7eb;
            }

            .mobile-nav-links a:hover {
                background: #f1f5f9;
            }

            .mobile-logout-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                margin-top: 4px;
                padding: 10px 12px;
                border-radius: 12px;
                border: 1px solid #fca5a5;
                background: #fee2e2;
                color: #b91c1c;
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 0.01em;
                transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
            }

            .mobile-logout-btn:hover {
                background: #fecaca;
                box-shadow: 0 6px 14px rgba(185, 28, 28, 0.14);
                transform: translateY(-1px);
            }

            .mobile-logout-btn:active {
                transform: translateY(0);
            }

            .mobile-drawer-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.32);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                z-index: 58;
            }

            .mobile-drawer-panel {
                position: fixed;
                top: 0;
                right: 0;
                height: 100dvh;
                width: min(90vw, 360px);
                background: #fff;
                border-left: 1px solid #e5e7eb;
                border-top-left-radius: 18px;
                border-bottom-left-radius: 18px;
                box-shadow: -10px 0 28px rgba(15, 23, 42, 0.14);
                padding: 14px 12px 16px;
                z-index: 59;
                overflow-y: auto;
            }

            /* Nav auto hide/show */
            #main-nav.nav-hidden {
                transform: translateY(-110%);
            }

            #save-loading-overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            }

            #save-loading-overlay.is-visible {
                display: flex;
            }

            .save-loading-overlay__card {
                min-width: 260px;
                max-width: min(92vw, 360px);
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                background: #ffffff;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 24px 48px rgba(15, 23, 42, 0.22);
            }

            .save-loading-overlay__row {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .save-loading-overlay__spinner {
                width: 1.25rem;
                height: 1.25rem;
                border-radius: 999px;
                border: 2px solid #cbd5e1;
                border-top-color: #4f46e5;
                animation: save-loading-spin 0.75s linear infinite;
                flex: 0 0 auto;
            }

            .save-loading-overlay__title {
                margin: 0;
                font-size: 0.95rem;
                font-weight: 600;
                color: #1e293b;
                line-height: 1.35;
            }

            .save-loading-overlay__subtitle {
                margin: 0.15rem 0 0;
                font-size: 0.75rem;
                color: #64748b;
                line-height: 1.4;
            }

            @keyframes save-loading-spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
    @endif

    @if (!$isModalView)
        <!-- ✅ เปลี่ยนจาก max-w-7xl เป็น container -->
        <nav id="main-nav"
            class="bg-white border-b border-slate-200 shadow-sm fixed top-0 inset-x-0 z-50 transition-transform duration-200"
            x-data="{ openNav: false }"
            x-effect="window.dispatchEvent(new CustomEvent('app-mobile-nav', { detail: { open: openNav } }))">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center gap-3 lg:gap-4 mobile-nav md:rounded-none md:border-0 md:bg-transparent md:p-0">
                    <a href="/dashboard" class="font-bold brand text-slate-900 tracking-tight text-base lg:text-lg whitespace-nowrap">Research
                        Dashboard</a>

                    <div class="hidden md:flex items-center gap-0.5 lg:gap-1 flex-1 justify-start lg:justify-center min-w-0">
                        <a href="/dashboard"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">📊
                            Dashboard</a>
                        <a href="/rm"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">📋
                            รายการโปรเจค</a>
                        <a href="/rm/create"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50">➕
                            เพิ่มโครงการ</a>

                        <a href="{{ route('back_office.index') }}"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-slate-700 hover:text-blue-700 hover:bg-slate-100">🧾
                            Back Office</a> <a href="{{ route('outputs.index') }}"
                            class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold text-amber-700 hover:text-amber-800 hover:bg-amber-50">📑
                            outputs</a>

                        <div class="relative" x-data="{ openFte: false }">
                            <button type="button" @click="openFte = !openFte" @click.away="openFte = false"
                                class="px-2 lg:px-3 py-2 rounded-lg text-[13px] lg:text-sm font-semibold inline-flex items-center gap-1 text-slate-700 hover:text-blue-700 hover:bg-slate-100 whitespace-nowrap">
                                🔥 FTE
                                <svg :class="{ 'rotate-180': openFte }" class="w-4 h-4 text-gray-500 transition-transform"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div x-show="openFte" x-cloak
                                class="absolute left-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
                                <a href="{{ route('fte.index') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    FTE รายบุคคล
                                </a>
                                <a href="{{ route('fte.project.index') }}"
                                    class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                                    FTE รายโปรเจค
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 ml-auto">
                        <!-- Profile Dropdown (desktop only) -->
                        <div class="relative hidden md:block" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-10 h-10 bg-gray-200 rounded-full overflow-hidden focus:outline-none">
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}?v={{ time() }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                    alt="Profile" class="w-full h-full object-cover">
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                class="absolute right-0 mt-2 w-72 bg-white border rounded shadow-md z-50">
                                <div class="px-4 py-2 text-sm text-gray-800 border-b leading-tight">
                                    <div class="font-medium">
                                        {{ Auth::user()->prefix }} {{ Auth::user()->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ตำแหน่ง: {{ Auth::user()->position }} ({{ Auth::user()->role }})
                                    </div>
                                </div>
                                <a href="{{ route('user_guide') }}"
                                    class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">📘 คู่มือ</a>

                                @if (Auth::user()->role === 'admin')
                                    <a href="/user" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                        👥 จัดการผู้ใช้
                                    </a>
                                @endif

                                @if (Auth::user()->role === 'admin')
                                    <a href="/project-logs"
                                        class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                        📝 Project log
                                    </a>
                                @endif
                                <a href="{{ route('profile.show') }}"
                                    class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                    👤 แก้ไขข้อมูลส่วนตัว
                                </a>

                                <form method="POST" action="{{ route('logout') }}" data-logout-form>
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        🚪 ออกจากระบบ
                                    </button>
                                </form>
                            </div>
                        </div>

                        <button type="button" class="md:hidden inline-flex mobile-nav-toggle text-gray-600"
                            :class="{ 'is-open': openNav }"
                            @click="openNav = !openNav" aria-label="Toggle navigation">
                            <span class="mobile-hamburger" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="md:hidden" x-show="openNav" x-cloak>
                    <div class="mobile-drawer-overlay" x-show="openNav" @click="openNav = false"
                        x-transition.opacity.duration.200ms></div>

                    <aside class="mobile-drawer-panel" x-show="openNav" @click.stop
                        x-transition:enter="transform transition ease-out duration-250"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full">
                        <div class="pt-1 pb-2 border-b flex items-center justify-between border-slate-200">
                            <div class="text-sm font-semibold text-slate-700">เมนู</div>
                            <button type="button" class="inline-flex mobile-nav-toggle text-gray-600"
                                @click="openNav = false" aria-label="Close navigation">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                                </svg>
                            </button>
                        </div>
                        <div class="pt-3 pb-2 flex flex-col gap-2 mobile-nav-links">
                        <div class="flex items-center gap-3 px-1 pb-2 border-b border-slate-200">
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}?v={{ time() }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                alt="Profile" class="w-10 h-10 rounded-full object-cover">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-900 truncate">
                                    {{ Auth::user()->prefix }} {{ Auth::user()->name }}
                                </div>
                                <div class="text-xs text-slate-500 truncate">
                                    {{ Auth::user()->position }} ({{ Auth::user()->role }})
                                </div>
                            </div>
                        </div>
                        <a href="/dashboard">📊 Dashboard</a>
                        <a href="/rm">📋 รายการโปรเจค</a>
                        <a href="{{ route('back_office.index') }}">🧾 Back Office</a>
                        <a href="/rm/create">➕ เพิ่มโครงการ</a>
                        <a href="{{ route('outputs.index') }}">📑 outputs</a>
                        <div class="text-gray-700 font-medium">🔥 FTE</div>
                        <a href="{{ route('fte.index') }}">FTE รายบุคคล</a>
                        <a href="{{ route('fte.project.index') }}">FTE รายโปรเจค</a>
                        <a href="{{ route('user_guide') }}">📘 คู่มือ</a>
                        @if (Auth::user()->role === 'admin')
                            <a href="/user">👥 จัดการผู้ใช้</a>
                            <a href="/project-logs">📝 Project log</a>
                        @endif
                        <a href="{{ route('profile.show') }}">👤 แก้ไขข้อมูลส่วนตัว</a>
                        <form method="POST" action="{{ route('logout') }}" data-logout-form>
                            @csrf
                            <button type="submit" class="mobile-logout-btn">
                                🚪 ออกจากระบบ
                            </button>
                        </form>
                        </div>
                    </aside>
                    </div>
                </div>
            </div>
        </nav>
    @endif

    <!-- ✅ main ก็ใช้ container เช่นกัน -->
    <main class="container mx-auto px-4 {{ $isModalView ? 'pt-4 pb-4' : 'pt-24 pb-6' }}">
        @if (isset($header))
            <div class="mb-4">{{ $header }}</div>
        @endif
        @if (isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    @php
        $hideAiChatOnPage = request()->routeIs('projects.create') || request()->routeIs('projects.edit');
    @endphp
    @unless ($hideAiChatOnPage)
        @include('partials.global-ai-chat')
    @endunless

    @livewireScripts
</body>

</html>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<script>
    (() => {
        const logoutFlagKey = 'auth:logged_out';
        const loginUrl = @json(route('login'));

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form[data-logout-form]').forEach((form) => {
                if (form.dataset.logoutBound === '1') return;
                form.dataset.logoutBound = '1';
                form.addEventListener('submit', () => {
                    try {
                        window.__clearAppAiChatSession?.();
                        sessionStorage.setItem(logoutFlagKey, '1');
                    } catch (error) {}
                });
            });

            try {
                const navEntry = window.performance?.getEntriesByType?.('navigation')?.[0];
                const isBackForward = navEntry?.type === 'back_forward';
                if (!isBackForward) {
                    sessionStorage.removeItem(logoutFlagKey);
                }
            } catch (error) {}
        });

        window.addEventListener('pageshow', (event) => {
            try {
                const navEntry = window.performance?.getEntriesByType?.('navigation')?.[0];
                const isBackForward = event.persisted || navEntry?.type === 'back_forward';
                const hasLogoutFlag = sessionStorage.getItem(logoutFlagKey) === '1';

                if (isBackForward && hasLogoutFlag) {
                    window.location.replace(loginUrl);
                }
            } catch (error) {}
        });
    })();
</script>

<script>
    // ===== Auto hide nav on scroll down, show on scroll up =====
    (function() {
        const nav = document.getElementById('main-nav');
        if (!nav) return;

        let lastY = window.scrollY || 0;
        let ticking = false;

        function onScroll() {
            const currentY = window.scrollY || 0;
            const delta = currentY - lastY;

            // Always show near top
            if (currentY < 40) {
                nav.classList.remove('nav-hidden');
                lastY = currentY;
                ticking = false;
                return;
            }

            if (Math.abs(delta) > 6) {
                if (delta > 0) {
                    nav.classList.add('nav-hidden'); // scrolling down -> hide
                } else {
                    nav.classList.remove('nav-hidden'); // scrolling up -> show
                }
                lastY = currentY;
            }
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(onScroll);
                ticking = true;
            }
        }, {
            passive: true
        });
    })();

    /* =========================================================
       DATE-BE + REPEATABLE (FINAL / PRODUCTION SAFE)
       ========================================================= */

    function getClientTodayLocal() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return today;
    }

    function normalizeDateOnly(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function parseDateInputValue(raw) {
        const normalizeThaiDigits = (input) => String(input || '').replace(/[๐-๙]/g, (ch) => String('๐๑๒๓๔๕๖๗๘๙'.indexOf(ch)));
        const value = normalizeThaiDigits(String(raw || '').trim()).replace(/\s+/g, ' ');
        if (!value) return null;

        let match = value.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
        if (match) {
            const [, dd, mm, yyyy] = match;
            let year = Number(yyyy);
            if (year > 2400) year -= 543;
            return new Date(year, Number(mm) - 1, Number(dd));
        }

        match = value.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
        if (match) {
            const [, yyyy, mm, dd] = match;
            let year = Number(yyyy);
            if (year > 2400) year -= 543;
            return new Date(year, Number(mm) - 1, Number(dd));
        }

        match = value.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/);
        if (match) {
            const [, dd, monthRaw, yyyyRaw] = match;
            const monthMap = {
                'มกราคม': 0, 'ม.ค.': 0, 'ม.ค': 0,
                'กุมภาพันธ์': 1, 'ก.พ.': 1, 'ก.พ': 1,
                'มีนาคม': 2, 'มี.ค.': 2, 'มี.ค': 2,
                'เมษายน': 3, 'เม.ย.': 3, 'เม.ย': 3,
                'พฤษภาคม': 4, 'พ.ค.': 4, 'พ.ค': 4,
                'มิถุนายน': 5, 'มิ.ย.': 5, 'มิ.ย': 5,
                'กรกฎาคม': 6, 'ก.ค.': 6, 'ก.ค': 6,
                'สิงหาคม': 7, 'ส.ค.': 7, 'ส.ค': 7,
                'กันยายน': 8, 'ก.ย.': 8, 'ก.ย': 8,
                'ตุลาคม': 9, 'ต.ค.': 9, 'ต.ค': 9,
                'พฤศจิกายน': 10, 'พ.ย.': 10, 'พ.ย': 10,
                'ธันวาคม': 11, 'ธ.ค.': 11, 'ธ.ค': 11
            };
            const monthIdx = monthMap[monthRaw];
            if (monthIdx !== undefined) {
                let yyyy = Number(yyyyRaw);
                if (yyyy > 2400) yyyy -= 543;
                return new Date(yyyy, monthIdx, Number(dd));
            }
        }

        const fallback = new Date(value);
        if (!Number.isNaN(fallback.getTime())) return fallback;
        return null;
    }

    function isProjectMobileWheelDateMode() {
        return window.PROJECT_MOBILE_WHEEL_DATE === true;
    }

    function toYmdCE(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const yyyy = String(date.getFullYear());
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function toDmyCE(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const yyyy = String(date.getFullYear());
        return `${dd}-${mm}-${yyyy}`;
    }

    function formatDateBELabel(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const day = date.getDate();
        const month = date.toLocaleDateString('th-TH', {
            month: 'long'
        });
        const year = date.getFullYear() + 543;
        return `${day} ${month} ${year}`;
    }

    function ensureThaiWheelPickerModal() {
        if (window.__thaiWheelPickerModal) return window.__thaiWheelPickerModal;

        if (!document.getElementById('thaiWheelPickerStyle')) {
            const style = document.createElement('style');
            style.id = 'thaiWheelPickerStyle';
            style.textContent = `
                .thai-wheel-overlay{position:fixed;inset:0;background:rgba(15,23,42,.14);display:none;align-items:flex-end;justify-content:center;z-index:120;backdrop-filter:blur(1.5px)}
                .thai-wheel-overlay.is-open{display:flex}
                .thai-wheel-sheet{width:min(680px,96vw);background:rgba(255,255,255,.86);border-radius:18px 18px 0 0;box-shadow:0 -10px 32px rgba(0,0,0,.13);padding:12px;border:1px solid rgba(203,213,225,.65)}
                .thai-wheel-title{font-size:14px;font-weight:700;color:#0f172a;margin:2px 4px 10px}
                .thai-wheel-body{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
                .thai-wheel-col{display:flex;flex-direction:column}
                .thai-wheel-col label{font-size:11px;color:#64748b;margin:0 0 4px 2px}
                .thai-wheel-select{height:168px;border:1px solid #cbd5e1;border-radius:12px;padding:2px 0;background:rgba(255,255,255,.9);font-size:18px;font-weight:600;color:#0f172a}
                .thai-wheel-select option{padding:8px 4px;text-align:center}
                .thai-wheel-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
                .thai-wheel-btn{border:none;border-radius:12px;padding:11px 10px;font-size:16px;font-weight:700}
                .thai-wheel-btn--ok{background:#2563eb;color:#fff}
                .thai-wheel-btn--cancel{background:#e2e8f0;color:#0f172a}
            `;
            document.head.appendChild(style);
        }

        const monthNames = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const now = new Date();
        const nowBE = now.getFullYear() + 543;
        const yearMin = nowBE - 10;
        const yearMax = nowBE + 10;

        const overlay = document.createElement('div');
        overlay.className = 'thai-wheel-overlay';
        overlay.innerHTML = `
            <div class="thai-wheel-sheet" role="dialog" aria-modal="true" aria-label="เลือกวันที่">
                <div class="thai-wheel-title">เลือกวันที่ (พ.ศ.)</div>
                <div class="thai-wheel-body">
                    <div class="thai-wheel-col">
                        <label>วัน</label>
                        <select class="thai-wheel-select" id="thaiWheelDay" size="5"></select>
                    </div>
                    <div class="thai-wheel-col">
                        <label>เดือน</label>
                        <select class="thai-wheel-select" id="thaiWheelMonth" size="5"></select>
                    </div>
                    <div class="thai-wheel-col">
                        <label>ปี พ.ศ.</label>
                        <select class="thai-wheel-select" id="thaiWheelYear" size="5"></select>
                    </div>
                </div>
                <div class="thai-wheel-actions">
                    <button type="button" class="thai-wheel-btn thai-wheel-btn--ok" id="thaiWheelOk">เลือกวัน</button>
                    <button type="button" class="thai-wheel-btn thai-wheel-btn--cancel" id="thaiWheelCancel">ยกเลิก</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const daySel = overlay.querySelector('#thaiWheelDay');
        const monthSel = overlay.querySelector('#thaiWheelMonth');
        const yearSel = overlay.querySelector('#thaiWheelYear');
        const okBtn = overlay.querySelector('#thaiWheelOk');
        const cancelBtn = overlay.querySelector('#thaiWheelCancel');
        let onConfirm = null;

        for (let m = 0; m < 12; m++) {
            const opt = document.createElement('option');
            opt.value = String(m + 1);
            opt.textContent = monthNames[m];
            monthSel.appendChild(opt);
        }
        for (let y = yearMin; y <= yearMax; y++) {
            const opt = document.createElement('option');
            opt.value = String(y);
            opt.textContent = String(y);
            yearSel.appendChild(opt);
        }

        const daysInMonth = (yearCE, month1) => new Date(yearCE, month1, 0).getDate();
        const refillDays = () => {
            const yearBE = Number(yearSel.value || nowBE);
            const month = Number(monthSel.value || 1);
            const maxDay = daysInMonth(yearBE - 543, month);
            const prev = Number(daySel.value || 1);
            daySel.innerHTML = '';
            for (let d = 1; d <= maxDay; d++) {
                const opt = document.createElement('option');
                opt.value = String(d);
                opt.textContent = String(d);
                daySel.appendChild(opt);
            }
            daySel.value = String(Math.min(prev, maxDay));
        };

        const setDate = (date) => {
            const d = date instanceof Date && !Number.isNaN(date.getTime()) ? date : new Date();
            monthSel.value = String(d.getMonth() + 1);
            yearSel.value = String(d.getFullYear() + 543);
            refillDays();
            daySel.value = String(d.getDate());
        };

        const open = (date, callback) => {
            onConfirm = callback;
            setDate(date);
            overlay.classList.add('is-open');
        };
        const close = () => overlay.classList.remove('is-open');

        monthSel.addEventListener('change', refillDays);
        yearSel.addEventListener('change', refillDays);
        cancelBtn.addEventListener('click', close);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close();
        });
        okBtn.addEventListener('click', () => {
            const day = Number(daySel.value || 1);
            const month = Number(monthSel.value || 1);
            const yearBE = Number(yearSel.value || nowBE);
            const picked = new Date(yearBE - 543, month - 1, day);
            if (typeof onConfirm === 'function') onConfirm(picked);
            close();
        });

        window.__thaiWheelPickerModal = { open };
        return window.__thaiWheelPickerModal;
    }

    function initNativeMobileDateInput(input) {
        if (!(input instanceof HTMLInputElement)) return;
        if (input.dataset.nativeDateReady === '1') return;

        const parsed = parseDateInputValue(input.value);
        const holder = input.closest('.relative') || input.parentElement;
        if (!holder) return;

        let displayInput = holder.querySelector(`input[data-native-date-display-for="${CSS.escape(input.name || '')}"]`);
        if (!displayInput) {
            displayInput = document.createElement('input');
            displayInput.type = 'text';
            displayInput.readOnly = true;
            displayInput.className = input.className;
            displayInput.dataset.nativeDateDisplayFor = input.name || '';
            holder.appendChild(displayInput);
        }

        input.type = 'hidden';
        input.classList.add('native-date-raw');
        input.style.display = 'none';
        input.value = parsed ? toYmdCE(parsed) : '';

        const syncDisplay = () => {
            const rawDate = parseDateInputValue(input.value);
            displayInput.value = rawDate ? formatDateBELabel(rawDate) : '';
        };
        syncDisplay();

        const openWheel = () => {
            const picker = ensureThaiWheelPickerModal();
            const current = parseDateInputValue(input.value) || getClientTodayLocal();
            picker.open(current, (picked) => {
                if (input.dataset.noPast === '1' && normalizeDateOnly(picked) < getClientTodayLocal()) {
                    return;
                }
                input.value = toYmdCE(picked);
                syncDisplay();
                input.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                input.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            });
        };

        displayInput.addEventListener('click', openWheel);

        if (input.id) {
            const btn = document.querySelector(`[data-open-picker][data-target="${input.id}"]`);
            if (btn && btn.dataset.nativePickerBound !== '1') {
                btn.dataset.nativePickerBound = '1';
                btn.addEventListener('click', openWheel);
            }
        }
        input.dataset.nativeDateReady = '1';
    }

    window.initNativeMobileDateInput = initNativeMobileDateInput;

    function appendFlatpickrHook(instance, hook, callback) {
        if (!instance || typeof callback !== 'function') return;
        const current = instance.config?.[hook];
        if (Array.isArray(current)) {
            if (!current.includes(callback)) current.push(callback);
            return;
        }
        if (typeof current === 'function') {
            instance.config[hook] = [current, callback];
            return;
        }
        instance.config[hook] = [callback];
    }

    function bindEndDateMinFromStart(startInput, endInput, key) {
        if (!(startInput instanceof HTMLElement) || !(endInput instanceof HTMLElement)) {
            return;
        }

        const retryKey = `__dateRangeRetry_${key}`;
        const startReady = !!startInput._flatpickr;
        const endReady = !!endInput._flatpickr;
        if (!startReady || !endReady) {
            const retries = Number(endInput[retryKey] || 0);
            if (retries >= 60) return;
            endInput[retryKey] = String(retries + 1);
            setTimeout(() => bindEndDateMinFromStart(startInput, endInput, key), 80);
            return;
        }
        delete endInput[retryKey];

        const updaterKey = `__dateRangeUpdater_${key}`;
        if (!endInput[updaterKey]) {
            endInput[updaterKey] = () => {
                const fpEnd = endInput._flatpickr;
                if (!fpEnd) return;

                const startDate = startInput._flatpickr?.selectedDates?.[0] ||
                    parseDateInputValue(startInput.value) ||
                    parseDateInputValue(startInput._flatpickr?.altInput?.value);
                const minDate = startDate
                    ? normalizeDateOnly(startDate)
                    : (endInput.dataset.noPast === '1' ? getClientTodayLocal() : null);

                fpEnd.set('minDate', minDate || null);

                const endDate = fpEnd.selectedDates?.[0] ||
                    parseDateInputValue(endInput.value) ||
                    parseDateInputValue(fpEnd.altInput?.value);
                if (endDate && minDate && normalizeDateOnly(endDate) < minDate) {
                    fpEnd.clear();
                    endInput.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            };

            startInput.addEventListener('change', endInput[updaterKey]);
            startInput.addEventListener('input', endInput[updaterKey]);
        }

        const hookKey = `${updaterKey}_hooked`;
        if (!endInput[hookKey]) {
            const fpStart = startInput._flatpickr;
            if (fpStart) {
                appendFlatpickrHook(fpStart, 'onChange', endInput[updaterKey]);
                appendFlatpickrHook(fpStart, 'onValueUpdate', endInput[updaterKey]);
            }

            const fpEnd = endInput._flatpickr;
            if (fpEnd) {
                appendFlatpickrHook(fpEnd, 'onOpen', endInput[updaterKey]);
                fpEnd.altInput?.addEventListener('focus', endInput[updaterKey]);
                fpEnd.altInput?.addEventListener('click', endInput[updaterKey]);
            }

            endInput[hookKey] = '1';
        }

        endInput[updaterKey]();
    }

    function bindProjectDatePairs() {
        if (isProjectMobileWheelDateMode()) {
            const bindNativeRange = (startInput, endInput) => {
                if (!(startInput instanceof HTMLInputElement) || !(endInput instanceof HTMLInputElement)) return;
                const update = () => {
                    const startDate = parseDateInputValue(startInput.value);
                    if (startDate) {
                        endInput.min = toYmdCE(normalizeDateOnly(startDate));
                    } else if (endInput.dataset.noPast === '1') {
                        endInput.min = toYmdCE(getClientTodayLocal());
                    } else {
                        endInput.removeAttribute('min');
                    }
                    const endDate = parseDateInputValue(endInput.value);
                    if (startDate && endDate && normalizeDateOnly(endDate) < normalizeDateOnly(startDate)) {
                        endInput.value = '';
                        endInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                };
                startInput.addEventListener('change', update);
                startInput.addEventListener('input', update);
                endInput.addEventListener('change', update);
                endInput.addEventListener('input', update);
                update();
            };

            bindNativeRange(
                document.querySelector('input[name="team_start_date"]'),
                document.querySelector('input[name="team_end_date"]')
            );
            bindNativeRange(
                document.querySelector('input[name="start_date"]'),
                document.querySelector('input[name="end_date"]')
            );
            document.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
                const endName = startInput.name.replace('[start_date]', '[end_date]');
                const endInput = document.querySelector(`input[name="${endName}"]`);
                bindNativeRange(startInput, endInput);
            });
            return;
        }

        bindEndDateMinFromStart(
            document.querySelector('input[name="team_start_date"]'),
            document.querySelector('input[name="team_end_date"]'),
            'team'
        );

        bindEndDateMinFromStart(
            document.querySelector('input[name="start_date"]'),
            document.querySelector('input[name="end_date"]'),
            'contract'
        );

        document.querySelectorAll('input[name^="fte_roles["][name$="[start_date]"]').forEach((startInput) => {
            const endName = startInput.name.replace('[start_date]', '[end_date]');
            const endInput = document.querySelector(`input[name="${endName}"]`);
            bindEndDateMinFromStart(startInput, endInput, `fte_${startInput.name}`);
        });
    }

    /* ---------- DATE BE INIT ---------- */
    function initDateBE(scope = document) {
        const toDmyCE = (date) => {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
            const dd = String(date.getDate()).padStart(2, '0');
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const yyyy = String(date.getFullYear());
            return `${dd}-${mm}-${yyyy}`;
        };

        scope.querySelectorAll('.js-date-be').forEach(input => {
            if (isProjectMobileWheelDateMode()) {
                initNativeMobileDateInput(input);
                return;
            }

            // ❗ กัน init ซ้ำ
            if (input._flatpickr) return;

            // รองรับค่าที่อาจเป็น พ.ศ. หรือรูปแบบเก่าจาก old()/autosave
            // ให้ normalize เป็น d-m-Y (ค.ศ.) ก่อน init picker
            const normalized = toDmyCE(parseDateInputValue(input.value));
            if (normalized) {
                input.value = normalized;
            }

            const noPast = input.dataset.noPast === '1';

            const fp = flatpickr(input, {
                locale: 'th',
                dateFormat: 'd-m-Y', // ✅ ค่าจริง (ค.ศ.)
                altInput: true,
                altFormat: 'j F Y', // ใช้ตอน init เท่านั้น
                disableMobile: true,
                ...(noPast ? {
                    minDate: getClientTodayLocal()
                } : {}),

                onReady(selectedDates, __, inst) {
                    if (noPast) {
                        inst.set('minDate', getClientTodayLocal());
                    }
                    syncAltStyle(input, inst);
                    forceHeaderBE(inst);
                    if (inst.altInput && selectedDates && selectedDates[0]) {
                        const d = selectedDates[0];
                        const day = d.getDate();
                        const month = d.toLocaleDateString('th-TH', {
                            month: 'long'
                        });
                        const year = d.getFullYear() + 543;
                        inst.altInput.value = `${day} ${month} ${year}`;
                    }
                },

                onOpen(_, __, inst) {
                    if (noPast) {
                        inst.set('minDate', getClientTodayLocal());
                    }
                    forceHeaderBE(inst);
                },

                onMonthChange(_, __, inst) {
                    setTimeout(() => forceHeaderBE(inst), 0);
                },

                onYearChange(_, __, inst) {
                    setTimeout(() => forceHeaderBE(inst), 0);
                },

                // ✅ จุดสำคัญอยู่ตรงนี้
                onChange(selectedDates, dateStr, inst) {
                    forceHeaderBE(inst);

                    if (!inst.altInput || !selectedDates[0]) return;

                    const d = selectedDates[0];

                    const day = d.getDate();
                    const month = d.toLocaleDateString('th-TH', {
                        month: 'long'
                    });
                    const year = d.getFullYear() + 543;

                    // 🔥 บังคับค่าแสดงผลเป็น พ.ศ.
                    inst.altInput.value = `${day} ${month} ${year}`;
                }
            });

            /* ✅ ปุ่ม 📅 เปิด input ของตัวเอง */
            const btn = scope.querySelector(
                `[data-open-picker][data-target="${input.id}"]`
            );

            if (btn) {
                btn.addEventListener('click', () => fp.open());
            }
        });

        bindProjectDatePairs();
    }

    function syncFlatpickrRawValue(input) {
        if (!(input instanceof HTMLInputElement)) return;

        const fp = input._flatpickr;
        if (!fp) return;

        // Always prefer the visible text field near this input first.
        const scope = input.closest('label, .relative, .w-full, .grid, .space-y-2') || input.parentElement;
        const visibleInput = scope?.querySelector?.('input:not([name]):not([type="hidden"])');
        const visibleRaw = (visibleInput?.value || '').trim();

        let picked = parseDateInputValue(visibleRaw);
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            const altRaw = (fp.altInput?.value || '').trim();
            picked = parseDateInputValue(altRaw);
        }
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            picked = fp.selectedDates?.[0];
        }
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            picked = parseDateInputValue((input.value || '').trim());
        }
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) return;

        const day = String(picked.getDate()).padStart(2, '0');
        const month = String(picked.getMonth() + 1).padStart(2, '0');
        const year = String(picked.getFullYear());
        input.value = `${day}-${month}-${year}`;
    }

    function syncNativeWheelRawValue(input) {
        if (!(input instanceof HTMLInputElement)) return;
        if (!input.classList.contains('native-date-raw')) return;

        const holder = input.closest('.relative') || input.parentElement;
        const displayInput = holder?.querySelector?.(`input[data-native-date-display-for="${CSS.escape(input.name || '')}"]`);

        let picked = parseDateInputValue((displayInput?.value || '').trim());
        if (!(picked instanceof Date) || Number.isNaN(picked.getTime())) {
            picked = parseDateInputValue((input.value || '').trim());
        }

        input.value = (picked instanceof Date && !Number.isNaN(picked.getTime()))
            ? toYmdCE(picked)
            : '';
    }

    function syncDateInputsBeforeSubmit(form) {
        if (!(form instanceof HTMLFormElement)) return;
        form.querySelectorAll('input.thai-datepicker, input.js-date-be').forEach((input) => {
            syncFlatpickrRawValue(input);
            syncNativeWheelRawValue(input);
        });
    }

    document.addEventListener('submit', (event) => {
        syncDateInputsBeforeSubmit(event.target);
    }, true);


    /* ---------- ทำให้ altInput หน้าตาเหมือน input เดิม ---------- */
    function syncAltStyle(originalInput, inst) {
        if (!inst.altInput) return;

        // copy class
        inst.altInput.className = originalInput.className;

        // placeholder
        const ph = originalInput.getAttribute('placeholder') || originalInput.dataset.placeholder;
        if (ph) inst.altInput.placeholder = ph;

        inst.altInput.readOnly = true;

        // hide the original input to avoid duplicate fields (mobile bug)
        originalInput.classList.add('flatpickr-hidden');
        originalInput.setAttribute('aria-hidden', 'true');
        originalInput.tabIndex = -1;
        originalInput.style.display = 'none';
    }

    /* ---------- บังคับ header เป็น พ.ศ. ทุกกรณี ---------- */
    function forceHeaderBE(inst) {
        const cal = inst.calendarContainer;
        if (!cal) return;

        // year input
        const yearInput = cal.querySelector('.flatpickr-current-month input.cur-year');
        if (yearInput) {
            const ce = parseInt(yearInput.value || '0', 10);
            if (ce && ce < 2400) yearInput.value = ce + 543;

            if (!yearInput._beBound) {
                yearInput._beBound = true;
                yearInput.addEventListener('blur', () => {
                    const be = parseInt(yearInput.value || '0', 10);
                    if (be > 2400) {
                        inst.currentYear = be - 543;
                        inst.redraw();
                        setTimeout(() => forceHeaderBE(inst), 0);
                    }
                });
            }
        }

        // prev / next
        ['.flatpickr-prev-month', '.flatpickr-next-month'].forEach(sel => {
            const btn = cal.querySelector(sel);
            if (!btn || btn._beBound) return;
            btn._beBound = true;
            btn.addEventListener('click', () =>
                setTimeout(() => forceHeaderBE(inst), 0)
            );
        });
    }
</script>

<style>
    /* highlight แถวที่เพิ่มล่าสุด */
    .repeatable-highlight {
        animation: flash-bg 1.2s ease-out;
        background-color: #eff6ff;
    }

    @keyframes flash-bg {
        0% {
            background-color: #dbeafe;
        }

        100% {
            background-color: transparent;
        }
    }

    /* collapse rows when adding new entries */
    .row-collapsible {
        position: relative;
    }

    .row-collapse-toggle {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 5;
        font-size: 0.75rem;
        line-height: 1rem;
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        padding: 0.15rem 0.6rem;
        border-radius: 9999px;
    }

    .row-collapse-toggle-with-remove {
        right: 0.5rem;
    }

    .row-collapsed> :not(.row-collapse-toggle) {
        display: none !important;
    }

    .row-collapsed>.row-collapse-summary {
        display: flex !important;
    }

    /* ซ่อนปุ่มลูกศร < > บน Flatpickr */
    .flatpickr-calendar .flatpickr-prev-month,
    .flatpickr-calendar .flatpickr-next-month {
        display: none !important;
    }

    .row-collapse-summary {
        width: 100%;
        display: none;
        white-space: normal;
        overflow: hidden;
        text-overflow: clip;
        flex-wrap: wrap;
        gap: 0.5rem;
        word-break: break-word;
        padding-right: 6.5rem;
        grid-column: 1 / -1;
    }

    .row-collapse-summary .summary-chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        overflow-wrap: anywhere;
        margin-right: 0.75rem;
        color: #0f172a;
    }

    .row-collapse-summary .summary-muted {
        color: #64748b;
    }

    .row-collapse-summary-text {
        flex: 1 1 auto;
        min-width: 0;
    }

    .row-collapse-remove {
        flex: 0 0 auto;
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fff1f2;
        border-radius: 9999px;
        padding: 0.1rem 0.55rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .row-collapsible.has-inline-remove {
        padding-bottom: 2rem;
    }

    .row-collapsible.has-inline-remove [data-repeatable-remove].repeatable-remove-text {
        position: absolute;
        right: 0.5rem;
        bottom: 0.5rem;
        z-index: 6;
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fff1f2;
        border-radius: 9999px;
        padding: 0.1rem 0.55rem;
    }

    .row-collapsed .row-collapse-remove {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        z-index: 6;
    }

    .row-collapsed .row-collapse-toggle,
    .row-collapsed .row-collapse-toggle-with-remove {
        top: 0.5rem;
        right: 0.5rem;
    }

    .row-collapsed {
        min-height: 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
        box-sizing: border-box;
        padding-bottom: 2rem;
    }

    /* project create/edit modern buttons */
    .project-form button[data-repeatable-add],
    .project-form .add-author-btn,
    .project-form .add-conf-btn,
    .project-form button[onclick*="addIRBCode"],
    .project-form button[onclick*="addCopiRow"],
    .project-form button[onclick*="addCoResearchRow"],
    .project-form button[onclick*="addFTERow"],
    .project-form button[onclick*="addOutputRow"] {
        border-radius: 0.75rem;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .project-form button[data-repeatable-add]:hover,
    .project-form .add-author-btn:hover,
    .project-form .add-conf-btn:hover,
    .project-form button[onclick*="addIRBCode"]:hover,
    .project-form button[onclick*="addCopiRow"]:hover,
    .project-form button[onclick*="addCoResearchRow"]:hover,
    .project-form button[onclick*="addFTERow"]:hover,
    .project-form button[onclick*="addOutputRow"]:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    }

    .project-form button[type="submit"] {
        border-radius: 0.9rem;
        box-shadow: 0 10px 26px rgba(37, 99, 235, 0.2);
    }

    .project-form .repeatable-remove-text {
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 600;
        color: #b91c1c;
    }

    .row-collapse-summary {
        padding: 0.4rem 0.8rem;
    }

    .no-row-collapse-toggle .row-collapse-toggle,
    .no-row-collapse-toggle .row-collapse-summary {
        display: none !important;
    }

    @media (max-width: 640px) {
        .row-collapse-summary {
            padding-right: 3.5rem;
        }

        .row-collapse-toggle {
            top: 0.35rem;
            right: 0.35rem;
        }

        .row-collapse-toggle-with-remove {
            right: 0.35rem;
        }

        .row-collapsed .row-collapse-remove {
            bottom: 0.35rem;
            right: 0.35rem;
        }

        .row-collapsible.has-inline-remove [data-repeatable-remove].repeatable-remove-text {
            right: 0.35rem;
            bottom: 0.35rem;
        }

        .row-collapsed .row-collapse-toggle,
        .row-collapsed .row-collapse-toggle-with-remove {
            top: 0.35rem;
            right: 0.35rem;
        }
    }

    .fte-tools {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        width: 100%;
    }

    .fte-tools__search {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.9rem;
    }

    .fte-tools__table-btn {
        border: 1px solid #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 0.5rem;
        padding: 0.5rem 0.85rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
    }

    .fte-tools__table-btn.is-active {
        background: #dbeafe;
    }

    .fte-tools__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 0 0 auto;
    }

    .fte-tools__clear-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
    }

    @media (min-width: 768px) {
        .fte-tools {
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
        }

        .fte-tools__search {
            width: auto;
            flex: 1 1 auto;
            min-width: 0;
        }

        #fte-container.fte-cards-hidden {
            position: absolute !important;
            left: -99999px !important;
            top: auto !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .fte-table-wrap {
            display: none;
            margin-bottom: 1rem;
            border: 1px solid #dbeafe;
            border-radius: 0.75rem;
            overflow: visible;
            background: #ffffff;
        }

        .fte-table-wrap.is-active {
            display: block;
        }

        .fte-table-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .fte-table-grid thead th {
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.5rem;
            border-bottom: 1px solid #bfdbfe;
            text-align: left;
            white-space: normal;
            position: sticky;
            top: 0;
            z-index: 1;
            line-height: 1.2;
        }

        .fte-table-grid tbody td {
            border-top: 1px solid #e2e8f0;
            padding: 0.4rem;
            vertical-align: middle;
        }

        .fte-table-grid input,
        .fte-table-grid select {
            width: 100%;
            min-width: 0;
            border: 1px solid #cbd5e1;
            border-radius: 0.4rem;
            padding: 0.38rem 0.5rem;
            font-size: 0.9rem;
            background: #fff;
            box-sizing: border-box;
        }

        .fte-table-grid .fte-table-index {
            text-align: center;
            color: #1d4ed8;
            font-weight: 700;
            width: 48px;
        }

        .fte-table-grid .fte-table-remove-btn {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fff5f5;
            border-radius: 0.4rem;
            padding: 0.35rem 0.5rem;
            font-size: 0.75rem;
            width: 100%;
            white-space: nowrap;
        }

        .fte-table-grid td:nth-child(9) input {
            font-size: 0.84rem;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }

        .fte-table-grid td:nth-child(10) input {
            font-size: 0.82rem;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-variant-numeric: tabular-nums;
        }

        .fte-table-grid td:nth-child(3) select,
        .fte-table-grid td:nth-child(4) select {
            font-size: 0.84rem;
        }

        .fte-table-grid td:nth-child(5) input,
        .fte-table-grid td:nth-child(6) input {
            font-size: 0.86rem;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-variant-numeric: tabular-nums;
        }

        .fte-table-empty {
            text-align: center;
            color: #64748b;
            padding: 1rem;
            font-size: 0.85rem;
        }

        .fte-table-grid input[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        .project-form {
            max-width: 96rem !important;
        }

        .project-form,
        .project-form input,
        .project-form select,
        .project-form textarea,
        .project-form button {
            font-size: 1rem;
        }

        .project-form h2 {
            font-size: 1.75rem;
        }

        .project-form h3 {
            font-size: 1.35rem;
        }
    }
</style>

<script>
    (function() {
        const COLLAPSIBLE_SELECTOR =
            '[data-repeatable-row], .irb-row, .copi-row, .coresearch-row, .fte-row, .output-row';
        const isProjectCreatePage = () => /^\/rm\/create\/?$/.test(window.location?.pathname || '');
        const isMobileViewport = () => window.matchMedia('(max-width: 640px)').matches;
        const disableRowCollapse = () => window.disableRowCollapse === true;
        const disableByRoute = () => {
            const path = window.location?.pathname || '';
            return /\/fte\/.+\/detail$/.test(path) || /\/fte\/project\/.+/.test(path);
        };
        const isCollapseDisabled = () =>
            disableRowCollapse() ||
            disableByRoute() ||
            document.body.classList.contains('no-row-collapse-toggle');
        const isRowCollapseLocked = () => false;

        function clearRowCollapseUi(row) {
            if (!row) return;
            row.querySelectorAll('.row-collapse-toggle, .row-collapse-summary').forEach((el) => el.remove());
            row.classList.remove('row-collapsible', 'row-collapsed');
            row.dataset.collapsed = '0';
        }

        function getSiblings(row) {
            if (!row) return [];
            const repeatable = row.closest('[data-repeatable]');
            if (repeatable) {
                const container = document.getElementById(repeatable.dataset.container);
                if (!container) return [];
                return Array.from(container.querySelectorAll(COLLAPSIBLE_SELECTOR));
            }

            const customContainer = row.closest(
                '#irb-code-container, #copi-container, #coresearch-container, #fte-container, #output-container'
            );
            if (customContainer) {
                return Array.from(customContainer.querySelectorAll(COLLAPSIBLE_SELECTOR));
            }

            const parent = row.parentElement;
            if (!parent) return [];
            return Array.from(parent.querySelectorAll(COLLAPSIBLE_SELECTOR))
                .filter(el => el.parentElement === parent);
        }

        function updateToggleLabel(row) {
            const btn = row.querySelector('.row-collapse-toggle');
            if (!btn) return;
            const isCollapsed = row.dataset.collapsed === '1';
            btn.textContent = isCollapsed ? 'ขยายแถว' : 'ย่อแถว';
        }

        function setCollapsed(row, collapsed) {
            if (isRowCollapseLocked(row)) {
                clearRowCollapseUi(row);
                updateSummary(row);
                return;
            }
            if (collapsed && row?.dataset?.forceOpen === '1') {
                collapsed = false;
            }
            updateSummary(row);
            row.dataset.collapsed = collapsed ? '1' : '0';
            row.classList.toggle('row-collapsed', collapsed);
            updateToggleLabel(row);
        }

        function ensureToggle(row) {
            if (isCollapseDisabled()) return;
            if (isRowCollapseLocked(row)) {
                clearRowCollapseUi(row);
                return;
            }
            if (row.querySelector('.row-collapse-toggle')) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'row-collapse-toggle';
            if (row.querySelector('[data-repeatable-remove]')) {
                btn.classList.add('row-collapse-toggle-with-remove');
            }
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (row.dataset.forceOpen === '1') {
                    setCollapsed(row, false);
                    return;
                }
                setCollapsed(row, row.dataset.collapsed !== '1');
            });
            row.classList.add('row-collapsible');
            row.appendChild(btn);
            ensureSummary(row);
            updateToggleLabel(row);
        }

        function ensureSummary(row) {
            if (isCollapseDisabled()) return;
            if (document.body.classList.contains('no-row-collapse-summary')) return;
            if (isMobileViewport() && !window.enableRowCollapseSummaryOnMobile) return;
            if (row.querySelector('.row-collapse-summary')) return;
            const summary = document.createElement('div');
            summary.className = 'row-collapse-summary text-sm text-slate-700 mt-2 hidden';
            const text = document.createElement('div');
            text.className = 'row-collapse-summary-text';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'row-collapse-remove';
            removeBtn.textContent = 'ลบ';
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const targetRemove = row.querySelector('[data-repeatable-remove]');
                if (targetRemove) targetRemove.click();
            });
            summary.appendChild(text);
            summary.appendChild(removeBtn);
            row.appendChild(summary);
        }

        function getFieldValue(row, selectors) {
            for (const sel of selectors) {
                const el = row.querySelector(sel);
                if (!el) continue;
                if (el._flatpickr?.altInput?.value) {
                    const altVal = el._flatpickr.altInput.value.trim();
                    if (altVal) return altVal;
                }
                if (el.tagName === 'SELECT') {
                    const text = el.options[el.selectedIndex]?.textContent?.trim();
                    if (text) return text;
                }
                const value = el.value?.trim();
                if (value) return maybeFormatDateForSummary(el, value);
            }
            return '';
        }

        function toBuddhistNumericDate(value) {
            const raw = (value ?? '').toString().trim();
            const dmy = raw.match(/^(\d{1,2})([/-])(\d{1,2})\2(\d{4})$/);
            const ymd = raw.match(/^(\d{4})([/-])(\d{1,2})\2(\d{1,2})$/);
            const thaiText = raw.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/);
            if (!dmy && !ymd && !thaiText) return raw;

            let day;
            let month;
            let year;

            if (dmy) {
                day = parseInt(dmy[1], 10);
                month = parseInt(dmy[3], 10);
                year = parseInt(dmy[4], 10);
            } else if (ymd) {
                year = parseInt(ymd[1], 10);
                month = parseInt(ymd[3], 10);
                day = parseInt(ymd[4], 10);
            } else {
                const monthMap = {
                    'มกราคม': 1,
                    'ม.ค.': 1,
                    'ม.ค': 1,
                    'กุมภาพันธ์': 2,
                    'ก.พ.': 2,
                    'ก.พ': 2,
                    'มีนาคม': 3,
                    'มี.ค.': 3,
                    'มี.ค': 3,
                    'เมษายน': 4,
                    'เม.ย.': 4,
                    'เม.ย': 4,
                    'พฤษภาคม': 5,
                    'พ.ค.': 5,
                    'พ.ค': 5,
                    'มิถุนายน': 6,
                    'มิ.ย.': 6,
                    'มิ.ย': 6,
                    'กรกฎาคม': 7,
                    'ก.ค.': 7,
                    'ก.ค': 7,
                    'สิงหาคม': 8,
                    'ส.ค.': 8,
                    'ส.ค': 8,
                    'กันยายน': 9,
                    'ก.ย.': 9,
                    'ก.ย': 9,
                    'ตุลาคม': 10,
                    'ต.ค.': 10,
                    'ต.ค': 10,
                    'พฤศจิกายน': 11,
                    'พ.ย.': 11,
                    'พ.ย': 11,
                    'ธันวาคม': 12,
                    'ธ.ค.': 12,
                    'ธ.ค': 12,
                };

                day = parseInt(thaiText[1], 10);
                month = monthMap[(thaiText[2] || '').trim()] ?? NaN;
                year = parseInt(thaiText[3], 10);
            }

            if (
                Number.isNaN(day) || Number.isNaN(month) || Number.isNaN(year) ||
                day < 1 || day > 31 || month < 1 || month > 12
            ) {
                return raw;
            }

            if (year < 2400) year += 543;
            const dayText = String(day).padStart(2, '0');
            const monthText = String(month).padStart(2, '0');
            return `${dayText}-${monthText}-${year}`;
        }

        function toBuddhistFromDateLike(value) {
            const raw = (value ?? '').toString().trim();
            if (!raw) return '';
            const parsed = new Date(raw);
            if (Number.isNaN(parsed.getTime())) return '';
            const day = String(parsed.getDate()).padStart(2, '0');
            const month = String(parsed.getMonth() + 1).padStart(2, '0');
            const year = String(parsed.getFullYear() + 543);
            return `${day}-${month}-${year}`;
        }

        function maybeFormatDateForSummary(el, value) {
            const isDateInput = el.classList?.contains('thai-datepicker') || el.classList?.contains('js-date-be');
            if (!isDateInput) return value;
            return toBuddhistNumericDate(value);
        }

        function formatSummaryDateText(value) {
            const raw = (value ?? '').toString().trim();
            if (!raw) return '';
            const normalized = toBuddhistNumericDate(raw);
            if (normalized !== raw) return normalized;
            return toBuddhistFromDateLike(raw) || raw;
        }

        function normalizeSummaryDateForRowInput(inputEl) {
            if (!inputEl) return '';
            const altRaw = inputEl._flatpickr?.altInput?.value?.trim();
            if (altRaw) return altRaw;

            const visibleAltInLabel = inputEl.closest('label')?.querySelector?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (
                visibleAltInLabel &&
                visibleAltInLabel !== inputEl &&
                (visibleAltInLabel.value || '').trim() !== ''
            ) {
                return visibleAltInLabel.value.trim();
            }

            const raw = (inputEl.value ?? '').toString().trim();
            if (!raw) return '';

            const dmy = raw.match(/^(\d{1,2})([/-])(\d{1,2})\2(\d{4})$/);
            if (dmy) {
                const day = String(parseInt(dmy[1], 10)).padStart(2, '0');
                const month = String(parseInt(dmy[3], 10)).padStart(2, '0');
                let year = parseInt(dmy[4], 10);
                if (year < 2400) year += 543;
                return `${day}-${month}-${year}`;
            }

            const ymd = raw.match(/^(\d{4})([/-])(\d{1,2})\2(\d{1,2})$/);
            if (ymd) {
                let year = parseInt(ymd[1], 10);
                const month = String(parseInt(ymd[3], 10)).padStart(2, '0');
                const day = String(parseInt(ymd[4], 10)).padStart(2, '0');
                if (year < 2400) year += 543;
                return `${day}-${month}-${year}`;
            }

            return raw;
        }

        function hasName(row, matcher) {
            return Array.from(row.querySelectorAll('[name]')).some(el => matcher(el.name, el));
        }

        function getValueFromEl(el) {
            if (!el) return '';
            if (el._flatpickr?.altInput?.value) {
                const altVal = el._flatpickr.altInput.value.trim();
                if (altVal) return altVal;
            }
            if (el.tagName === 'SELECT') {
                const text = el.options[el.selectedIndex]?.textContent?.trim();
                if (text) return text;
            }
            return maybeFormatDateForSummary(el, (el.value ?? '').toString().trim());
        }

        function matchKey(name) {
            const m = name.match(/^([a-zA-Z_]+)\[(\d+)\]\[([^\]]+)\]/);
            if (!m) return null;
            return {
                root: m[1],
                index: m[2],
                field: m[3]
            };
        }

        function getIndexFromId(row, prefix) {
            const id = row.getAttribute('id') || '';
            const m = id.match(new RegExp(`^${prefix}-(\\d+)$`));
            return m ? m[1] : null;
        }

        function getGroupValues(row, root, index) {
            const result = {};
            row.querySelectorAll('[name]').forEach(el => {
                const meta = matchKey(el.name);
                if (!meta || meta.root !== root || meta.index !== index) return;
                result[meta.field] = getValueFromEl(el);
            });
            return result;
        }

        function getFieldValueByName(row, matcher) {
            const els = row.querySelectorAll('[name]');
            for (const el of els) {
                if (matcher(el.name, el)) {
                    const val = getValueFromEl(el);
                    if (val) return val;
                }
            }
            return '';
        }

        function getFieldElementByName(row, matcher) {
            const els = row.querySelectorAll('[name]');
            for (const el of els) {
                if (matcher(el.name, el)) return el;
            }
            return null;
        }

        function getDateSummaryFromElement(el) {
            if (!el) return '';
            const altRaw = el._flatpickr?.altInput?.value?.trim();
            if (altRaw) {
                return altRaw;
            }
            const picked = el._flatpickr?.selectedDates?.[0];
            if (picked instanceof Date && !Number.isNaN(picked.getTime())) {
                const day = String(picked.getDate()).padStart(2, '0');
                const month = String(picked.getMonth() + 1).padStart(2, '0');
                const year = picked.getFullYear() + 543;
                return `${day}-${month}-${year}`;
            }
            // Do not fallback to raw input value here; raw hidden value can be stale and
            // differ from the visible date text shown to users.
            return '';
        }

        function pickBestDateInput(inputs) {
            if (!inputs || inputs.length === 0) return null;

            const scored = inputs.map((el) => {
                let score = 0;
                if ((el.type || '').toLowerCase() !== 'hidden') score += 4;
                if (el.classList?.contains('thai-datepicker') || el.classList?.contains('js-date-be')) score += 3;
                if (el._flatpickr) score += 2;
                if ((el.value || '').trim()) score += 1;
                return {
                    el,
                    score
                };
            });

            scored.sort((a, b) => b.score - a.score);
            return scored[0]?.el || inputs[0];
        }

        function getVisibleDateNearInput(input, row) {
            if (!input) return '';

            const nearby = input.closest('label, .relative, .w-full, .grid, .space-y-2') || input.parentElement || row;
            const visibleCandidates = nearby?.querySelectorAll?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (!visibleCandidates || visibleCandidates.length === 0) return '';

            for (const candidate of visibleCandidates) {
                if (candidate === input) continue;
                const raw = (candidate.value || '').trim();
                if (raw) return raw;
            }
            return '';
        }

        function getProjectCardDateValue(row, root, field) {
            const inputs = Array.from(row.querySelectorAll(`input[name^="${root}["][name$="[${field}]"]`));
            if (!inputs.length) return '';

            const primary = pickBestDateInput(inputs);
            const orderedInputs = [primary, ...inputs.filter((el) => el !== primary)];

            for (const input of orderedInputs) {
                const fromPicker = getDateSummaryFromElement(input);
                if (fromPicker) return formatSummaryDateText(fromPicker);

                const nearVisible = getVisibleDateNearInput(input, row);
                if (nearVisible) return formatSummaryDateText(nearVisible);

                const normalized = normalizeSummaryDateForRowInput(input);
                if (normalized) return formatSummaryDateText(normalized);
            }

            return '';
        }

        function findLabelByText(row, text) {
            const labels = Array.from(row.querySelectorAll('label'));
            return labels.find((label) => (label.textContent || '').includes(text)) || null;
        }

        function getDateTextFromLabel(label, fallbackInput) {
            if (!label) return '';

            const visibleFlatpickr = label.querySelector(
                'input:not([name]):not([type="hidden"])'
            );
            if (visibleFlatpickr && (visibleFlatpickr.value || '').trim()) {
                return formatSummaryDateText(visibleFlatpickr.value.trim());
            }

            if (fallbackInput) {
                const fromPicker = fallbackInput._flatpickr?.altInput?.value?.trim();
                if (fromPicker) return formatSummaryDateText(fromPicker);
                const normalized = normalizeSummaryDateForRowInput(fallbackInput);
                if (normalized) return formatSummaryDateText(normalized);
            }

            const anyVisibleInput = label.querySelector(
                'input:not([name]):not([type="hidden"])'
            );
            if (anyVisibleInput && (anyVisibleInput.value || '').trim()) {
                return formatSummaryDateText(anyVisibleInput.value.trim());
            }

            return '';
        }

        function getVisibleFlatpickrDateInputs(row) {
            if (!row) return [];
            return Array.from(
                row.querySelectorAll('input:not([name]):not([type="hidden"])')
            ).filter((el) => {
                const val = (el.value || '').trim();
                if (!val) return false;
                if (el.offsetParent !== null) return true;
                const style = window.getComputedStyle(el);
                return style.display !== 'none' && style.visibility !== 'hidden';
            });
        }

        function extractDisplayedDateText(inputEl) {
            if (!inputEl) return '';
            const fromPicker = inputEl._flatpickr?.altInput?.value?.trim();
            if (fromPicker) return formatSummaryDateText(fromPicker);

            const nearVisible = inputEl.closest('label, .relative, .w-full, .grid, .space-y-2')?.querySelector?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (nearVisible && (nearVisible.value || '').trim()) {
                return formatSummaryDateText(nearVisible.value.trim());
            }

            const raw = (inputEl.value || '').trim();
            return raw ? formatSummaryDateText(raw) : '';
        }

        function cacheProjectRowDateSummary(row) {
            if (!row) return;

            const fteStartInput = row.querySelector('input[name^="fte_roles["][name$="[start_date]"]');
            const fteEndInput = row.querySelector('input[name^="fte_roles["][name$="[end_date]"]');
            if (fteStartInput || fteEndInput) {
                row.dataset.fteStartDisplay = extractDisplayedDateText(fteStartInput);
                row.dataset.fteEndDisplay = extractDisplayedDateText(fteEndInput);
            }

            const irbExpInput = row.querySelector('input[name^="irbs["][name$="[exp_date]"]');
            if (irbExpInput) {
                row.dataset.irbExpDisplay = extractDisplayedDateText(irbExpInput);
            }
        }

        function getStrictDisplayedDateForNamedInput(row, selector) {
            const input = row.querySelector(selector);
            if (!input) return '';

            const altRaw = input._flatpickr?.altInput?.value?.trim();
            if (altRaw) return formatSummaryDateText(altRaw);

            const scope = input.closest('label, .relative, .w-full, .grid, .space-y-2') || input.parentElement || row;
            const visible = scope.querySelector('input:not([name]):not([type="hidden"])');
            if (visible && (visible.value || '').trim()) {
                return formatSummaryDateText(visible.value.trim());
            }

            return '';
        }

        function getDisplayDateFromInput(inputEl) {
            if (!inputEl) return '';

            const altRaw = inputEl._flatpickr?.altInput?.value?.trim();
            if (altRaw) return altRaw;

            const visibleInLabel = inputEl.closest('label')?.querySelector?.(
                'input:not([name]):not([type="hidden"])'
            );
            if (visibleInLabel && (visibleInLabel.value || '').trim() !== '') {
                return visibleInLabel.value.trim();
            }

            return normalizeSummaryDateForRowInput(inputEl);
        }

        function updateSummary(row) {
            const summary = row.querySelector('.row-collapse-summary');
            if (!summary) return;
            const summaryText = summary.querySelector('.row-collapse-summary-text');
            if (!summaryText) return;
            cacheProjectRowDateSummary(row);

            const siblings = getSiblings(row);
            const index = Math.max(1, siblings.indexOf(row) + 1);
            const prefix = `#${index}`;

            if (row.classList.contains('irb-row') || hasName(row, (n) => n.startsWith('irbs['))) {
                let idx = getIndexFromId(row, 'irb');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach(el => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'irbs') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'irbs', idx) : {};
                const code = values.code || '-';
                const source = values.source || '-';
                const exp = getStrictDisplayedDateForNamedInput(row, 'input[name^="irbs["][name$="[exp_date]"]') ||
                    (row.dataset.irbExpDisplay || '').trim() ||
                    '-';
                summaryText.innerHTML = `
        <span class="summary-chip">${prefix} • IRB รหัส ${code}</span>
        <span class="summary-chip summary-muted">หน่วยงาน ${source}</span>
        <span class="summary-chip summary-muted">หมดอายุ ${exp}</span>
      `;
                return;
            }

            if (row.classList.contains('copi-row') || hasName(row, (n) => n.startsWith('copis['))) {
                let idx = getIndexFromId(row, 'copi');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach(el => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'copis') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'copis', idx) : {};
                const name = values.name || 'ไม่ระบุชื่อ';
                const aff = values.affiliation || '-';
                summaryText.innerHTML = `
        <span class="summary-chip">${prefix} • COPI ชื่อ ${name}</span>
        <span class="summary-chip summary-muted">สังกัด ${aff}</span>
      `;
                return;
            }

            if (row.classList.contains('coresearch-row') || hasName(row, (n) => n.startsWith('coresearch['))) {
                let idx = getIndexFromId(row, 'coresearch');
                if (idx === null) {
                    row.querySelectorAll('[name]').forEach(el => {
                        const meta = matchKey(el.name);
                        if (meta && meta.root === 'coresearch') idx = meta.index;
                    });
                }
                const values = idx !== null ? getGroupValues(row, 'coresearch', idx) : {};
                const name = values.name || 'ไม่ระบุชื่อ';
                const aff = values.affiliation || '-';
                const status = values.unit_status || '-';
                summaryText.innerHTML = `
        <span class="summary-chip">${prefix} • ${name}</span>
        <span class="summary-chip summary-muted">${aff}</span>
        <span class="summary-chip summary-muted">${status}</span>
      `;
                return;
            }

            if (row.classList.contains('fte-row') || hasName(row, (n) => n.startsWith('fte_roles['))) {
                const name = getFieldValueByName(row, (n) => n.startsWith('fte_roles[') && n.endsWith('[name]')) ||
                    'ไม่ระบุชื่อ';
                const task = getFieldValueByName(row, (n) =>
                    n.startsWith('fte_roles[') && (n.endsWith('[job_detail]') || n.endsWith('[task_detail]'))
                ) || 'ไม่ระบุภาระงาน';
                const fte = getFieldValueByName(row, (n) =>
                    n.startsWith('fte_roles[') && (n.endsWith('[fte_value]') || n.endsWith('[fte]'))
                );
                const startText = getStrictDisplayedDateForNamedInput(row,
                        'input[name^="fte_roles["][name$="[start_date]"]') ||
                    (row.dataset.fteStartDisplay || '').trim() ||
                    '-';
                const endText = getStrictDisplayedDateForNamedInput(row,
                        'input[name^="fte_roles["][name$="[end_date]"]') ||
                    (row.dataset.fteEndDisplay || '').trim() ||
                    '-';
                const hasDate = (startText && startText !== '-') || (endText && endText !== '-');
                const dateText = hasDate ? `${startText} ถึง ${endText}` : 'ยังไม่ระบุช่วงเวลา';
                const fteText = fte ? `FTE ${fte}` : 'FTE -';
                summaryText.innerHTML = `
        <span class="summary-chip">${prefix} • ${name}</span>
        <span class="summary-chip summary-muted">${task}</span>
        <span class="summary-chip summary-muted">${dateText}</span>
        <span class="summary-chip summary-muted">${fteText}</span>
      `;
                return;
            }

            if (row.classList.contains('output-row') || hasName(row, (n) => n.startsWith('outputs['))) {
                const title = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[title]')) ||
                    'ไม่ระบุชื่อผลงาน';
                const type = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[type]')) ||
                    '-';
                const publish = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith('[year]')) ||
                    '-';
                const projectOutput = getFieldValueByName(row, (n) => n.startsWith('outputs[') && n.endsWith(
                    '[project_output]')) || '';
                summaryText.innerHTML = `
        <span class="summary-chip">${prefix} • ${title}</span>
        <span class="summary-chip summary-muted">${type}${projectOutput ? ` • ${projectOutput}` : ''}</span>
        <span class="summary-chip summary-muted">เผยแพร่ ${publish}</span>
      `;
                return;
            }

            const name = getFieldValueByName(row, (n) => n.endsWith('[name]') || n.endsWith('[person_name]')) ||
                'ไม่ระบุชื่อ';
            const task = getFieldValueByName(row, (n) => n.endsWith('[job_detail]') || n.endsWith(
                '[task_detail]')) || 'ไม่ระบุภาระงาน';
            const start = getFieldValueByName(row, (n) => n.endsWith('[start_date]') || n.endsWith('[start]'));
            const end = getFieldValueByName(row, (n) => n.endsWith('[end_date]') || n.endsWith('[end]'));
            const startEl = getFieldElementByName(row, (n) => n.endsWith('[start_date]') || n.endsWith('[start]'));
            const endEl = getFieldElementByName(row, (n) => n.endsWith('[end_date]') || n.endsWith('[end]'));
            const fte = getFieldValueByName(row, (n) => n.endsWith('[fte_value]') || n.endsWith('[fte]'));
            const startText = normalizeSummaryDateForRowInput(startEl) || formatSummaryDateText(start) || '-';
            const endText = normalizeSummaryDateForRowInput(endEl) || formatSummaryDateText(end) || '-';
            const dateText = (start || end) ? `${startText} ถึง ${endText}` : 'ยังไม่ระบุช่วงเวลา';
            const fteText = fte ? `FTE ${fte}` : 'FTE -';

            summaryText.innerHTML = `
      <span class="summary-chip">${prefix} • ${name}</span>
      <span class="summary-chip summary-muted">${task}</span>
      <span class="summary-chip summary-muted">${dateText}</span>
      <span class="summary-chip summary-muted">${fteText}</span>
    `;
        }

        function prepareRow(row) {
            if (!row || row.dataset.collapseReady === '1') return;
            row.dataset.collapseReady = '1';
            if (isCollapseDisabled()) return;
            if (isRowCollapseLocked(row)) {
                clearRowCollapseUi(row);
                updateSummary(row);
                row.querySelectorAll('input, select, textarea').forEach(el => {
                    el.addEventListener('input', () => updateSummary(row));
                    el.addEventListener('change', () => updateSummary(row));
                    const altInput = el._flatpickr?.altInput;
                    if (altInput) {
                        altInput.addEventListener('input', () => updateSummary(row));
                        altInput.addEventListener('change', () => updateSummary(row));
                    }
                });
                return;
            }
            ensureToggle(row);
            updateSummary(row);
            row.querySelectorAll('input, select, textarea').forEach(el => {
                el.addEventListener('input', () => updateSummary(row));
                el.addEventListener('change', () => updateSummary(row));
                const altInput = el._flatpickr?.altInput;
                if (altInput) {
                    altInput.addEventListener('input', () => updateSummary(row));
                    altInput.addEventListener('change', () => updateSummary(row));
                }
            });
        }

        document.addEventListener('input', (e) => {
            const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
            if (row) updateSummary(row);
        });

        document.addEventListener('change', (e) => {
            const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
            if (row) updateSummary(row);
        });

        function rowHasData(row) {
            const fields = row.querySelectorAll('input, select, textarea');
            for (const el of fields) {
                const val = getValueFromEl(el);
                if (val && val !== '-') return true;
            }
            return false;
        }

        document.addEventListener('click', (e) => {
            if (isCollapseDisabled()) return;
            const target = e.target;
            if (
                target?.closest?.('.thai-wheel-overlay') ||
                target?.closest?.('.thai-wheel-sheet') ||
                target?.closest?.('.thai-wheel-select') ||
                target?.closest?.('.thai-wheel-btn') ||
                target?.closest?.('.flatpickr-calendar') ||
                target?.classList?.contains?.('flatpickr-calendar') ||
                target?.classList?.contains?.('flatpickr-day') ||
                target?.classList?.contains?.('flatpickr-monthDropdown-month')
            ) {
                return;
            }
            const row = target?.closest?.(COLLAPSIBLE_SELECTOR);
            if (!row) {
                document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(el => setCollapsed(el, true));
                return;
            }
            if (target.closest('.row-collapse-toggle')) return;
            if (row.dataset.collapsed === '1') {
                setCollapsed(row, false);
                const siblings = getSiblings(row);
                siblings.forEach(el => {
                    if (el !== row) setCollapsed(el, true);
                });
            }
        });

        document.addEventListener('focusin', (e) => {
            if (isCollapseDisabled()) return;
            const row = e.target?.closest?.(COLLAPSIBLE_SELECTOR);
            if (!row) return;
            if (row.dataset.collapsed === '1') setCollapsed(row, false);
            const siblings = getSiblings(row);
            siblings.forEach(el => {
                if (el !== row) setCollapsed(el, true);
            });
        });

        function collapseSiblings(row) {
            if (isCollapseDisabled()) return;
            const siblings = getSiblings(row);
            siblings.forEach(el => setCollapsed(el, el !== row));
        }

        function updateIndex(container) {
            container.querySelectorAll('[data-repeatable-index]')
                .forEach((el, i) => el.textContent = i + 1);
            container.querySelectorAll(COLLAPSIBLE_SELECTOR)
                .forEach(row => updateToggleLabel(row));
        }

        function replaceIndex(root, index) {
            root.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replaceAll('__i__', index);
            });
        }

        function highlightRow(row) {
            row.classList.add('repeatable-highlight');
            setTimeout(() => row.classList.remove('repeatable-highlight'), 1200);
        }

        function confirmRemove(callback) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบ',
                    text: 'ต้องการลบรายการนี้ใช่หรือไม่?',
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc2626'
                }).then(r => r.isConfirmed && callback());
            } else {
                if (confirm('ต้องการลบรายการนี้ใช่หรือไม่?')) callback();
            }
        }

        function bindRemove(row) {
            row.querySelectorAll('[data-repeatable-remove]').forEach(btn => {
                const currentText = (btn.textContent || '').trim();
                if (currentText === '✕' || currentText.toLowerCase() === 'x') {
                    btn.textContent = 'ลบ';
                    btn.classList.add('repeatable-remove-text');
                }
                row.classList.add('has-inline-remove');

                btn.onclick = () => {
                    const group = row.closest('[data-repeatable]');
                    const container = document.getElementById(group.dataset.container);

                    confirmRemove(() => {
                        row.remove();
                        updateIndex(container);
                    });
                };
            });
        }

        function addRow(group) {
            const container = document.getElementById(group.dataset.container);
            const tpl = document.getElementById(group.dataset.template);
            let index = parseInt(group.dataset.index || '1', 10);
            const groupId = group.dataset.id || '';

            if (!container || !tpl) return;

            const fragment = tpl.content.cloneNode(true);
            const row = fragment.querySelector('[data-repeatable-row]');
            if (!row) return;

            replaceIndex(row, index);
            bindRemove(row);
            prepareRow(row);

            container.appendChild(row);
            updateIndex(container);
            highlightRow(row);
            if (groupId === 'fte' && typeof window.handleFteRowAdded === 'function') {
                window.handleFteRowAdded(row);
            } else {
                setCollapsed(row, false);
            }

            group.dataset.index = index + 1;

            if (window.initDateBE) initDateBE(row);
        }

        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-repeatable-add]');
            if (!btn) return;

            const id = btn.getAttribute('data-repeatable-add');
            const group = document.querySelector(`[data-repeatable][data-id="${id}"]`);
            if (!group) return;

            if (
                id === 'fte' &&
                e.isTrusted &&
                typeof window.validateFteBeforeAdd === 'function' &&
                !window.validateFteBeforeAdd({
                    showAlert: true
                })
            ) {
                return;
            }

            addRow(group);
            if (id === 'fte') {
                window.refreshFteUx?.();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (isCollapseDisabled()) {
                document.querySelectorAll('.row-collapse-toggle, .row-collapse-summary').forEach(el => el
                    .remove());
                document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => {
                    row.classList.remove('row-collapsed');
                    row.dataset.collapsed = '0';
                });
                return;
            }
            document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => prepareRow(row));
            document.querySelectorAll('[data-repeatable]').forEach(group => {
                const container = document.getElementById(group.dataset.container);
                updateIndex(container);
                container.querySelectorAll('[data-repeatable-row]')
                    .forEach(row => {
                        bindRemove(row);
                        prepareRow(row);
                    });
            });

            // /rm/create: ไม่ให้มีแถวตั้งต้นติดมา ต้องกด + เพิ่ม ก่อนเสมอ
            if (isProjectCreatePage()) {
                document.querySelectorAll('[data-repeatable]').forEach(group => {
                    const container = document.getElementById(group.dataset.container);
                    if (!container) return;
                    container.innerHTML = '';
                    group.dataset.index = '0';
                });
            }

            // collapse pre-filled rows on edit screens only
            const isEdit = document.querySelector(
                'input[name="_method"][value="PUT"], input[name="_method"][value="PATCH"]');
            if (isEdit) {
                document.querySelectorAll(COLLAPSIBLE_SELECTOR).forEach(row => {
                    if (isRowCollapseLocked(row)) {
                        clearRowCollapseUi(row);
                        return;
                    }
                    if (rowHasData(row)) setCollapsed(row, true);
                });
            }

            window.initFteUx?.();
        });

        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (!(node instanceof HTMLElement)) return;
                    if (node.matches && node.matches(COLLAPSIBLE_SELECTOR)) {
                        prepareRow(node);
                        collapseSiblings(node);
                        if (node.matches('.fte-row') || node.id === 'fte-container') {
                            window.refreshFteUx?.();
                        }
                        return;
                    }
                    node.querySelectorAll?.(COLLAPSIBLE_SELECTOR).forEach(row => {
                        prepareRow(row);
                        collapseSiblings(row);
                        if (row.matches('.fte-row')) {
                            window.refreshFteUx?.();
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });

    })();
</script>

<script>
    (() => {
        const FTE_CONTAINER_ID = 'fte-container';
        const FTE_TOOLS_ID = 'fte-tools';
        const FTE_SEARCH_ID = 'fte-search-input';
        const FTE_TABLE_MODE_BTN_ID = 'fte-table-mode-btn';
        const FTE_CLEAR_SEARCH_BTN_ID = 'fte-search-clear-btn';
        const FTE_TABLE_WRAP_ID = 'fte-table-wrap';
        const FTE_TABLE_MODE_STORAGE_KEY = `fte:table-mode:${window.AUTH_USER_ID || 'guest'}`;
        const isTableModeDisabledOnPage = window.DISABLE_FTE_TABLE_MODE === true;
        const isDesktopLike = () => window.matchMedia('(min-width: 768px)').matches;

        const getContainer = () => document.getElementById(FTE_CONTAINER_ID);
        const getRows = () => Array.from(document.querySelectorAll(`#${FTE_CONTAINER_ID} .fte-row`))
            .filter((row) => !row.closest('template'));
        const FTE_PROXY_LINK_ATTR = 'data-fte-proxy-id';
        let fteProxyIdSeq = 0;
        let fteSubmitAttempted = false;

        const getRequiredFields = (row) => Array.from(row.querySelectorAll('input[required], select[required], textarea[required]'))
            .filter((field) => !field.disabled && field.type !== 'hidden');

        const getFieldLabel = (field) => {
            const text = field.closest('label')?.querySelector('span')?.textContent?.trim();
            return text || 'ช่องข้อมูลที่จำเป็น';
        };

        const highlightInvalidField = (field) => {
            field.classList.add('border-red-500', 'bg-red-50');
            setTimeout(() => field.classList.remove('bg-red-50'), 1200);
        };

        const focusField = (field) => {
            const row = field.closest('.fte-row');
            if (row) {
                row.classList.remove('row-collapsed');
                row.dataset.collapsed = '0';
                row.dataset.forceOpen = '1';
            }
            field.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            setTimeout(() => field.focus({
                preventScroll: true
            }), 200);
        };

        window.validateFteBeforeAdd = ({
            showAlert = true
        } = {}) => {
            if (window.isFteTableMode?.()) {
                const result = validateFteTableFields({
                    showAlert,
                    title: 'กรอก FTE ให้ครบก่อนเพิ่มแถวใหม่',
                    markStyles: false
                });
                if (!result.ok) return false;
            }

            const rows = getRows();
            let firstInvalid = null;

            for (const row of rows) {
                for (const field of getRequiredFields(row)) {
                    const val = String(field.value ?? '').trim();
                    const isEmpty = val === '';
                    const invalid = isEmpty || !field.checkValidity();
                    if (!invalid) {
                        field.classList.remove('border-red-500', 'bg-red-50');
                        continue;
                    }
                    firstInvalid = field;
                    break;
                }
                if (firstInvalid) break;
            }

            if (!firstInvalid) return true;

            highlightInvalidField(firstInvalid);
            focusField(firstInvalid);
            firstInvalid.reportValidity?.();

            if (!showAlert) return false;

            const label = getFieldLabel(firstInvalid);
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรอก FTE ให้ครบก่อนเพิ่มแถวใหม่',
                    text: `กรุณากรอกข้อมูลที่ยังไม่ครบ: ${label}`,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#2563eb',
                    didClose: () => focusField(firstInvalid)
                });
            } else {
                alert(`กรุณากรอกข้อมูลที่ยังไม่ครบ: ${label}`);
            }

            return false;
        };

        const getFteTableRequiredFields = () => Array.from(document.querySelectorAll(
            `#${FTE_TABLE_WRAP_ID} input[required], #${FTE_TABLE_WRAP_ID} select[required], #${FTE_TABLE_WRAP_ID} textarea[required]`
        ));

        const clearProxyInvalidStyles = (el) => {
            if (!el) return;
            el.classList.remove('border-red-500', 'bg-red-50');
            el.style.borderColor = '';
            el.style.backgroundColor = '';
            if (el._sourceField) {
                el._sourceField.classList.remove('border-red-500', 'bg-red-50');
            }
        };

        const markProxyInvalidStyles = (el) => {
            if (!el) return;
            el.classList.add('border-red-500', 'bg-red-50');
            el.style.borderColor = '#ef4444';
            el.style.backgroundColor = '#fef2f2';
            if (el._sourceField) {
                el._sourceField.classList.add('border-red-500', 'bg-red-50');
            }
        };

        const syncProxyInvalidFromSource = (source, proxy) => {
            if (!source || !proxy) return;
            const hasInvalidClass = source.classList.contains('border-red-500') || source.classList.contains('bg-red-50');
            if (hasInvalidClass) {
                proxy.classList.add('border-red-500', 'bg-red-50');
                proxy.style.borderColor = '#ef4444';
                proxy.style.backgroundColor = '#fef2f2';
            } else {
                proxy.classList.remove('border-red-500', 'bg-red-50');
                proxy.style.borderColor = '';
                proxy.style.backgroundColor = '';
            }
        };

        const syncAllFteProxyInvalidStates = () => {
            document.querySelectorAll(`#${FTE_TABLE_WRAP_ID} [${FTE_PROXY_LINK_ATTR}]`).forEach((proxy) => {
                const proxyId = proxy.getAttribute(FTE_PROXY_LINK_ATTR);
                if (!proxyId) return;
                const source = document.querySelector(`#${FTE_CONTAINER_ID} [${FTE_PROXY_LINK_ATTR}="${proxyId}"]`);
                syncProxyInvalidFromSource(source, proxy);
            });
        };

        const getProxyFieldPositionText = (field) => {
            const td = field.closest('td');
            const tr = field.closest('tr');
            const rowNo = tr?.querySelector('.fte-table-index')?.textContent?.trim() || '?';
            const colIndex = td?.cellIndex ?? -1;
            const th = colIndex >= 0 ?
                document.querySelector(`#${FTE_TABLE_WRAP_ID} thead th:nth-child(${colIndex + 1})`) :
                null;
            const colName = th?.textContent?.trim() || 'ช่องข้อมูล';
            return `แถว ${rowNo} : ${colName}`;
        };

        const validateFteTableFields = ({
            showAlert = true,
            title = 'กรุณากรอกข้อมูลให้ครบถ้วน',
            markStyles = true
        } = {}) => {
            const requiredFields = getFteTableRequiredFields();
            let firstInvalid = null;

            requiredFields.forEach((field) => {
                const empty = String(field.value ?? '').trim() === '';
                const invalid = empty || !field.checkValidity();
                if (invalid) {
                    if (markStyles) markProxyInvalidStyles(field);
                    if (!firstInvalid) firstInvalid = field;
                } else {
                    if (markStyles) clearProxyInvalidStyles(field);
                }
            });

            if (!firstInvalid) return {
                ok: true
            };

            firstInvalid.reportValidity?.();
            firstInvalid.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            setTimeout(() => firstInvalid.focus({
                preventScroll: true
            }), 200);

            if (showAlert) {
                const pos = getProxyFieldPositionText(firstInvalid);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title,
                        text: `กรุณากรอก: ${pos}`,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert(`กรุณากรอก: ${pos}`);
                }
            }

            return {
                ok: false,
                firstInvalid
            };
        };
        window.validateFteTableFields = validateFteTableFields;

        const rowSearchText = (row) => {
            const pick = (selector) => {
                const el = row.querySelector(selector);
                if (!el) return '';
                if (el.tagName === 'SELECT') {
                    return (el.options[el.selectedIndex]?.textContent || '').trim();
                }
                return String(el.value || '').trim();
            };

            return [
                pick('input[name^="fte_roles["][name$="[name]"]'),
                pick('select[name^="fte_roles["][name$="[role_group]"]'),
                pick('select[name^="fte_roles["][name$="[job_detail]"]'),
                pick('select[name^="fte_roles["][name$="[task_detail]"]')
            ].join(' ').toLowerCase();
        };

        window.applyFteRowSearchFilter = () => {
            const query = (document.getElementById(FTE_SEARCH_ID)?.value || '').trim().toLowerCase();
            if (window.isFteTableMode?.()) {
                renderFteTable();
                return;
            }
            getRows().forEach((row) => row.style.display = !query || rowSearchText(row).includes(query) ? '' : 'none');
        };

        const isTableModeEnabled = () => getContainer()?.classList.contains('fte-table-mode');
        window.isFteTableMode = isTableModeEnabled;

        window.collapseFteRowsExcept = (activeRow) => {
            const active = typeof activeRow === 'string' ? document.getElementById(activeRow) : activeRow;
            getRows().forEach((row) => {
                if (!row) return;
                if (active && row === active) {
                    row.classList.remove('row-collapsed');
                    row.dataset.collapsed = '0';
                    row.dataset.forceOpen = '1';
                    return;
                }
                row.dataset.collapsed = '1';
                row.classList.add('row-collapsed');
                delete row.dataset.forceOpen;
            });
        };

        const expandAllFteRows = () => {
            getRows().forEach((row) => {
                row.classList.remove('row-collapsed');
                row.dataset.collapsed = '0';
                delete row.dataset.forceOpen;
            });
        };

        window.handleFteRowAdded = (rowOrId) => {
            const row = typeof rowOrId === 'string' ? document.getElementById(rowOrId) : rowOrId;
            if (!row) return;
            if (isTableModeEnabled()) {
                expandAllFteRows();
            } else {
                window.collapseFteRowsExcept(row);
            }
            window.refreshFteUx?.();
        };

        const getField = (row, selector) => row.querySelector(selector);
        const getFieldValue = (el) => {
            if (!el) return '';
            if (el.tagName === 'SELECT') return el.value || '';
            return String(el.value ?? '');
        };

        const parseDateAny = (raw) => {
            const normalizeThaiDigits = (input) => String(input || '').replace(/[๐-๙]/g, (ch) => String('๐๑๒๓๔๕๖๗๘๙'.indexOf(ch)));
            const value = normalizeThaiDigits(String(raw || '').trim()).replace(/\s+/g, ' ');
            if (!value) return null;

            let m = value.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
            if (m) {
                let yyyy = Number(m[3]);
                if (yyyy > 2400) yyyy -= 543;
                return {
                    dd: String(Number(m[1])).padStart(2, '0'),
                    mm: String(Number(m[2])).padStart(2, '0'),
                    yyyy
                };
            }

            m = value.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
            if (m) {
                let yyyy = Number(m[1]);
                if (yyyy > 2400) yyyy -= 543;
                return {
                    dd: String(Number(m[3])).padStart(2, '0'),
                    mm: String(Number(m[2])).padStart(2, '0'),
                    yyyy
                };
            }

            m = value.match(/^(\d{1,2})\s+([^\s]+)\s+(\d{4})$/u);
            if (m) {
                const monthRaw = m[2];
                const monthMap = {
                    'มกราคม': 1, 'ม.ค.': 1, 'ม.ค': 1,
                    'กุมภาพันธ์': 2, 'ก.พ.': 2, 'ก.พ': 2,
                    'มีนาคม': 3, 'มี.ค.': 3, 'มี.ค': 3,
                    'เมษายน': 4, 'เม.ย.': 4, 'เม.ย': 4,
                    'พฤษภาคม': 5, 'พ.ค.': 5, 'พ.ค': 5,
                    'มิถุนายน': 6, 'มิ.ย.': 6, 'มิ.ย': 6,
                    'กรกฎาคม': 7, 'ก.ค.': 7, 'ก.ค': 7,
                    'สิงหาคม': 8, 'ส.ค.': 8, 'ส.ค': 8,
                    'กันยายน': 9, 'ก.ย.': 9, 'ก.ย': 9,
                    'ตุลาคม': 10, 'ต.ค.': 10, 'ต.ค': 10,
                    'พฤศจิกายน': 11, 'พ.ย.': 11, 'พ.ย': 11,
                    'ธันวาคม': 12, 'ธ.ค.': 12, 'ธ.ค': 12
                };
                const month = monthMap[monthRaw];
                if (month) {
                    let yyyy = Number(m[3]);
                    if (yyyy > 2400) yyyy -= 543;
                    return {
                        dd: String(Number(m[1])).padStart(2, '0'),
                        mm: String(month).padStart(2, '0'),
                        yyyy
                    };
                }
            }

            return null;
        };

        const toBuddhistDateText = (raw) => {
            const parsed = parseDateAny(raw);
            if (!parsed) return String(raw || '').trim();
            const yyyyBE = parsed.yyyy < 2400 ? parsed.yyyy + 543 : parsed.yyyy;
            return `${parsed.dd}-${parsed.mm}-${yyyyBE}`;
        };

        const toGregorianDateText = (raw) => {
            const parsed = parseDateAny(raw);
            if (!parsed) return String(raw || '').trim();
            let yyyy = parsed.yyyy;
            if (yyyy > 2400) yyyy -= 543;
            return `${parsed.dd}-${parsed.mm}-${yyyy}`;
        };

        const setFieldValue = (el, value, opts = {}) => {
            if (!el) return;
            const nextValue = opts.isDate ? toGregorianDateText(value) : value;
            if (el.tagName === 'SELECT') {
                el.value = nextValue;
            } else {
                el.value = nextValue;
            }
            if (el._flatpickr) {
                try {
                    el._flatpickr.setDate(nextValue, false);
                } catch (_) {}
            }
            el.dispatchEvent(new Event('input', {
                bubbles: true
            }));
            el.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        };

        const createProxyInput = (source, opts = {}) => {
            if (!source) {
                const fallback = document.createElement('input');
                fallback.type = 'text';
                fallback.disabled = true;
                return fallback;
            }

            let proxy;
            const isDateField = opts.isDate === true;

            if (source.tagName === 'SELECT') {
                proxy = source.cloneNode(true);
                proxy.value = source.value;
            } else {
                if (isDateField) {
                    proxy = document.createElement('input');
                    proxy.type = 'text';
                    const fromPicker = source._flatpickr?.altInput?.value || source.value || '';
                    proxy.value = toBuddhistDateText(fromPicker);
                    proxy.placeholder = 'วว-ดด-ปปปป (พ.ศ.)';
                } else {
                    proxy = source.cloneNode(false);
                    proxy.value = getFieldValue(source);
                    if (!proxy.type) proxy.type = opts.type || 'text';
                }
            }

            proxy.className = '';
            proxy.removeAttribute('name');
            proxy.removeAttribute('id');
            if (source.required) proxy.required = true;
            if (source.readOnly || opts.readOnly) proxy.readOnly = true;
            if (opts.readOnly) proxy.setAttribute('readonly', 'readonly');
            proxy._sourceField = source;
            if (!source.getAttribute(FTE_PROXY_LINK_ATTR)) {
                source.setAttribute(FTE_PROXY_LINK_ATTR, `ftep-${++fteProxyIdSeq}`);
            }
            proxy.setAttribute(FTE_PROXY_LINK_ATTR, source.getAttribute(FTE_PROXY_LINK_ATTR));

            const syncFromSource = () => {
                if (isDateField) {
                    const fromPicker = source._flatpickr?.altInput?.value || source.value || '';
                    proxy.value = toBuddhistDateText(fromPicker);
                } else {
                    proxy.value = getFieldValue(source);
                }
            };
            source.addEventListener('input', syncFromSource);
            source.addEventListener('change', syncFromSource);
            source.addEventListener('input', () => syncProxyInvalidFromSource(source, proxy));
            source.addEventListener('change', () => syncProxyInvalidFromSource(source, proxy));

            proxy.addEventListener('input', () => setFieldValue(source, proxy.value, opts));
            proxy.addEventListener('change', () => setFieldValue(source, proxy.value, opts));
            proxy.addEventListener('input', () => clearProxyInvalidStyles(proxy));
            proxy.addEventListener('change', () => clearProxyInvalidStyles(proxy));

            if (isDateField && window.flatpickr) {
                const locale = window.flatpickr?.l10ns?.th ? 'th' : undefined;
                const formatBE = (date) => {
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear() + 543;
                    return `${dd}-${mm}-${yyyy}`;
                };
                window.flatpickr(proxy, {
                    dateFormat: 'd-m-Y',
                    allowInput: false,
                    locale,
                    defaultDate: toGregorianDateText(proxy.value || ''),
                    formatDate: (date) => formatBE(date),
                    parseDate: (dateStr) => {
                        const g = toGregorianDateText(dateStr);
                        return window.flatpickr.parseDate(g, 'd-m-Y');
                    },
                    onReady: (_sd, _ds, inst) => {
                        if (inst.calendarContainer) {
                            const yearInput = inst.calendarContainer.querySelector('.flatpickr-current-month input.cur-year');
                            if (yearInput) {
                                const ce = Number(yearInput.value || 0);
                                if (ce > 0 && ce < 2400) yearInput.value = String(ce + 543);
                            }
                        }
                    },
                    onMonthChange: (_sd, _ds, inst) => {
                        const yearInput = inst.calendarContainer?.querySelector('.flatpickr-current-month input.cur-year');
                        if (yearInput) {
                            const ce = Number(yearInput.value || 0);
                            if (ce > 0 && ce < 2400) yearInput.value = String(ce + 543);
                        }
                    },
                    onYearChange: (_sd, _ds, inst) => {
                        const yearInput = inst.calendarContainer?.querySelector('.flatpickr-current-month input.cur-year');
                        if (yearInput) {
                            const ce = Number(yearInput.value || 0);
                            if (ce > 0 && ce < 2400) yearInput.value = String(ce + 543);
                        }
                    },
                    onChange: (_dates, dateStr) => {
                        proxy.value = toBuddhistDateText(dateStr);
                        setFieldValue(source, proxy.value, opts);
                    },
                });
            }

            return proxy;
        };

        const ensureTableWrap = () => {
            const container = getContainer();
            if (!container) return null;
            let wrap = document.getElementById(FTE_TABLE_WRAP_ID);
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = FTE_TABLE_WRAP_ID;
                wrap.className = 'fte-table-wrap';
                container.parentElement?.insertBefore(wrap, container);
            }
            return wrap;
        };

        const renderFteTable = () => {
            const wrap = ensureTableWrap();
            if (!wrap) return;
            const query = (document.getElementById(FTE_SEARCH_ID)?.value || '').trim().toLowerCase();
            const rows = getRows().filter((row) => !query || rowSearchText(row).includes(query));

            if (!rows.length) {
                wrap.innerHTML = '<div class="fte-table-empty">ไม่พบรายการ FTE</div>';
                return;
            }

            const table = document.createElement('table');
            table.className = 'fte-table-grid';
            table.innerHTML = `
                <colgroup>
                    <col style="width:3%">
                    <col style="width:15%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:6%">
                    <col style="width:12%">
                    <col style="width:7%">
                    <col style="width:6%">
                    <col style="width:5%">
                </colgroup>
                <thead>
                    <tr>
                        <th>ลำดับ</th><th>ชื่อ</th><th>กลุ่ม</th><th>ภาระงาน</th><th>เริ่ม</th><th>สิ้นสุด</th>
                        <th>ชั่วโมง</th><th>รายละเอียด</th><th>FTE Auto</th><th>FTE หัวหน้า</th><th>ลบ</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');

            rows.forEach((row, idx) => {
                const tr = document.createElement('tr');
                const tdIndex = document.createElement('td');
                tdIndex.className = 'fte-table-index';
                tdIndex.textContent = String(idx + 1);
                tr.appendChild(tdIndex);

                const cellDefs = [
                    ['input[name^="fte_roles["][name$="[name]"]'],
                    ['select[name^="fte_roles["][name$="[role_group]"]'],
                    ['select[name^="fte_roles["][name$="[job_detail]"]'],
                    ['input[name^="fte_roles["][name$="[start_date]"]', {
                        isDate: true
                    }],
                    ['input[name^="fte_roles["][name$="[end_date]"]', {
                        isDate: true
                    }],
                    ['input[name^="fte_roles["][name$="[hours]"]', {
                        type: 'number'
                    }],
                    ['select[name^="fte_roles["][name$="[task_detail]"]'],
                    ['input[name^="fte_roles["][name$="[fte_value]"]', {
                        readOnly: true
                    }],
                    ['input[name^="fte_roles["][name$="[fte_value_research]"]', {
                        type: 'number'
                    }],
                ];

                cellDefs.forEach(([selector, options = {}]) => {
                    const td = document.createElement('td');
                    td.appendChild(createProxyInput(getField(row, selector), options));
                    tr.appendChild(td);
                });

                const removeTd = document.createElement('td');
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'fte-table-remove-btn';
                removeBtn.textContent = 'ลบ';
                removeBtn.addEventListener('click', () => {
                    const doRemove = () => {
                        const proxyInRow = tr.querySelector(`[${FTE_PROXY_LINK_ATTR}]`);
                        const proxyId = proxyInRow?.getAttribute(FTE_PROXY_LINK_ATTR) || '';
                        const sourceField = proxyId ?
                            document.querySelector(`#${FTE_CONTAINER_ID} [${FTE_PROXY_LINK_ATTR}="${proxyId}"]`) :
                            null;
                        const sourceRow = sourceField?.closest('.fte-row') || row;
                        if (sourceRow) {
                            const idInput = sourceRow.querySelector('input[name^="fte_roles["][name$="[id]"]');
                            const existingId = Number(idInput?.value || 0);
                            if (existingId > 0) {
                                const deletedContainer = document.getElementById('fte-deleted-ids');
                                if (deletedContainer && !deletedContainer.querySelector(`input[value="${existingId}"]`)) {
                                    deletedContainer.insertAdjacentHTML(
                                        'beforeend',
                                        `<input type="hidden" name="fte_deleted_ids[]" value="${existingId}">`
                                    );
                                }
                            }
                        }

                        // remove source row directly to avoid duplicate/overridden remove handlers
                        sourceRow?.remove();
                        if (typeof window.reindexFTE === 'function') {
                            window.reindexFTE();
                        }
                        setTimeout(() => window.refreshFteUx?.(), 0);
                    };

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ยืนยันการลบ',
                            text: 'ต้องการลบรายการนี้ใช่หรือไม่?',
                            showCancelButton: true,
                            confirmButtonText: 'ลบ',
                            cancelButtonText: 'ยกเลิก',
                            confirmButtonColor: '#dc2626'
                        }).then((result) => {
                            if (result.isConfirmed) doRemove();
                        });
                        return;
                    }

                    if (confirm('ต้องการลบรายการนี้ใช่หรือไม่?')) {
                        doRemove();
                    }
                });
                removeTd.appendChild(removeBtn);
                tr.appendChild(removeTd);
                tbody.appendChild(tr);
            });

            wrap.innerHTML = '';
            wrap.appendChild(table);
            wrap.scrollLeft = 0;
            syncAllFteProxyInvalidStates();
        };

        const forceMarkFteProxyInvalidFromSourceValue = () => {
            if (!fteSubmitAttempted) return;
            document.querySelectorAll(`#${FTE_TABLE_WRAP_ID} [${FTE_PROXY_LINK_ATTR}]`).forEach((proxy) => {
                const source = proxy._sourceField;
                if (!source) return;
                const invalid = !source.checkValidity?.() || (source.required && String(source.value ?? '').trim() === '');
                if (invalid) {
                    markProxyInvalidStyles(proxy);
                } else {
                    clearProxyInvalidStyles(proxy);
                }
            });
        };

        const applyTableModeState = () => {
            const container = getContainer();
            const wrap = ensureTableWrap();
            if (!container || !wrap) return;
            if (isTableModeDisabledOnPage) {
                container.classList.remove('fte-table-mode');
            }
            const active = !isTableModeDisabledOnPage && isTableModeEnabled() && isDesktopLike();
            container.classList.toggle('fte-cards-hidden', active);
            wrap.classList.toggle('is-active', active);
            if (active) {
                expandAllFteRows();
                renderFteTable();
            }
        };

        const ensureTools = () => {
            const container = getContainer();
            if (!container || document.getElementById(FTE_TOOLS_ID)) return;
            const wrap = ensureTableWrap();

            const tools = document.createElement('div');
            tools.id = FTE_TOOLS_ID;
            tools.className = 'fte-tools';
            tools.innerHTML = `
                <input
                    id="${FTE_SEARCH_ID}"
                    type="search"
                    class="fte-tools__search"
                    placeholder="ค้นหา FTE: ชื่อ / กลุ่ม / ภาระงาน"
                >
                <div class="fte-tools__actions">
                    <button
                        id="${FTE_CLEAR_SEARCH_BTN_ID}"
                        type="button"
                        class="fte-tools__clear-btn"
                    >
                        ล้างคำค้นหา
                    </button>
                    ${isTableModeDisabledOnPage ? '' : `
                    <button
                        id="${FTE_TABLE_MODE_BTN_ID}"
                        type="button"
                        class="fte-tools__table-btn hidden md:inline-flex"
                    >
                        ดูแบบตาราง
                    </button>`}
                </div>
            `;

            if (wrap) {
                container.parentElement?.insertBefore(tools, wrap);
            } else {
                container.parentElement?.insertBefore(tools, container);
            }

            const searchInput = document.getElementById(FTE_SEARCH_ID);
            searchInput?.addEventListener('input', window.applyFteRowSearchFilter);

            const clearBtn = document.getElementById(FTE_CLEAR_SEARCH_BTN_ID);
            clearBtn?.addEventListener('click', () => {
                if (!searchInput) return;
                searchInput.value = '';
                window.applyFteRowSearchFilter();
                searchInput.focus();
            });

            const tableBtn = document.getElementById(FTE_TABLE_MODE_BTN_ID);
            const setTableBtnState = () => {
                const active = container.classList.contains('fte-table-mode');
                tableBtn?.classList.toggle('is-active', active);
                if (tableBtn) tableBtn.textContent = active ? 'ดูแบบ Card' : 'ดูแบบตาราง';
                applyTableModeState();
            };

            const shouldEnableTableMode = !isTableModeDisabledOnPage && localStorage.getItem(FTE_TABLE_MODE_STORAGE_KEY) === '1';
            if (shouldEnableTableMode) {
                container.classList.add('fte-table-mode');
            } else if (isTableModeDisabledOnPage) {
                container.classList.remove('fte-table-mode');
            }
            setTableBtnState();

            tableBtn?.addEventListener('click', () => {
                container.classList.toggle('fte-table-mode');
                const active = container.classList.contains('fte-table-mode');
                localStorage.setItem(FTE_TABLE_MODE_STORAGE_KEY, active ? '1' : '0');
                setTableBtnState();
            });

            window.addEventListener('resize', () => {
                applyTableModeState();
            });
        };

        window.initFteUx = () => {
            ensureTools();
            window.applyFteRowSearchFilter();
        };

        window.refreshFteUx = () => {
            ensureTools();
            applyTableModeState();
            window.applyFteRowSearchFilter();
        };

        window.syncFteTableValidationUi = ({
            ensureRender = false
        } = {}) => {
            if (!window.isFteTableMode?.()) return;
            fteSubmitAttempted = true;
            if (ensureRender) renderFteTable();
            validateFteTableFields({
                showAlert: false,
                markStyles: true
            });
            forceMarkFteProxyInvalidFromSourceValue();
            syncAllFteProxyInvalidStates();
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.initFteUx();
        });

        const onFteFormSubmit = (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.querySelector(`#${FTE_CONTAINER_ID}`)) return;
            fteSubmitAttempted = true;
            if (window.isFteTableMode?.()) {
                // mark table fields immediately and after DOM settles from page-level validators
                window.syncFteTableValidationUi?.({
                    ensureRender: true
                });
                requestAnimationFrame(() => {
                    window.syncFteTableValidationUi?.({
                        ensureRender: true
                    });
                });
            }
            // Let form-level validators run first, then mirror red-border state to table proxies.
            setTimeout(() => {
                window.syncFteTableValidationUi?.({
                    ensureRender: true
                });
            }, 0);
        };
        // capture phase so this still runs even when page-level validator calls stopPropagation()
        document.addEventListener('submit', onFteFormSubmit, true);

        document.addEventListener('invalid', (event) => {
            if (!fteSubmitAttempted) return;
            if (!window.isFteTableMode?.()) return;
            const source = event.target;
            if (!(source instanceof HTMLElement)) return;
            const name = source.getAttribute('name') || '';
            if (!/^fte_roles\[\d+\]\[/.test(name)) return;

            const proxyId = source.getAttribute(FTE_PROXY_LINK_ATTR);
            if (!proxyId) return;
            const proxy = document.querySelector(`#${FTE_TABLE_WRAP_ID} [${FTE_PROXY_LINK_ATTR}="${proxyId}"]`);
            if (!proxy) return;
            markProxyInvalidStyles(proxy);
        }, true);

        // Ensure submit-attempt state is set as soon as browser emits any invalid event in this form.
        document.addEventListener('invalid', (event) => {
            const source = event.target;
            if (!(source instanceof HTMLElement)) return;
            const form = source.closest('form');
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.querySelector(`#${FTE_CONTAINER_ID}`)) return;
            fteSubmitAttempted = true;
        }, true);
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 🔥 บังคับ init ทุก js-date-be ที่ยังไม่ถูก init
        if (typeof initDateBE === 'function') {
            initDateBE(document);
        }
    });
</script>


<script>
    /* ===============================
   AUTO FTE (REPEATABLE SAFE)
   สูตร: hours × 0.00745 0.000621
   =============================== */
    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('fte-hours')) return;

        const row = e.target.closest('.fte-row');
        const fteInput = row?.querySelector('.fte-value');
        if (!fteInput) return;

        const hr = parseFloat(e.target.value) || 0;
        // const fte = hr * 0.00745;
        const fte = hr * 0.000621;

        console.log('hr=', hr, 'fte(raw)=', fte, 'fte(fixed)=', fte.toFixed(5));

        fteInput.value = fte.toFixed(8);
        fteInput.dispatchEvent(new Event('input', {
            bubbles: true
        }));
        fteInput.dispatchEvent(new Event('change', {
            bubbles: true
        }));
    });
</script>

<script>
    (() => {
        const overlayId = 'save-loading-overlay';
        const submitControlsSelector = 'button[type="submit"], input[type="submit"]';
        const saveKeywords = ['บันทึก', 'save'];

        const normalize = (value) => String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();

        const getControlLabel = (control) => {
            if (!control) return '';
            if (control.tagName === 'INPUT') {
                return normalize(control.value);
            }
            return normalize(control.textContent);
        };

        const shouldHandleForm = (form, submitter) => {
            if (!(form instanceof HTMLFormElement)) return false;

            const controls = Array.from(form.querySelectorAll(submitControlsSelector));
            if (submitter && submitter.matches?.(submitControlsSelector) && !controls.includes(submitter)) {
                controls.push(submitter);
            }

            return controls.some((control) => {
                const label = getControlLabel(control);
                return saveKeywords.some((keyword) => label.includes(keyword));
            });
        };

        const ensureOverlay = () => {
            let overlay = document.getElementById(overlayId);
            if (overlay) return overlay;

            overlay = document.createElement('div');
            overlay.id = overlayId;
            overlay.className = 'save-loading-overlay';
            overlay.setAttribute('aria-live', 'assertive');
            overlay.innerHTML = `
                <div class="save-loading-overlay__card">
                    <div class="save-loading-overlay__row">
                        <span class="save-loading-overlay__spinner" aria-hidden="true"></span>
                        <div>
                            <p class="save-loading-overlay__title">กำลังบันทึกข้อมูล...</p>
                            <p class="save-loading-overlay__subtitle">กรุณารอสักครู่</p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            return overlay;
        };

        const showOverlay = () => {
            const overlay = ensureOverlay();
            overlay.classList.add('is-visible');
        };

        const hideOverlay = () => {
            const overlay = document.getElementById(overlayId);
            if (!overlay) return;
            overlay.classList.remove('is-visible');
        };

        const markControlLoading = (control, isPrimary) => {
            if (!(control instanceof HTMLElement)) return;

            control.dataset.saveGuardDisabled = '1';
            control.classList.add('opacity-70', 'cursor-not-allowed');

            if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                control.disabled = true;
            }

            if (!isPrimary) return;

            if (control instanceof HTMLInputElement) {
                if (!control.dataset.originalValue) {
                    control.dataset.originalValue = control.value;
                }
                control.value = 'กำลังบันทึก...';
                return;
            }

            if (!control.dataset.originalHtml) {
                control.dataset.originalHtml = control.innerHTML;
            }
            control.innerHTML =
                '<span class="inline-flex items-center gap-2"><span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/70 border-t-white"></span><span>กำลังบันทึก...</span></span>';
        };

        const restoreForm = (form) => {
            if (!(form instanceof HTMLFormElement)) return;
            form.removeAttribute('data-save-submitting');

            form.querySelectorAll('[data-save-guard-disabled="1"]').forEach((control) => {
                if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                    control.disabled = false;
                }

                if (control instanceof HTMLElement) {
                    control.classList.remove('opacity-70', 'cursor-not-allowed');
                }

                if (control instanceof HTMLInputElement && control.dataset.originalValue) {
                    control.value = control.dataset.originalValue;
                    delete control.dataset.originalValue;
                }

                if (control instanceof HTMLElement && control.dataset.originalHtml) {
                    control.innerHTML = control.dataset.originalHtml;
                    delete control.dataset.originalHtml;
                }

                if (control instanceof HTMLElement) {
                    delete control.dataset.saveGuardDisabled;
                }
            });
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.dataset.saveSubmitting === '1') {
                event.preventDefault();
                return;
            }

            if (event.defaultPrevented) return;

            const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
            if (!shouldHandleForm(form, submitter)) return;

            form.dataset.saveSubmitting = '1';

            const submitControls = Array.from(form.querySelectorAll(submitControlsSelector));
            const primaryControl =
                submitter && submitter.matches?.(submitControlsSelector) ? submitter : submitControls[0] || null;

            submitControls.forEach((control) => {
                markControlLoading(control, control === primaryControl);
            });

            showOverlay();
        });

        window.addEventListener('pageshow', () => {
            hideOverlay();
            document.querySelectorAll('form[data-save-submitting="1"]').forEach((form) => {
                restoreForm(form);
            });
        });
    })();
</script>
