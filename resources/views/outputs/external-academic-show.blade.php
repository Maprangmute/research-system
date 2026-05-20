@extends('layouts.app')

@php
    $typeBaseColorMap = [
        'EA101' => '#7aa2f7',
        'EA103' => '#f7b267',
        'EA118' => '#a78bfa',
        'EA119' => '#f4b183',
        'EA120' => '#e8a5a5',
    ];
    $withAlpha = static function (string $hex, string $alpha): string {
        $base = ltrim(trim($hex), '#');
        if (strlen($base) !== 6) {
            return '#cbd5e1';
        }
        return '#' . $base . $alpha;
    };
    $months = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $thaiDate = function ($date) use ($months) {
        if (!$date) return '-';
        return $date->day.' '.($months[$date->month] ?? $date->format('m')).' '.($date->year + 543);
    };
    $typeLabel = match ($externalOutput->type) {
        'EA101' => 'Academic Paper',
        'EA103' => 'Presentation',
        'EA118' => 'Award and Recognition',
        'EA119' => 'Textbook / Book chapter',
        'EA120' => 'Manual / Instruction / Guideline',
        default => $externalOutput->type ?: '-',
    };
    $typeBaseColor = $typeBaseColorMap[$externalOutput->type] ?? '#94a3b8';
    $typeBadge = sprintf(
        'background-color:%s; border-color:%s; color:%s;',
        $withAlpha($typeBaseColor, '1A'),
        $withAlpha($typeBaseColor, '66'),
        $typeBaseColor
    );
    $formatDateTimeThai = function ($value) use ($months) {
        if (!$value) return '';
        try {
            $date = \Carbon\Carbon::parse($value)->setTimezone('Asia/Bangkok');
            return $date->day.' '.($months[$date->month] ?? $date->format('m')).' '.($date->year + 543).' '.$date->format('H:i').' น.';
        } catch (\Exception $e) {
            return (string) $value;
        }
    };
    $updatedUser = $externalOutput->updated_by ? $externalOutput->updater : $externalOutput->creator;
    $updatedBy = $updatedUser ? trim(($updatedUser->prefix ?? '') . ' ' . ($updatedUser->name ?? '')) : '-';
    $createdBy = $externalOutput->creator ? trim(($externalOutput->creator->prefix ?? '') . ' ' . ($externalOutput->creator->name ?? '')) : '-';
    $hasValue = static function ($value): bool {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return count($value) > 0;
        }
        return true;
    };
    $detailItems = collect([
        ['label' => 'รหัสผลงาน', 'value' => $externalOutput->output_code],
        ['label' => 'ประเภท', 'value' => $typeLabel],
        ['label' => 'ประเภทของงาน', 'value' => $externalOutput->job_category],
        ['label' => 'วันที่ publish', 'value' => $externalOutput->publish_date ? $thaiDate($externalOutput->publish_date) : null],
        ['label' => 'Journal', 'value' => $externalOutput->type === 'EA101' ? $externalOutput->journal : null],
        [
            'label' => 'Ranking / IF',
            'value' => $externalOutput->type === 'EA101' && ($hasValue($externalOutput->quartile) || $hasValue($externalOutput->impact_factor))
                ? collect([$externalOutput->quartile, $externalOutput->impact_factor])->filter($hasValue(...))->implode(' / ')
                : null,
        ],
        ['label' => 'ชื่อหนังสือ', 'value' => $externalOutput->type === 'EA119' ? $externalOutput->book_title : null],
        ['label' => 'ชื่อสำนักพิมพ์', 'value' => $externalOutput->type === 'EA119' ? $externalOutput->publisher_name : null],
        ['label' => 'ชื่อบท / เลขหน้า', 'value' => $externalOutput->type === 'EA119' ? $externalOutput->chapter_pages : null, 'full' => true],
        ['label' => 'เลข ISBN', 'value' => $externalOutput->type === 'EA119' ? $externalOutput->isbn : null],
        ['label' => 'บันทึกโดย', 'value' => $createdBy, 'full' => true],
        ['label' => 'ข้อมูลล่าสุดโดย', 'value' => $updatedBy, 'full' => true],
    ])->filter(fn ($item) => $hasValue($item['value'] ?? null))->values();
    $authorSectionTitle = 'Author';
