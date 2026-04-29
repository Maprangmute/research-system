@extends('layouts.app')

@section('content')
    <style>
        .bo-editor-shell {
            background: transparent;
        }

        .bo-editor-panel {
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        }

        .bo-editor-hero {
            overflow: hidden;
            position: relative;
        }

        .bo-editor-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.12), transparent 24%);
            pointer-events: none;
        }

        .bo-editor-shell input:not([type='checkbox']):not([type='radio']),
        .bo-editor-shell select,
        .bo-editor-shell textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: 0.82rem 0.95rem;
            font-size: 0.95rem;
            color: #0f172a;
            background: #ffffff;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .bo-editor-shell textarea {
            min-height: 3.25rem;
            resize: vertical;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: pre-wrap;
        }

        .bo-editor-shell input:not([type='checkbox']):not([type='radio']):focus,
        .bo-editor-shell select:focus,
        .bo-editor-shell textarea:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.25);
        }

        .bo-editor-shell .project-search-select {
            position: relative;
            width: 100%;
            margin-top: 0.25rem;
        }

        .bo-editor-shell .project-search-select__native {
            display: none !important;
        }

        .bo-editor-shell .project-search-select__button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            min-height: 3.3rem;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            background: #ffffff;
            padding: 0.82rem 0.95rem;
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.5;
            text-align: left;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .bo-editor-shell .project-search-select__button:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.25);
        }

        .bo-editor-shell .project-search-select__label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .bo-editor-shell .project-search-select__icon {
            flex: 0 0 auto;
            margin-left: 0.75rem;
            color: #94a3b8;
        }

        .bo-editor-shell .project-search-select__menu {
            position: absolute;
            z-index: 80;
            left: 0;
            right: 0;
            top: calc(100% + 0.35rem);
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.16);
            padding: 0.55rem;
        }

        .bo-editor-shell .project-search-select__search {
            margin: 0 0 0.45rem;
            min-height: 2.65rem;
        }

        .bo-editor-shell .project-search-select__options {
            max-height: 16rem;
            overflow-y: auto;
        }

        .bo-editor-shell .project-search-select__option {
            display: block;
            width: 100%;
            border-radius: 12px;
            padding: 0.62rem 0.75rem;
            text-align: left;
            color: #0f172a;
            font-size: 0.92rem;
        }

        .bo-editor-shell .project-search-select__option:hover,
        .bo-editor-shell .project-search-select__option.is-selected {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .bo-editor-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 1.6rem;
            min-width: 1.6rem;
            border-radius: 8px;
            padding: 0 0.45rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #ffffff;
            box-shadow: none;
        }

        .bo-editor-step.is-dark {
            background: linear-gradient(135deg, #0f172a 0%, #155e75 100%);
        }

        .bo-editor-step.is-blue {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        }

        .bo-editor-step.is-green {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }

        .bo-editor-helper {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: #f8fbff;
            padding: 0.68rem 0.8rem;
        }

        .bo-type-helper {
            margin-top: 0.45rem;
            font-size: 0.78rem;
            line-height: 1.5;
            color: #64748b;
        }

        .bo-special-placeholder {
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            background: #fff;
            padding: 0.85rem 1rem;
            font-size: 0.82rem;
            color: #64748b;
        }

        .bo-special-panel {
            border-radius: 18px;
            border: 1px solid #dbe5f0;
            padding: 1rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.65);
        }

        .bo-special-panel__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
            padding-bottom: 0.75rem;
        }

        .bo-special-panel__title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
        }

        .bo-special-panel__hint {
            margin-top: 0.15rem;
            font-size: 0.76rem;
            color: #64748b;
            line-height: 1.5;
        }

        .bo-mini-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 0.55rem 0.85rem;
            font-size: 0.76rem;
            font-weight: 700;
            line-height: 1;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .bo-mini-action--add {
            background: #dcfce7;
            color: #166534;
        }

        .bo-mini-action--add:hover {
            background: #bbf7d0;
        }

        .bo-mini-action--remove {
            background: #fee2e2;
            color: #b91c1c;
        }

        .bo-mini-action--remove:hover {
            background: #fecaca;
        }

        .bo-special-row {
            border: 1px solid #dbe5f0;
            border-radius: 16px;
            background: rgba(255,255,255,0.78);
            padding: 1rem;
        }

        .bo-required {
            color: #dc2626;
            font-weight: 700;
            margin-left: 0.2rem;
        }

        .leader-stack {
            display: grid;
            gap: 0.75rem;
        }

        .leader-row {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto auto;
            gap: 0.6rem;
            align-items: center;
            border: 1px solid #dbe5f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 0.65rem;
        }

        .leader-row__index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            background: #0f172a;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .leader-row__append,
        .leader-row__remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 0.72rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .leader-row__append {
            background: #dcfce7;
            color: #166534;
        }

        .leader-row__append:hover {
            background: #bbf7d0;
        }

        .leader-row__remove {
            background: #fee2e2;
            color: #b91c1c;
        }

        .leader-row__remove:hover {
            background: #fecaca;
        }

        .leader-row__remove.is-disabled {
            opacity: 0.45;
            pointer-events: none;
        }

        .entry-card {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: #f8fafc;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .entry-summary,
        .entry-summary * {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .drawer-header {
            cursor: pointer;
        }

        .drawer-header:focus-visible {
            outline: 2px solid #38bdf8;
            outline-offset: 4px;
            border-radius: 16px;
        }

        .bo-field-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.9) !important;
            background: #fff5f5 !important;
        }

        .bo-field-error {
            margin-top: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #dc2626;
        }

        .bo-section-nav {
            position: fixed;
            left: 4px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 55;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.45rem;
            padding: 0.55rem;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(8px);
        }

        .bo-section-nav::before {
            content: '';
            position: absolute;
            top: 0.85rem;
            bottom: 0.85rem;
            left: 1.3rem;
            width: 2px;
            background: linear-gradient(180deg, #bfdbfe 0%, #cbd5e1 100%);
            border-radius: 999px;
            z-index: 0;
        }

        .bo-section-dot {
            position: relative;
            z-index: 1;
            width: auto;
            min-width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 800;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 0.46rem;
            overflow: hidden;
            white-space: nowrap;
            max-width: 38px;
            transition: max-width 0.2s ease, padding 0.2s ease, transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease, box-shadow 0.16s ease, color 0.16s ease;
        }

        .bo-section-dot:hover,
        .bo-section-dot:focus-visible {
            outline: none;
            transform: translateY(-1px);
            border-color: #60a5fa;
            background: #eff6ff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.18);
        }

        .bo-section-dot.is-active {
            border-color: #2563eb;
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.28);
        }

        .bo-section-dot__short {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            border-radius: 9px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.65);
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
        }

        .bo-section-dot__full {
            margin-left: 0.5rem;
            font-size: 0.78rem;
            font-weight: 700;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 0.14s ease, transform 0.14s ease;
        }

        .bo-section-nav.is-expanded .bo-section-dot {
            width: 270px;
            max-width: 270px;
            padding: 0 0.62rem;
        }

        .bo-section-nav.is-expanded .bo-section-dot__full {
            opacity: 1;
            transform: translateX(0);
        }

        .bo-section-dot.is-active .bo-section-dot__short {
            border-color: rgba(191, 219, 254, 0.9);
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        .bo-section-target {
            scroll-margin-top: 88px;
        }

        @media (max-width: 768px) {
            .bo-editor-panel,
            .entry-card {
                border-radius: 20px;
            }

            .bo-special-panel {
                border-radius: 16px;
                padding: 0.85rem;
            }

            .bo-special-row {
                padding: 0.85rem;
            }

            .bo-special-row .bo-mini-action {
                width: 100%;
            }

            .leader-row {
                grid-template-columns: 1fr;
            }

            .leader-row__index {
                display: none;
            }
        }

        @media (max-width: 1280px) {
            .bo-section-nav {
                display: none;
            }
        }
    </style>

    <div class="bo-editor-shell mx-auto max-w-[1720px] space-y-5 p-4 xl:px-6 2xl:px-8">
        <aside class="bo-section-nav" aria-label="เมนูลัดหัวข้อ">
            <button type="button" class="bo-section-dot" data-nav-target="bo-section-main" aria-label="1: ข้อมูลงานหลัก"><span class="bo-section-dot__short">1</span><span class="bo-section-dot__full">1: ข้อมูลงานหลัก</span></button>
            <button type="button" class="bo-section-dot" data-nav-target="bo-section-outputs" aria-label="3: ผลงานของงานนอกโครงการ"><span class="bo-section-dot__short">3</span><span class="bo-section-dot__full">3: ผลงานของงานนอกโครงการ</span></button>
        </aside>
        <section class="bo-editor-panel bo-editor-hero">
            <div class="relative flex flex-col gap-3 px-5 py-5 md:px-7">
                <div class="max-w-5xl">
                    <a href="{{ route('back_office.index') }}" class="text-sm text-sky-700 hover:underline">← กลับไปรายการงาน</a>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 md:text-[1.9rem]">เพิ่มงานนอกโครงการ</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        กรอกข้อมูลจากบนลงล่างในหน้าเดียว แล้วบันทึกได้ทันที
                    </p>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-[24px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('back_office.store') }}" class="space-y-6" novalidate>
            @csrf

            <div id="bo-section-main" class="bo-editor-panel bo-section-target p-6">
                <div class="flex items-center gap-2 mb-4">
                        <div class="bo-editor-step is-dark">1</div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">ข้อมูลงานหลัก</h3>
                        </div>
                    </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-600">ชื่องาน <span class="bo-required">*</span></span>
                        <input name="title" value="{{ old('title') }}" required
                            class="w-full mt-1 border border-slate-200 rounded-lg p-3" placeholder="เช่น งานจัดทำเอกสารประกอบการประชุม">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-600">จุดประสงค์ของงาน</span>
                        <textarea name="purpose" rows="5"
                            class="w-full mt-1 min-h-[9rem] border border-slate-200 rounded-lg p-3"
                            placeholder="อธิบายจุดประสงค์ของงาน">{{ old('purpose') }}</textarea>
                    </label>
                    <div class="block md:col-span-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-600">หัวหน้างานผู้รับผิดชอบ (เพิ่มได้หลายคน)</span>
                        </div>
                        <div id="leaders-body" class="leader-stack mt-3"></div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" id="add-leader"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                เพิ่มหัวหน้างานผู้รับผิดชอบ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="bo-section-entries" class="bo-editor-panel bo-section-target p-6">
                <div id="entries-body" class="space-y-4"></div>
                <div class="mt-4 flex justify-end">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="duplicate-entry"
                            class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm hover:bg-slate-50">
                            คัดลอกรายการล่าสุด
                        </button>
                        <button type="button" id="add-entry"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">เพิ่มภาระงาน</button>
                    </div>
                </div>
            </div>

            <div id="bo-section-outputs" class="bo-editor-panel bo-section-target p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="bo-editor-step is-green">3</div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">ผลงานของงานนอกโครงการ</h3>
                        </div>
                    </div>
                </div>

                <div id="task-outputs-body" class="space-y-3"></div>
                <div class="mt-4 flex justify-end">
                    <button type="button" id="add-task-output"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">เพิ่มผลงาน</button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('back_office.index') }}" class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">ยกเลิก</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">บันทึกงาน</button>
            </div>
        </form>
    </div>

    <datalist id="person-list">
        @foreach ($personList as $person)
            <option value="{{ $person }}"></option>
        @endforeach
    </datalist>
    <datalist id="output-author-names">
        @foreach (\App\Models\OutputAuthor::query()->whereNotNull('name')->whereRaw("TRIM(name) <> ''")->selectRaw('TRIM(name) as name')->distinct()->orderBy('name')->pluck('name') as $authorName)
            <option value="{{ $authorName }}"></option>
        @endforeach
    </datalist>

    <template id="entry-template">
        <div class="entry-card border rounded-2xl p-4 bg-slate-50" data-entry>
            <div class="drawer-header flex flex-col gap-3 md:flex-row md:items-center md:justify-between" data-entry-header tabindex="0" role="button" aria-expanded="true">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="text-sm font-semibold text-slate-700">ภาระงาน <span class="entry-index"></span></div>
                    <button type="button" class="text-blue-600 text-xs hover:underline toggle-entry">ย่อรายละเอียด</button>
                </div>
                <button type="button" class="text-red-500 text-sm hover:underline remove-entry">ลบภาระงาน</button>
            </div>
            <div class="entry-summary mt-2 text-sm text-slate-600 hidden">
                <div class="summary-main"></div>
                <div class="summary-sub text-xs text-slate-500"></div>
            </div>
            <div class="entry-body mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ชื่อ-นามสกุล ผู้รับผิดชอบ <span class="bo-required">*</span></span>
                    <select name="entries[__INDEX__][person_name]" required data-back-office-person-select
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3">
                        <option value="">กรุณาเลือกผู้รับผิดชอบ</option>
                        @foreach ($personList as $person)
                            <option value="{{ $person }}">{{ $person }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภท <span class="bo-required">*</span></span>
                    <select name="entries[__INDEX__][category]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3">
                        <option value="" selected disabled>กรุณาเลือกประเภทงาน</option>
                        @foreach ($categoryOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ผลงานของภาระงานนี้ / ลิ้งค์ผลงาน <span class="bo-required">*</span></span>
                    <input name="entries[__INDEX__][job_name]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3" placeholder="ผลงานของภาระงานนี้ / ลิ้งค์ผลงาน">
                </label>
                
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียดภาระงาน <span class="bo-required">*</span></span>
                    <textarea name="entries[__INDEX__][job_detail]" required rows="1"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 auto-grow"
                        placeholder="รายละเอียดงาน"></textarea>
                    <p class="mt-1 text-xs leading-5 text-slate-500">รองรับข้อความยาวและจะคงการเว้นบรรทัดตามที่พิมพ์ไว้เมื่อแสดงผล แก้ไข และส่งออก PDF</p>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่เริ่ม <span class="bo-required">*</span></span>
                    <input type="text" name="entries[__INDEX__][start_date]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 js-date-be"
                        placeholder="กรุณาใส่วันที่เริ่มต้นภาระงาน" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่สิ้นสุด <span class="bo-required">*</span></span>
                    <input type="text" name="entries[__INDEX__][end_date]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 js-date-be"
                        placeholder="กรุณาใส่วันที่สิ้นสุดภาระงาน" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ชั่วโมง <span class="bo-required">*</span></span>
                    <input type="number" step="0.01" min="0" name="entries[__INDEX__][hours]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 text-right hours-input" value="0">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">FTE (อัตโนมัติ)</span>
                    <input type="text" readonly
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 text-right bg-white fte-output" value="0.00000000">
                </label>
            </div>
        </div>
    </template>

    <template id="leader-template">
        <div class="leader-row" data-leader-row>
            <span class="leader-row__index">1</span>
            <input name="leaders[]" list="person-list" placeholder="ชื่อหัวหน้างาน / เลือกจากรายการ">
            <button type="button" class="leader-row__append append-leader">เพิ่มถัดไป</button>
            <button type="button" class="leader-row__remove remove-leader">ลบ</button>
        </div>
    </template>

    <template id="task-output-template">
        <div class="entry-card border rounded-2xl p-4 bg-slate-50" data-task-output>
            <div class="drawer-header flex flex-col gap-3 md:flex-row md:items-center md:justify-between" data-output-header tabindex="0" role="button" aria-expanded="true">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="text-sm font-semibold text-slate-700">ผลงาน <span class="task-output-index"></span></div>
                    <button type="button" class="text-emerald-700 text-xs hover:underline toggle-task-output">ย่อรายละเอียด</button>
                </div>
                <button type="button" class="text-red-500 text-sm hover:underline remove-task-output">ลบผลงาน</button>
            </div>
            <div class="task-output-summary mt-2 text-sm text-slate-600 hidden">
                <div class="summary-main"></div>
                <div class="summary-sub text-xs text-slate-500"></div>
            </div>
            <div class="task-output-body mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ชื่อผลงาน <span class="bo-required">*</span></span>
                    <input name="outputs[__TINDEX__][title]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" required>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน <span class="bo-required">*</span></span>
                    <select name="outputs[__TINDEX__][type]" class="output-type w-full mt-1 border border-slate-200 rounded-lg p-2">
                        <option value="">เลือกประเภทของผลงาน</option>
                        <option>EA101 Research Paper or Journal Article</option>
                        <option>EA102 Reports and White Papers</option>
                        <option>EA103 Presentations in Academic Conferences</option>
                        <option>EA104 Data Sets and Databases</option>
                        <option>EA105 Inventions or Innovations (Application / Website)</option>
                        <option>EA106 Policy Recommendations</option>
                        <option>EA107 Educational Materials</option>
                        <option>EA108 Communication Materials (Creations)</option>
                        <option>EA109 Change in Practices / Procedures / Policy</option>
                        <option>EA110 Number of Participants (Persons)</option>
                        <option>EA111 Satisfaction Rating (1–5)</option>
                        <option>EA112 Presentation to Committee / Policy Maker / Board</option>
                        <option>EA113 Appointment Orders (SIHP as Committee / Secretary)</option>
                        <option>EA114 Events / Forum</option>
                        <option>EA115 Capacity Building / Knowledge Sharing</option>
                        <option>EA116 Research Project</option>
                        <option>EA117 MOU</option>
                        <option>EA118 Award and Recognition</option>
                        <option>EA119 Textbook / Book chapter</option>
                        <option>EA120 Manual / Instruction / Guideline</option>
                        <option>EA999 Other Outputs</option>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียด / ลิงค์ผลงาน</span>
                    <input name="outputs[__TINDEX__][project_output]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="รายละเอียด / ลิงค์ผลงาน">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ผู้มีส่วนร่วมในงาน</span>
                    <select name="outputs[__TINDEX__][contributors][0][user_id]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-back-office-person-select data-placeholder="กรุณาเลือกผู้มีส่วนร่วม" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้มีส่วนร่วม">
                        <option value="">กรุณาเลือกผู้มีส่วนร่วม</option>
                        @foreach (($thaiPersonOptions ?? collect()) as $person)
                            <option value="{{ $person['id'] }}">{{ $person['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่เผยแพร่ <span class="bo-required">*</span></span>
                    <input type="text" name="outputs[__TINDEX__][year]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันที่เผยแพร่" autocomplete="off">
                </label>
            </div>
            <div class="special-mount mt-3"></div>
        </div>
    </template>

    <script>
        const FTE_RATE = 0.000621;
        const backOfficeErrorKeys = @json($errors->keys());
        const entriesBody = document.getElementById('entries-body');
        const addEntryBtn = document.getElementById('add-entry');
        const duplicateEntryBtn = document.getElementById('duplicate-entry');
        const entryTemplate = document.getElementById('entry-template');
        const initialEntries = @json(old('entries', []));
        const initialTaskOutputs = @json(old('outputs', []));
        const initialLeaders = @json(old('leaders', []));
        const leadersBody = document.getElementById('leaders-body');
        const leaderTemplate = document.getElementById('leader-template');
        const addLeaderBtn = document.getElementById('add-leader');
        const taskOutputsBody = document.getElementById('task-outputs-body');
        const taskOutputTemplate = document.getElementById('task-output-template');
        const addTaskOutputBtn = document.getElementById('add-task-output');
        const backOfficeForm = document.querySelector('form[action="{{ route('back_office.store') }}"]');

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getDateDisplayFields(input) {
            if (!(input instanceof HTMLElement) || !input.classList.contains('js-date-be')) return [];
            const fields = [
                input._flatpickr?.altInput,
                input.nextElementSibling,
                input.previousElementSibling,
                ...Array.from(input.closest('label')?.querySelectorAll('input') || []),
                ...Array.from(input.parentElement?.querySelectorAll('input') || []),
            ];

            return [...new Set(fields)]
                .filter((field) => field instanceof HTMLInputElement && field !== input && field.type !== 'hidden' && !field.classList.contains('flatpickr-hidden'));
        }

        function setDateInvalidInlineStyle(input, invalid) {
            getDateDisplayFields(input).forEach((field) => {
                if (invalid) {
                    field.style.setProperty('border-color', '#ef4444', 'important');
                    field.style.setProperty('box-shadow', '0 0 0 4px rgba(254, 202, 202, 0.75)', 'important');
                    field.style.setProperty('background', '#fff5f5', 'important');
                } else {
                    field.style.removeProperty('border-color');
                    field.style.removeProperty('box-shadow');
                    field.style.removeProperty('background');
                }
            });
        }

        function getVisibleField(input) {
            if (!(input instanceof HTMLElement)) return null;
            const altInput = input._flatpickr?.altInput;
            if (altInput instanceof HTMLElement) return altInput;
            const searchButton = input.tomselect?.wrapper?.querySelector?.('[data-project-search-select-toggle]');
            if (searchButton instanceof HTMLElement) return searchButton;
            return input;
        }

        function getFieldErrorNode(input) {
            const field = getVisibleField(input);
            if (!field) return null;
            let errorNode = field.parentElement?.querySelector('.bo-field-error');
            if (!errorNode) {
                errorNode = document.createElement('div');
                errorNode.className = 'bo-field-error';
                field.parentElement?.appendChild(errorNode);
            }
            return errorNode;
        }

        function clearFieldInvalid(input) {
            const field = getVisibleField(input);
            field?.classList.remove('bo-field-invalid');
            input?.classList?.remove('bo-field-invalid');
            setDateInvalidInlineStyle(input, false);
            const errorNode = field?.parentElement?.querySelector('.bo-field-error');
            if (errorNode) errorNode.remove();
        }

        function markFieldInvalid(input, message) {
            const field = getVisibleField(input);
            field?.classList.add('bo-field-invalid');
            input?.classList?.add('bo-field-invalid');
            setDateInvalidInlineStyle(input, true);
            const errorNode = getFieldErrorNode(input);
            if (errorNode) errorNode.textContent = message || 'กรุณาตรวจสอบข้อมูลนี้';
        }

        function normalizeLaravelErrorKey(key) {
            if (!key) return '';
            return key.replace(/\.(\d+)(?=\.|$)/g, '[$1]').replace(/\.([^.]+)/g, '[$1]');
        }

        function findInputByErrorKey(key) {
            const normalized = normalizeLaravelErrorKey(key);
            return backOfficeForm?.querySelector(`[name="${normalized}"]`) || null;
        }

        function revealFieldContext(input) {
            const entryCard = input?.closest?.('[data-entry]');
            if (entryCard) {
                collapseAllEntries(entryCard);
                setCollapsed(entryCard, false);
            }
            const outputCard = input?.closest?.('[data-task-output]');
            if (outputCard) {
                collapseAllTaskOutputs(outputCard);
                setTaskOutputCollapsed(outputCard, false);
            }
        }

        function scrollToField(input) {
            const field = getVisibleField(input);
            if (!field) return;
            revealFieldContext(input);
            window.setTimeout(() => {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus?.({ preventScroll: true });
            }, 80);
        }

        function getBackOfficeFieldLabel(input) {
            const rawLabel = input.closest('label')?.querySelector('span')?.textContent
                || input.parentElement?.querySelector('span')?.textContent
                || input.dataset.placeholder
                || input.placeholder
                || input.name
                || 'ช่องนี้';

            return String(rawLabel)
                .replace(/\*/g, '')
                .replace(/\s+/g, ' ')
                .trim() || 'ช่องนี้';
        }

        function getInvalidSummaryLabel(field) {
            if (!(field instanceof HTMLElement)) return 'ช่องข้อมูลที่จำเป็น';
            if (field.dataset?.invalidSummary) return field.dataset.invalidSummary;
            if (field.matches?.('.ea101-section')) return 'รายชื่อผู้เขียน';
            if (field.matches?.('.ea103-section')) return 'รายชื่อผู้นำเสนอ';
            if (field.matches?.('.ea118-section')) return 'ผู้ได้รับรางวัล';
            if (field.matches?.('.ea119-section')) return 'รายชื่อผู้เขียนหนังสือ';
            if (field.name === 'year') return 'วันที่เผยแพร่';
            return getBackOfficeFieldLabel(field);
        }

        function shouldSkipRequiredField(input) {
            if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) {
                return true;
            }

            if (input.disabled || input.readOnly || input.closest('template')) return true;
            if (input.type === 'hidden' && !input.classList.contains('js-date-be')) return true;

            const name = String(input.name || '').trim();
            if (!name && !input.matches('[data-person-search-select],[data-person-manual-input]')) return true;
            if (['_token', '_method'].includes(name)) return true;
            if (name === 'purpose') return true;
            if (name.endsWith('[project_output]')) return true;

            return false;
        }

        function isOptionalOutputMetricField(input) {
            const name = String(input?.name || '').trim();
            return name === 'quartile' || name === 'impact_factor' || name.endsWith('[quartile]') || name.endsWith('[impact_factor]');
        }

        function validateSingleField(input) {
            if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) {
                return true;
            }

            clearFieldInvalid(input);
            if (shouldSkipRequiredField(input)) return true;

            const rawValue = (input.value ?? '').toString().trim();
            const label = getBackOfficeFieldLabel(input);

            if (rawValue === '') {
                if (isOptionalOutputMetricField(input)) return true;
                input.dataset.invalidSummary = label;
                markFieldInvalid(input, `กรุณากรอก${label}`);
                return false;
            }

            if (rawValue !== '' && input.classList.contains('js-date-be') && !parseDateInputValue(rawValue)) {
                input.dataset.invalidSummary = `${label}ไม่ถูกต้อง`;
                markFieldInvalid(input, `${label}ไม่ถูกต้อง`);
                return false;
            }

            if (typeof input.checkValidity === 'function' && !input.checkValidity()) {
                input.dataset.invalidSummary = label;
                markFieldInvalid(input, input.validationMessage || `${label}ไม่ถูกต้อง`);
                return false;
            }

            return true;
        }

        function validateConditionalPersonRows() {
            const invalidFields = [];

            backOfficeForm?.querySelectorAll('[data-conditional-person-row]').forEach((row) => {
                const typeSelect = row.querySelector('[data-person-type-select]');
                const searchSelect = row.querySelector('[data-person-search-select]');
                const manualInput = row.querySelector('[data-person-manual-input]');
                const hiddenInput = row.querySelector('[data-person-name-hidden]');
                const mode = String(typeSelect?.value || '').trim();
                const displayInput = mode === 'internal' ? searchSelect : manualInput;

                if (typeSelect instanceof HTMLSelectElement && !validateSingleField(typeSelect)) {
                    invalidFields.push(typeSelect);
                    return;
                }

                if (!['internal', 'external', 'unit'].includes(mode)) return;

                const value = mode === 'unit'
                    ? String(hiddenInput?.value || SPECIAL_UNIT_NAME).trim()
                    : String((displayInput?.value || hiddenInput?.value || '')).trim();

                if (!value && displayInput instanceof HTMLElement) {
                    clearFieldInvalid(displayInput);
                    displayInput.dataset.invalidSummary = getBackOfficeFieldLabel(displayInput);
                    markFieldInvalid(displayInput, `กรุณากรอก${getBackOfficeFieldLabel(displayInput)}`);
                    invalidFields.push(displayInput);
                }
            });

            return invalidFields;
        }

        function validateRequiredRepeatRows() {
            const invalidFields = [];
            const rules = [
                ['.ea101-section', '[data-output-author]', 'กรุณาเพิ่มผู้เขียนอย่างน้อย 1 คน'],
                ['.ea103-section', '[data-output-conference-presenter]', 'กรุณาเพิ่มผู้นำเสนออย่างน้อย 1 คน'],
                ['.ea118-section', '[data-output-award-recipient]', 'กรุณาเพิ่มผู้ได้รับรางวัลอย่างน้อย 1 คน'],
                ['.ea119-section', '[data-output-book-author]', 'กรุณาเพิ่มผู้เขียนอย่างน้อย 1 คน'],
            ];

            rules.forEach(([sectionSelector, rowSelector, message]) => {
                backOfficeForm?.querySelectorAll(sectionSelector).forEach((section) => {
                    section.classList.remove('bo-field-invalid');
                    const oldError = section.querySelector(':scope > .bo-field-error');
                    if (oldError) oldError.remove();

                    if (section.querySelector(rowSelector)) return;

                    section.classList.add('bo-field-invalid');
                    const errorNode = document.createElement('div');
                    errorNode.className = 'bo-field-error';
                    errorNode.textContent = message;
                    section.appendChild(errorNode);
                    section.dataset.invalidSummary = message.replace(/^กรุณา/, '');
                    invalidFields.push(section);
                });
            });

            return invalidFields;
        }

        function validateBackOfficeForm() {
            const fields = Array.from(backOfficeForm?.querySelectorAll('input, select, textarea') || [])
                .filter(field => !field.disabled && !field.closest('template'));
            const invalidFields = [];

            fields.forEach(field => {
                if (!validateSingleField(field)) invalidFields.push(field);
            });

            invalidFields.push(...validateConditionalPersonRows());
            invalidFields.push(...validateRequiredRepeatRows());

            return invalidFields;
        }

        function showRequiredAlert(invalidFields = []) {
            const labels = [...new Set(
                invalidFields
                    .map(getInvalidSummaryLabel)
                    .map((label) => String(label || '').replace(/\*/g, '').replace(/\s+/g, ' ').trim())
                    .filter(Boolean)
            )].slice(0, 8);
            const moreCount = Math.max(0, invalidFields.length - labels.length);
            const detailHtml = labels.length
                ? `<div style="margin-top:14px;text-align:left"><div style="font-weight:700;margin-bottom:6px">ยังขาด:</div><ul style="margin:0;padding-left:1.2rem">${labels.map((label) => `<li>${escapeHtml(label)}</li>`).join('')}${moreCount ? `<li>และอีก ${moreCount} รายการ</li>` : ''}</ul></div>`
                : '';

            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ยังบันทึกไม่ได้',
                    html: `กรุณากรอกข้อมูลให้ครบและตรวจสอบช่องสีแดง${detailHtml}`,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#dc2626',
                });
                return;
            }

            window.alert(`กรุณากรอกข้อมูลให้ครบและตรวจสอบช่องสีแดง${labels.length ? `\n\nยังขาด:\n- ${labels.join('\n- ')}` : ''}`);
        }

        async function confirmRemoval(options = {}) {
            if (window.Swal) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: options.title || 'ยืนยันการลบ',
                    text: options.text || 'ต้องการลบรายการนี้ใช่หรือไม่',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'ลบรายการ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                });
                return result.isConfirmed;
            }

            return window.confirm(options.text || 'ต้องการลบรายการนี้ใช่หรือไม่');
        }

        function applyServerValidationState() {
            const invalidInputs = backOfficeErrorKeys
                .map(findInputByErrorKey)
                .filter(Boolean);

            invalidInputs.forEach((input) => {
                const label = input.closest('label')?.querySelector('span')?.textContent?.trim() || 'ฟิลด์นี้';
                markFieldInvalid(input, `${label}ไม่ถูกต้องหรือยังไม่ครบ`);
            });

            if (invalidInputs[0]) {
                scrollToField(invalidInputs[0]);
            }

            if (invalidInputs.length && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อมูลไม่ครบหรือไม่ถูกต้อง',
                    text: 'กรุณาตรวจสอบช่องที่ไฮไลต์สีแดง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#dc2626',
                });
            }
        }

        function syncMainTaskDateField(name) {
            const input = backOfficeForm?.querySelector(`input[name="${name}"]`);
            const fp = input?._flatpickr;
            const selectedDate = fp?.selectedDates?.[0];
            if (!fp?.altInput || !selectedDate) return;

            const day = selectedDate.getDate();
            const month = selectedDate.toLocaleDateString('th-TH', { month: 'long' });
            const year = selectedDate.getFullYear() + 543;
            fp.altInput.value = `${day} ${month} ${year}`;
        }

        function syncMainTaskDateCalendarYear(name) {
            const input = backOfficeForm?.querySelector(`input[name="${name}"]`);
            const fp = input?._flatpickr;
            const yearInput = fp?.calendarContainer?.querySelector('.flatpickr-current-month input.cur-year');
            if (!yearInput) return;

            const rawYear = parseInt(yearInput.value || '0', 10);
            if (!Number.isNaN(rawYear) && rawYear > 0 && rawYear < 2400) {
                yearInput.value = String(rawYear + 543);
            }
        }

        function syncMainTaskDateUi(name) {
            syncMainTaskDateField(name);
            syncMainTaskDateCalendarYear(name);
            requestAnimationFrame(() => syncMainTaskDateCalendarYear(name));
            window.setTimeout(() => syncMainTaskDateCalendarYear(name), 0);
        }

        function syncDateFieldUi(input) {
            const fp = input?._flatpickr;
            const selectedDate = fp?.selectedDates?.[0];
            if (fp?.altInput && selectedDate) {
                const day = selectedDate.getDate();
                const month = selectedDate.toLocaleDateString('th-TH', { month: 'long' });
                const year = selectedDate.getFullYear() + 543;
                fp.altInput.value = `${day} ${month} ${year}`;
            }

            const yearInput = fp?.calendarContainer?.querySelector('.flatpickr-current-month input.cur-year');
            const rawYear = parseInt(yearInput?.value || '0', 10);
            if (yearInput && !Number.isNaN(rawYear) && rawYear > 0 && rawYear < 2400) {
                yearInput.value = String(rawYear + 543);
            }
        }

        function bindDateFieldBeSync(input, key, retries = 0) {
            const fp = input?._flatpickr;
            if (!fp) {
                if (retries >= 40) return;
                window.setTimeout(() => bindDateFieldBeSync(input, key, retries + 1), 80);
                return;
            }

            const boundKey = `beSync${key}`;
            if (!input.dataset[boundKey]) {
                input.dataset[boundKey] = '1';
                ['onReady', 'onOpen', 'onMonthChange', 'onYearChange', 'onValueUpdate', 'onChange'].forEach((hook) => {
                    appendFlatpickrHook(fp, hook, () => {
                        syncDateFieldUi(input);
                        requestAnimationFrame(() => syncDateFieldUi(input));
                    });
                });
            }

            syncDateFieldUi(input);
        }

        function bindMainTaskDateFix(retries = 0) {
            const startInput = backOfficeForm?.querySelector('input[name="start_date"]');
            const endInput = backOfficeForm?.querySelector('input[name="end_date"]');
            if (!startInput?.classList.contains('js-date-be') || !endInput?.classList.contains('js-date-be')) return;
            const startFp = startInput?._flatpickr;
            const endFp = endInput?._flatpickr;

            if (!startFp || !endFp) {
                if (retries >= 40) return;
                window.setTimeout(() => bindMainTaskDateFix(retries + 1), 80);
                return;
            }

            if (!startInput.dataset.beMainDateBound) {
                startInput.dataset.beMainDateBound = '1';
                ['onReady', 'onOpen', 'onMonthChange', 'onYearChange', 'onValueUpdate', 'onChange'].forEach((hook) => {
                    startFp.config[hook].push(() => {
                        syncMainTaskDateUi('start_date');
                    });
                });
            }

            if (!endInput.dataset.beMainDateBound) {
                endInput.dataset.beMainDateBound = '1';
                ['onReady', 'onOpen', 'onMonthChange', 'onYearChange', 'onValueUpdate', 'onChange'].forEach((hook) => {
                    endFp.config[hook].push(() => {
                        syncMainTaskDateUi('end_date');
                    });
                });
            }

            syncMainTaskDateUi('start_date');
            syncMainTaskDateUi('end_date');
        }

        function bindMinDateFromSource(sourceInput, targetInput, key, retries = 0) {
            if (!(sourceInput instanceof HTMLElement) || !(targetInput instanceof HTMLElement)) return;

            const sourceFp = sourceInput._flatpickr;
            const targetFp = targetInput._flatpickr;
            if (!sourceFp || !targetFp) {
                if (retries >= 40) return;
                window.setTimeout(() => bindMinDateFromSource(sourceInput, targetInput, key, retries + 1), 80);
                return;
            }

            const updaterKey = `__backOfficeMinDate_${key}`;
            if (!targetInput[updaterKey]) {
                targetInput[updaterKey] = () => {
                    const sourceDate = sourceInput._flatpickr?.selectedDates?.[0]
                        || parseDateInputValue(sourceInput.value)
                        || parseDateInputValue(sourceInput._flatpickr?.altInput?.value);
                    const minDate = sourceDate ? normalizeDateOnly(sourceDate) : null;

                    targetFp.set('minDate', minDate || null);
                    syncDateFieldUi(targetInput);
                    requestAnimationFrame(() => syncDateFieldUi(targetInput));

                    const targetDate = targetFp.selectedDates?.[0]
                        || parseDateInputValue(targetInput.value)
                        || parseDateInputValue(targetFp.altInput?.value);

                    if (targetDate && minDate && normalizeDateOnly(targetDate) < minDate) {
                        targetFp.clear();
                        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                };
            }

            if (!targetInput.dataset[`minBound${key}`]) {
                targetInput.dataset[`minBound${key}`] = '1';
                sourceInput.addEventListener('change', targetInput[updaterKey]);
                sourceInput.addEventListener('input', targetInput[updaterKey]);
                appendFlatpickrHook(sourceFp, 'onChange', targetInput[updaterKey]);
                appendFlatpickrHook(sourceFp, 'onValueUpdate', targetInput[updaterKey]);
                appendFlatpickrHook(targetFp, 'onOpen', targetInput[updaterKey]);
            }

            targetInput[updaterKey]();
        }

        function bindMainTaskDateMinRules() {
            const mainStart = backOfficeForm?.querySelector('input[name="start_date"]');
            const mainEnd = backOfficeForm?.querySelector('input[name="end_date"]');
            if (mainStart?.classList.contains('js-date-be') && mainEnd?.classList.contains('js-date-be')) {
                bindMinDateFromSource(mainStart, mainEnd, 'main_range');
            }
        }

        function bindEntryDateRules(entry, index) {
            const mainStart = backOfficeForm?.querySelector('input[name="start_date"]');
            const entryStart = entry.querySelector(`input[name="entries[${index}][start_date]"]`);
            const entryEnd = entry.querySelector(`input[name="entries[${index}][end_date]"]`);

            if (entryStart) bindDateFieldBeSync(entryStart, `entryStart${index}`);
            if (entryEnd) bindDateFieldBeSync(entryEnd, `entryEnd${index}`);

            if (entryStart && entryEnd) {
                bindMinDateFromSource(entryStart, entryEnd, `entry_range_${index}`);
            }
            if (mainStart?.classList.contains('js-date-be') && entryStart) {
                bindMinDateFromSource(mainStart, entryStart, `entry_main_start_${index}`);
            }
        }

        function bindTaskOutputDateRules(row, index) {
            const mainStart = backOfficeForm?.querySelector('input[name="start_date"]');
            const outputDate = row.querySelector(`input[name="outputs[${index}][year]"]`);
            if (outputDate) bindDateFieldBeSync(outputDate, `outputYear${index}`);
            if (mainStart?.classList.contains('js-date-be') && outputDate) {
                bindMinDateFromSource(mainStart, outputDate, `output_main_start_${index}`);
            }
        }

        function resizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;
        }

        function updateFte(row) {
            const hoursInput = row.querySelector('.hours-input');
            const fteOutput = row.querySelector('.fte-output');
            const hours = parseFloat(hoursInput.value || 0);
            fteOutput.value = (hours * FTE_RATE).toFixed(8);
        }

        function updateIndex() {
            entriesBody.querySelectorAll('[data-entry]').forEach((entry, idx) => {
                entry.querySelector('.entry-index').textContent = `#${idx + 1}`;
            });
        }

        function updateTaskOutputIndex() {
            taskOutputsBody.querySelectorAll('[data-task-output]').forEach((output, idx) => {
                const indexEl = output.querySelector('.task-output-index');
                if (indexEl) indexEl.textContent = `#${idx + 1}`;
            });
        }

        function bindEntry(entry) {
            const hoursInput = entry.querySelector('.hours-input');
            const textarea = entry.querySelector('.auto-grow');
            const removeBtn = entry.querySelector('.remove-entry');
            const toggleBtn = entry.querySelector('.toggle-entry');
            const header = entry.querySelector('[data-entry-header]');
            const inputs = entry.querySelectorAll('input, select, textarea');
            hoursInput?.addEventListener('input', () => updateFte(entry));
            textarea?.addEventListener('input', () => resizeTextarea(textarea));
            removeBtn?.addEventListener('click', async () => {
                const person = entry.querySelector('[name$="[person_name]"]')?.value?.trim() || 'ไม่ระบุผู้รับผิดชอบ';
                const jobName = entry.querySelector('input[name$="[job_name]"]')?.value?.trim() || 'ไม่มีชื่อภาระงาน';
                const confirmed = await confirmRemoval({
                    title: 'ลบภาระงาน',
                    text: `ต้องการลบภาระงานของ ${person} : ${jobName} ใช่หรือไม่`,
                    confirmText: 'ลบภาระงาน',
                });
                if (!confirmed) return;
                entry.remove();
                updateIndex();
            });
            const toggleEntry = () => {
                collapseAllEntries(entry);
                setCollapsed(entry, !isCollapsed(entry));
            };
            toggleBtn?.addEventListener('click', (event) => {
                event.stopPropagation();
                toggleEntry();
            });
            const handleEntryCardClick = (event) => {
                if (event.target.closest('button, input, select, textarea, a, option, label')) return;
                if (!isCollapsed(entry) && event.target.closest('.entry-body')) return;
                toggleEntry();
            };
            entry.addEventListener('click', handleEntryCardClick);
            header?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                toggleEntry();
            });
            removeBtn?.addEventListener('click', event => event.stopPropagation());
            inputs.forEach(input => {
                input.addEventListener('input', () => updateSummary(entry));
                input.addEventListener('change', () => updateSummary(entry));
                input.addEventListener('input', () => clearFieldInvalid(input));
                input.addEventListener('change', () => clearFieldInvalid(input));
            });
            resizeTextarea(textarea);
            updateFte(entry);
            updateSummary(entry);
        }

        function renderSpecialPlaceholder(message, subMessage = '') {
            const wrap = document.createElement('div');
            wrap.className = 'bo-special-placeholder';
            wrap.innerHTML = `
                <div class="font-semibold text-slate-700">${message}</div>
                ${subMessage ? `<div class="mt-1 text-[12px] text-slate-500">${subMessage}</div>` : ''}
            `;
            return wrap;
        }

        function getOutputTypeDescription(type) {
            if (type.startsWith('EA101')) {
                return 'ฟอร์มเฉพาะสำหรับผลงานบทความวิจัยในวารสาร พร้อมข้อมูล journal, quartile, IF และผู้เขียน';
            }
            if (type.startsWith('EA103')) {
                return 'ฟอร์มเฉพาะสำหรับผลงานการนำเสนอวิชาการ พร้อมข้อมูล conference, รูปแบบการนำเสนอ และผู้นำเสนอ';
            }
            if (type.startsWith('EA118')) {
                return 'ฟอร์มเฉพาะสำหรับรางวัลและการยกย่อง พร้อมข้อมูลผู้ได้รับรางวัลหลายรายการ';
            }
            if (type.startsWith('EA119')) {
                return 'ฟอร์มเฉพาะสำหรับหนังสือหรือบทในหนังสือ พร้อมข้อมูลชื่อหนังสือ สำนักพิมพ์ และผู้เขียน';
            }
            return 'ประเภทนี้ใช้ข้อมูลพื้นฐานของผลงานได้ทันที และยังไม่มีฟิลด์เฉพาะประเภทเพิ่มเติม';
        }

        function renderEA101Section(prefix) {
            const wrap = document.createElement('div');
            wrap.className = 'bo-special-panel ea101-section bg-blue-50/60 space-y-3';
            wrap.innerHTML = `
                <div class="bo-special-panel__head">
                    <div>
                        <div class="bo-special-panel__title">EA101 Research Paper or Journal Article</div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="bo-special-row md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">Journal</span>
                            <input name="${prefix}[journal]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น IEEE Access">
                        </label>
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">Ranking</span>
                            <select name="${prefix}[quartile]" class="w-full mt-1 border border-slate-200 rounded-lg p-2">
                                <option value="">เลือก Ranking</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
          <option value="TCI 1">TCI 1</option>
          <option value="TCI 2">TCI 2</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">Impact Factor</span>
                            <input name="${prefix}[impact_factor]" type="number" step="0.001"
                                class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น 3.215">
                        </label>
                    </div>
                </div>
                <div class="bo-special-row">
                    <div class="bo-special-panel__head">
                        <div>
                            <div class="bo-special-panel__title">รายชื่อผู้เขียน (Authors)</div>
                        </div>
                    </div>
                    <div class="space-y-2" data-output-authors data-prefix="${prefix}"></div>
                    <div class="mt-4 flex justify-center">
                        <button type="button" class="bo-mini-action bo-mini-action--add" onclick="addOutputAuthor(this)">+ เพิ่มผู้เขียน</button>
                    </div>
                </div>
            `;
            return wrap;
        }

        function renderEA103Section(prefix) {
            const wrap = document.createElement('div');
            wrap.className = 'bo-special-panel ea103-section bg-amber-50/60 space-y-3';
            wrap.innerHTML = `
                <div class="bo-special-panel__head">
                    <div>
                        <div class="bo-special-panel__title">EA103 Presentations in Academic Conferences</div>
                    </div>
                </div>
                <div class="bo-special-row grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                    <label class="block md:col-span-3">
                        <span class="text-xs font-semibold text-slate-600">ชื่อเวที/Conference</span>
                        <input name="${prefix}[conference_name]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น PMAC 2025">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภทนำเสนอ</span>
                        <select name="${prefix}[conference_presentation_type]" class="w-full mt-1 border border-slate-200 rounded-lg p-2">
                            <option value="">เลือก</option>
                            <option value="Oral Presentation">Oral Presentation</option>
                            <option value="Poster Presentation">Poster Presentation</option>
                            <option value="Panel Discussion">Panel Discussion</option>
                            <option value="Keynote Speech">Keynote Speech</option>
                        </select>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-xs font-semibold text-slate-600">สถานที่</span>
                        <input name="${prefix}[conference_location]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เมือง/ประเทศ">
                    </label>
                </div>
                <div class="bo-special-row">
                    <div class="bo-special-panel__head">
                        <div>
                            <div class="bo-special-panel__title">รายชื่อผู้นำเสนอ</div>
                        </div>
                    </div>
                    <div class="space-y-2" data-output-conference-presenters data-prefix="${prefix}"></div>
                    <div class="mt-4 flex justify-center">
                        <button type="button" class="bo-mini-action bo-mini-action--add" onclick="addOutputConferencePresenter(this)">+ เพิ่มผู้นำเสนอ</button>
                    </div>
                </div>
            `;
            return wrap;
        }

        function renderEA118Section(prefix) {
            const wrap = document.createElement('div');
            wrap.className = 'bo-special-panel ea118-section bg-violet-50/60 space-y-3';
            wrap.innerHTML = `
                <div class="bo-special-panel__head">
                    <div>
                        <div class="bo-special-panel__title">EA118 Award and Recognition</div>
                    </div>
                </div>
                <div class="space-y-2" data-output-award-recipients data-prefix="${prefix}"></div>
                <div class="mt-6 flex justify-center pt-2">
                    <button type="button" class="bo-mini-action bo-mini-action--add" onclick="addOutputAwardRecipient(this)">+ เพิ่มผู้ได้รับรางวัล</button>
                </div>
            `;
            return wrap;
        }

        function renderEA119Section(prefix) {
            const wrap = document.createElement('div');
            wrap.className = 'bo-special-panel ea119-section bg-emerald-50/60 space-y-3';
            wrap.innerHTML = `
                <div class="bo-special-panel__head">
                    <div>
                        <div class="bo-special-panel__title">EA119 Textbook / Book chapter</div>
                    </div>
                </div>
                <div class="bo-special-row grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ชื่อหนังสือ</span>
                        <input name="${prefix}[book_title]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น Textbook of Public Health">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ชื่อสำนักพิมพ์</span>
                        <input name="${prefix}[publisher_name]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น Elsevier">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ชื่อบท / เลขหน้า</span>
                        <input name="${prefix}[chapter_pages]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น Chapter 2, pp. 45-66">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เลข ISBN</span>
                        <input name="${prefix}[isbn]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เลข ISBN">
                    </label>
                </div>
                <div class="bo-special-row">
                    <div class="bo-special-panel__head">
                        <div>
                            <div class="bo-special-panel__title">รายชื่อผู้เขียน</div>
                        </div>
                    </div>
                    <div class="space-y-2" data-output-book-authors data-prefix="${prefix}"></div>
                    <div class="mt-6 flex justify-center pt-2">
                        <button type="button" class="bo-mini-action bo-mini-action--add" onclick="addOutputBookAuthor(this)">+ เพิ่มผู้เขียน</button>
                    </div>
                </div>
            `;
            return wrap;
        }

        const backOfficePeople = @json(($personList ?? collect())->values());
        const backOfficeResearchPeople = @json(($researchPersonList ?? collect())->values());
        const escapeOption = (value) => String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const backOfficePeopleOptions = backOfficePeople.map((name) => `<option value="${escapeOption(name)}">${escapeOption(name)}</option>`).join('');
        const backOfficeResearchPeopleOptions = backOfficeResearchPeople.map((name) => `<option value="${escapeOption(name)}">${escapeOption(name)}</option>`).join('');
        const SPECIAL_UNIT_NAME = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';

        function closeBackOfficeSearchDropdowns(except = null) {
            document.querySelectorAll('[data-project-search-select-menu]').forEach((menu) => {
                if (except && menu === except) return;
                menu.classList.add('hidden');
            });
        }

        function ensureBackOfficeSearchDropdown(select, config = {}) {
            if (!(select instanceof HTMLSelectElement)) return null;

            const staleWrapper = select.nextElementSibling;
            if (staleWrapper instanceof HTMLElement && staleWrapper.matches('.project-search-select') && !select.tomselect) {
                staleWrapper.remove();
                delete select.dataset.projectSearchDropdown;
            }

            if (select.dataset.projectSearchDropdown === '1' && select.tomselect) {
                select.tomselect.refreshOptions?.();
                return select.tomselect;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'project-search-select';
            wrapper.dataset.projectSearchDropdown = '1';
            wrapper.innerHTML = `
                <button type="button" class="project-search-select__button" data-project-search-select-toggle>
                    <span class="project-search-select__label" data-project-search-select-label></span>
                    <span class="project-search-select__icon">▾</span>
                </button>
                <div class="project-search-select__menu hidden" data-project-search-select-menu>
                    <input type="text" class="project-search-select__search" data-project-search-select-search>
                    <div class="project-search-select__options" data-project-search-select-options></div>
                    <div class="project-search-select__empty hidden" data-project-search-select-empty>ไม่พบข้อมูล</div>
                </div>
            `;

            select.classList.add('project-search-select__native');
            select.insertAdjacentElement('afterend', wrapper);

            const toggle = wrapper.querySelector('[data-project-search-select-toggle]');
            const label = wrapper.querySelector('[data-project-search-select-label]');
            const menu = wrapper.querySelector('[data-project-search-select-menu]');
            const searchInput = wrapper.querySelector('[data-project-search-select-search]');
            const optionsWrap = wrapper.querySelector('[data-project-search-select-options]');
            const emptyEl = wrapper.querySelector('[data-project-search-select-empty]');

            if (!(toggle instanceof HTMLButtonElement) ||
                !(label instanceof HTMLElement) ||
                !(menu instanceof HTMLElement) ||
                !(searchInput instanceof HTMLInputElement) ||
                !(optionsWrap instanceof HTMLElement) ||
                !(emptyEl instanceof HTMLElement)) {
                wrapper.remove();
                return null;
            }

            const placeholder = String(config.placeholder || select.dataset.placeholder || select.querySelector('option[value=""]')?.textContent?.trim() || 'กรุณาเลือกชื่อ').trim();
            searchInput.placeholder = String(config.searchPlaceholder || select.dataset.searchPlaceholder || 'พิมพ์เพื่อค้นหา').trim();

            const optionItems = () => Array.from(select.options)
                .filter((option) => option.value !== '')
                .map((option) => ({ value: String(option.value), label: String(option.textContent || '').trim() }));

            const syncLabel = () => {
                const selected = Array.from(select.options).find((option) => option.value === select.value);
                label.textContent = selected && selected.value ? String(selected.textContent || '').trim() : placeholder;
            };

            const renderOptions = () => {
                const term = String(searchInput.value || '').toLowerCase().trim();
                const items = optionItems().filter((item) => term === '' || item.label.toLowerCase().includes(term));
                optionsWrap.innerHTML = '';
                if (!items.length) {
                    emptyEl.classList.remove('hidden');
                    return;
                }
                emptyEl.classList.add('hidden');
                items.forEach((item) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'project-search-select__option';
                    button.textContent = item.label;
                    if (item.value === String(select.value || '')) button.classList.add('is-selected');
                    button.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                    });
                    button.addEventListener('click', () => {
                        select.value = item.value;
                        syncLabel();
                        renderOptions();
                        menu.classList.add('hidden');
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    optionsWrap.appendChild(button);
                });
            };

            toggle.addEventListener('click', () => {
                if (select.disabled) return;
                const willOpen = menu.classList.contains('hidden');
                closeBackOfficeSearchDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                if (willOpen) {
                    searchInput.value = '';
                    renderOptions();
                    searchInput.focus();
                }
            });

            searchInput.addEventListener('input', renderOptions);
            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target) && event.target !== select) {
                    menu.classList.add('hidden');
                }
            });
            select.addEventListener('change', () => {
                syncLabel();
                renderOptions();
            });

            const api = {
                wrapper,
                refreshOptions() {
                    renderOptions();
                    syncLabel();
                },
                clear(silent = false) {
                    select.value = '';
                    syncLabel();
                    renderOptions();
                    if (!silent) select.dispatchEvent(new Event('change', { bubbles: true }));
                },
                setValue(value, silent = false) {
                    select.value = String(value ?? '');
                    syncLabel();
                    renderOptions();
                    if (!silent) select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            syncLabel();
            renderOptions();
            select.dataset.projectSearchDropdown = '1';
            select.tomselect = api;
            return api;
        }

        function initBackOfficeSearchDropdown(scope = document) {
            scope.querySelectorAll('[data-back-office-person-select]').forEach((select) => {
                if (!(select instanceof HTMLSelectElement)) return;
                const placeholder = select.dataset.placeholder || select.querySelector('option[value=""]')?.textContent?.trim() || 'กรุณาเลือกชื่อ';
                const searchPlaceholder = select.dataset.searchPlaceholder || 'พิมพ์เพื่อค้นหาชื่อ';
                const dropdown = ensureBackOfficeSearchDropdown(select, { placeholder, searchPlaceholder });
                dropdown?.refreshOptions?.();
            });
        }

        function syncBackOfficeConditionalRow(row) {
            if (!(row instanceof HTMLElement)) return;
            const typeSelect = row.querySelector('[data-person-type-select]');
            const selectWrap = row.querySelector('[data-person-select-wrap]');
            const manualWrap = row.querySelector('[data-person-manual-wrap]');
            const searchSelect = row.querySelector('[data-person-search-select]');
            const manualInput = row.querySelector('[data-person-manual-input]');
            const hiddenInput = row.querySelector('[data-person-name-hidden]');
            if (!(typeSelect instanceof HTMLSelectElement) || !(hiddenInput instanceof HTMLInputElement)) return;

            const mode = String(typeSelect.value || '').trim();
            const isInternal = mode === 'internal';
            const isExternal = mode === 'external';
            const isUnit = mode === 'unit';

            selectWrap?.classList.toggle('hidden', !isInternal);
            manualWrap?.classList.toggle('hidden', !isExternal && !isUnit);

            if (searchSelect instanceof HTMLSelectElement) {
                searchSelect.disabled = !isInternal;
                if (isInternal) {
                    initBackOfficeSearchDropdown(row);
                    hiddenInput.value = searchSelect.value || '';
                } else {
                    try { searchSelect.tomselect?.clear?.(true); } catch (_) {}
                    searchSelect.value = '';
                }
            }

            if (manualInput instanceof HTMLInputElement) {
                const unitLabel = manualInput.dataset.unitPlaceholder || SPECIAL_UNIT_NAME;
                manualInput.disabled = !isExternal && !isUnit;
                manualInput.readOnly = isUnit;
                if (isUnit) {
                    manualInput.placeholder = unitLabel;
                    manualInput.value = unitLabel;
                    hiddenInput.value = unitLabel;
                } else if (isExternal) {
                    manualInput.placeholder = manualInput.dataset.externalPlaceholder || 'ชื่อผู้ได้รับรางวัล';
                    hiddenInput.value = manualInput.value || '';
                } else if (!isInternal) {
                    manualInput.placeholder = manualInput.dataset.idlePlaceholder || 'เลือกประเภทผู้ได้รับรางวัลก่อน';
                    manualInput.value = '';
                    hiddenInput.value = '';
                }
            }
        }

        function bindBackOfficeConditionalRow(row) {
            if (!(row instanceof HTMLElement) || row.dataset.conditionalBound === '1') return;
            row.dataset.conditionalBound = '1';
            row.querySelector('[data-person-type-select]')?.addEventListener('change', () => syncBackOfficeConditionalRow(row));
            row.querySelector('[data-person-search-select]')?.addEventListener('change', () => syncBackOfficeConditionalRow(row));
            row.querySelector('[data-person-manual-input]')?.addEventListener('input', () => syncBackOfficeConditionalRow(row));
            syncBackOfficeConditionalRow(row);
        }

        function addOutputAuthor(btn, data = {}) {
            const section = btn.closest('.ea101-section');
            const container = section?.querySelector('[data-output-authors]');
            if (!container) return;
            const prefix = container.dataset.prefix;
            const idx = container.querySelectorAll('[data-output-author]').length;
            const row = document.createElement('div');
            const personType = String(data.person_type || '').trim() || (data.name && backOfficeResearchPeople.includes(data.name) ? 'internal' : (data.name ? 'external' : ''));
            row.className = 'bo-special-row grid grid-cols-1 md:grid-cols-[minmax(0,180px)_minmax(0,220px)_minmax(0,1fr)_auto] gap-2 items-end';
            row.setAttribute('data-output-author', '1');
            row.setAttribute('data-conditional-person-row', '1');
            row.innerHTML = `
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">บทบาท</span>
                    <select name="${prefix}[authors][${idx}][role]" class="w-full mt-1 border border-slate-200 rounded-lg p-2">
                        <option value="">เลือกบทบาท</option>
                        <option value="first">First-author</option>
                        <option value="co">Co-author</option>
                        <option value="corresponding">Corresponding author</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก</span>
                    <select name="${prefix}[authors][${idx}][person_type]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-person-type-select>
                        <option value="">กรุณาเลือก</option>
                        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external">คนนอก</option>
                    </select>
                </label>
                <div class="hidden" data-person-select-wrap>
                    <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                    <select class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน">
                        <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>${backOfficeResearchPeopleOptions}
                    </select>
                </div>
                <label data-person-manual-wrap>
                    <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                    <input class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เลือกประเภทผู้เขียนก่อน" data-idle-placeholder="เลือกประเภทผู้เขียนก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)" data-person-manual-input disabled>
                </label>
                <input type="hidden" name="${prefix}[authors][${idx}][name]" data-person-name-hidden>
                <div class="text-right">
                    <button type="button" class="bo-mini-action bo-mini-action--remove" onclick="this.closest('[data-output-author]').remove()">ลบผู้เขียน</button>
                </div>
            `;
            if (data.role) {
                row.querySelector(`select[name="${prefix}[authors][${idx}][role]"]`).value = data.role;
            }
            row.querySelector(`select[name="${prefix}[authors][${idx}][person_type]"]`).value = personType;
            row.querySelector(`[data-person-name-hidden]`).value = data.name || '';
            if (personType === 'internal') row.querySelector('[data-person-search-select]').value = data.name || '';
            if (personType === 'external') row.querySelector('[data-person-manual-input]').value = data.name || '';
            container.appendChild(row);
            bindBackOfficeConditionalRow(row);
        }

        function addOutputConferencePresenter(btn, data = {}) {
            const section = btn.closest('.ea103-section');
            const container = section?.querySelector('[data-output-conference-presenters]');
            if (!container) return;
            const prefix = container.dataset.prefix;
            const idx = container.querySelectorAll('[data-output-conference-presenter]').length;
            const row = document.createElement('div');
            const personType = String(data.person_type || '').trim() || (data.name && backOfficePeople.includes(data.name) ? 'internal' : (data.name ? 'external' : ''));
            row.className = 'bo-special-row grid grid-cols-1 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-2 items-end';
            row.setAttribute('data-output-conference-presenter', '1');
            row.setAttribute('data-conditional-person-row', '1');
            row.innerHTML = `
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทผู้นำเสนอ</span>
                    <select name="${prefix}[conference_presenters][${idx}][person_type]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-person-type-select>
                        <option value="">กรุณาเลือก</option>
                        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external">คนนอก</option>
                    </select>
                </label>
                <div class="hidden" data-person-select-wrap>
                    <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้นำเสนอ</span>
                    <select class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้นำเสนอ" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ">
                        <option value="">พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ</option>${backOfficePeopleOptions}
                    </select>
                </div>
                <label data-person-manual-wrap>
                    <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้นำเสนอ</span>
                    <input class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เลือกประเภทผู้นำเสนอก่อน" data-idle-placeholder="เลือกประเภทผู้นำเสนอก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้นำเสนอ" data-person-manual-input disabled>
                </label>
                <input type="hidden" name="${prefix}[conference_presenters][${idx}][name]" data-person-name-hidden>
                <div class="text-right">
                    <button type="button" class="bo-mini-action bo-mini-action--remove" onclick="this.closest('[data-output-conference-presenter]').remove()">ลบผู้นำเสนอ</button>
                </div>
            `;
            row.querySelector(`select[name="${prefix}[conference_presenters][${idx}][person_type]"]`).value = personType;
            row.querySelector(`[data-person-name-hidden]`).value = data.name || '';
            if (personType === 'internal') row.querySelector('[data-person-search-select]').value = data.name || '';
            if (personType === 'external') row.querySelector('[data-person-manual-input]').value = data.name || '';
            container.appendChild(row);
            bindBackOfficeConditionalRow(row);
        }

        function addOutputAwardRecipient(btn, data = {}) {
            const section = btn.closest('.ea118-section');
            const container = section?.querySelector('[data-output-award-recipients]');
            if (!container) return;
            const prefix = container.dataset.prefix;
            const idx = container.querySelectorAll('[data-output-award-recipient]').length;
            const row = document.createElement('div');
            const personType = String(data.person_type || '').trim();
            row.className = 'bo-special-row grid grid-cols-1 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-2 items-end';
            row.setAttribute('data-output-award-recipient', '1');
            row.setAttribute('data-conditional-person-row', '1');
            row.innerHTML = `
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก</span>
                    <select name="${prefix}[award_recipients][${idx}][person_type]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-person-type-select>
                        <option value="">กรุณาเลือก</option>
                        <option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external">คนนอก</option>
                    </select>
                </label>
                <div class="hidden" data-person-select-wrap>
                    <span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล</span>
                    <select class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้ได้รับรางวัล" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล">
                        <option value="">พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล</option>${backOfficePeopleOptions}
                    </select>
                </div>
                    <label class="hidden" data-person-manual-wrap>
                        <span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล</span>
                        <input class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน" data-idle-placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน" data-external-placeholder="ชื่อผู้ได้รับรางวัล" data-unit-placeholder="หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ" data-person-manual-input disabled>
                    </label>
                <input type="hidden" name="${prefix}[award_recipients][${idx}][name]" data-person-name-hidden>
                <div class="text-right">
                    <button type="button" class="bo-mini-action bo-mini-action--remove" onclick="this.closest('[data-output-award-recipient]').remove()">ลบรายชื่อ</button>
                </div>
            `;
            row.querySelector(`select[name="${prefix}[award_recipients][${idx}][person_type]"]`).value = personType;
            row.querySelector(`[data-person-name-hidden]`).value = personType === 'unit' ? (data.name || SPECIAL_UNIT_NAME) : (data.name || '');
            if (personType === 'internal') row.querySelector('[data-person-search-select]').value = data.name || '';
            if (personType === 'external') row.querySelector('[data-person-manual-input]').value = data.name || '';
            if (personType === 'unit') row.querySelector('[data-person-manual-input]').value = data.name || SPECIAL_UNIT_NAME;
            container.appendChild(row);
            bindBackOfficeConditionalRow(row);
        }

        function addOutputBookAuthor(btn, data = {}) {
            const section = btn.closest('.ea119-section');
            const container = section?.querySelector('[data-output-book-authors]');
            if (!container) return;
            const prefix = container.dataset.prefix;
            const idx = container.querySelectorAll('[data-output-book-author]').length;
            const row = document.createElement('div');
            const personType = String(data.person_type || '').trim() || (data.name && backOfficePeople.includes(data.name) ? 'internal' : (data.name ? 'external' : ''));
            row.className = 'bo-special-row grid grid-cols-1 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-2 items-end';
            row.setAttribute('data-output-book-author', '1');
            row.setAttribute('data-conditional-person-row', '1');
            row.innerHTML = `
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก</span>
                    <select name="${prefix}[book_authors][${idx}][person_type]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-person-type-select>
                        <option value="">กรุณาเลือก</option>
                        <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                        <option value="external">คนนอก</option>
                    </select>
                </label>
                <div class="hidden" data-person-select-wrap>
                    <span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน</span>
                    <select class="w-full mt-1 border border-slate-200 rounded-lg p-2" data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน">
                        <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>${backOfficePeopleOptions}
                    </select>
                </div>
                <label class="hidden" data-person-manual-wrap>
                    <span class="text-xs font-semibold text-slate-600">ชื่อผู้เขียน</span>
                    <input class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="ชื่อผู้เขียน" data-person-manual-input disabled>
                </label>
                <input type="hidden" name="${prefix}[book_authors][${idx}][name]" data-person-name-hidden>
                <div class="text-right">
                    <button type="button" class="bo-mini-action bo-mini-action--remove" onclick="this.closest('[data-output-book-author]').remove()">ลบผู้เขียน</button>
                </div>
            `;
            row.querySelector(`select[name="${prefix}[book_authors][${idx}][person_type]"]`).value = personType;
            row.querySelector(`[data-person-name-hidden]`).value = data.name || '';
            if (personType === 'internal') row.querySelector('[data-person-search-select]').value = data.name || '';
            if (personType === 'external') row.querySelector('[data-person-manual-input]').value = data.name || '';
            if (data.id) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `${prefix}[book_authors][${idx}][id]`;
                hidden.value = data.id;
                row.appendChild(hidden);
            }
            container.appendChild(row);
            bindBackOfficeConditionalRow(row);
        }

        function handleOutputType(selectEl) {
            const row = selectEl.closest('[data-task-output],[data-entry-output]');
            const mount = row?.querySelector('.special-mount');
            if (!row || !mount) return;
            const prefix = row.dataset.outputPrefix || '';
            mount.innerHTML = '';
            const val = selectEl.value || '';
            if (val.startsWith('EA101')) {
                mount.appendChild(renderEA101Section(prefix));
            } else if (val.startsWith('EA103')) {
                mount.appendChild(renderEA103Section(prefix));
            } else if (val.startsWith('EA118')) {
                mount.appendChild(renderEA118Section(prefix));
            } else if (val.startsWith('EA119')) {
                mount.appendChild(renderEA119Section(prefix));
            }
        }

        function hydrateSpecialFields(row, data = {}) {
            if (!row || !data) return;
            const prefix = row.dataset.outputPrefix || '';
            if (!prefix) return;
            const journal = row.querySelector(`[name="${prefix}[journal]"]`);
            if (journal) journal.value = data.journal || '';
            const quartile = row.querySelector(`[name="${prefix}[quartile]"]`);
            if (quartile) quartile.value = data.quartile || '';
            const impact = row.querySelector(`[name="${prefix}[impact_factor]"]`);
            if (impact) impact.value = data.impact_factor ?? '';
            const bookTitle = row.querySelector(`[name="${prefix}[book_title]"]`);
            if (bookTitle) bookTitle.value = data.book_title || '';
            const publisherName = row.querySelector(`[name="${prefix}[publisher_name]"]`);
            if (publisherName) publisherName.value = data.publisher_name || '';
            const chapterPages = row.querySelector(`[name="${prefix}[chapter_pages]"]`);
            if (chapterPages) chapterPages.value = data.chapter_pages || '';
            const isbn = row.querySelector(`[name="${prefix}[isbn]"]`);
            if (isbn) isbn.value = data.isbn || '';

            if (Array.isArray(data.authors)) {
                const btn = row.querySelector('.ea101-section button');
                if (btn) data.authors.forEach(author => addOutputAuthor(btn, author));
            }
            if (Array.isArray(data.conferences) && data.conferences.length) {
                const firstConference = data.conferences[0] || {};
                const conferenceName = row.querySelector(`[name="${prefix}[conference_name]"]`);
                if (conferenceName) conferenceName.value = firstConference.name || data.conference_name || '';
                const conferencePresentationType = row.querySelector(`[name="${prefix}[conference_presentation_type]"]`);
                if (conferencePresentationType) conferencePresentationType.value = firstConference.presentation_type || data.conference_presentation_type || '';
                const conferenceLocation = row.querySelector(`[name="${prefix}[conference_location]"]`);
                if (conferenceLocation) conferenceLocation.value = firstConference.location || data.conference_location || '';
                const btn = row.querySelector('.ea103-section button');
                const presenters = Array.isArray(firstConference.presenters) && firstConference.presenters.length
                    ? firstConference.presenters
                    : ((firstConference.note || firstConference.presenter_person_type)
                        ? [{ name: firstConference.note || '', person_type: firstConference.presenter_person_type || '' }]
                        : []);
                if (btn && presenters.length) presenters.forEach((presenter) => addOutputConferencePresenter(btn, presenter));
            }
            const awardRecipients = Array.isArray(data.award_recipients)
                ? data.award_recipients
                : (Array.isArray(data.authors) ? data.authors.filter(author => (author?.role || '') === 'awardee') : []);
            if (awardRecipients.length) {
                const btn = row.querySelector('.ea118-section button');
                if (btn) awardRecipients.forEach(recipient => addOutputAwardRecipient(btn, recipient));
            }
            if (Array.isArray(data.book_authors)) {
                const btn = row.querySelector('.ea119-section button');
                if (btn) data.book_authors.forEach(author => addOutputBookAuthor(btn, author));
            }
        }

        function addTaskOutput(data = {}) {
            const index = taskOutputsBody.querySelectorAll('[data-task-output]').length;
            const html = taskOutputTemplate.innerHTML.replaceAll('__TINDEX__', index);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const row = wrapper.firstElementChild;
            row.dataset.outputPrefix = `outputs[${index}]`;

            row.querySelector(`[name="outputs[${index}][title]"]`).value = data.title || '';
            row.querySelector(`[name="outputs[${index}][type]"]`).value = data.type || '';
            row.querySelector(`[name="outputs[${index}][contributors][0][user_id]"]`).value = data.contributors?.[0]?.user_id || data.owner_user_id || '';
            row.querySelector(`[name="outputs[${index}][project_output]"]`).value = data.project_output || '';
            row.querySelector(`[name="outputs[${index}][year]"]`).value = data.year || '';

            const typeSelect = row.querySelector('.output-type');
            if (typeSelect) {
                typeSelect.addEventListener('change', () => handleOutputType(typeSelect));
                handleOutputType(typeSelect);
                hydrateSpecialFields(row, data);
            }

            bindTaskOutput(row);
            taskOutputsBody.appendChild(row);
            initBackOfficePersonSelect(row);
            updateTaskOutputIndex();
            if (window.initDateBE) initDateBE(row);
            bindTaskOutputDateRules(row, index);
            collapseAllTaskOutputs(row);
            setTaskOutputCollapsed(row, false);
        }

        function addEntry(data = {}) {
            const index = entriesBody.querySelectorAll('[data-entry]').length;
            const html = entryTemplate.innerHTML.replaceAll('__INDEX__', index);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const entry = wrapper.firstElementChild;

            entry.querySelector(`[name="entries[${index}][person_name]"]`).value = data.person_name || '';
            entry.querySelector(`[name="entries[${index}][category]"]`).value = data.category || '';
            entry.querySelector(`[name="entries[${index}][job_name]"]`).value = data.job_name || '';
            entry.querySelector(`[name="entries[${index}][job_detail]"]`).value = data.job_detail || '';
            entry.querySelector(`[name="entries[${index}][start_date]"]`).value = data.start_date || '';
            entry.querySelector(`[name="entries[${index}][end_date]"]`).value = data.end_date || '';
            entry.querySelector(`[name="entries[${index}][hours]"]`).value = data.hours ?? 0;

            entriesBody.appendChild(entry);
            initBackOfficePersonSelect(entry);
            if (window.initDateBE) initDateBE(entry);
            bindEntryDateRules(entry, index);
            bindEntry(entry);
            updateIndex();
            collapseAllEntries(entry);
            setCollapsed(entry, false);
            return entry;
        }

        function initBackOfficePersonSelect(scope = document) {
            scope.querySelectorAll('[data-back-office-person-select]').forEach((select) => {
                if (!(select instanceof HTMLSelectElement)) return;
                const dropdown = ensureBackOfficeSearchDropdown(select, {
                    placeholder: 'กรุณาเลือกผู้รับผิดชอบ',
                    searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อผู้รับผิดชอบ',
                });
                dropdown?.refreshOptions?.();
            });
        }

        function collectEntryData(entry) {
            if (!entry) return null;
            return {
                person_name: entry.querySelector('[name$="[person_name]"]')?.value || '',
                category: entry.querySelector('select[name$="[category]"]')?.value || '',
                job_name: entry.querySelector('input[name$="[job_name]"]')?.value || '',
                job_detail: entry.querySelector('textarea[name$="[job_detail]"]')?.value || '',
                start_date: entry.querySelector('input[name$="[start_date]"]')?.value || '',
                end_date: entry.querySelector('input[name$="[end_date]"]')?.value || '',
                hours: entry.querySelector('input[name$="[hours]"]')?.value || 0,
            };
        }

        function isTaskOutputCollapsed(row) {
            return row.dataset.collapsed === '1';
        }

        function setTaskOutputCollapsed(row, collapsed) {
            const body = row.querySelector('.task-output-body');
            const specialMount = row.querySelector('.special-mount');
            const summary = row.querySelector('.task-output-summary');
            const toggleBtn = row.querySelector('.toggle-task-output');
            const header = row.querySelector('[data-output-header]');

            row.dataset.collapsed = collapsed ? '1' : '0';
            body?.classList.toggle('hidden', collapsed);
            specialMount?.classList.toggle('hidden', collapsed);
            summary?.classList.toggle('hidden', !collapsed);
            if (toggleBtn) toggleBtn.textContent = collapsed ? 'ขยายลิ้นชัก' : 'ย่อรายละเอียด';
            if (header) header.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function collapseAllTaskOutputs(exceptRow = null) {
            taskOutputsBody.querySelectorAll('[data-task-output]').forEach(row => {
                if (row === exceptRow) return;
                setTaskOutputCollapsed(row, true);
            });
        }

        function updateTaskOutputSummary(row) {
            const title = row.querySelector('input[name$="[title]"]')?.value?.trim() || '';
            const type = row.querySelector('select[name$="[type]"]')?.value?.trim() || '';
            const detail = row.querySelector('input[name$="[project_output]"]')?.value?.trim() || '';
            const year = row.querySelector('input[name$="[year]"]')?.value?.trim() || '';
            const main = row.querySelector('.task-output-summary .summary-main');
            const sub = row.querySelector('.task-output-summary .summary-sub');

            const titleText = title || 'ไม่ระบุชื่อผลงาน';
            const typeText = type || 'ไม่ระบุประเภท';
            const detailText = detail ? detail.replace(/\s+/g, ' ').slice(0, 80) : 'ไม่มีรายละเอียด/ลิงก์';

            if (main) main.textContent = `${titleText} • ${typeText}`;
            if (sub) sub.textContent = `${detailText}${year ? ` • ${year}` : ''}`;
        }

        function bindTaskOutput(row) {
            const removeBtn = row.querySelector('.remove-task-output');
            const toggleBtn = row.querySelector('.toggle-task-output');
            const header = row.querySelector('[data-output-header]');
            const inputs = row.querySelectorAll('input, select, textarea');
            const toggleOutput = () => {
                collapseAllTaskOutputs(row);
                setTaskOutputCollapsed(row, !isTaskOutputCollapsed(row));
            };

            removeBtn?.addEventListener('click', async event => {
                event.stopPropagation();
                const title = row.querySelector('input[name$="[title]"]')?.value?.trim() || 'ไม่มีชื่อผลงาน';
                const type = row.querySelector('select[name$="[type]"]')?.value?.trim() || 'ไม่ระบุประเภท';
                const confirmed = await confirmRemoval({
                    title: 'ลบผลงาน',
                    text: `ต้องการลบผลงาน ${title} (${type}) ใช่หรือไม่`,
                    confirmText: 'ลบผลงาน',
                });
                if (!confirmed) return;
                row.remove();
                updateTaskOutputIndex();
            });
            toggleBtn?.addEventListener('click', event => {
                event.stopPropagation();
                toggleOutput();
            });
            const handleTaskOutputCardClick = event => {
                if (event.target.closest('button, input, select, textarea, a, option, label')) return;
                if (!isTaskOutputCollapsed(row) && event.target.closest('.task-output-body, .special-mount')) return;
                toggleOutput();
            };
            row.addEventListener('click', handleTaskOutputCardClick);
            header?.addEventListener('keydown', event => {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                toggleOutput();
            });
            inputs.forEach(input => {
                input.addEventListener('input', () => updateTaskOutputSummary(row));
                input.addEventListener('change', () => updateTaskOutputSummary(row));
                input.addEventListener('input', () => clearFieldInvalid(input));
                input.addEventListener('change', () => clearFieldInvalid(input));
            });
            updateTaskOutputSummary(row);
        }

        addEntryBtn?.addEventListener('click', () => addEntry());
        duplicateEntryBtn?.addEventListener('click', () => {
            const entries = Array.from(entriesBody.querySelectorAll('[data-entry]'));
            const lastEntry = entries.at(-1);
            const payload = collectEntryData(lastEntry) || {};
            const newEntry = addEntry(payload);
            const personInput = newEntry?.querySelector('[name$="[person_name]"]');
            if (personInput) {
                window.setTimeout(() => personInput.focus(), 0);
            }
        });
        addTaskOutputBtn?.addEventListener('click', () => addTaskOutput());
        addLeaderBtn?.addEventListener('click', () => addLeader('', true));

        if (Array.isArray(initialEntries) && initialEntries.length) {
            initialEntries.forEach(entry => addEntry(entry));
        }

        initBackOfficePersonSelect(document);

        if (Array.isArray(initialTaskOutputs) && initialTaskOutputs.length) {
            initialTaskOutputs.forEach(output => addTaskOutput(output));
        }

        bindMainTaskDateFix();
        bindMainTaskDateMinRules();
        applyServerValidationState();

        function updateLeaderRows() {
            if (!leadersBody) return;
            const rows = Array.from(leadersBody.querySelectorAll('[data-leader-row]'));
            rows.forEach((row, idx) => {
                const indexEl = row.querySelector('.leader-row__index');
                const removeBtn = row.querySelector('.remove-leader');
                if (indexEl) indexEl.textContent = String(idx + 1);
                if (removeBtn) {
                    const disable = rows.length === 1;
                    removeBtn.classList.toggle('is-disabled', disable);
                    removeBtn.disabled = disable;
                }
            });
        }

        function bindLeaderRow(row) {
            const input = row.querySelector('input[name="leaders[]"]');
            const removeBtn = row.querySelector('.remove-leader');
            const appendBtn = row.querySelector('.append-leader');

            appendBtn?.addEventListener('click', () => addLeader('', true));
            removeBtn?.addEventListener('click', async () => {
                if (leadersBody.querySelectorAll('[data-leader-row]').length <= 1) return;
                const leaderName = input?.value?.trim() || 'หัวหน้างานรายการนี้';
                const confirmed = await confirmRemoval({
                    title: 'ลบหัวหน้างาน',
                    text: `ต้องการลบ ${leaderName} ออกจากรายชื่อหัวหน้างานใช่หรือไม่`,
                    confirmText: 'ลบรายชื่อ',
                });
                if (!confirmed) return;
                row.remove();
                updateLeaderRows();
            });
            input?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                addLeader('', true);
            });
        }

        function addLeader(value = '', focusInput = false) {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = leaderTemplate.innerHTML.trim();
            const row = wrapper.firstElementChild;
            const input = row.querySelector('input[name="leaders[]"]');
            if (input) input.value = value;
            bindLeaderRow(row);
            leadersBody.appendChild(row);
            updateLeaderRows();
            if (focusInput && input) {
                window.setTimeout(() => input.focus(), 0);
            }
            return row;
        }

        backOfficeForm?.querySelectorAll('input, select, textarea').forEach((input) => {
            input.addEventListener('input', () => clearFieldInvalid(input));
            input.addEventListener('change', () => clearFieldInvalid(input));
        });
        backOfficeForm?.addEventListener('input', (event) => {
            if (event.target instanceof HTMLElement) clearFieldInvalid(event.target);
        });
        backOfficeForm?.addEventListener('change', (event) => {
            if (event.target instanceof HTMLElement) clearFieldInvalid(event.target);
        });

        backOfficeForm?.addEventListener('submit', (event) => {
            const invalidFields = validateBackOfficeForm();
            if (!invalidFields.length) return;

            event.preventDefault();
            showRequiredAlert(invalidFields);
            scrollToField(invalidFields[0]);
        });

        if (Array.isArray(initialLeaders) && initialLeaders.length) {
            initialLeaders.forEach(name => addLeader(name));
        } else {
            addLeader('', true);
        }

        function isCollapsed(entry) {
            return entry.dataset.collapsed === '1';
        }

        function setCollapsed(entry, collapsed) {
            const body = entry.querySelector('.entry-body');
            const summary = entry.querySelector('.entry-summary');
            const toggleBtn = entry.querySelector('.toggle-entry');
            const header = entry.querySelector('[data-entry-header]');

            entry.dataset.collapsed = collapsed ? '1' : '0';
            body?.classList.toggle('hidden', collapsed);
            summary?.classList.toggle('hidden', !collapsed);
            if (toggleBtn) toggleBtn.textContent = collapsed ? 'ขยายลิ้นชัก' : 'ย่อรายละเอียด';
            if (header) header.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function collapseAllEntries(exceptEntry = null) {
            entriesBody.querySelectorAll('[data-entry]').forEach(entry => {
                if (entry === exceptEntry) return;
                setCollapsed(entry, true);
            });
        }

        function updateSummary(entry) {
            const person = entry.querySelector('[name$="[person_name]"]')?.value?.trim() || '';
            const category = entry.querySelector('select[name$="[category]"]')?.value?.trim() || '';
            const jobDetail = entry.querySelector('textarea[name$="[job_detail]"]')?.value?.trim() || '';
            const startDate = entry.querySelector('input[name$="[start_date]"]')?.value?.trim() || '';
            const endDate = entry.querySelector('input[name$="[end_date]"]')?.value?.trim() || '';
            const hours = entry.querySelector('input[name$="[hours]"]')?.value?.trim() || '0';

            const main = entry.querySelector('.summary-main');
            const sub = entry.querySelector('.summary-sub');

            const jobSnippet = jobDetail ? jobDetail.replace(/\s+/g, ' ').slice(0, 80) : 'ไม่มีรายละเอียด';
            const nameText = person || 'ไม่ระบุผู้รับผิดชอบ';
            const categoryText = category || '-';
            const dateText = startDate || endDate ? `${startDate || '-'} ถึง ${endDate || '-'}` : 'ยังไม่ระบุช่วงเวลา';

            if (main) {
                main.textContent = `${nameText} • ${categoryText} • ${jobSnippet}`;
            }
            if (sub) {
                sub.textContent = `${dateText} • ${hours} ชม.`;
            }
        }
    </script>
    <script>
        (() => {
            const formatBackOfficeBeText = (date) => {
                if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
                const day = date.getDate();
                const month = date.toLocaleDateString('th-TH', { month: 'long' });
                const year = date.getFullYear() + 543;
                return `${day} ${month} ${year}`;
            };

            const forceBackOfficeBeDisplay = (inst) => {
                const altInput = inst?.altInput;
                const selectedDate = inst?.selectedDates?.[0];
                if (!(altInput instanceof HTMLInputElement) || !(selectedDate instanceof Date) || Number.isNaN(selectedDate.getTime())) return;
                altInput.value = formatBackOfficeBeText(selectedDate);
            };

            const forceBackOfficeBeYear = (inst) => {
                const cal = inst?.calendarContainer;
                if (!cal) return;

                const yearInput = cal.querySelector('.flatpickr-current-month input.cur-year');
                if (yearInput) {
                    const year = parseInt(yearInput.value || '0', 10);
                    if (!Number.isNaN(year) && year > 0 && year < 2400) {
                        yearInput.value = String(year + 543);
                    }
                }

                ['.flatpickr-prev-month', '.flatpickr-next-month'].forEach((selector) => {
                    const btn = cal.querySelector(selector);
                    if (!btn || btn.dataset.boBeBound === '1') return;
                    btn.dataset.boBeBound = '1';
                    btn.addEventListener('click', () => {
                        requestAnimationFrame(() => forceBackOfficeBeYear(inst));
                        window.setTimeout(() => forceBackOfficeBeYear(inst), 0);
                    });
                });
            };

            const bindBackOfficeBeDatepickers = (scope = document) => {
                scope.querySelectorAll('.js-date-be').forEach((input) => {
                    const inst = input?._flatpickr;
                    if (!inst || input.dataset.boBeInit === '1') return;
                    input.dataset.boBeInit = '1';

                    const sync = () => {
                        forceBackOfficeBeDisplay(inst);
                        requestAnimationFrame(() => forceBackOfficeBeYear(inst));
                        window.setTimeout(() => forceBackOfficeBeYear(inst), 0);
                    };

                    inst.altInput?.addEventListener('focus', sync);
                    inst.altInput?.addEventListener('click', sync);
                    inst.altInput?.addEventListener('input', sync);
                    inst.altInput?.addEventListener('blur', sync);
                    sync();
                });
            };

            let tries = 0;
            const timer = window.setInterval(() => {
                bindBackOfficeBeDatepickers(document);
                tries += 1;
                if (tries >= 20) window.clearInterval(timer);
            }, 250);

            window.setTimeout(() => bindBackOfficeBeDatepickers(document), 50);
            window.setTimeout(() => bindBackOfficeBeDatepickers(document), 500);
        })();
    </script>
    <script>
        (() => {
            const navDots = Array.from(document.querySelectorAll('.bo-section-dot[data-nav-target]'));
            if (!navDots.length) return;

            const quickNav = document.querySelector('.bo-section-nav');
            const sectionById = new Map();

            const expandQuickNav = () => quickNav?.classList.add('is-expanded');
            const collapseQuickNav = () => quickNav?.classList.remove('is-expanded');

            quickNav?.addEventListener('mouseenter', expandQuickNav);
            quickNav?.addEventListener('mouseleave', collapseQuickNav);
            quickNav?.addEventListener('focusin', expandQuickNav);
            quickNav?.addEventListener('focusout', (event) => {
                if (!quickNav?.contains(event.relatedTarget)) collapseQuickNav();
            });

            navDots.forEach((dot) => {
                const targetId = dot.dataset.navTarget || '';
                const targetEl = document.getElementById(targetId);
                if (targetEl) sectionById.set(targetId, targetEl);

                dot.addEventListener('click', () => {
                    if (!targetEl) return;
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    dot.blur();
                    collapseQuickNav();
                });
            });

            const updateActiveDot = () => {
                let activeId = '';
                let bestTop = Number.POSITIVE_INFINITY;

                sectionById.forEach((el, id) => {
                    const rect = el.getBoundingClientRect();
                    const delta = Math.abs(rect.top - 120);
                    if (rect.top <= 180 && delta < bestTop) {
                        bestTop = delta;
                        activeId = id;
                    }
                });

                if (!activeId) {
                    const firstVisible = Array.from(sectionById.entries())
                        .find(([, el]) => el.getBoundingClientRect().top > 0);
                    activeId = firstVisible ? firstVisible[0] : Array.from(sectionById.keys())[0] || '';
                }

                navDots.forEach((dot) => {
                    dot.classList.toggle('is-active', dot.dataset.navTarget === activeId);
                });
            };

            window.addEventListener('scroll', updateActiveDot, { passive: true });
            window.addEventListener('resize', updateActiveDot);
            window.setTimeout(updateActiveDot, 60);
            updateActiveDot();
        })();

        (() => {
            const syncBackOfficeDatePlaceholders = () => {
                document.querySelectorAll('.js-date-be[placeholder]').forEach((input) => {
                    const placeholder = input.getAttribute('placeholder') || '';
                    const altInput = input._flatpickr?.altInput;
                    if (!altInput || !placeholder) return;
                    altInput.placeholder = placeholder;
                    altInput.setAttribute('placeholder', placeholder);
                    if (!altInput.value) altInput.value = '';
                });
            };

            let tries = 0;
            const timer = window.setInterval(() => {
                syncBackOfficeDatePlaceholders();
                tries += 1;
                if (tries >= 20) window.clearInterval(timer);
            }, 250);

            window.setTimeout(syncBackOfficeDatePlaceholders, 50);
            window.setTimeout(syncBackOfficeDatePlaceholders, 500);
        })();
    </script>
@endsection
