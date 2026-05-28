@extends('layouts.app')

@section('content')
    @php
        $selectedMonthKey = old('filter_month_key')
            ?: sprintf(
                '%04d-%02d',
                (($report->filter_year ?: now()->year + 543) > 2500 ? ($report->filter_year ?: now()->year + 543) - 543 : ($report->filter_year ?: now()->year + 543)),
                $report->filter_month ?: now()->month
            );
        $previousOptionYear = null;
    @endphp

    <style>
        .wfa-form-shell { width: 100%; margin: 0 auto; }
        .wfa-panel {
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
        }
        .wfa-hero {
            position: relative;
            overflow: hidden;
        }
        .wfa-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 30%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.10), transparent 24%);
            pointer-events: none;
        }
        .wfa-label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.78rem;
            font-weight: 800;
            color: #475569;
        }
        .wfa-input,
        .wfa-select {
            width: 100%;
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #ffffff;
            padding: 0.62rem 0.78rem;
            color: #0f172a;
            font-size: 0.92rem;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .wfa-input:focus,
        .wfa-select:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.25);
        }
        .wfa-tool {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            padding: 0 0.65rem;
            font-size: 0.78rem;
            font-weight: 900;
            line-height: 1;
        }
        .wfa-tool svg {
            width: 17px;
            height: 17px;
            stroke-width: 2.25;
        }
        .wfa-tool:hover { background: #f1f5f9; }
        .wfa-editor-shell {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 18px;
        }
        .wfa-editor-page {
            width: 100%;
            min-height: 520px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 34px 42px;
            color: #0f172a;
            font-size: 18px;
            line-height: 1.75;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            outline: none;
        }
        .wfa-editor-page:empty::before {
            content: attr(data-placeholder);
            color: #94a3b8;
        }
        .wfa-editor-page ul,
        .wfa-editor-page ol {
            margin-left: 1.3rem;
            padding-left: 1.1rem;
        }
        .wfa-editor-page ol { list-style: decimal; }
        .wfa-editor-page ul { list-style: disc; }
        .wfa-editor-page p,
        .wfa-editor-page div { margin: 0 0 0.35rem 0; }
        .wfa-action {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 0 1rem;
            font-size: 0.88rem;
            font-weight: 900;
        }
        .wfa-save-prompt {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.48);
        }
        .wfa-save-prompt-card {
            width: min(420px, 100%);
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 18px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
        }
        .wfa-save-prompt.is-hidden,
        .wfa-save-prompt-step.is-hidden { display: none; }
        @media (max-width: 640px) {
            .wfa-editor-shell { padding: 10px; }
            .wfa-editor-page {
                min-height: 420px;
                padding: 22px 18px;
                font-size: 16px;
                line-height: 1.65;
            }
        }
    </style>

    <div class="wfa-form-shell space-y-5">
        <section class="wfa-panel wfa-hero p-5 sm:p-6">
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-extrabold text-slate-600">
                        Work From Anywhere
                    </div>
                    <h1 class="mt-3 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ $isEdit ? 'แก้ไข WFA' : 'เพิ่ม WFA' }}</h1>
                </div>
                <a href="{{ route('wfa.index') }}"
                    class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">
                    กลับหน้ารายการ
                </a>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($isEdit && session('status'))
            <div id="wfa-save-prompt" class="wfa-save-prompt">
                <div class="wfa-save-prompt-card">
                    <div id="wfa-save-export-step" class="wfa-save-prompt-step">
                        <div class="text-base font-extrabold text-slate-950">อัพเดทข้อมูลแล้ว</div>
                        <div class="mt-1 text-sm text-slate-500">ต้องการ export เป็น PDF / Word หรือไม่</div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-3">
                            <a href="{{ route('wfa.pdf', $report) }}" class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">PDF</a>
                            <a href="{{ route('wfa.word', $report) }}" class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">Word</a>
                            <button type="button" id="wfa-save-no-export" class="wfa-action bg-slate-900 text-white hover:bg-slate-700">ไม่</button>
                        </div>
                    </div>
                    <div id="wfa-save-stay-step" class="wfa-save-prompt-step is-hidden">
                        <div class="text-base font-extrabold text-slate-950">ต้องการอยู่หน้านี้ต่อหรือกลับไปยังรายการ WFA</div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <button type="button" id="wfa-save-stay" class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">อยู่หน้านี้ต่อ</button>
                            <a href="{{ route('wfa.index') }}" class="wfa-action bg-blue-600 text-white hover:bg-blue-700">กลับรายการ WFA</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                กรุณาตรวจสอบข้อมูลที่กรอก
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('wfa.update', $report) : route('wfa.store') }}" class="space-y-5">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <section class="wfa-panel p-5">
                <div class="mb-4">
                    <h2 class="text-base font-extrabold text-slate-950">ข้อมูลรายงาน</h2>
                    <p class="text-xs text-slate-500">รายงานจะถูกบันทึกเป็นของผู้ใช้งานที่เข้าสู่ระบบเท่านั้น</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @if ($isEdit)
                        <label class="block">
                            <span class="wfa-label">รหัส WFA</span>
                            <input type="text" value="{{ $report->wfa_code ?: \App\Models\WfaReport::formatCode((int) $report->id) }}" class="wfa-input bg-slate-50 text-slate-600" readonly>
                        </label>
                    @endif

                    <label class="block">
                        <span class="wfa-label">เรื่อง</span>
                        <input type="text" id="wfa-title-preview" value="{{ $report->title }}" class="wfa-input bg-slate-50 text-slate-600" readonly>
                    </label>

                    <label class="block">
                        <span class="wfa-label">WFA ประจำเดือน</span>
                        <select name="filter_month_key" id="wfa-filter-month" class="wfa-select">
                            @foreach ($monthOptions as $option)
                                @if ($previousOptionYear !== null && $previousOptionYear !== $option['year'])
                                    <option disabled>──────── ปี {{ $option['year'] }} ────────</option>
                                @endif
                                <option value="{{ $option['value'] }}"
                                    data-title="WFA {{ auth()->user()->full_name }} ประจำเดือน {{ $option['label'] }}"
                                    data-output-title="Output ที่เกิดขึ้นจากการทำงานของเดือน {{ $option['output_label'] }}"
                                    @selected($selectedMonthKey === $option['value'])>
                                    {{ $option['label'] }}{{ $option['is_current'] ? '  เดือนนี้' : '' }}
                                </option>
                                @php $previousOptionYear = $option['year']; @endphp
                            @endforeach
                        </select>
                        @error('filter_month_key') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section class="wfa-panel overflow-hidden">
                <div class="border-b border-slate-200 p-5">
                    <h2 id="wfa-output-title" class="text-base font-extrabold text-slate-950">Output ที่เกิดขึ้นจากการทำงานของเดือน {{ collect($monthOptions)->firstWhere('value', $selectedMonthKey)['output_label'] ?? '' }}</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-3">
                    <button type="button" data-wfa-command="bold" class="wfa-tool" title="ตัวหนา">B</button>
                    <button type="button" data-wfa-command="italic" class="wfa-tool italic" title="ตัวเอียง">I</button>
                    <button type="button" data-wfa-command="underline" class="wfa-tool underline" title="ขีดเส้นใต้">U</button>
                    <span class="mx-1 h-6 w-px bg-slate-200"></span>
                    <button type="button" data-wfa-command="insertOrderedList" class="wfa-tool" title="ลำดับเลข">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M10 6h10M10 12h10M10 18h10M4 6h1.5M4 12h1.5M4 18h1.5" />
                        </svg>
                    </button>
                    <button type="button" data-wfa-command="insertUnorderedList" class="wfa-tool" title="หัวข้อย่อย">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M9 6h11M9 12h11M9 18h11" />
                            <path fill="currentColor" stroke="none" d="M4.75 7.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM4.75 13.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM4.75 19.25a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z" />
                        </svg>
                    </button>
                    <span class="mx-1 h-6 w-px bg-slate-200"></span>
                    <button type="button" data-wfa-command="justifyLeft" class="wfa-tool" title="ชิดซ้าย">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M4 6h14M4 10h10M4 14h14M4 18h8" />
                        </svg>
                    </button>
                    <button type="button" data-wfa-command="justifyCenter" class="wfa-tool" title="กึ่งกลาง">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M5 6h14M7 10h10M5 14h14M8 18h8" />
                        </svg>
                    </button>
                    <button type="button" data-wfa-command="justifyRight" class="wfa-tool" title="ชิดขวา">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M6 6h14M10 10h10M6 14h14M12 18h8" />
                        </svg>
                    </button>
                    <button type="button" data-wfa-command="removeFormat" class="wfa-tool" title="ล้างรูปแบบ">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 19h14M8 5h8M12 5v10M9 15h6M17 9l3 3M20 9l-3 3" />
                        </svg>
                    </button>
                </div>
                <div class="wfa-editor-shell">
                    @php
                        $editorHtml = old('output_html');
                        if ($editorHtml !== null) {
                            $editorHtml = strip_tags($editorHtml, '<div><p><br><b><strong><i><em><u><s><ul><ol><li><span>');
                            $editorHtml = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $editorHtml);
                            $editorHtml = preg_replace('/javascript\s*:/i', '', $editorHtml);
                        } else {
                            $editorHtml = $report->output_html;
                        }
                    @endphp
                    <div id="wfa-editor" contenteditable="true" class="wfa-editor-page"
                        data-placeholder="พิมพ์ output ที่เกิดขึ้นจาก WFA">{!! $editorHtml !!}</div>
                </div>
                <input type="hidden" name="output_html" id="wfa-output-html" value="">
                @error('output_html') <div class="px-5 pb-4 text-xs text-red-600">{{ $message }}</div> @enderror
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    @if ($isEdit)
                        <a href="{{ route('wfa.pdf', $report) }}" class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">Export PDF</a>
                        <a href="{{ route('wfa.word', $report) }}" class="wfa-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">Export Word</a>
                    @endif
                </div>
                <button type="submit" class="wfa-action bg-blue-600 text-white shadow-sm hover:bg-blue-700">บันทึก WFA</button>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const editor = document.getElementById('wfa-editor');
            const hidden = document.getElementById('wfa-output-html');
            const monthSelect = document.getElementById('wfa-filter-month');
            const titlePreview = document.getElementById('wfa-title-preview');
            const outputTitle = document.getElementById('wfa-output-title');
            const form = editor?.closest('form');
            const savePrompt = document.getElementById('wfa-save-prompt');
            const exportStep = document.getElementById('wfa-save-export-step');
            const stayStep = document.getElementById('wfa-save-stay-step');

            document.querySelectorAll('[data-wfa-command]').forEach((button) => {
                button.addEventListener('click', () => {
                    editor.focus();
                    document.execCommand(button.dataset.wfaCommand, false, null);
                });
            });

            const syncOutput = () => {
                hidden.value = editor.innerHTML.trim();
            };

            editor.addEventListener('input', syncOutput);
            form.addEventListener('submit', syncOutput);
            monthSelect.addEventListener('change', () => {
                const option = monthSelect.options[monthSelect.selectedIndex];
                if (option?.dataset.title) {
                    titlePreview.value = option.dataset.title;
                }
                if (option?.dataset.outputTitle) {
                    outputTitle.textContent = option.dataset.outputTitle;
                }
            });
            document.getElementById('wfa-save-no-export')?.addEventListener('click', () => {
                exportStep?.classList.add('is-hidden');
                stayStep?.classList.remove('is-hidden');
            });
            document.getElementById('wfa-save-stay')?.addEventListener('click', () => {
                savePrompt?.classList.add('is-hidden');
            });
            syncOutput();
        })();
    </script>
@endsection
