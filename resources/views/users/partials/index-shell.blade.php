@php
    $query = request()->query();
@endphp

<style>
    .users-page-shell {
        position: relative;
        overflow: hidden;
        background: #ffffff;
    }

    .users-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid #dbe5f0;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
    }

    .users-hero::before {
        content: none;
    }

    .users-search-card,
    .user-panel {
        border: 1px solid #dbe5f0;
        border-radius: 28px;
        background: #ffffff;
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
    }

    .users-search-input,
    .users-select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        background: #fff;
        padding: 0.78rem 0.95rem;
        font-size: 0.95rem;
        color: #0f172a;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }

    .users-search-input:focus,
    .users-select:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.25);
    }

    .users-stat-card {
        border: 1px solid #dbe5f0;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1rem;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    .user-stat-card--blue {
        background: linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%);
    }

    .user-stat-card--violet {
        background: linear-gradient(180deg, #f5f3ff 0%, #f8fafc 100%);
    }

    .user-stat-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
    }

    .user-stat-value {
        margin-top: 0.35rem;
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
    }

    .user-stat-help {
        margin-top: 0.25rem;
        font-size: 0.78rem;
        color: #64748b;
    }

    .user-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        background: #ecfeff;
        color: #0f766e;
        border: 1px solid #99f6e4;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .user-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        border: 1px solid #dbe5f0;
        background: #f8fafc;
        padding: 0.35rem 0.7rem;
        font-size: 0.78rem;
        color: #334155;
    }

    .user-card {
        border: 1px solid #dbe5f0;
        border-radius: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        padding: 1rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }

    .user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        object-fit: cover;
        border: 1px solid #dbe5f0;
        background: #fff;
        flex-shrink: 0;
    }

    .user-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .user-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        padding: 0.72rem 0.92rem;
        font-size: 0.88rem;
        font-weight: 700;
        transition: transform 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
    }

    .user-action:hover {
        transform: translateY(-1px);
    }

    .user-action--primary {
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        color: #fff;
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
    }

    .user-action--ghost {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f172a;
    }

    .user-action--edit {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .user-action--danger {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .user-directory-table {
        table-layout: fixed;
        width: 100%;
    }

    .user-directory-table th,
    .user-directory-table td {
        vertical-align: top;
    }

    .user-directory-table-cell {
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .user-info-pill {
        display: inline-flex;
        max-width: 100%;
        align-items: center;
        border-radius: 999px;
        border: 1px solid #dbe5f0;
        background: #f8fafc;
        padding: 0.28rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
    }

    .user-empty-state {
        border: 1px dashed #cbd5e1;
        border-radius: 24px;
        background: #f8fafc;
        padding: 2rem;
        text-align: center;
    }

    .user-pagination {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        justify-content: center;
        padding: 0.35rem;
        border: 1px solid #dbe5f0;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .user-page-link,
    .user-page-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 0.8rem;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .user-page-link {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
    }

    .user-page-link:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.12);
    }

    .user-page-link.is-active {
        border-color: #2563eb;
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        color: #fff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
    }

    .user-page-link.is-disabled,
    .user-page-ellipsis {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #94a3b8;
        cursor: default;
        box-shadow: none;
        transform: none;
    }

    .users-loading {
        position: relative;
        overflow: hidden;
    }

    .users-loading::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.55), transparent);
        animation: usersShimmer 1.2s infinite;
        transform: translateX(-100%);
    }

    @keyframes usersShimmer {
        100% {
            transform: translateX(100%);
        }
    }

    @media (min-width: 1024px) and (max-width: 1180px) {
        .users-page-shell .user-action {
            padding: 0.62rem 0.72rem;
            font-size: 0.8rem;
        }

        .user-directory-table {
            font-size: 0.78rem;
        }

        .user-directory-table th,
        .user-directory-table td {
            padding: 0.75rem 0.6rem;
        }

        .user-role-badge,
        .user-info-pill {
            white-space: normal;
        }
    }
</style>

