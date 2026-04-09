@extends('layouts.app')

@php
    $typeBadge = function ($type) {
        return match ($type) {
            'EA101' => 'bg-sky-100 text-sky-700',
            'EA103' => 'bg-amber-100 text-amber-700',
            'EA119' => 'bg-emerald-100 text-emerald-700',
            'EA120' => 'bg-cyan-100 text-cyan-700',
            default => 'bg-slate-100 text-slate-700',
        };
    };
    $typeLabel = fn ($type) => match ($type) {
        'EA101' => 'Academic Paper',
        'EA103' => 'Presentation',
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
@endphp

@section('content')
    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Academic Paper / Presentation / Textbook / Manual นอกเนื่องโครงการวิจัยในหน่วย</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        รายการผลงานเพิ่มเติมแบบไม่ผูกกับโครงการ พร้อมค้นหา เปิดดูรายละเอียด และย้อนกลับมาเพิ่มข้อมูลต่อได้ใน flow เดียว
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.external-academic.create') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                        เพิ่มรายการใหม่
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-slate-500">ทั้งหมด</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($allCount) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-slate-500">Academic Paper</div>
                <div class="mt-2 text-3xl font-bold text-sky-700">{{ number_format($paperCount) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-slate-500">Presentation</div>
                <div class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($presentationCount) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-slate-500">Textbook / Book chapter</div>
                <div class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($textbookCount) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-slate-500">Manual / Instruction / Guideline</div>
                <div class="mt-2 text-3xl font-bold text-cyan-700">{{ number_format($manualCount) }}</div>
            </article>
        </section>

        <form method="GET" action="{{ route('outputs.external-academic') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input type="text" name="search" value="{{ $search }}"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm"
                        placeholder="ชื่อผลงาน / journal / author / presenter / conference">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภท</span>
                    <select name="type" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="EA101" @selected($type === 'EA101')>Academic Paper</option>
                        <option value="EA103" @selected($type === 'EA103')>Presentation</option>
                        <option value="EA119" @selected($type === 'EA119')>Textbook / Book chapter</option>
                        <option value="EA120" @selected($type === 'EA120')>Manual / Instruction / Guideline</option>
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm">
                        ค้นหา
                    </button>
                    <a href="{{ route('outputs.external-academic') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700">
                        ล้าง
                    </a>
                </div>
            </div>
        </form>

        <section class="space-y-4">
            @forelse ($outputs as $output)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeBadge($output->type) }}">
                                    {{ $typeLabel($output->type) }}
                                </span>
                                @if ($output->output_code)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ $output->output_code }}
                                    </span>
                                @endif
                                <span class="text-xs font-medium text-slate-500">เผยแพร่ {{ $thaiDate($output->publish_date) }}</span>
                            </div>
                            <h2 class="mt-3 text-lg font-bold leading-7 text-slate-900">{{ $output->title }}</h2>

                            @if ($output->note)
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $output->note }}</p>
                            @endif

                            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ประเภทของงาน</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->job_category ?: '-' }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Journal / Conference</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">
                                        {{
                                            $output->type === 'EA101'
                                                ? ($output->journal ?: '-')
                                                : ($output->type === 'EA103'
                                                    ? ($output->conferences->pluck('name')->filter()->implode(', ') ?: '-')
                                                    : '-')
                                        }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">People</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">
                                        {{
                                            $output->type === 'EA101'
                                                ? ($output->authors->pluck('name')->filter()->implode(', ') ?: '-')
                                                : ($output->type === 'EA103'
                                                    ? ($output->conferences->pluck('presenter_name')->filter()->implode(', ') ?: '-')
                                                    : '-')
                                        }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Quartile / Type</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">
                                        {{
                                            $output->type === 'EA101'
                                                ? ($output->quartile ?: '-')
                                                : ($output->type === 'EA103'
                                                    ? ($output->conferences->pluck('presentation_type')->filter()->implode(', ') ?: '-')
                                                    : '-')
                                        }}
                                    </div>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">บันทึกโดย</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">
                                        {{ optional($output->creator)->name ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-start">
                            <div class="flex gap-2">
                                <a href="{{ route('outputs.external-academic.show', $output) }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    ดูรายละเอียด
                                </a>
                                <a href="{{ route('outputs.external-academic.edit', $output) }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    แก้ไข
                                </a>
                                <form method="POST" action="{{ route('outputs.external-academic.destroy', $output) }}"
                                    onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                                        ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
                    <div class="text-lg font-semibold text-slate-800">ยังไม่มีรายการ</div>
                    <p class="mt-2 text-sm text-slate-500">เริ่มต้นบันทึกรายการเพิ่มเติมได้จากปุ่มเพิ่มรายการใหม่</p>
                </div>
            @endforelse
        </section>

        @if ($outputs->hasPages())
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                {{ $outputs->links() }}
            </section>
        @endif
    </div>
@endsection