@endphp

@section('content')
    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section id="external-output-record" class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold" style="{{ $typeBadge }}">{{ $typeLabel }}</span>
                        @if ($externalOutput->output_code)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $externalOutput->output_code }}
                            </span>
                        @endif
                        @if ($externalOutput->publish_date)
                            <span class="text-xs font-medium text-slate-500">เผยแพร่ {{ $thaiDate($externalOutput->publish_date) }}</span>
                        @endif
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
                    @foreach ($detailItems as $item)
                        <div class="rounded-xl bg-slate-50 px-4 py-3 {{ !empty($item['full']) ? 'md:col-span-2' : '' }}">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</div>
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $item['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">ข้อมูลเสริม</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">สร้างเมื่อ</div>
                        <div class="mt-1 font-medium">{{ $formatDateTimeThai($externalOutput->created_at) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">อัปเดตล่าสุด</div>
                        <div class="mt-1 font-medium">{{ $formatDateTimeThai($externalOutput->updated_at) }}</div>
                    </div>
                </div>
            </article>
        </section>

        @php
            $displayPeople = $externalOutput->type === 'EA118'
                ? $externalOutput->awardRecipients
                : ($externalOutput->type === 'EA119' ? $externalOutput->bookAuthors : $externalOutput->authors);
        @endphp
        @if (in_array($externalOutput->type, ['EA101', 'EA118', 'EA119'], true) && $displayPeople->isNotEmpty())
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ $externalOutput->type === 'EA118' ? 'Award Recipients' : $authorSectionTitle }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($displayPeople as $author)
                        <div class="rounded-xl border border-slate-200 px-4 py-3">
                            @if ($author->role && $author->role !== 'awardee')
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $author->role }}</div>
                            @endif
                            <div class="mt-1 text-sm font-medium text-slate-800">{{ $author->name }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @elseif ($externalOutput->type === 'EA103' && $externalOutput->conferences->isNotEmpty())
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Presentation Details</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($externalOutput->conferences as $conference)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="grid gap-3 md:grid-cols-2">
                                @if ($conference->name)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Conference</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->name }}</div>
                                    </div>
                                @endif
                                @if ($conference->presentation_type)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presentation Type</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->presentation_type }}</div>
                                    </div>
                                @endif
                                @if ($conference->location)
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Location</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->location }}</div>
                                    </div>
                                @endif
                                @if ($conference->presenterDisplay())
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presenter</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $conference->presenterDisplay() }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <script>
        (() => {
            const hasMissingOutputLinkNotice = () => {
                try {
                    return new URLSearchParams(window.location.search).get('output_missing_link') === '1';
                } catch (error) {
                    return false;
                }
            };
            let outputTargetLoading = null;
            const showOutputTargetLoading = () => {
                if (!hasMissingOutputLinkNotice()) return;
                if (!outputTargetLoading) {
                    outputTargetLoading = document.createElement('div');
                    outputTargetLoading.className = 'output-target-loading open';
                    outputTargetLoading.setAttribute('role', 'status');
                    outputTargetLoading.setAttribute('aria-live', 'polite');
                    outputTargetLoading.innerHTML = `
                        <div class="output-target-loading__card">
                            <span class="output-target-loading__spinner" aria-hidden="true"></span>
                            <span>กำลังประมวลผล...</span>
                        </div>
                    `;
                    document.body.appendChild(outputTargetLoading);
                }
                outputTargetLoading.classList.add('open');
            };
            const hideOutputTargetLoading = () => {
                outputTargetLoading?.classList.remove('open');
            };
            const missingOutputLinkMessage = () => {
                let targetType = 'paper';
                try {
                    targetType = new URLSearchParams(window.location.search).get('output_missing_target') || 'paper';
                } catch (error) {}

                if (targetType === 'presentation') {
                    return 'ไม่มีการลง link สำหรับ presentation นี้';
                }
                if (targetType === 'textbook') {
                    return 'ไม่มีการลง link สำหรับ textbook นี้';
                }
                return 'ไม่มีการลง link สำหรับ paper นี้';
            };

            const showMissingOutputLinkNotice = (target) => {
                if (!hasMissingOutputLinkNotice() || document.getElementById('missing-output-link-notice')) return;

                const notice = document.createElement('div');
                notice.id = 'missing-output-link-notice';
                notice.className = 'missing-output-link-notice';
                notice.innerHTML = `
                    <div class="missing-output-link-notice__text">${missingOutputLinkMessage()}</div>
                    <button type="button" class="missing-output-link-notice__close" aria-label="ปิด">×</button>
                `;
                target.insertAdjacentElement('beforebegin', notice);
                notice.querySelector('button')?.addEventListener('click', () => notice.remove());
            };

            const focusExternalOutputRecord = () => {
                if (!hasMissingOutputLinkNotice()) return;
                showOutputTargetLoading();
                const target = document.getElementById('external-output-record');
                if (!target) {
                    hideOutputTargetLoading();
                    return;
                }

                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.classList.add('external-output-focus-highlight');
                showMissingOutputLinkNotice(target);
                window.setTimeout(hideOutputTargetLoading, 700);
            };

            const style = document.createElement('style');
            style.textContent = `
                .external-output-focus-highlight {
                    outline: 3px solid rgba(16, 185, 129, 0.72);
                    outline-offset: 5px;
                    background: rgba(236, 253, 245, 0.85) !important;
                    transition: background-color 0.2s ease, outline-color 0.2s ease;
                }
                .missing-output-link-notice {
                    display: flex;
                    width: 100%;
                    align-items: center;
                    gap: 12px;
                    margin: 0 0 12px;
                    border: 2px solid rgba(220, 38, 38, 0.85);
                    border-radius: 16px;
                    background: #fef2f2;
                    box-shadow: 0 14px 32px rgba(185, 28, 28, 0.16);
                    color: #7f1d1d;
                    padding: 12px 12px 12px 14px;
                    font-size: 0.92rem;
                    font-weight: 700;
                    line-height: 1.45;
                }
                .missing-output-link-notice__text {
                    min-width: 0;
                }
                .missing-output-link-notice__close {
                    display: inline-flex;
                    width: 30px;
                    height: 30px;
                    flex: 0 0 auto;
                    align-items: center;
                    justify-content: center;
                    border-radius: 999px;
                    border: 1px solid rgba(220, 38, 38, 0.35);
                    background: #ffffff;
                    color: #b91c1c;
                    font-size: 1.25rem;
                    font-weight: 900;
                    line-height: 1;
                }
                .output-target-loading {
                    position: fixed;
                    inset: 0;
                    z-index: 110;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    background: rgba(15, 23, 42, 0.42);
                    padding: 16px;
                }
                .output-target-loading.open {
                    display: flex;
                }
                .output-target-loading__card {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    border: 1px solid rgba(255, 255, 255, 0.72);
                    border-radius: 16px;
                    background: rgba(255, 255, 255, 0.96);
                    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
                    color: #0f172a;
                    padding: 14px 18px;
                    font-size: 0.95rem;
                    font-weight: 800;
                }
                .output-target-loading__spinner {
                    width: 24px;
                    height: 24px;
                    border-radius: 999px;
                    border: 3px solid #cbd5e1;
                    border-top-color: #dc2626;
                    animation: output-target-spin 0.8s linear infinite;
                }
                @keyframes output-target-spin {
                    to {
                        transform: rotate(360deg);
                    }
                }
            `;
            document.head.appendChild(style);

            window.addEventListener('load', () => window.setTimeout(focusExternalOutputRecord, 450), { once: true });
        })();
    </script>
@endsection
