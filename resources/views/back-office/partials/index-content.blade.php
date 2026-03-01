@php
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
                <h2 class="text-lg font-semibold text-slate-900">รายการงาน Back Office</h2>
                <p class="mt-1 text-sm text-slate-500">
                    แสดงผล {{ $tasks->firstItem() ?? 0 }}–{{ $tasks->lastItem() ?? 0 }} จากทั้งหมด {{ $tasks->total() }} รายการ
                </p>
            </div>
            <div class="text-xs text-slate-500">
                เรียงตาม <span class="font-semibold text-slate-700">{{ $currentSortLabel ?? 'บันทึกล่าสุด' }}</span>
            </div>
        </div>

        <div class="space-y-4 p-4 md:p-5">
            @forelse ($tasks as $task)
                <article class="back-office-task-card p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-lg font-semibold leading-7 text-slate-900">
                                <a href="{{ route('back_office.show', $task->id) }}" data-back-office-open
                                    class="text-sky-700 hover:text-sky-800 hover:underline">
                                    {{ $task->title }}
                                </a>
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                ช่วงเวลา {{ $formatDateBE($task->start_date) }} ถึง {{ $formatDateBE($task->end_date) }}
                            </p>

                            @if ($task->description)
                                <p class="cell-clamp-3 mt-2 text-sm leading-6 text-slate-600">{{ $task->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            <a href="{{ route('back_office.show', $task->id) }}" data-back-office-open
                                class="back-office-action bg-sky-50 px-3.5 py-2 text-sm text-sky-700 hover:bg-sky-100">
                                รายละเอียด
                            </a>
                            <a href="{{ route('back_office.edit', $task->id) }}"
                                class="back-office-action bg-amber-50 px-3.5 py-2 text-sm text-amber-700 hover:bg-amber-100">
                                แก้ไข
                            </a>
                            <a href="{{ route('back_office.pdf', $task->id) }}" target="_blank" rel="noopener"
                                class="back-office-action bg-emerald-50 px-3.5 py-2 text-sm text-emerald-700 hover:bg-emerald-100">
                                PDF
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                        <div class="back-office-task-meta p-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ภาระงาน</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">{{ number_format($task->entries_count) }}</div>
                        </div>
                        <div class="back-office-task-meta p-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ผลงาน</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">{{ number_format($task->outputs_count ?? 0) }}</div>
                        </div>
                        <div class="back-office-task-meta p-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ชั่วโมงรวม</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">{{ number_format($task->entries_sum_hours ?? 0, 2) }}</div>
                        </div>
                        <div class="back-office-task-meta p-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">FTE รวม</div>
                            <div class="mt-1 text-xl font-bold text-slate-900">{{ number_format($task->entries_sum_fte_value ?? 0, 6) }}</div>
                        </div>
                        <div class="back-office-task-meta p-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ผู้บันทึก</div>
                            <div class="mt-1 text-base font-semibold text-slate-900">{{ $task->creator?->name ?? '-' }}</div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                        <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 6h14M5 12h14M5 18h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-800">ไม่พบรายการงาน</h3>
                    <p class="mt-2 text-sm text-slate-500">ลองปรับคำค้นหา ผู้บันทึก บุคคล ประเภท หรือสถานะผลงาน แล้วระบบจะอัปเดตผลลัพธ์ใหม่</p>
                </div>
            @endforelse
        </div>

        @if ($tasks->count() > 0)
            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3 md:px-5">
                {{ $tasks->links('vendor.pagination.th') }}
            </div>
        @endif
    </section>
</div>
