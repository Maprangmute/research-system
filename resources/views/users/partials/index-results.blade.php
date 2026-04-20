@php
    $roleMeta = [
        'admin' => ['label' => 'ผู้ดูแลระบบ', 'class' => 'bg-sky-100 text-sky-800 border-sky-200'],
        'user' => ['label' => 'ผู้ใช้งานทั่วไป', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
        'user_all' => ['label' => 'all_user', 'class' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
        'research_executive' => ['label' => 'ผู้บริหารงานวิจัย', 'class' => 'bg-cyan-100 text-cyan-800 border-cyan-200'],
        'unit_executive' => ['label' => 'ผู้บริหารหน่วย', 'class' => 'bg-violet-100 text-violet-800 border-violet-200'],
        'resigned' => ['label' => 'ลาออกแล้ว', 'class' => 'bg-rose-100 text-rose-800 border-rose-200'],
    ];

    $sortLabel = match ($sort ?? 'name_asc') {
        'name_desc' => 'ชื่อ (ฮ-ก)',
        'newest' => 'ใหม่ล่าสุด',
        'oldest' => 'เก่าสุด',
        'email_asc' => 'อีเมล (ก-ฮ)',
        'email_desc' => 'อีเมล (ฮ-ก)',
        default => 'ชื่อ (ก-ฮ)',
    };

    $searchValue = trim((string) ($search ?? ''));
    $pageFrom = $users->firstItem() ?? 0;
    $pageTo = $users->lastItem() ?? 0;
    $pageTotal = $users->total();
    $currentPage = $users->currentPage();
    $lastPage = $users->lastPage();
    $pageWindowStart = max(1, $currentPage - 2);
    $pageWindowEnd = min($lastPage, $currentPage + 2);
@endphp

<div id="usersResultsArea" class="space-y-6">
    <section class="user-panel">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 md:flex-row md:items-start md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-slate-900">รายชื่อผู้ใช้งาน</h2>
                    <span class="user-live-badge">Realtime search</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    แสดงผล {{ $pageFrom }}-{{ $pageTo }} จากทั้งหมด {{ $pageTotal }} รายการ
                    <span class="mx-2 text-slate-300">•</span>
                    เรียงตาม {{ $sortLabel }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('users.create') }}" class="user-action user-action--primary">เพิ่มผู้ใช้</a>
                <a href="{{ route('users.manage') }}" class="user-action user-action--ghost">จัดการสิทธิ์</a>
            </div>
        </div>

        <div class="px-5 py-4">
            @if (!empty($activeFilterLabels))
                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach ($activeFilterLabels as $filter)
                        <span class="user-filter-chip">
                            <span class="font-semibold">{{ $filter['label'] }}:</span>
                            <span>{{ $filter['value'] }}</span>
                        </span>
                    @endforeach
                </div>
            @endif

            @if ($users->count())
                <div class="md:hidden space-y-3">
                    @foreach ($users as $user)
                        @php
                            $meta = $roleMeta[$user->role] ?? ['label' => ucfirst($user->role), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                            $avatar = $user->profile_photo_path
                                ? asset('storage/' . $user->profile_photo_path) . '?v=' . optional($user->updated_at)->timestamp
                                : 'https://ui-avatars.com/api/?name=' . urlencode(trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''))) . '&background=0F172A&color=fff&size=128';
                            $updatedAt = optional($user->updated_at)?->format('d/m/Y H:i');
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <img src="{{ $avatar }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                    alt="{{ $user->name }}"
                                    class="h-14 w-14 rounded-2xl border border-slate-200 object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="truncate font-semibold text-slate-900">
                                            {{ trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) }}
                                        </div>
                                        <span class="user-role-badge {{ $meta['class'] }}">
                                            {{ $meta['label'] }}
                                        </span>
                                    </div>
                                    <div class="mt-1 truncate text-xs text-slate-500">{{ $user->email }}</div>
                                    <div class="mt-1 text-sm text-slate-700">{{ $user->position ?: '-' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $updatedAt ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-end gap-2">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="user-action user-action--edit px-3 py-2">แก้ไข</a>
                                    <button type="button"
                                        onclick="confirmDelete({{ $user->id }}, @json(trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''))))"
                                        class="user-action user-action--danger px-3 py-2">ลบ</button>
                                    <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto rounded-2xl border border-slate-200 md:block">
                    <table class="min-w-[980px] w-full border-separate border-spacing-0 text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-50">
                            <tr>
                                <th class="border-b border-slate-200 px-4 py-3 text-left font-semibold text-slate-700">ผู้ใช้งาน</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left font-semibold text-slate-700">ตำแหน่ง</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left font-semibold text-slate-700">สิทธิ์</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left font-semibold text-slate-700">อัปเดตล่าสุด</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach ($users as $user)
                                @php
                                    $meta = $roleMeta[$user->role] ?? ['label' => ucfirst($user->role), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                                    $avatar = $user->profile_photo_path
                                        ? asset('storage/' . $user->profile_photo_path) . '?v=' . optional($user->updated_at)->timestamp
                                        : 'https://ui-avatars.com/api/?name=' . urlencode(trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''))) . '&background=0F172A&color=fff&size=128';
                                    $updatedAt = optional($user->updated_at)?->format('d/m/Y H:i');
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="border-b border-slate-100 px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $avatar }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                                alt="{{ $user->name }}"
                                                class="h-12 w-12 rounded-2xl border border-slate-200 object-cover">
                                            <div class="min-w-0">
                                                <div class="truncate font-semibold text-slate-900">
                                                    {{ trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')) }}
                                                </div>
                                                <div class="truncate text-xs text-slate-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-b border-slate-100 px-4 py-4 text-slate-700">
                                        <div class="max-w-[260px] break-words">
                                            {{ $user->position ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="border-b border-slate-100 px-4 py-4">
                                        <span class="user-role-badge {{ $meta['class'] }}">
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td class="border-b border-slate-100 px-4 py-4 text-slate-600">
                                        {{ $updatedAt ?: '-' }}
                                    </td>
                                    <td class="border-b border-slate-100 px-4 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('users.edit', $user) }}" class="user-action user-action--edit px-3 py-2">แก้ไข</a>
                                            <button type="button"
                                                onclick="confirmDelete({{ $user->id }}, @json(trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''))))"
                                                class="user-action user-action--danger px-3 py-2">ลบ</button>
                                            <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex items-center justify-center">
                    @if ($lastPage > 1)
                        <nav class="user-pagination" aria-label="Pagination">
                            @if ($users->onFirstPage())
                                <span class="user-page-link is-disabled">‹ ก่อนหน้า</span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" class="user-page-link">‹ ก่อนหน้า</a>
                            @endif

                            @if ($pageWindowStart > 1)
                                <a href="{{ $users->url(1) }}" class="user-page-link">1</a>
                                @if ($pageWindowStart > 2)
                                    <span class="user-page-ellipsis">…</span>
                                @endif
                            @endif

                            @for ($page = $pageWindowStart; $page <= $pageWindowEnd; $page++)
                                @if ($page === $currentPage)
                                    <span class="user-page-link is-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $users->url($page) }}" class="user-page-link">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($pageWindowEnd < $lastPage)
                                @if ($pageWindowEnd < $lastPage - 1)
                                    <span class="user-page-ellipsis">…</span>
                                @endif
                                <a href="{{ $users->url($lastPage) }}" class="user-page-link">{{ $lastPage }}</a>
                            @endif

                            @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" class="user-page-link">ถัดไป ›</a>
                            @else
                                <span class="user-page-link is-disabled">ถัดไป ›</span>
                            @endif
                        </nav>
                    @endif
                </div>
            @else
                <div class="user-empty-state">
                    <div class="text-lg font-semibold text-slate-900">ไม่พบผู้ใช้งาน</div>
                    <p class="mt-1 text-sm text-slate-500">ลองล้างตัวกรองหรือค้นหาด้วยคำอื่น</p>
                    <div class="mt-4">
                        <a href="{{ route('users.index') }}" class="user-action user-action--ghost">ล้างตัวกรอง</a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
