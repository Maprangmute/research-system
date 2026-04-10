@extends('layouts.app')

@section('content')
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

    <style>
        .back-office-shell {
            background: transparent;
        }

        .back-office-panel {
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        }

        .back-office-hero {
            overflow: hidden;
            position: relative;
        }

        .back-office-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.12), transparent 24%);
            pointer-events: none;
        }

        .back-office-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .back-office-action:hover {
            transform: translateY(-1px);
        }

        .back-office-action--edit {
            background: #fef3c7;
            color: #92400e;
        }

        .back-office-action--edit:hover {
            background: #fde68a;
        }

        .back-office-action--pdf {
            gap: 0.55rem;
            border: 1px solid #86efac;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
            box-shadow: 0 10px 22px rgba(16, 185, 129, 0.16);
        }

        .back-office-action--pdf:hover {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #4ade80;
        }

        .back-office-action--delete {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .back-office-action--delete:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .back-office-action__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .back-office-panel {
                border-radius: 20px;
            }
        }
    </style>

    <div class="back-office-shell min-h-screen">
        <div class="mx-auto mt-11 max-w-6xl space-y-6 px-4 pb-8">
            <section class="back-office-panel back-office-hero">
                <div class="relative flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-4xl">
                        <a href="{{ route('back_office.index') }}" class="text-sm text-sky-700 hover:underline">← กลับไปยังรายการงาน</a>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Back Office Detail</p>
                        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{{ $task->title }}</h1>
                        <p class="mt-3 text-sm leading-6 text-slate-600 md:text-[15px]">
                            ช่วงเวลา {{ $formatDateBE($task->start_date) }} ถึง {{ $formatDateBE($task->end_date) }}
                        </p>

                        @if ($task->description)
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $task->description }}</p>
                        @endif

                        @if ($task->purpose)
                            <div class="mt-4 text-sm text-slate-600">
                                <span class="font-semibold text-slate-700">จุดประสงค์:</span> {{ $task->purpose }}
                            </div>
                        @endif

                        <div class="mt-2 text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">ผู้บันทึก:</span>
                            {{ $task->creator?->name ?? '-' }}
                        </div>

                        <div class="mt-2 text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">หัวหน้างานผู้รับผิดชอบ:</span>
                            {{ !empty($task->leaders) ? implode(', ', $task->leaders) : '-' }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                        <a href="{{ route('back_office.edit', $task->id) }}"
                            class="back-office-action back-office-action--edit">
                            แก้ไข
                        </a>
                        <a href="{{ route('back_office.pdf', $task->id) }}" target="_blank" rel="noopener"
                            class="back-office-action back-office-action--pdf">
                            <span class="back-office-action__icon" aria-hidden="true">🖨️</span>
                            <span>ดาวน์โหลด PDF</span>
                        </a>
                        @if (auth()->user()?->role === 'admin' || (int) $task->created_by === (int) auth()->id())
                            <form action="{{ route('back_office.destroy', $task->id) }}" method="POST" class="inline-flex"
                                data-back-office-delete-form
                                data-record-id="{{ $task->id }}"
                                data-record-title="{{ $task->title }}"
                                data-record-creator="{{ trim((string) ($task->creator?->name ?? '-')) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="back-office-action back-office-action--delete border-0">
                                    ลบ
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </section>

            @include('back-office.partials.detail-content', [
                'task' => $task,
                'summary' => $summary,
                'drawerMode' => false,
            ])
        </div>
    </div>

    <script>
        (() => {
            if (window.__backOfficeDeleteConfirmBound) return;
            window.__backOfficeDeleteConfirmBound = true;

            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            document.addEventListener('submit', (event) => {
                const form = event.target?.closest('form[data-back-office-delete-form]');
                if (!form) return;

                event.preventDefault();

                const recordId = form.dataset.recordId || '-';
                const title = form.dataset.recordTitle || '-';
                const creator = form.dataset.recordCreator || '-';

                const fallbackText = `ยืนยันลบ Record #${recordId}\nงาน: ${title}\nผู้บันทึก: ${creator}`;

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ยืนยันการลบงาน Back Office',
                        html: `Record #${escapeHtml(recordId)}<br>งาน: ${escapeHtml(title)}<br>ผู้บันทึก: ${escapeHtml(creator)}`,
                        showCancelButton: true,
                        confirmButtonText: 'ลบรายการนี้',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#dc2626'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                    return;
                }

                if (window.confirm(fallbackText)) {
                    form.submit();
                }
            });
        })();
    </script>
@endsection
