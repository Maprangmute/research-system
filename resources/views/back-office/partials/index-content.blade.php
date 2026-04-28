@php
    $canManageBackOffice = $canManageBackOffice ?? (auth()->user()?->hasRole(\App\Models\User::ROLE_ADMIN) ?? false);
    $formatDateBE = function ($value) {
        if (!$value) {
            return '-';
        }

        try {
            $d = \Carbon\Carbon::createFromFormat('d-m-Y', $value)->locale('th');
            return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
        } catch (\Exception $e) {
            try {
                $d = \Carbon\Carbon::parse($value)->locale('th');
                return $d->translatedFormat('j F') . ' ' . ($d->year + 543);
            } catch (\Exception $e) {
                return $value ?: '-';
            }
        }
    };
@endphp

<div id="backOfficeIndexContent" class="space-y-6">
    <section id="backOfficeStatsGrid" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="back-office-stat p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">จำนวนงาน</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($summary['tasks']) }}</div>
            <div class="mt-1 text-xs text-slate-500">รายการที่ตรงตามตัวกรอง</div>
        </article>
        <article class="back-office-stat p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">จำนวนภาระงาน</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($summary['entries']) }}</div>
            <div class="mt-1 text-xs text-slate-500">รวม entry ทั้งหมดในชุดข้อมูลนี้</div>
        </article>
        <article class="back-office-stat p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ชั่วโมงรวม</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($summary['hours'], 2) }}</div>
            <div class="mt-1 text-xs text-slate-500">คิดจากภาระงานที่บันทึกไว้</div>
        </article>
        <article class="back-office-stat p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">FTE รวม</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($summary['fte'], 6) }}</div>
            <div class="mt-1 text-xs text-slate-500">สรุป FTE ของรายการที่พบ</div>
        </article>
    </section>

    <section id="backOfficeResultsShell" class="back-office-panel overflow-hidden transition-opacity duration-200">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Missions list</h2>
                <p id="backOfficeResultsCount" class="mt-1 text-sm text-slate-500">
                    แสดงผล {{ $tasks->firstItem() ?? 0 }}–{{ $tasks->lastItem() ?? 0 }} จากทั้งหมด {{ $tasks->total() }} รายการ
                </p>
            </div>
        </div>

        <div id="backOfficeResultsList" class="p-4 md:p-5">
            @if ($tasks->count() > 0)
                <div class="back-office-mobile-list back-office-mobile-only">
                    @foreach ($tasks as $task)
                        @php($backofficeId = $task->backoffice_id ?: \App\Models\BackOfficeTask::formatBackOfficeId((int) $task->id))
                        @php($rowNumber = ($tasks->firstItem() ?? 1) + $loop->index)
                        <article class="back-office-mobile-card" data-back-office-search-text="{{ trim(implode(' ', array_filter([
                            $task->title,
                            $backofficeId,
                            $task->description,
                            $task->purpose,
                            is_array($task->leaders) ? implode(' ', array_filter($task->leaders)) : $task->leaders,
                            $task->creator?->prefix,
                            $task->creator?->name,
                        ], fn ($value) => filled($value)))) }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex min-w-0 items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">{{ $rowNumber }}</span>
                                    <a href="{{ route('back_office.show', $task) }}"
                                        class="inline-block text-[0.95rem] font-semibold leading-5 text-sky-700 hover:text-sky-800 hover:underline">
                                        {{ $task->title }}
                                    </a>
                                </div>
                            </div>
                            <div class="back-office-mobile-meta-grid">
                                <div class="back-office-mobile-meta">
                                    <div class="back-office-mobile-meta__label">ภาระงาน</div>
                                    <div class="back-office-mobile-meta__value">{{ number_format($task->entries_count) }}</div>
                                </div>
                                <div class="back-office-mobile-meta">
                                    <div class="back-office-mobile-meta__label">ผลงาน</div>
                                    <div class="back-office-mobile-meta__value">{{ number_format($task->outputs_count ?? 0) }}</div>
                                </div>
                                <div class="back-office-mobile-meta">
                                    <div class="back-office-mobile-meta__label">ชั่วโมง</div>
                                    <div class="back-office-mobile-meta__value">{{ number_format($task->entries_sum_hours ?? 0, 2) }}</div>
                                </div>
                                <div class="back-office-mobile-meta">
                                    <div class="back-office-mobile-meta__label">FTE</div>
                                    <div class="back-office-mobile-meta__value">{{ number_format($task->entries_sum_fte_value ?? 0, 6) }}</div>
                                </div>
                            </div>

                            <div class="mt-2.5">
                                <div class="back-office-action-cluster">
                                    <a href="{{ route('back_office.show', $task) }}"
                                        class="back-office-action-primary">
                                        รายละเอียด
                                    </a>
                                    @if ($canManageBackOffice)
                                        <form action="{{ route('back_office.destroy', $task) }}" method="POST"
                                            data-back-office-delete-form
                                            data-record-id="{{ $task->id }}"
                                            data-record-title="{{ $task->title }}"
                                            data-record-creator="{{ trim((string) ($task->creator?->name ?? '-')) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="back-office-action-danger border-0">
                                                ลบ
                                            </button>
                                        </form>
                                    @endif
                                    <details class="back-office-action-menu">
                                        <summary aria-label="เมนูเพิ่มเติม">⋯</summary>
                                        <div class="back-office-action-menu__panel">
                                            @if ($canManageBackOffice)
                                                <a href="{{ route('back_office.edit', $task) }}"
                                                    class="back-office-action-menu__item">
                                                    แก้ไข
                                                </a>
                                            @endif
                                            <a href="{{ route('back_office.pdf', $task) }}" target="_blank" rel="noopener"
                                                class="back-office-action-menu__item">
                                                PDF
                                            </a>
                                        </div>
                                    </details>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="back-office-desktop-only">
                    <div class="back-office-table-wrap">
                        <table class="back-office-table">
                            <thead>
                                <tr>
                                    <th class="w-[64px] text-center">ลำดับ</th>
                                    <th class="min-w-[320px]">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'page' => null]) }}"
                                            class="inline-flex items-center gap-1 text-slate-700 hover:text-sky-700">
                                            ชื่องาน
                                            <span class="text-[0.65rem] font-semibold {{ ($currentSort ?? '') === 'title' ? 'text-sky-700' : 'text-slate-400' }}">ก-ฮ</span>
                                        </a>
                                    </th>
                                    <th class="hidden w-[100px] text-right lg:table-cell">ภาระงาน</th>
                                    <th class="hidden w-[90px] text-right lg:table-cell">ผลงาน</th>
                                    <th class="hidden w-[130px] text-right xl:table-cell">ชั่วโมงรวม</th>
                                    <th class="hidden w-[130px] text-right xl:table-cell">FTE รวม</th>
                                    <th class="min-w-[240px] text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    @php($backofficeId = $task->backoffice_id ?: \App\Models\BackOfficeTask::formatBackOfficeId((int) $task->id))
                                    @php($rowNumber = ($tasks->firstItem() ?? 1) + $loop->index)
                                    <tr data-back-office-search-text="{{ trim(implode(' ', array_filter([
                                        $task->title,
                                        $backofficeId,
                                        $task->description,
                                        $task->purpose,
                                        is_array($task->leaders) ? implode(' ', array_filter($task->leaders)) : $task->leaders,
                                        $task->creator?->prefix,
                                        $task->creator?->name,
                                    ], fn ($value) => filled($value)))) }}">
                                        <td class="text-center text-sm font-bold text-slate-500">{{ $rowNumber }}</td>
                                        <td>
                                            <a href="{{ route('back_office.show', $task) }}"
                                                class="text-sm font-semibold text-sky-700 hover:text-sky-800 hover:underline">
                                                {{ $task->title }}
                                            </a>
                                            <div class="mt-2 text-xs text-slate-500 lg:hidden">
                                                ภาระงาน {{ number_format($task->entries_count) }} | ผลงาน {{ number_format($task->outputs_count ?? 0) }}
                                            </div>
                                            <div class="mt-0.5 text-xs text-slate-500 xl:hidden">
                                                ชั่วโมง {{ number_format($task->entries_sum_hours ?? 0, 2) }} | FTE {{ number_format($task->entries_sum_fte_value ?? 0, 6) }}
                                            </div>
                                        </td>
                                        <td class="hidden text-right text-sm font-semibold text-slate-800 lg:table-cell">{{ number_format($task->entries_count) }}</td>
                                        <td class="hidden text-right text-sm font-semibold text-slate-800 lg:table-cell">{{ number_format($task->outputs_count ?? 0) }}</td>
                                        <td class="hidden text-right text-sm font-semibold text-slate-800 xl:table-cell">{{ number_format($task->entries_sum_hours ?? 0, 2) }}</td>
                                        <td class="hidden text-right text-sm font-semibold text-slate-800 xl:table-cell">{{ number_format($task->entries_sum_fte_value ?? 0, 6) }}</td>
                                        <td>
                                            <div class="back-office-action-cluster">
                                                <a href="{{ route('back_office.show', $task) }}"
                                                    class="back-office-action-primary">
                                                    รายละเอียด
                                                </a>
                                                @if ($canManageBackOffice)
                                                    <form action="{{ route('back_office.destroy', $task) }}" method="POST"
                                                        data-back-office-delete-form
                                                        data-record-id="{{ $task->id }}"
                                                        data-record-title="{{ $task->title }}"
                                                        data-record-creator="{{ trim((string) ($task->creator?->name ?? '-')) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="back-office-action-danger border-0">
                                                            ลบ
                                                        </button>
                                                    </form>
                                                @endif
                                                <details class="back-office-action-menu">
                                                    <summary aria-label="เมนูเพิ่มเติม">⋯</summary>
                                                    <div class="back-office-action-menu__panel">
                                                        @if ($canManageBackOffice)
                                                            <a href="{{ route('back_office.edit', $task) }}"
                                                                class="back-office-action-menu__item">
                                                                แก้ไข
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('back_office.pdf', $task) }}" target="_blank" rel="noopener"
                                                            class="back-office-action-menu__item">
                                                            PDF
                                                        </a>
                                                    </div>
                                                </details>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                        <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 6h14M5 12h14M5 18h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-800">ไม่พบรายการงาน</h3>
                    <p class="mt-2 text-sm text-slate-500">ลองปรับคำค้นหา ผู้บันทึก บุคคล ประเภท หรือสถานะผลงาน แล้วระบบจะอัปเดตผลลัพธ์ใหม่</p>
                </div>
            @endif
            <div id="backOfficeEmptySearchState" class="hidden rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                    <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 6h14M5 12h14M5 18h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-slate-800">ไม่มีข้อมูลตรงกับคำค้นหาในหน้านี้</h3>
                <p class="mt-2 text-sm text-slate-500">ระบบกำลังกรองรายการในหน้าปัจจุบันทันที ลองพิมพ์ต่อหรือรอผลลัพธ์จากฐานข้อมูลสักครู่</p>
            </div>
        </div>

        @if ($tasks->count() > 0)
            <div id="backOfficePaginationWrap" class="border-t border-slate-100 bg-slate-50 px-4 py-3 md:px-5">
                {{ $tasks->links('vendor.pagination.th') }}
            </div>
        @endif
    </section>
</div>
