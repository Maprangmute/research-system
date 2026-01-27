@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-4 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <a href="{{ route('back_office.show', $task->id) }}" class="text-blue-700 text-sm hover:underline">← กลับไปหน้ารายละเอียด</a>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">แก้ไขงานนอกโครงการ</h2>
                <p class="text-sm text-slate-500">ปรับรายละเอียดงานหลักและภาระงานย่อยได้จากหน้านี้</p>
            </div>
            <a href="{{ route('back_office.index') }}" class="text-sm text-slate-600 hover:underline">กลับไปรายการงาน</a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('back_office.update', $task->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white border rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-10 w-10 rounded-full bg-slate-900 text-white flex items-center justify-center text-lg">1</div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">ข้อมูลงานหลัก</h3>
                        <p class="text-sm text-slate-500">กำหนดชื่องานและช่วงเวลาโดยรวม</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-600">ชื่องาน</span>
                        <input name="title" value="{{ old('title', $task->title) }}" required
                            class="w-full mt-1 border border-slate-200 rounded-lg p-3" placeholder="เช่น งานจัดทำเอกสารประกอบการประชุม">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-600">รายละเอียดเพิ่มเติม</span>
                        <input name="description" value="{{ old('description', $task->description) }}"
                            class="w-full mt-1 border border-slate-200 rounded-lg p-3" placeholder="อธิบายสั้นๆ (ถ้ามี)">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-600">วันที่เริ่ม</span>
                        <input type="text" name="start_date" value="{{ old('start_date', $task->start_date) }}" required
                            class="w-full mt-1 border border-slate-200 rounded-lg p-3 js-date-be"
                            placeholder="วันเริ่มต้น" autocomplete="off">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-600">วันที่สิ้นสุด</span>
                        <input type="text" name="end_date" value="{{ old('end_date', $task->end_date) }}" required
                            class="w-full mt-1 border border-slate-200 rounded-lg p-3 js-date-be"
                            placeholder="วันสิ้นสุด" autocomplete="off">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-600">จุดประสงค์ของงาน</span>
                        <textarea name="purpose" rows="2"
                            class="w-full mt-1 border border-slate-200 rounded-lg p-3"
                            placeholder="อธิบายจุดประสงค์ของงาน">{{ old('purpose', $task->purpose) }}</textarea>
                    </label>
                    <div class="block md:col-span-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-600">หัวหน้างานผู้รับผิดชอบ (เพิ่มได้หลายคน)</span>
                            <button type="button" id="add-leader"
                                class="text-emerald-600 text-xs hover:underline">เพิ่มหัวหน้างาน</button>
                        </div>
                        <div id="leaders-body" class="mt-2 space-y-2"></div>
                        <div class="text-xs text-slate-500 mt-1">พิมพ์ชื่อแล้วกดเพิ่มได้หลายคน</div>
                    </div>
                </div>
            </div>

            <div class="bg-white border rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg">2</div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">ภาระงานและผู้รับผิดชอบ</h3>
                            <p class="text-sm text-slate-500">ใส่รายละเอียดแต่ละภาระงานแบบแยกการ์ด ไม่อึดอัด</p>
                        </div>
                    </div>
                    <button type="button" id="add-entry"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">เพิ่มภาระงาน</button>
                </div>

                <div id="entries-body" class="space-y-4"></div>
            </div>

            <div class="bg-white border rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-lg">3</div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">ผลงานของงานนอกโครงการ</h3>
                            <p class="text-sm text-slate-500">เพิ่มผลงานได้หลายรายการ</p>
                        </div>
                    </div>
                    <button type="button" id="add-task-output"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">เพิ่มผลงาน</button>
                </div>

                <div id="task-outputs-body" class="space-y-3"></div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('back_office.show', $task->id) }}" class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">ยกเลิก</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>

    <datalist id="person-list">
        @foreach ($personList as $person)
            <option value="{{ $person }}"></option>
        @endforeach
    </datalist>

    <template id="entry-template">
        <div class="entry-card border rounded-2xl p-4 bg-slate-50" data-entry>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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
                    <span class="text-xs font-semibold text-slate-600">บุคคล</span>
                    <input list="person-list" name="entries[__INDEX__][person_name]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3" placeholder="ชื่อ-นามสกุล">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภท</span>
                    @php
                        $categoryOptions = [
                            'งานบริการด้านเอกสารงานวิจัย',
                            'งานบริการด้านการขอคำปรึกษางานวิจัย',
                            'งานบริการหน่วยงาน',
                            'งานบริการด้านทรัพยากรบุคคล',
                            'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย',
                            'งานบริการขนส่งสินค้าเเละเอกสาร',
                            'งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์',
                            'งานด้านการสื่อสาร',
                            'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ',
                            'งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์',
                            'การจัดงานหรือเข้าร่วมงานนิทรรศการ',
                            'การออกแบบหลักสูตร เเละ การเรียนการสอน',
                        ];
                    @endphp
                    <select name="entries[__INDEX__][category]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3">
                        @foreach ($categoryOptions as $idx => $option)
                            <option value="{{ $option }}" @selected($idx === 0)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียดงาน</span>
                    <textarea name="entries[__INDEX__][job_detail]" required rows="1"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 auto-grow"
                        placeholder="รายละเอียดงาน"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ลิงก์เอกสาร</span>
                    <input name="entries[__INDEX__][document_link]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3"
                        placeholder="ลิงก์เอกสาร (Google Drive)">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่เริ่ม</span>
                    <input type="text" name="entries[__INDEX__][start_date]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 js-date-be"
                        placeholder="วันเริ่มต้น" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่สิ้นสุด</span>
                    <input type="text" name="entries[__INDEX__][end_date]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 js-date-be"
                        placeholder="วันสิ้นสุด" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ชั่วโมง</span>
                    <input type="number" step="0.01" min="0" name="entries[__INDEX__][hours]" required
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 text-right hours-input" value="0">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">FTE (อัตโนมัติ)</span>
                    <input type="text" readonly
                        class="w-full mt-1 border border-slate-200 rounded-lg p-3 text-right bg-white fte-output" value="0.00000000">
                </label>
            </div>
            <div class="mt-4 border-t pt-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-600">ผลงานของภาระงาน</span>
                    <button type="button" class="text-emerald-600 text-xs hover:underline add-entry-output">เพิ่มผลงาน</button>
                </div>
                <div class="entry-outputs space-y-2" data-entry-outputs></div>
            </div>
        </div>
    </template>

    <template id="leader-template">
        <div class="flex items-center gap-2" data-leader-row>
            <input name="leaders[]" list="person-list"
                class="w-full border border-slate-200 rounded-lg p-2" placeholder="ชื่อหัวหน้างาน">
            <button type="button" class="text-red-500 text-xs hover:underline remove-leader">ลบ</button>
        </div>
    </template>

    <template id="task-output-template">
        <div class="border rounded-xl p-3 bg-slate-50" data-task-output>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ชื่อผลงาน</span>
                    <input name="outputs[__TINDEX__][title]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" required>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน</span>
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
                        <option>EA115 Capacity Building</option>
                        <option>EA116 Research Project</option>
                        <option>EA117 MOU</option>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียด / ลิงก์ผลงาน</span>
                    <input name="outputs[__TINDEX__][project_output]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="รายละเอียด / ลิงก์ผลงาน">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่เผยแพร่</span>
                    <input type="text" name="outputs[__TINDEX__][year]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันที่เผยแพร่" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่จัดส่ง</span>
                    <input type="text" name="outputs[__TINDEX__][delivered_at]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันจัดส่ง" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ลิงก์ไฟล์เอกสาร</span>
                    <input name="outputs[__TINDEX__][document_link]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="ลิงก์ไฟล์เอกสาร">
                </label>
                <label class="block md:col-span-3">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียดเพิ่มเติม</span>
                    <input name="outputs[__TINDEX__][description]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="รายละเอียดเพิ่มเติม">
                </label>
            </div>
            <div class="special-mount mt-3"></div>
            <div class="mt-2 text-right">
                <button type="button" class="text-red-500 text-xs hover:underline remove-task-output">ลบผลงาน</button>
            </div>
        </div>
    </template>

    <template id="entry-output-template">
        <div class="border rounded-xl p-3 bg-slate-50" data-entry-output>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ชื่อผลงาน</span>
                    <input name="entries[__INDEX__][outputs][__OINDEX__][title]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" required>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียด / ลิงก์ผลงาน</span>
                    <input name="entries[__INDEX__][outputs][__OINDEX__][project_output]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="รายละเอียด / ลิงก์ผลงาน">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่เผยแพร่</span>
                    <input type="text" name="entries[__INDEX__][outputs][__OINDEX__][year]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันที่เผยแพร่" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">วันที่จัดส่ง</span>
                    <input type="text" name="entries[__INDEX__][outputs][__OINDEX__][delivered_at]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันจัดส่ง" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ลิงก์ไฟล์เอกสาร</span>
                    <input name="entries[__INDEX__][outputs][__OINDEX__][document_link]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="ลิงก์ไฟล์เอกสาร">
                </label>
                <label class="block md:col-span-3">
                    <span class="text-xs font-semibold text-slate-600">รายละเอียดเพิ่มเติม</span>
                    <input name="entries[__INDEX__][outputs][__OINDEX__][description]"
                        class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="รายละเอียดเพิ่มเติม">
                </label>
            </div>
            <div class="mt-2 text-right">
                <button type="button" class="text-red-500 text-xs hover:underline remove-entry-output">ลบผลงาน</button>
            </div>
        </div>
    </template>

    <script>
        const FTE_RATE = 0.000621;
        const entriesBody = document.getElementById('entries-body');
        const addEntryBtn = document.getElementById('add-entry');
        const entryTemplate = document.getElementById('entry-template');
        const initialEntries = @json(old('entries', $task->entries));
        const initialTaskOutputs = @json(old('outputs', $task->outputs));
        const initialLeaders = @json(old('leaders', $task->leaders ?? []));
        const leadersBody = document.getElementById('leaders-body');
        const leaderTemplate = document.getElementById('leader-template');
        const addLeaderBtn = document.getElementById('add-leader');
        const taskOutputsBody = document.getElementById('task-outputs-body');
        const taskOutputTemplate = document.getElementById('task-output-template');
        const entryOutputTemplate = document.getElementById('entry-output-template');
        const addTaskOutputBtn = document.getElementById('add-task-output');

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

        function bindEntry(entry) {
            const hoursInput = entry.querySelector('.hours-input');
            const textarea = entry.querySelector('.auto-grow');
            const removeBtn = entry.querySelector('.remove-entry');
            const toggleBtn = entry.querySelector('.toggle-entry');
            const inputs = entry.querySelectorAll('input, select, textarea');
            const addEntryOutputBtn = entry.querySelector('.add-entry-output');
            const outputsContainer = entry.querySelector('[data-entry-outputs]');

            hoursInput?.addEventListener('input', () => updateFte(entry));
            textarea?.addEventListener('input', () => resizeTextarea(textarea));
            removeBtn?.addEventListener('click', () => {
                entry.remove();
                updateIndex();
            });
            toggleBtn?.addEventListener('click', () => {
                setCollapsed(entry, !isCollapsed(entry));
            });
            inputs.forEach(input => {
                input.addEventListener('input', () => updateSummary(entry));
                input.addEventListener('change', () => updateSummary(entry));
            });
            addEntryOutputBtn?.addEventListener('click', () => {
                addEntryOutput(entry, outputsContainer);
            });

            resizeTextarea(textarea);
            updateFte(entry);
            updateSummary(entry);
        }

        function renderEA101Section(prefix) {
            const wrap = document.createElement('div');
            wrap.className = 'ea101-section border rounded-lg p-3 bg-blue-50/60 space-y-3';
            wrap.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Journal</span>
                        <input name="${prefix}[journal]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น IEEE Access">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Quartile (Q)</span>
                        <select name="${prefix}[quartile]" class="w-full mt-1 border border-slate-200 rounded-lg p-2">
                            <option value="">เลือก Quartile</option>
                            <option value="Q1">Q1</option>
                            <option value="Q2">Q2</option>
                            <option value="Q3">Q3</option>
                            <option value="Q4">Q4</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Impact Factor (IF)</span>
                        <input name="${prefix}[impact_factor]" type="number" step="0.001"
                            class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น 3.215">
                    </label>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-600">รายชื่อผู้เขียน (Authors)</span>
                        <button type="button" class="text-emerald-600 text-xs hover:underline" onclick="addOutputAuthor(this)">เพิ่มผู้เขียน</button>
                    </div>
                    <div class="space-y-2" data-output-authors data-prefix="${prefix}"></div>
                </div>
            `;
            return wrap;
        }

        function renderEA103Section(prefix) {
            const wrap = document.createElement('div');
            wrap.className = 'ea103-section border rounded-lg p-3 bg-amber-50/60 space-y-3';
            wrap.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-600">Conferences</span>
                    <button type="button" class="text-emerald-600 text-xs hover:underline" onclick="addOutputConference(this)">เพิ่มรายการ</button>
                </div>
                <div class="space-y-2" data-output-confs data-prefix="${prefix}"></div>
            `;
            return wrap;
        }

        function addOutputAuthor(btn, data = {}) {
            const section = btn.closest('.ea101-section');
            const container = section?.querySelector('[data-output-authors]');
            if (!container) return;
            const prefix = container.dataset.prefix;
            const idx = container.querySelectorAll('[data-output-author]').length;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-3 gap-2 items-end';
            row.setAttribute('data-output-author', '1');
            row.innerHTML = `
                <label class="block md:col-span-1">
                    <span class="text-xs font-semibold text-slate-600">บทบาท</span>
                    <select name="${prefix}[authors][${idx}][role]" class="w-full mt-1 border border-slate-200 rounded-lg p-2">
                        <option value="">เลือกบทบาท</option>
                        <option value="first">First-author</option>
                        <option value="co">Co-author</option>
                        <option value="corresponding">Corresponding author</option>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ชื่อ-นามสกุล</span>
                    <input name="${prefix}[authors][${idx}][name]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="ชื่อ-นามสกุล">
                </label>
                <div class="md:col-span-3 text-right">
                    <button type="button" class="text-red-500 text-xs hover:underline" onclick="this.closest('[data-output-author]').remove()">ลบ</button>
                </div>
            `;
            if (data.role) {
                row.querySelector(`select[name="${prefix}[authors][${idx}][role]"]`).value = data.role;
            }
            if (data.name) {
                row.querySelector(`input[name="${prefix}[authors][${idx}][name]"]`).value = data.name;
            }
            container.appendChild(row);
        }

        function addOutputConference(btn, data = {}) {
            const section = btn.closest('.ea103-section');
            const container = section?.querySelector('[data-output-confs]');
            if (!container) return;
            const prefix = container.dataset.prefix;
            const idx = container.querySelectorAll('[data-output-conf]').length;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-5 gap-2 items-end';
            row.setAttribute('data-output-conf', '1');
            row.innerHTML = `
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ชื่อเวที/Conference</span>
                    <input name="${prefix}[conferences][${idx}][name]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เช่น PMAC 2025">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทนำเสนอ</span>
                    <select name="${prefix}[conferences][${idx}][presentation_type]" class="w-full mt-1 border border-slate-200 rounded-lg p-2">
                        <option value="">เลือก</option>
                        <option value="Oral">Oral</option>
                        <option value="Poster">Poster</option>
                        <option value="Panel">Panel</option>
                        <option value="Invited Talk">Invited Talk</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">สถานที่</span>
                    <input name="${prefix}[conferences][${idx}][location]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="เมือง/ประเทศ">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">หมายเหตุ</span>
                    <input name="${prefix}[conferences][${idx}][note]" class="w-full mt-1 border border-slate-200 rounded-lg p-2" placeholder="หมายเหตุ">
                </label>
                <div class="md:col-span-5 text-right">
                    <button type="button" class="text-red-500 text-xs hover:underline" onclick="this.closest('[data-output-conf]').remove()">ลบ</button>
                </div>
            `;
            if (data.name) row.querySelector(`input[name="${prefix}[conferences][${idx}][name]"]`).value = data.name;
            if (data.presentation_type) row.querySelector(`select[name="${prefix}[conferences][${idx}][presentation_type]"]`).value = data.presentation_type;
            if (data.location) row.querySelector(`input[name="${prefix}[conferences][${idx}][location]"]`).value = data.location;
            if (data.note) row.querySelector(`input[name="${prefix}[conferences][${idx}][note]"]`).value = data.note;
            container.appendChild(row);
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

            if (Array.isArray(data.authors)) {
                const btn = row.querySelector('.ea101-section button');
                if (btn) data.authors.forEach(author => addOutputAuthor(btn, author));
            }
            if (Array.isArray(data.conferences)) {
                const btn = row.querySelector('.ea103-section button');
                if (btn) data.conferences.forEach(conf => addOutputConference(btn, conf));
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
            row.querySelector(`[name="outputs[${index}][project_output]"]`).value = data.project_output || '';
            row.querySelector(`[name="outputs[${index}][year]"]`).value = data.year || '';
            row.querySelector(`[name="outputs[${index}][delivered_at]"]`).value = data.delivered_at || '';
            row.querySelector(`[name="outputs[${index}][description]"]`).value = data.description || '';
            row.querySelector(`[name="outputs[${index}][document_link]"]`).value = data.document_link || '';

            const typeSelect = row.querySelector('.output-type');
            if (typeSelect) {
                typeSelect.addEventListener('change', () => handleOutputType(typeSelect));
                handleOutputType(typeSelect);
                hydrateSpecialFields(row, data);
            }

            row.querySelector('.remove-task-output')?.addEventListener('click', () => row.remove());
            taskOutputsBody.appendChild(row);
            if (window.initDateBE) initDateBE(row);
        }

        function addEntryOutput(entry, outputsContainer, data = {}) {
            if (!outputsContainer) return;
            const entryIndex = entry.querySelector('[name^="entries["]')?.name.match(/^entries\[(\d+)\]/)?.[1];
            if (entryIndex === undefined) return;
            const outputIndex = outputsContainer.querySelectorAll('[data-entry-output]').length;
            const html = entryOutputTemplate.innerHTML
                .replaceAll('__INDEX__', entryIndex)
                .replaceAll('__OINDEX__', outputIndex);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const row = wrapper.firstElementChild;
            row.querySelector(`[name="entries[${entryIndex}][outputs][${outputIndex}][title]"]`).value = data.title || '';
            row.querySelector(`[name="entries[${entryIndex}][outputs][${outputIndex}][project_output]"]`).value = data.project_output || '';
            row.querySelector(`[name="entries[${entryIndex}][outputs][${outputIndex}][year]"]`).value = data.year || '';
            row.querySelector(`[name="entries[${entryIndex}][outputs][${outputIndex}][delivered_at]"]`).value = data.delivered_at || '';
            row.querySelector(`[name="entries[${entryIndex}][outputs][${outputIndex}][description]"]`).value = data.description || '';
            row.querySelector(`[name="entries[${entryIndex}][outputs][${outputIndex}][document_link]"]`).value = data.document_link || '';

            row.querySelector('.remove-entry-output')?.addEventListener('click', () => row.remove());
            outputsContainer.appendChild(row);
            if (window.initDateBE) initDateBE(row);
        }

        function addEntry(data = {}) {
            const index = entriesBody.querySelectorAll('[data-entry]').length;
            const html = entryTemplate.innerHTML.replaceAll('__INDEX__', index);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const entry = wrapper.firstElementChild;

            entry.querySelector(`[name="entries[${index}][person_name]"]`).value = data.person_name || '';
            entry.querySelector(`[name="entries[${index}][category]"]`).value = data.category || '{{ $categoryOptions[0] }}';
            entry.querySelector(`[name="entries[${index}][job_detail]"]`).value = data.job_detail || '';
            entry.querySelector(`[name="entries[${index}][document_link]"]`).value = data.document_link || '';
            entry.querySelector(`[name="entries[${index}][start_date]"]`).value = data.start_date || '';
            entry.querySelector(`[name="entries[${index}][end_date]"]`).value = data.end_date || '';
            entry.querySelector(`[name="entries[${index}][hours]"]`).value = data.hours ?? 0;

            entriesBody.appendChild(entry);
            if (window.initDateBE) initDateBE(entry);
            bindEntry(entry);
            updateIndex();
            collapseAllEntries(entry);
            setCollapsed(entry, false);
            const outputsContainer = entry.querySelector('[data-entry-outputs]');
            if (Array.isArray(data.outputs)) {
                data.outputs.forEach(output => addEntryOutput(entry, outputsContainer, output));
            }
        }

        addEntryBtn?.addEventListener('click', () => addEntry());
        addTaskOutputBtn?.addEventListener('click', () => addTaskOutput());
        addLeaderBtn?.addEventListener('click', () => addLeader());

        if (Array.isArray(initialEntries) && initialEntries.length) {
            initialEntries.forEach(entry => addEntry(entry));
        } else {
            addEntry();
        }

        if (Array.isArray(initialTaskOutputs) && initialTaskOutputs.length) {
            initialTaskOutputs.forEach(output => addTaskOutput(output));
        }

        function addLeader(value = '') {
            if (!leadersBody || !leaderTemplate) return;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = leaderTemplate.innerHTML.trim();
            const row = wrapper.firstElementChild;
            const input = row.querySelector('input[name="leaders[]"]');
            if (input) input.value = value;
            row.querySelector('.remove-leader')?.addEventListener('click', () => row.remove());
            leadersBody.appendChild(row);
        }

        if (Array.isArray(initialLeaders) && initialLeaders.length) {
            initialLeaders.forEach(name => addLeader(name));
        } else {
            addLeader();
        }

        function isCollapsed(entry) {
            return entry.dataset.collapsed === '1';
        }

        function setCollapsed(entry, collapsed) {
            const body = entry.querySelector('.entry-body');
            const summary = entry.querySelector('.entry-summary');
            const toggleBtn = entry.querySelector('.toggle-entry');

            entry.dataset.collapsed = collapsed ? '1' : '0';
            body?.classList.toggle('hidden', collapsed);
            summary?.classList.toggle('hidden', !collapsed);
            if (toggleBtn) {
                toggleBtn.textContent = collapsed ? 'ขยายลิ้นชัก' : 'ย่อรายละเอียด';
            }
        }

        function collapseAllEntries(exceptEntry = null) {
            entriesBody.querySelectorAll('[data-entry]').forEach(entry => {
                if (entry === exceptEntry) return;
                setCollapsed(entry, true);
            });
        }

        function updateSummary(entry) {
            const person = entry.querySelector('input[name$="[person_name]"]')?.value?.trim() || '';
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
@endsection
