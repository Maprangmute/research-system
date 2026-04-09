@extends('layouts.app')

@php
    $months = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $thaiDate = function ($date) use ($months) {
        if (!$date) return '-';
        return $date->day.' '.($months[$date->month] ?? $date->format('m')).' '.($date->year + 543);
    };
    $typeLabel = match ($externalOutput->type) {
        'EA101' => 'Academic Paper',
        'EA103' => 'Presentation',
        'EA119' => 'Textbook / Book chapter',
        'EA120' => 'Manual / Instruction / Guideline',
        default => $externalOutput->type ?: '-',
    };
    $typeBadge = match ($externalOutput->type) {
        'EA101' => 'bg-sky-100 text-sky-700',
        'EA103' => 'bg-amber-100 text-amber-700',
        'EA119' => 'bg-emerald-100 text-emerald-700',
        'EA120' => 'bg-cyan-100 text-cyan-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

@section('content')
    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeBadge }}">{{ $typeLabel }}</span>
                        @if ($externalOutput->output_code)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $externalOutput->output_code }}
                            </span>
                        @endif
                        <span class="text-xs font-medium text-slate-500">เผยแพร่ {{ $thaiDate($externalOutput->publish_date) }}</span>
                    </div>
                    <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{{ $externalOutput->title }}</h1>
                    @if ($externalOutput->note)
                        <p class="mt-3 text-sm leading-6 text-slate-600 md:text-[15px]">{{ $externalOutput->note }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.external-academic.edit', $externalOutput) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        แก้ไข
                    </a>
                    <form method="POST" action="{{ route('outputs.external-academic.destroy', $externalOutput) }}"
                        onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                            ลบ
                        </button>
                    </form>
                    <a href="{{ route('outputs.external-academic') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        กลับไปรายการ
                    </a>
                    <a href="{{ route('outputs.external-academic.create') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                        เพิ่มรายการใหม่
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.9fr)]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">รายละเอียดผลงาน</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">รหัสผลงาน</div>
                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $externalOutput->output_code ?: '-' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ประเภท</div>
                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $typeLabel }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ประเภทของงาน</div>
                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $externalOutput->job_category ?: '-' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">วันที่ publish</div>
                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $thaiDate($externalOutput->publish_date) }}</div>
                    </div>

                    @if ($externalOutput->type === 'EA101')
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Journal</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $externalOutput->journal ?: '-' }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quartile / IF</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">
                                {{ $externalOutput->quartile ?: '-' }} / {{ $externalOutput->impact_factor ?: '-' }}
                            </div>
                        </div>
                    @elseif ($externalOutput->type === 'EA119')
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ชื่อหนังสือ</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $externalOutput->book_title ?: '-' }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ชื่อสำนักพิมพ์</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $externalOutput->publisher_name ?: '-' }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 md:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ชื่อบท / เลขหน้า</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $externalOutput->chapter_pages ?: '-' }}</div>
                        </div>
                    @endif

                    <div class="rounded-xl bg-slate-50 px-4 py-3 md:col-span-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">บันทึกโดย</div>
                        <div class="mt-1 text-sm font-medium text-slate-800">{{ optional($externalOutput->creator)->name ?: '-' }}</div>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">ข้อมูลเสริม</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">สร้างเมื่อ</div>
                        <div class="mt-1 font-medium">{{ $thaiDate($externalOutput->created_at?->timezone('Asia/Bangkok')) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">อัปเดตล่าสุด</div>
                        <div class="mt-1 font-medium">{{ $thaiDate($externalOutput->updated_at?->timezone('Asia/Bangkok')) }}</div>
                    </div>
                </div>
            </article>
        </section>

        @if (in_array($externalOutput->type, ['EA101', 'EA119'], true))
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Authors</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($externalOutput->authors as $author)
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $author->role ?: 'Author' }}</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $author->name }}</div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">ยังไม่มีรายชื่อผู้เขียน</div>
                    @endforelse
                </div>
            </section>
        @elseif ($externalOutput->type === 'EA103')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Presentation Details</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($externalOutput->conferences as $conference)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Conference</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->name ?: '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presentation Type</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->presentation_type ?: '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Location</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->location ?: '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presenter</div>
                                    <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->presenter_name ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">ยังไม่มีรายละเอียดการนำเสนอ</div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
@endsection
