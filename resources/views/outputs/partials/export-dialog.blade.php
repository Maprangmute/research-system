@props([
    'id',
    'triggerLabel' => 'Export ข้อมูล',
    'pdfUrl',
    'excelUrl',
    'formSelector',
    'minDate' => null,
])

@once
    <style>
        .outputs-export-modal {
            position: fixed;
            inset: 0;
            z-index: 70;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(6px);
        }
        .outputs-export-modal.is-open {
            display: flex;
        }
        .outputs-export-dialog {
            width: min(100%, 560px);
            border-radius: 20px;
            border: 1px solid #dbe7f5;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
            overflow: hidden;
        }
    </style>
@endonce

<button type="button"
    class="output-export-btn"
    data-output-category-export-trigger="{{ $id }}">
    {{ $triggerLabel }}
</button>

<div id="{{ $id }}" class="outputs-export-modal" aria-hidden="true" data-form-selector="{{ $formSelector }}" data-pdf-url="{{ $pdfUrl }}" data-excel-url="{{ $excelUrl }}">
    <div class="outputs-export-dialog" role="dialog" aria-modal="true">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div>
                <div class="text-base font-semibold text-slate-900">Export ข้อมูล</div>
                <p class="mt-1 text-sm text-slate-500">เลือกชนิดไฟล์และช่วงข้อมูลก่อนโหลด</p>
            </div>
            <button type="button" class="rounded-lg px-2 py-1 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-700" data-output-category-export-close>
                ปิด
            </button>
        </div>
        <div class="space-y-4 px-5 py-5">
            <div>
                <div class="mb-2 text-xs font-semibold text-slate-600">รูปแบบไฟล์</div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <input type="radio" name="{{ $id }}_format" value="pdf" checked class="mt-1" data-output-category-format>
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">PDF</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">รายงานสำหรับอ่านและพิมพ์</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <input type="radio" name="{{ $id }}_format" value="excel" class="mt-1" data-output-category-format>
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">Excel</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">ตารางข้อมูลสำหรับทำงานต่อ</span>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <div class="mb-2 text-xs font-semibold text-slate-600">ช่วงข้อมูล</div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <input type="radio" name="{{ $id }}_scope" value="all" checked class="mt-1" data-output-category-scope>
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">ทั้งหมด</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">โหลดตามฟิลเตอร์ปัจจุบันทั้งหมด</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <input type="radio" name="{{ $id }}_scope" value="range" class="mt-1" data-output-category-scope>
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">ช่วงเวลา</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">เลือกจากวันที่ลงข้อมูล</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 opacity-45 sm:grid-cols-2" data-output-category-range>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ตั้งแต่วันที่</span>
                    <input type="text" class="thai-datepicker mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm" placeholder="เลือกวันที่เริ่มต้น" data-output-category-start data-min-date="{{ $minDate }}">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ถึงวันที่</span>
                    <input type="text" class="thai-datepicker mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-sm" placeholder="เลือกวันที่สิ้นสุด" data-output-category-end data-min-date="{{ $minDate }}">
                </label>
            </div>
        </div>
        <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-output-category-export-close>
                ยกเลิก
            </button>
            <button type="button" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700" data-output-category-download>
                Export PDF
            </button>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buildUrlFromForm = (form, baseUrl) => {
                const url = new URL(baseUrl, window.location.origin);
                if (!form) return url;
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (key === 'page' || String(value ?? '').trim() === '') continue;
                    url.searchParams.append(key, String(value).trim());
                }
                return url;
            };

            const syncRange = (modal) => {
                const scope = modal.querySelector('[data-output-category-scope]:checked')?.value || 'all';
                const format = modal.querySelector('[data-output-category-format]:checked')?.value || 'pdf';
                const range = modal.querySelector('[data-output-category-range]');
                range?.classList.toggle('opacity-45', scope !== 'range');
                range?.querySelectorAll('input').forEach((input) => input.disabled = scope !== 'range');
                const button = modal.querySelector('[data-output-category-download]');
                if (button) button.textContent = format === 'excel' ? 'Export Excel' : 'Export PDF';
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-output-category-export-trigger]');
                if (trigger) {
                    const modal = document.getElementById(trigger.dataset.outputCategoryExportTrigger);
                    if (!modal) return;
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    syncRange(modal);
                    modal.querySelectorAll('[data-min-date]').forEach((input) => {
                        const minDate = input.dataset.minDate;
                        if (minDate && input._flatpickr) input._flatpickr.set('minDate', minDate);
                    });
                    return;
                }

                if (event.target.closest('[data-output-category-export-close]')) {
                    event.target.closest('.outputs-export-modal')?.classList.remove('is-open');
                    return;
                }

                const download = event.target.closest('[data-output-category-download]');
                if (download) {
                    const modal = download.closest('.outputs-export-modal');
                    if (!modal) return;
                    const format = modal.querySelector('[data-output-category-format]:checked')?.value || 'pdf';
                    const scope = modal.querySelector('[data-output-category-scope]:checked')?.value || 'all';
                    const form = document.querySelector(modal.dataset.formSelector);
                    const url = buildUrlFromForm(form, format === 'excel' ? modal.dataset.excelUrl : modal.dataset.pdfUrl);
                    url.searchParams.set('export_scope', scope);
                    if (scope === 'range') {
                        const start = modal.querySelector('[data-output-category-start]')?.value?.trim();
                        const end = modal.querySelector('[data-output-category-end]')?.value?.trim();
                        if (start) url.searchParams.set('export_start_date', start);
                        if (end) url.searchParams.set('export_end_date', end);
                    }
                    window.open(url.toString(), '_blank', 'noopener');
                    modal.classList.remove('is-open');
                }
            });

            document.addEventListener('change', (event) => {
                if (event.target.matches('[data-output-category-format], [data-output-category-scope]')) {
                    const modal = event.target.closest('.outputs-export-modal');
                    if (modal) syncRange(modal);
                }
            });
        });
    </script>
@endonce