<div class="users-page-shell min-h-screen">
    <div class="mx-auto w-full max-w-[92rem] px-4 pb-10 pt-8 sm:px-6 lg:px-8">
        <section class="users-hero px-4 py-4 md:px-6 md:py-5">
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="w-full">
                    <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900">
                        ← ย้อนกลับ
                    </a>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl">รายชื่อผู้ใช้งาน</h1>
                        <span class="user-live-badge">ค้นหาแบบสด</span>
                    </div>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-500 md:text-[15px]">
                        จัดการผู้ใช้ทั้งหมด ค้นหาแบบ realtime ได้ทันที และกรองสิทธิ์/การเรียงลำดับได้ในที่เดียว
                    </p>
                </div>

            </div>
        </section>

        <section class="users-search-card mt-5 px-4 py-4 md:px-5">
            <form id="usersSearchForm" action="{{ route('users.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                    <label class="block md:col-span-2 xl:col-span-5">
                        <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                        <div class="relative mt-1">
                            <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="ชื่อ / อีเมล / ตำแหน่ง / คำนำหน้า"
                                class="users-search-input pl-10">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>
                        </div>
                    </label>

                    <label class="block xl:col-span-3">
                        <span class="text-xs font-semibold text-slate-600">สิทธิ์</span>
                        <select name="role" class="users-select mt-1">
                            <option value="all" @selected(($role ?? 'all') === 'all')>ทั้งหมด</option>
                            <option value="admin" @selected(($role ?? '') === 'admin')>admin</option>
                            <option value="su_admin" @selected(($role ?? '') === 'su_admin')>su_admin</option>
                            <option value="user" @selected(($role ?? '') === 'user')>user</option>
                            <option value="user_all" @selected(($role ?? '') === 'user_all')>user_all</option>
                            <option value="research_executive" @selected(($role ?? '') === 'research_executive')>research_executive</option>
                            <option value="unit_executive" @selected(($role ?? '') === 'unit_executive')>unit_executive</option>
                            <option value="resigned" @selected(($role ?? '') === 'resigned')>resigned</option>
                        </select>
                    </label>

                    <label class="block xl:col-span-3">
                        <span class="text-xs font-semibold text-slate-600">เรียงตาม</span>
                        <select name="sort" class="users-select mt-1">
                            <option value="name_asc" @selected(($sort ?? 'name_asc') === 'name_asc')>ชื่อ (ก-ฮ)</option>
                            <option value="name_desc" @selected(($sort ?? '') === 'name_desc')>ชื่อ (ฮ-ก)</option>
                            <option value="newest" @selected(($sort ?? '') === 'newest')>ใหม่ → เก่า</option>
                            <option value="oldest" @selected(($sort ?? '') === 'oldest')>เก่า → ใหม่</option>
                            <option value="email_asc" @selected(($sort ?? '') === 'email_asc')>อีเมล (ก-ฮ)</option>
                            <option value="email_desc" @selected(($sort ?? '') === 'email_desc')>อีเมล (ฮ-ก)</option>
                        </select>
                    </label>

                    <div class="flex items-end gap-2 xl:col-span-1">
                        <button type="submit" class="user-action user-action--primary w-full">ค้นหา</button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('users.index') }}" class="user-action user-action--ghost">ล้างตัวกรอง</a>
                    @if (filled($search ?? '') || (($role ?? 'all') !== 'all') || (($sort ?? 'name_asc') !== 'name_asc'))
                        <span class="text-xs text-slate-500">สามารถพิมพ์เพื่อค้นหาได้ทันที ไม่ต้องกดปุ่ม</span>
                    @endif
                </div>
            </form>
        </section>

        <div id="usersResultsWrapper" class="mt-5">
            @include('users.partials.index-results', [
                'users' => $users,
                'search' => $search ?? '',
                'role' => $role ?? 'all',
                'sort' => $sort ?? 'name_asc',
                'activeFilterLabels' => $activeFilterLabels ?? [],
                'stats' => $stats ?? [],
            ])
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (() => {
        const form = document.getElementById('usersSearchForm');
        const resultsWrapper = document.getElementById('usersResultsWrapper');
        if (!form || !resultsWrapper) return;

        const searchInput = form.querySelector('input[name="q"]');
        const roleSelect = form.querySelector('select[name="role"]');
        const sortSelect = form.querySelector('select[name="sort"]');
        let debounceTimer = null;
        let requestSeq = 0;

        const buildUrl = (page = null) => {
            const url = new URL(form.action, window.location.origin);
            const params = new URLSearchParams();
            const q = (searchInput?.value || '').trim();
            const role = (roleSelect?.value || 'all').trim();
            const sort = (sortSelect?.value || 'name_asc').trim();

            if (q !== '') params.set('q', q);
            if (role !== '' && role !== 'all') params.set('role', role);
            if (sort !== '' && sort !== 'name_asc') params.set('sort', sort);
            if (page && String(page) !== '1') params.set('page', String(page));

            url.search = params.toString();
            return url;
        };

        const setLoading = (loading) => {
            resultsWrapper.classList.toggle('users-loading', loading);
            resultsWrapper.style.opacity = loading ? '0.6' : '1';
            resultsWrapper.style.pointerEvents = loading ? 'none' : '';
        };

        const renderHtml = async (url) => {
            const seq = ++requestSeq;
            setLoading(true);
            try {
                const response = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const html = await response.text();
                if (seq !== requestSeq) return;

                resultsWrapper.innerHTML = html;
                history.replaceState({}, '', url.pathname + (url.search ? `?${url.searchParams.toString()}` : ''));
                window.requestAnimationFrame(() => {
                    const top = Math.max(0, resultsWrapper.getBoundingClientRect().top + window.scrollY - 92);
                    window.scrollTo({ top, behavior: 'smooth' });
                });
            } catch (error) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'โหลดข้อมูลไม่สำเร็จ',
                        text: 'ลองค้นหาใหม่อีกครั้ง',
                        confirmButtonColor: '#2563eb',
                    });
                }
            } finally {
                if (seq === requestSeq) {
                    setLoading(false);
                }
            }
        };

        const refresh = (page = null) => renderHtml(buildUrl(page));

        searchInput?.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(() => refresh(1), 180);
        });

        [roleSelect, sortSelect].forEach((el) => {
            el?.addEventListener('change', () => refresh(1));
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            refresh(1);
        });

        resultsWrapper.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link) return;
            const pageLink = new URL(link.href, window.location.origin);
            if (pageLink.pathname !== window.location.pathname) return;
            if (!pageLink.searchParams.has('page')) return;
            event.preventDefault();
            renderHtml(pageLink);
        });
    })();

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: 'คุณกำลังจะลบผู้ใช้: ' + name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id)?.submit();
            }
        });
    }
</script>
