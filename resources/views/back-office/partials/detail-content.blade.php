@php
    $drawerMode = $drawerMode ?? false;
    $personList = $personList ?? collect();
    $thaiPersonOptions = $thaiPersonOptions ?? collect();
    $researchPersonList = $researchPersonList ?? collect();
    $categoryOptions = $categoryOptions ?? [];
    $canManageBackOffice = $canManageBackOffice ?? (auth()->user()?->hasRole(\App\Models\User::ROLE_ADMIN) ?? false);
    $canManageBackOfficeLineItems = $canManageBackOfficeLineItems ?? auth()->check();
    $openQuickEntry = $openQuickEntry ?? false;
    $openQuickOutput = $openQuickOutput ?? false;
    $startCompactList = !$drawerMode && (($task->entries->count() + $task->outputs->count()) > 8);
    $inlineFieldClass = 'mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100';
    $inlineTextareaClass = 'mt-2 block min-h-[8rem] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-100';
    $allOutputTypeOptions = [
        'EA101 Research Paper or Journal Article',
        'EA102 Reports and White Papers',
        'EA103 Presentations in Academic Conferences',
        'EA104 Data Sets and Databases',
        'EA105 Inventions or Innovations (Application / Website)',
        'EA106 Policy Recommendations',
        'EA107 Educational Materials',
        'EA108 Communication Materials (Creations)',
        'EA109 Change in Practices / Procedures / Policy',
        'EA110 Number of Participants (Persons)',
        'EA111 Satisfaction Rating (1–5)',
        'EA112 Presentation to Committee / Policy Maker / Board',
        'EA113 Appointment Orders (SIHP as Committee / Secretary)',
        'EA114 Events / Forum',
        'EA115 Capacity Building / Knowledge Sharing',
        'EA116 Research Project',
        'EA117 MOU',
        'EA118 Award and Recognition',
        'EA119 Textbook / Book chapter',
        'EA120 Manual / Instruction / Guideline',
        'EA999 Other Outputs',
    ];
    $emptyOutputContributors = collect([['user_id' => '', 'name' => '']]);
    $oldQuickContributors = collect(old('contributors', []))->filter(fn ($item) => !empty($item['user_id']))->values();
    if ($oldQuickContributors->isEmpty()) {
        $oldQuickContributors = $emptyOutputContributors;
    }

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
    $formatOutputDateBE = function ($value) use ($formatDateBE) {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '-';
        }
        if (preg_match('/^\d{4}$/', $text) === 1) {
            $year = (int) $text;
            return (string) ($year < 2400 ? $year + 543 : $year);
        }
        return $formatDateBE($text);
    };
    $extractUrl = function ($value) {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }
        if (preg_match('/https?:\/\/[^\s<>"\']+/iu', $text, $match) === 1) {
            return $match[0];
        }
        if (preg_match('/(?:^|[\s(])((?:www\.)[^\s<>"\')]+)/iu', $text, $match) === 1) {
            return 'https://' . ltrim($match[1], '/');
        }
        return null;
    };
    $outputBadgeClass = function ($type) {
        $type = trim((string) ($type ?? ''));
        if (preg_match('/^EA(\d+)/', $type, $match) === 1) {
            $code = (int) $match[1];
            $toneByCode = [
                101 => 0,
                102 => 10,
                103 => 4,
                104 => 2,
                105 => 1,
                106 => 9,
                107 => 3,
                108 => 7,
                109 => 5,
                110 => 11,
                111 => 6,
                112 => 9,
                113 => 10,
                114 => 4,
                115 => 2,
                116 => 0,
                117 => 1,
                118 => 8,
                119 => 3,
                120 => 11,
                999 => 10,
            ];

            return 'back-office-table-pill--tone-' . ($toneByCode[$code] ?? ($code % 12));
        }

        return 'back-office-table-pill--tone-default';
    };
    $categoryBadgeClass = function ($category) {
        $category = trim((string) ($category ?? ''));
        if ($category === '') {
            return 'back-office-table-pill--tone-default';
        }

        return 'back-office-table-pill--tone-' . (abs((int) crc32($category)) % 12);
    };
    $awardPersonTypeLabel = function ($value) {
        return match (trim((string) $value)) {
            'unit' => 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
            'internal' => 'คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
            'external' => 'คนนอก',
            default => null,
        };
    };
    $authorRoleLabel = function ($value) {
        return match (trim((string) $value)) {
            'pi', 'PI' => 'PI',
            'first', 'first-author' => 'First-author',
            'co', 'co-author' => 'Co-author',
            'corresponding', 'corresponding-author' => 'Corresponding author',
            default => $value ?: null,
        };
    };
@endphp

<div class="space-y-6">
    @if ($drawerMode)
        <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0 flex-1">
                    <span class="inline-flex items-center rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                        รายละเอียด Back Office
                    </span>

                    <h2 class="mt-3 text-2xl font-semibold text-slate-900">{{ $task->title }}</h2>
                    <div class="mt-2 text-sm text-slate-500">ช่วงเวลา {{ $formatDateBE($task->start_date) }} ถึง {{ $formatDateBE($task->end_date) }}</div>

                    @if ($task->description)
                        <div class="mt-2 text-sm leading-6 text-slate-600">{{ $task->description }}</div>
                    @endif

                    @if ($task->purpose)
                        <div class="mt-3 text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">จุดประสงค์:</span> {{ $task->purpose }}
                        </div>
                    @endif

                    <div class="mt-2 text-sm text-slate-600">
                        <span class="font-semibold text-slate-700">หัวหน้างานผู้รับผิดชอบ:</span>
                        {{ !empty($task->leaders) ? implode(', ', $task->leaders) : '-' }}
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 md:justify-end">
                    <a href="{{ route('back_office.show', $task) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        เปิดหน้าเต็ม
                    </a>
                    @if ($canManageBackOffice)
                        <a href="{{ route('back_office.edit', $task) }}"
                            class="inline-flex items-center justify-center rounded-xl bg-amber-100 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-200">
                            แก้ไข
                        </a>
                    @endif
                    <a href="{{ route('back_office.pdf', $task) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        🖨️ PDF
                    </a>
                </div>
            </div>
        </section>
    @endif

    <section class="grid grid-cols-1 gap-2.5 sm:grid-cols-3 md:gap-3">
        <article class="back-office-stat-card">
            <div class="back-office-stat-card__label">จำนวนภาระงาน</div>
            <div class="back-office-stat-card__value">{{ number_format($summary['entries']) }}</div>
        </article>
        <article class="back-office-stat-card">
            <div class="back-office-stat-card__label">ชั่วโมงรวม</div>
            <div class="back-office-stat-card__value">{{ number_format($summary['hours'], 2) }}</div>
        </article>
        <article class="back-office-stat-card">
            <div class="back-office-stat-card__label">FTE รวม</div>
            <div class="back-office-stat-card__value">{{ number_format($summary['fte'], 6) }}</div>
        </article>
    </section>

    <section class="back-office-section" id="back-office-entry-section">
        <div class="back-office-section__head back-office-section__head--entry">
            <div>
                <div class="back-office-section__title">A: ภาระงาน</div>
                <div class="back-office-section__desc">รายการภาระงานของแต่ละผู้รับผิดชอบ พร้อมชั่วโมง FTE และช่วงเวลาการทำงาน</div>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if (!$drawerMode)
                    <button type="button" id="open-quick-entry"
                        class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-white px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50">
                        เพิ่มภาระงาน
                    </button>
                @endif
                <div class="back-office-section__count">{{ number_format($task->entries->count()) }} รายการ</div>
            </div>
        </div>
        <div class="back-office-section__body space-y-4" data-back-office-entry-list>
        @if (!$drawerMode)
            <div id="quick-entry-card-anchor"></div>
            <section id="quick-entry-card" class="back-office-quick-card {{ $openQuickEntry ? '' : 'hidden' }}">
                <div class="flex flex-col gap-4 px-5 py-5 md:px-6">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">เพิ่มภาระงานจากหน้านี้</h2>
                            <p class="text-sm text-slate-500">กรอกข้อมูลภาระงานต่อได้ทันทีใน section นี้</p>
                        </div>
                        <button type="button" id="close-quick-entry" class="text-sm font-semibold text-red-600 hover:text-red-700">ยกเลิกการเพิ่ม</button>
                    </div>

                    <form action="{{ route('back_office.entries.store', $task) }}" method="POST" class="space-y-4" novalidate data-back-office-required-form>
                        @csrf
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">ผู้รับผิดชอบ <span class="bo-required">*</span></span>
                                <select name="person_name" required data-back-office-person-select data-placeholder="กรุณาเลือกผู้รับผิดชอบ" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้รับผิดชอบ">
                                    <option value="">กรุณาเลือกผู้รับผิดชอบ</option>
                                    @foreach ($personList as $person)
                                        <option value="{{ $person }}" @selected(old('person_name') === $person)>{{ $person }}</option>
                                    @endforeach
                                </select>
                                @error('person_name')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">หมวดงาน <span class="bo-required">*</span></span>
                                <select name="category" required>
                                    <option value="">เลือกหมวดงาน</option>
                                    @foreach ($categoryOptions as $category)
                                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">ชื่อภาระงาน <span class="bo-required">*</span></span>
                                <input name="job_name" value="{{ old('job_name') }}" placeholder="เช่น ประสาน Consult HTA&SR" required>
                                @error('job_name')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">จำนวนชั่วโมง <span class="bo-required">*</span></span>
                                <input name="hours" type="number" min="0" step="0.01" value="{{ old('hours') }}" placeholder="เช่น 140" required>
                                @error('hours')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </label>
                        </div>

                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">รายละเอียดงาน <span class="bo-required">*</span></span>
                            <textarea name="job_detail" placeholder="รายละเอียดงานที่ทำ" required>{{ old('job_detail') }}</textarea>
                            <p class="mt-1 text-xs leading-5 text-slate-500">รองรับข้อความยาวและจะคงการเว้นบรรทัดตามที่พิมพ์ไว้</p>
                            @error('job_detail')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                        </label>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">วันที่เริ่ม <span class="bo-required">*</span></span>
                                <input type="text" name="start_date" value="{{ old('start_date') }}" class="js-date-be" placeholder="กรุณาใส่วันที่เริ่มต้นภาระงาน" autocomplete="off">
                                @error('start_date')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">วันที่สิ้นสุด <span class="bo-required">*</span></span>
                                <input type="text" name="end_date" value="{{ old('end_date') }}" class="js-date-be" placeholder="กรุณาใส่วันที่สิ้นสุดภาระงาน" autocomplete="off">
                                @error('end_date')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                            </label>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button" id="cancel-quick-entry" class="back-office-action back-office-action--cancel">ยกเลิก</button>
                            <button type="submit" class="back-office-action back-office-action--save">บันทึกภาระงาน</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif
        @if (!$drawerMode && $task->entries->count())
            <div class="back-office-table-wrap">
                <table class="back-office-data-table">
                    <thead>
                        <tr>
                            <th class="w-14">#</th>
                            <th>ภาระงาน / ผู้รับผิดชอบ</th>
                            <th>รายละเอียดงาน</th>
                            <th class="text-right">ชั่วโมง</th>
                            <th class="text-right">FTE</th>
                            <th>ช่วงเวลา</th>
                            <th class="text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($task->entries as $entry)
                            @php
                                $entrySearchText = trim($entry->person_name.' '.$entry->category.' '.$entry->job_name.' '.$entry->job_detail.' '.$formatDateBE($entry->start_date).' '.$formatDateBE($entry->end_date));
                            @endphp
                            <tr data-back-office-entry-row data-back-office-entry-id="{{ $entry->id }}" data-back-office-search="{{ $entrySearchText }}">
                                <td class="font-semibold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="back-office-entry-title-cell">
                                    <div class="font-semibold leading-6 text-slate-900">{{ $entry->job_name ?: 'ไม่ระบุชื่อภาระงาน' }}</div>
                                    <div class="mt-1 text-xs font-semibold leading-5 text-slate-600">{{ $entry->person_name }}</div>
                                    <div class="mt-2">
                                        <span class="back-office-table-pill {{ $categoryBadgeClass($entry->category) }}">{{ $entry->category }}</span>
                                    </div>
                                </td>
                                <td class="back-office-entry-detail-cell">{{ $entry->job_detail ?: '-' }}</td>
                                <td class="text-right font-semibold text-slate-900">{{ number_format($entry->hours, 2) }}</td>
                                <td class="text-right font-semibold text-slate-700">{{ number_format($entry->fte_value, 6) }}</td>
                                <td class="text-xs text-slate-600">{{ $formatDateBE($entry->start_date) }}<br>{{ $formatDateBE($entry->end_date) }}</td>
                                <td>
                                    <div class="back-office-table-actions">
                                        @if ($canManageBackOfficeLineItems)
                                            <button type="button" data-bo-table-edit data-target-card="entry-{{ $entry->id }}">แก้ไข</button>
                                        @endif
                                        <form action="{{ route('back_office.entries.destroy', [$task, $entry->id]) }}" method="POST"
                                            data-back-office-entry-delete-form
                                            data-entry-id="{{ $entry->id }}"
                                            data-entry-person="{{ trim((string) $entry->person_name) }}"
                                            data-entry-job="{{ trim((string) $entry->job_name) }}"
                                            data-entry-category="{{ trim((string) $entry->category) }}"
                                            data-entry-hours="{{ number_format((float) $entry->hours, 2) }}"
                                            data-entry-start="{{ $formatDateBE($entry->start_date) }}"
                                            data-entry-end="{{ $formatDateBE($entry->end_date) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger">ลบ</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @forelse ($task->entries as $entry)
            <article class="back-office-record-card back-office-detail-card" id="entry-{{ $entry->id }}" data-back-office-entry-card data-back-office-search="{{ trim($entry->person_name.' '.$entry->category.' '.$entry->job_name.' '.$entry->job_detail.' '.$formatDateBE($entry->start_date).' '.$formatDateBE($entry->end_date)) }}">
                <div class="back-office-record-card__top">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-500">
                                #{{ $loop->iteration }}
                            </span>
                            <div class="text-lg font-semibold text-slate-900">{{ $entry->person_name }}</div>
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                {{ $entry->category }}
                            </span>
                        </div>
                        @if ($entry->job_name)
                            <div class="mt-1 text-sm font-medium text-slate-700">{{ $entry->job_name }}</div>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <div class="back-office-record-card__actions">
                            @if (!$drawerMode && $canManageBackOfficeLineItems)
                                <button type="button"
                                    class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                    data-back-office-entry-edit-toggle>
                                    แก้ไข
                                </button>
                            @endif
                            @if (!$drawerMode)
                                <form action="{{ route('back_office.entries.destroy', [$task, $entry->id]) }}" method="POST"
                                    data-back-office-entry-delete-form
                                    data-entry-id="{{ $entry->id }}"
                                    data-entry-person="{{ trim((string) $entry->person_name) }}"
                                    data-entry-job="{{ trim((string) $entry->job_name) }}"
                                    data-entry-category="{{ trim((string) $entry->category) }}"
                                    data-entry-hours="{{ number_format((float) $entry->hours, 2) }}"
                                    data-entry-start="{{ $formatDateBE($entry->start_date) }}"
                                    data-entry-end="{{ $formatDateBE($entry->end_date) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                                        ลบ
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="grid w-full grid-cols-2 gap-3 text-sm md:min-w-[220px]">
                            <div class="rounded-xl bg-slate-50 p-3 text-center">
                                <div class="text-xs text-slate-500">ชั่วโมง</div>
                                <div class="text-lg font-semibold text-slate-900">{{ number_format($entry->hours, 2) }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 text-center">
                                <div class="text-xs text-slate-500">FTE</div>
                                <div class="text-lg font-semibold text-slate-900">{{ number_format($entry->fte_value, 8) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="back-office-record-card__detail-grid text-sm" data-bo-card-detail>
                    <div>
                        <div class="text-xs font-semibold text-slate-500">รายละเอียดงาน</div>
                        <div class="mt-1 whitespace-pre-wrap text-slate-800">{{ $entry->job_detail }}</div>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs font-semibold text-slate-500">วันที่เริ่ม</div>
                                <div class="mt-1 text-slate-800">{{ $formatDateBE($entry->start_date) }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs font-semibold text-slate-500">วันที่สิ้นสุด</div>
                                <div class="mt-1 text-slate-800">{{ $formatDateBE($entry->end_date) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (!$drawerMode && $canManageBackOfficeLineItems)
                    @php
                        $openEditEntry = (string) old('_edit_entry_id') === (string) $entry->id;
                    @endphp
                    <section class="mt-5 rounded-[28px] border border-sky-100 bg-sky-50/80 p-5 shadow-inner {{ $openEditEntry ? '' : 'hidden' }}" data-back-office-entry-edit-card>
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">แก้ไขภาระงานจากหน้านี้</div>
                                <div class="text-xs text-slate-500">แก้รายละเอียดแล้วบันทึกได้ทันที โดยไม่ต้องไปหน้า edit</div>
                            </div>
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-100" data-back-office-entry-edit-close>ปิดฟอร์ม</button>
                        </div>

                            <form action="{{ route('back_office.entries.update', [$task, $entry->id]) }}" method="POST" class="mt-5 space-y-5" novalidate data-back-office-required-form>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_edit_entry_id" value="{{ $entry->id }}">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">ผู้รับผิดชอบ <span class="bo-required">*</span></span>
                                    <select name="person_name" required class="{{ $inlineFieldClass }}" data-back-office-person-select data-placeholder="กรุณาเลือกผู้รับผิดชอบ" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้รับผิดชอบ">
                                        <option value="">กรุณาเลือกผู้รับผิดชอบ</option>
                                        @foreach ($personList as $person)
                                            <option value="{{ $person }}" @selected(old('_edit_entry_id') == $entry->id ? old('person_name') === $person : $entry->person_name === $person)>{{ $person }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">หมวดงาน <span class="bo-required">*</span></span>
                                    <select name="category" required class="{{ $inlineFieldClass }}">
                                        <option value="">เลือกหมวดงาน</option>
                                        @foreach ($categoryOptions as $category)
                                            <option value="{{ $category }}" @selected(old('_edit_entry_id') == $entry->id ? old('category') === $category : $entry->category === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">ชื่อภาระงาน <span class="bo-required">*</span></span>
                                    <input name="job_name" value="{{ old('_edit_entry_id') == $entry->id ? old('job_name') : $entry->job_name }}" required class="{{ $inlineFieldClass }}">
                                </label>
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">จำนวนชั่วโมง <span class="bo-required">*</span></span>
                                    <input name="hours" type="number" min="0" step="0.01" value="{{ old('_edit_entry_id') == $entry->id ? old('hours') : $entry->hours }}" required class="{{ $inlineFieldClass }}">
                                </label>
                            </div>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">รายละเอียดงาน <span class="bo-required">*</span></span>
                                <textarea name="job_detail" required class="{{ $inlineTextareaClass }}">{{ old('_edit_entry_id') == $entry->id ? old('job_detail') : $entry->job_detail }}</textarea>
                                <p class="mt-1 text-xs leading-5 text-slate-500">รองรับข้อความยาวและจะคงการเว้นบรรทัดตามที่พิมพ์ไว้</p>
                            </label>
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">วันที่เริ่ม <span class="bo-required">*</span></span>
                                    <input type="text" name="start_date" value="{{ old('_edit_entry_id') == $entry->id ? old('start_date') : $entry->start_date }}" class="js-date-be {{ $inlineFieldClass }}" placeholder="กรุณาใส่วันที่เริ่มต้นภาระงาน" autocomplete="off">
                                </label>
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">วันที่สิ้นสุด <span class="bo-required">*</span></span>
                                    <input type="text" name="end_date" value="{{ old('_edit_entry_id') == $entry->id ? old('end_date') : $entry->end_date }}" class="js-date-be {{ $inlineFieldClass }}" placeholder="กรุณาใส่วันที่สิ้นสุดภาระงาน" autocomplete="off">
                                </label>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <button type="button" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100" data-back-office-entry-edit-close>
                                    ยกเลิก
                                </button>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    บันทึกการแก้ไข
                                </button>
                            </div>
                        </form>
                    </section>
                @endif

            </article>
        @empty
            <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-400" data-back-office-entry-empty>
                ไม่มีข้อมูลภาระงาน
            </div>
        @endforelse
            <div class="hidden rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-400" data-back-office-entry-no-results>
                ไม่พบภาระงานที่ตรงกับคำค้นหา
            </div>
        </div>
    </section>

    <section class="back-office-section" id="back-office-output-section">
            <div class="back-office-section__head back-office-section__head--output">
                <div>
                    <div class="back-office-section__title">B: ผลงาน</div>
                    <div class="back-office-section__desc">สรุปผลงานที่บันทึกใต้ task นี้ พร้อมข้อมูลเฉพาะตามประเภทของ output</div>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    @if (!$drawerMode)
                        <button type="button" id="open-quick-output"
                            class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                            เพิ่มผลงาน
                        </button>
                    @endif
                    <div class="back-office-section__count">รวม {{ number_format($task->outputs->count()) }} รายการ</div>
                </div>
            </div>
            <div class="back-office-section__body space-y-4 text-sm" data-back-office-output-list>
                @if (!$drawerMode)
                    <div id="quick-output-card-anchor"></div>
                    <section id="quick-output-card" class="back-office-quick-card {{ $openQuickOutput ? '' : 'hidden' }}">
                        <div class="flex flex-col gap-4 px-5 py-5 md:px-6">
                            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">เพิ่มผลงานจากหน้านี้</h2>
                                    <p class="text-sm text-slate-500">กรอกข้อมูลงานที่เกิดขึ้นจาก Back Office ได้ทันทีใน section นี้</p>
                                </div>
                                <button type="button" id="close-quick-output" class="text-sm font-semibold text-red-600 hover:text-red-700">ยกเลิกการเพิ่ม</button>
                            </div>

                            <form action="{{ route('back_office.outputs.store', $task) }}" method="POST" class="space-y-4" novalidate data-back-office-required-form>
                                @csrf
                                <input type="hidden" name="_quick_add_output" value="1">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <label class="block">
                                        <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน <span class="bo-required">*</span></span>
                                        <select name="type" id="quick-output-type">
                                            <option value="">กรุณาเลือกประเภทผลงาน</option>
                                            @foreach ($allOutputTypeOptions as $outputType)
                                                <option value="{{ $outputType }}" @selected(old('type') === $outputType)>{{ $outputType }}</option>
                                            @endforeach
                                        </select>
                                        @error('type')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                                    </label>
                                    <label class="block" data-bo-output-date-field>
                                        <span class="text-xs font-semibold text-slate-600">วันที่เผยแพร่ <span class="bo-required">*</span></span>
                                        <input type="text" name="year" value="{{ old('year') }}" class="js-date-be" placeholder="วันที่เผยแพร่" autocomplete="off">
                                        @error('year')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                                    </label>

                                    <label class="block md:col-span-2">
                                        <span class="text-xs font-semibold text-slate-600">ชื่อผลงาน <span class="bo-required">*</span></span>
                                        <input name="title" value="{{ old('title') }}" placeholder="กรุณาใส่ชื่อผลงาน" required>
                                        @error('title')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                                    </label>

                                    <div class="md:col-span-2 rounded-2xl border border-sky-200 bg-sky-50/80">
                                        <div class="rounded-t-2xl border-b border-sky-200 bg-sky-100/80 px-4 py-3">
                                            <div class="text-sm font-semibold text-sky-950">ผู้มีส่วนร่วมในงาน <span class="bo-required">*</span></div>
                                        </div>
                                        <div class="relative z-30 space-y-3 p-4" data-output-contributor-list>
                                            @foreach ($oldQuickContributors as $contributorIndex => $contributor)
                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_auto]" data-output-contributor-row>
                                                    <label class="block">
                                                        <span class="text-xs font-semibold text-slate-600">ชื่อผู้มีส่วนร่วม <span class="bo-required">*</span></span>
                                                        <select name="contributors[{{ $contributorIndex }}][user_id]" required data-back-office-person-select data-placeholder="กรุณาเลือกผู้มีส่วนร่วม" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้มีส่วนร่วม">
                                                            <option value="">กรุณาเลือกผู้มีส่วนร่วม</option>
                                                            @foreach ($thaiPersonOptions as $person)
                                                                <option value="{{ $person['id'] }}" @selected((string) ($contributor['user_id'] ?? '') === (string) $person['id'])>{{ $person['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <div class="flex items-end justify-end">
                                                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-output-contributor>ลบ</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="flex justify-center border-t border-sky-100 px-4 py-3">
                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-sky-300 bg-white px-4 py-2 text-xs font-semibold text-sky-800 hover:bg-sky-50" data-add-output-contributor>เพิ่มรายชื่อ</button>
                                        </div>
                                        @error('contributors')<div class="mt-2 text-xs text-red-600">{{ $message }}</div>@enderror
                                    </div>

                                    <label class="block md:col-span-2">
                                        <span class="text-xs font-semibold text-slate-600">รายละเอียด / ลิงก์ผลงาน</span>
                                        <input name="project_output" value="{{ old('project_output') }}" placeholder="รายละเอียด / ลิงก์ผลงาน">
                                        @error('project_output')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
                                    </label>
                                </div>

                                <div id="quick-output-ea101" class="{{ str_starts_with((string) old('type'), 'EA101') ? '' : 'hidden' }} space-y-4">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">Journal <span class="bo-required">*</span></span>
                                            <input name="journal" value="{{ old('journal') }}" placeholder="เช่น Frontiers in Medicine">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">Ranking</span>
                                            <select name="quartile">
                                                <option value="">เลือก Ranking</option>
                                                @foreach (['Q1','Q2','Q3','Q4','TCI 1','TCI 2'] as $q)
                                                    <option value="{{ $q }}" @selected(old('quartile') === $q)>{{ $q }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">Impact Factor</span>
                                            <input type="number" step="0.001" name="impact_factor" value="{{ old('impact_factor') }}" placeholder="เช่น 3.215">
                                        </label>
                                    </div>
                                    <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4">
                                        <div class="flex flex-col gap-2">
                                            <div>
                                                <div class="text-sm font-semibold text-blue-900">รายชื่อผู้เขียน (Authors)</div>
                                                <div class="text-xs text-blue-700/80">เพิ่มได้หลายคน พร้อมระบุบทบาทของผู้เขียนแต่ละคน</div>
                                            </div>
                                        </div>
                                        @php
                                            $quickAuthors = collect(old('authors', []))->filter(function ($item) {
                                                return trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['role'] ?? '')) !== '';
                                            })->values();
                                            if ($quickAuthors->isEmpty() && (old('journal_author_name') || old('journal_author_role'))) {
                                                $quickAuthors = collect([[
                                                    'name' => old('journal_author_name'),
                                                    'role' => old('journal_author_role'),
                                                ]]);
                                            }
                                            if ($quickAuthors->isEmpty()) {
                                                $quickAuthors = collect([['name' => '', 'role' => '']]);
                                            }
                                        @endphp
                                        <div class="mt-4 space-y-3" data-quick-author-list>
                                            @foreach ($quickAuthors as $authorIndex => $author)
                                                @php
                                                    $authorName = trim((string) ($author['name'] ?? ''));
                                                    $authorPersonType = $author['person_type']
                                                        ?? ($authorName === '' ? '' : (collect($researchPersonList)->contains($authorName) ? 'internal' : 'external'));
                                                @endphp
                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,180px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-author-row data-conditional-person-row>
                                                    <label class="block">
                                                        <span class="text-xs font-semibold text-slate-600">บทบาท <span class="bo-required">*</span></span>
                                                        <select name="authors[{{ $authorIndex }}][role]">
                                                            <option value="">เลือกบทบาท</option>
                                                            <option value="first" @selected(($author['role'] ?? '') === 'first')>First-author</option>
                                                            <option value="co" @selected(($author['role'] ?? '') === 'co')>Co-author</option>
                                                            <option value="corresponding" @selected(($author['role'] ?? '') === 'corresponding')>Corresponding author</option>
                                                        </select>
                                                    </label>
                                                    <label class="block">
                                                        <span class="text-xs font-semibold text-slate-600">ประเภทผู้เขียน <span class="bo-required">*</span></span>
                                                        <select name="authors[{{ $authorIndex }}][person_type]" data-person-type-select>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal" @selected($authorPersonType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external" @selected($authorPersonType === 'external')>คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <div class="{{ $authorPersonType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน <span class="bo-required">*</span></span>
                                                        <select data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน">
                                                            <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
                                                            @foreach ($researchPersonList as $person)
                                                                <option value="{{ $person }}" @selected($authorPersonType === 'internal' && (($author['name'] ?? '') === $person))>{{ $person }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label class="{{ $authorPersonType === 'internal' ? 'hidden' : '' }}" data-person-manual-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ) <span class="bo-required">*</span></span>
                                                        <input value="{{ $authorPersonType === 'external' ? ($author['name'] ?? '') : '' }}" placeholder="{{ $authorPersonType === '' ? 'เลือกประเภทผู้เขียนก่อน' : 'ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)' }}" data-idle-placeholder="เลือกประเภทผู้เขียนก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)" data-person-manual-input @disabled($authorPersonType !== 'external')>
                                                    </label>
                                                    <input type="hidden" name="authors[{{ $authorIndex }}][name]" value="{{ $author['name'] ?? '' }}" data-person-name-hidden>
                                                    <div class="flex items-end justify-end">
                                                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-author>
                                                            ลบผู้เขียน
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-5 flex justify-center">
                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50" data-add-quick-author>
                                                เพิ่มผู้เขียน
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="quick-output-ea103" class="{{ str_starts_with((string) old('type'), 'EA103') ? '' : 'hidden' }}">
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
                                        <div class="flex flex-col gap-2">
                                            <div>
                                                <div class="text-sm font-semibold text-amber-900">ข้อมูลเวทีนำเสนอ</div>
                                                <div class="text-xs text-amber-700/80">1 ผลงานมีข้อมูลเวทีนำเสนอ 1 ชุด และเพิ่มผู้นำเสนอได้หลายคนเหมือนหน้า project</div>
                                            </div>
                                        </div>
                                        @php
                                            $quickConferences = collect(old('conferences', []))->filter(function ($item) {
                                                return trim((string) ($item['name'] ?? '')) !== ''
                                                    || trim((string) ($item['presentation_type'] ?? '')) !== ''
                                                    || trim((string) ($item['location'] ?? '')) !== ''
                                                    || !empty($item['presenters'])
                                                    || trim((string) ($item['note'] ?? '')) !== '';
                                            })->values();
                                            if ($quickConferences->isEmpty() && (old('conference_name') || old('conference_presentation_type') || old('conference_location') || old('conference_note'))) {
                                                $quickConferences = collect([[
                                                    'name' => old('conference_name'),
                                                    'presentation_type' => old('conference_presentation_type'),
                                                    'location' => old('conference_location'),
                                                    'note' => old('conference_note'),
                                                    'presenters' => old('conference_presenters', []),
                                                ]]);
                                            }
                                            $quickConference = $quickConferences->first() ?? ['name' => '', 'presentation_type' => '', 'location' => '', 'note' => '', 'presenter_person_type' => ''];
                                            $quickConferencePresenters = collect($quickConference['presenters'] ?? [])->filter(function ($item) {
                                                return trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['person_type'] ?? '')) !== '';
                                            })->values();
                                            if ($quickConferencePresenters->isEmpty() && (($quickConference['note'] ?? '') || ($quickConference['presenter_person_type'] ?? ''))) {
                                                $quickConferencePresenters = collect([[
                                                    'name' => $quickConference['note'] ?? '',
                                                    'person_type' => $quickConference['presenter_person_type'] ?? '',
                                                ]]);
                                            }
                                            if ($quickConferencePresenters->isEmpty()) {
                                                $quickConferencePresenters = collect([['name' => '', 'person_type' => '']]);
                                            }
                                        @endphp
                                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <label class="block md:col-span-3">
                                                <span class="text-xs font-semibold text-slate-600">ชื่อเวที/Conference <span class="bo-required">*</span></span>
                                                <input name="conference_name" value="{{ $quickConference['name'] ?? '' }}" placeholder="เช่น PMAC 2025">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">ประเภทนำเสนอ <span class="bo-required">*</span></span>
                                                <select name="conference_presentation_type">
                                                    <option value="">เลือก</option>
                                                    @foreach (['Oral Presentation', 'Poster Presentation', 'Abstract Presentation', 'Panel Discussion', 'Keynote Speech', 'World café', 'Moderator'] as $presentationType)
                                                        <option value="{{ $presentationType }}" @selected(($quickConference['presentation_type'] ?? '') === $presentationType)>{{ $presentationType }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label class="block md:col-span-2">
                                                <span class="text-xs font-semibold text-slate-600">สถานที่ <span class="bo-required">*</span></span>
                                                <input name="conference_location" value="{{ $quickConference['location'] ?? '' }}" placeholder="เมือง/ประเทศ">
                                            </label>
                                        </div>
                                        <div class="mt-5 rounded-2xl border border-amber-200/70 bg-white/70 p-4">
                                            <div class="flex flex-col gap-2">
                                                <div>
                                                    <div class="text-sm font-semibold text-amber-900">รายชื่อผู้นำเสนอ</div>
                                                    <div class="text-xs text-slate-500">เพิ่มได้หลายคน เหมือนรูปแบบของ project</div>
                                                </div>
                                            </div>
                                            <div class="mt-4 space-y-3" data-quick-conference-presenter-list>
                                            @foreach ($quickConferencePresenters as $presenterIndex => $presenter)
                                                @php
                                                    $presenterName = trim((string) ($presenter['name'] ?? ''));
                                                    $presenterType = $presenter['person_type']
                                                        ?? ($presenterName === '' ? '' : (collect($personList)->contains($presenterName) ? 'internal' : 'external'));
                                                @endphp
                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-conference-presenter-row data-conditional-person-row data-show-idle-person-select>
                                                    <label class="block">
                                                        <span class="text-xs font-semibold text-slate-600">ประเภทผู้นำเสนอ <span class="bo-required">*</span></span>
                                                        <select name="conference_presenters[{{ $presenterIndex }}][person_type]" data-person-type-select>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal" @selected($presenterType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external" @selected($presenterType === 'external')>คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <div class="{{ $presenterType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">เลือกผู้นำเสนอ <span class="bo-required">*</span></span>
                                                        <select data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้นำเสนอ" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ">
                                                            <option value="">เลือกผู้นำเสนอ</option>
                                                            @foreach ($personList as $person)
                                                                <option value="{{ $person }}" @selected($presenterType === 'internal' && $presenterName === $person)>{{ $person }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label class="{{ $presenterType === 'internal' ? 'hidden' : '' }}" data-person-manual-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้นำเสนอ <span class="bo-required">*</span></span>
                                                        <input value="{{ $presenterType === 'external' ? $presenterName : '' }}" placeholder="{{ $presenterType === '' ? 'เลือกประเภทผู้นำเสนอก่อน' : 'ชื่อ - นามสกุล ผู้นำเสนอ' }}" data-idle-placeholder="เลือกประเภทผู้นำเสนอก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้นำเสนอ" data-person-manual-input @disabled($presenterType !== 'external')>
                                                    </label>
                                                    <input type="hidden" name="conference_presenters[{{ $presenterIndex }}][name]" value="{{ $presenterName }}" data-person-name-hidden>
                                                    <div class="flex items-end justify-end">
                                                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-conference-presenter>
                                                            ลบผู้นำเสนอ
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                            </div>
                                            <div class="mt-5 flex justify-center">
                                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-white px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50" data-add-quick-conference-presenter>
                                                    เพิ่มผู้นำเสนอ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="quick-output-ea118" class="{{ str_starts_with((string) old('type'), 'EA118') ? '' : 'hidden' }} grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="md:col-span-2 rounded-2xl border border-violet-200 bg-violet-50/70 p-4">
                                        <div class="flex flex-col gap-2">
                                            <div>
                                                <div class="text-sm font-semibold text-violet-900">ผู้ได้รับรางวัล</div>
                                                <div class="text-xs text-violet-700/80">เพิ่มได้หลายคน โดยระบุประเภทของผู้ได้รับรางวัลแต่ละราย</div>
                                            </div>
                                        </div>
                                        @php
                                            $quickAwardRecipients = collect(old('award_recipients', []))->filter(function ($item) {
                                                return trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['person_type'] ?? '')) !== '';
                                            })->values();
                                            if ($quickAwardRecipients->isEmpty() && (old('award_recipient_name') || old('award_recipient_person_type'))) {
                                                $quickAwardRecipients = collect([[
                                                    'name' => old('award_recipient_name'),
                                                    'person_type' => old('award_recipient_person_type'),
                                                ]]);
                                            }
                                            if ($quickAwardRecipients->isEmpty()) {
                                                $quickAwardRecipients = collect([['name' => '', 'person_type' => '']]);
                                            }
                                        @endphp
                                        <div class="mt-4 space-y-3" data-quick-award-list>
                                            @foreach ($quickAwardRecipients as $recipientIndex => $recipient)
                                                @php $recipientType = $recipient['person_type'] ?? ''; @endphp
                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-award-recipient-row data-conditional-person-row data-show-idle-person-select>
                                                    <label class="block">
                                                        <span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก <span class="bo-required">*</span></span>
                                                        <select name="award_recipients[{{ $recipientIndex }}][person_type]" data-person-type-select>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="unit" @selected($recipientType === 'unit')>หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="internal" @selected($recipientType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external" @selected($recipientType === 'external')>คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <div class="{{ $recipientType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล <span class="bo-required">*</span></span>
                                                        <select data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้ได้รับรางวัล" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล">
                                                            <option value="">เลือกผู้ได้รับรางวัล</option>
                                                            @foreach ($personList as $person)
                                                                <option value="{{ $person }}" @selected($recipientType === 'internal' && (($recipient['name'] ?? '') === $person))>{{ $person }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label class="{{ in_array($recipientType, ['external', 'unit'], true) ? '' : 'hidden' }}" data-person-manual-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล <span class="bo-required">*</span></span>
                                                        <input
                                                            value="{{ in_array($recipientType, ['external', 'unit'], true) ? (($recipient['name'] ?? '') !== '' ? ($recipient['name'] ?? '') : ($recipientType === 'unit' ? 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' : '')) : '' }}"
                                                            placeholder="ชื่อผู้ได้รับรางวัล"
                                                            @disabled(! in_array($recipientType, ['external', 'unit'], true))
                                                            @readonly($recipientType === 'unit')
                                                            data-idle-placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน"
                                                            data-external-placeholder="ชื่อผู้ได้รับรางวัล"
                                                            data-person-manual-input
                                                        >
                                                    </label>
                                                    <input type="hidden" name="award_recipients[{{ $recipientIndex }}][name]" value="{{ ($recipientType === 'unit' && empty($recipient['name'])) ? 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' : ($recipient['name'] ?? '') }}" data-person-name-hidden>
                                                    <div class="flex items-end justify-end">
                                                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-award-recipient>
                                                            ลบรายชื่อ
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-6 flex justify-center pt-2">
                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-white px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-50" data-add-quick-award-recipient>
                                                เพิ่มผู้ได้รับรางวัล
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="quick-output-ea119" class="{{ str_starts_with((string) old('type'), 'EA119') ? '' : 'hidden' }} space-y-4">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">ชื่อหนังสือ <span class="bo-required">*</span></span>
                                            <input name="book_title" value="{{ old('book_title') }}" placeholder="ชื่อหนังสือ">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">สำนักพิมพ์ <span class="bo-required">*</span></span>
                                            <input name="publisher_name" value="{{ old('publisher_name') }}" placeholder="ชื่อสำนักพิมพ์">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">ชื่อบท / เลขหน้า <span class="bo-required">*</span></span>
                                            <input name="chapter_pages" value="{{ old('chapter_pages') }}" placeholder="เช่น Chapter 2, pp. 45-66">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">เลข ISBN</span>
                                            <input name="isbn" value="{{ old('isbn') }}" placeholder="เลข ISBN">
                                        </label>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4">
                                        <div class="flex flex-col gap-2">
                                            <div>
                                                <div class="text-sm font-semibold text-emerald-900">รายชื่อผู้เขียน</div>
                                                <div class="text-xs text-emerald-700/80">เพิ่มรายชื่อผู้เขียนทีละคนตามข้อมูลหนังสือหรือบทในหนังสือ</div>
                                            </div>
                                        </div>
                                        @php
                                            $quickBookAuthors = collect(old('book_authors', []))->filter(function ($item) {
                                                return trim((string) ($item['name'] ?? '')) !== '';
                                            })->values();
                                            if ($quickBookAuthors->isEmpty() && old('book_author_name')) {
                                                $quickBookAuthors = collect([['name' => old('book_author_name')]]);
                                            }
                                            if ($quickBookAuthors->isEmpty()) {
                                                $quickBookAuthors = collect([['name' => '']]);
                                            }
                                        @endphp
                                        <div class="mt-4 space-y-3" data-quick-book-author-list>
                                            @foreach ($quickBookAuthors as $bookAuthorIndex => $bookAuthor)
                                                @php
                                                    $bookAuthorName = trim((string) ($bookAuthor['name'] ?? ''));
                                                    $bookAuthorType = $bookAuthor['person_type']
                                                        ?? ($bookAuthorName === '' ? '' : (collect($personList)->contains($bookAuthorName) ? 'internal' : 'external'));
                                                @endphp
                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-book-author-row data-conditional-person-row data-show-idle-person-select>
                                                    <label class="block">
                                                        <span class="text-xs font-semibold text-slate-600">ประเภทผู้เขียน <span class="bo-required">*</span></span>
                                                        <select name="book_authors[{{ $bookAuthorIndex }}][person_type]" data-person-type-select>
                                                            <option value="">กรุณาเลือก</option>
                                                            <option value="internal" @selected($bookAuthorType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                            <option value="external" @selected($bookAuthorType === 'external')>คนนอก</option>
                                                        </select>
                                                    </label>
                                                    <div class="{{ $bookAuthorType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน <span class="bo-required">*</span></span>
                                                        <select data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน">
                                                            <option value="">เลือกผู้เขียน</option>
                                                            @foreach ($personList as $person)
                                                                <option value="{{ $person }}" @selected($bookAuthorType === 'internal' && (($bookAuthor['name'] ?? '') === $person))>{{ $person }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label class="{{ $bookAuthorType === 'external' ? '' : 'hidden' }}" data-person-manual-wrap>
                                                        <span class="text-xs font-semibold text-slate-600">ชื่อผู้เขียน <span class="bo-required">*</span></span>
                                                        <input value="{{ $bookAuthorType === 'external' ? ($bookAuthor['name'] ?? '') : '' }}" placeholder="{{ $bookAuthorType === '' ? 'เลือกประเภทผู้เขียนก่อน' : 'ชื่อผู้เขียน' }}" data-idle-placeholder="เลือกประเภทผู้เขียนก่อน" data-external-placeholder="ชื่อผู้เขียน" data-person-manual-input @disabled($bookAuthorType !== 'external')>
                                                    </label>
                                                    <input type="hidden" name="book_authors[{{ $bookAuthorIndex }}][name]" value="{{ $bookAuthor['name'] ?? '' }}" data-person-name-hidden>
                                                    <div class="flex items-end justify-end">
                                                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-book-author>
                                                            ลบ
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-6 flex justify-center pt-2">
                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50" data-add-quick-book-author>
                                                เพิ่มผู้เขียน
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                    <button type="button" id="cancel-quick-output" class="back-office-action back-office-action--cancel">ยกเลิก</button>
                                    <button type="submit" class="back-office-action back-office-action--save">บันทึกผลงาน</button>
                                </div>
                            </form>
                        </div>
                    </section>
                @endif
                @if (!$drawerMode && $task->outputs->count())
                    <div class="back-office-table-wrap">
                        <table class="back-office-data-table back-office-output-table">
                            <colgroup>
                                <col class="back-office-output-table__index">
                                <col class="back-office-output-table__title">
                                <col class="back-office-output-table__type">
                                <col class="back-office-output-table__people">
                                <col class="back-office-output-table__actions">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="w-14">#</th>
                                    <th>ชื่อผลงาน</th>
                                    <th>ประเภท</th>
                                    <th>ผู้มีส่วนร่วม</th>
                                    <th class="text-right">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($task->outputs as $output)
                                    @php
                                        $tableContributors = $output->resolvedContributors()->pluck('name')->filter()->values();
                                        $tableAuthors = collect($output->resolvedAuthors());
                                        $tableBookAuthors = collect($output->resolvedBookAuthors());
                                        $tableAwardees = collect($output->resolvedAwardRecipients());
                                        $tableConfs = collect($output->resolvedConferences());
                                        $tableIsEA101 = str_starts_with((string) ($output->type ?? ''), 'EA101');
                                        $tableIsEA103 = str_starts_with((string) ($output->type ?? ''), 'EA103');
                                        $tableIsEA118 = str_starts_with((string) ($output->type ?? ''), 'EA118');
                                        $tableIsEA119 = str_starts_with((string) ($output->type ?? ''), 'EA119');
                                        $tableSpecialToneClass = match (true) {
                                            $tableIsEA101 => 'back-office-output-special-inline--ea101',
                                            $tableIsEA103 => 'back-office-output-special-inline--ea103',
                                            $tableIsEA118 => 'back-office-output-special-inline--ea118',
                                            $tableIsEA119 => 'back-office-output-special-inline--ea119',
                                            default => 'back-office-output-special-inline--default',
                                        };
                                        $tableSpecialItems = collect();
                                        $tableOutputDate = $formatOutputDateBE($output->year);
                                        $tableSpecialTitle = trim((string) preg_replace('/^EA\d+\s*/', '', (string) ($output->type ?? '')));
                                        if ($tableSpecialTitle === '') {
                                            $tableSpecialTitle = 'Output';
                                        }
                                        if ($tableIsEA101) {
                                            $tableAuthorItems = $tableAuthors
                                                ->map(function ($author) use ($authorRoleLabel) {
                                                    $name = trim((string) ($author['name'] ?? ''));
                                                    if ($name === '') {
                                                        return null;
                                                    }
                                                    $role = $authorRoleLabel($author['role'] ?? null);
                                                    return $role ? "{$name} ({$role})" : $name;
                                                })
                                                ->filter()
                                                ->values();
                                            $tableSpecialItems = collect([
                                                ['label' => 'Journal', 'value' => $output->journal, 'area' => 'journal'],
                                                ['label' => 'Authors', 'value' => $tableAuthorItems, 'area' => 'authors', 'list' => true],
                                                ['label' => 'Ranking', 'value' => $output->quartile, 'area' => 'metric', 'compact' => true],
                                                ['label' => 'IF', 'value' => $output->impact_factor, 'area' => 'metric', 'compact' => true],
                                            ]);
                                        } elseif ($tableIsEA103) {
                                            $firstConf = $tableConfs->first() ?: [];
                                            $presenterNames = collect(data_get($firstConf, 'presenters', []))->pluck('name')->filter()->values();
                                            $presenterItems = $presenterNames->isNotEmpty()
                                                ? $presenterNames
                                                : collect([data_get($firstConf, 'note')])->filter()->values();
                                            $tableSpecialItems = collect([
                                                ['label' => 'Conference', 'value' => data_get($firstConf, 'name'), 'area' => 'main'],
                                                ['label' => 'Type', 'value' => data_get($firstConf, 'presentation_type'), 'area' => 'metric', 'compact' => true],
                                                ['label' => 'Place', 'value' => data_get($firstConf, 'location'), 'area' => 'metric', 'compact' => true],
                                                ['label' => 'Presenter', 'value' => $presenterItems, 'area' => 'detail', 'list' => true],
                                            ]);
                                        } elseif ($tableIsEA118) {
                                            $awardeeItems = $tableAwardees->pluck('name')->filter()->values();
                                            $tableSpecialItems = collect([
                                                ['label' => 'Awardee', 'value' => $awardeeItems, 'area' => 'detail', 'list' => true],
                                            ]);
                                        } elseif ($tableIsEA119) {
                                            $tableBookAuthorItems = $tableBookAuthors->pluck('name')->filter()->values();
                                            $tableSpecialItems = collect([
                                                ['label' => 'Book', 'value' => $output->book_title, 'area' => 'main'],
                                                ['label' => 'Publisher', 'value' => $output->publisher_name, 'area' => 'metric'],
                                                ['label' => 'Pages', 'value' => $output->chapter_pages, 'area' => 'metric', 'compact' => true],
                                                ['label' => 'ISBN', 'value' => $output->isbn, 'area' => 'main', 'full' => true],
                                                ['label' => 'Authors', 'value' => $tableBookAuthorItems, 'area' => 'detail', 'list' => true],
                                            ]);
                                        }
                                        $tableSpecialItems = $tableSpecialItems
                                            ->filter(function ($item) {
                                                $value = $item['value'] ?? '';
                                                if ($value instanceof \Illuminate\Support\Collection) {
                                                    return $value->isNotEmpty();
                                                }
                                                if (is_array($value)) {
                                                    return !empty(array_filter($value));
                                                }
                                                return trim((string) $value) !== '';
                                            })
                                            ->values();
                                        $tableSpecialTopItems = $tableIsEA101
                                            ? $tableSpecialItems->filter(fn ($item) => in_array(($item['area'] ?? ''), ['journal', 'authors'], true))->values()
                                            : $tableSpecialItems;
                                        $tableSpecialMetricItems = $tableIsEA101
                                            ? $tableSpecialItems->filter(fn ($item) => ($item['area'] ?? '') === 'metric')->values()
                                            : collect();
                                        $tableSpecialUsesSplitLayout = $tableIsEA101 || $tableIsEA103 || $tableIsEA118 || $tableIsEA119;
                                        $tableSpecialLeftItems = $tableSpecialUsesSplitLayout
                                            ? $tableSpecialItems->filter(fn ($item) => in_array(($item['area'] ?? ''), ['journal', 'main'], true))->values()
                                            : collect();
                                        $tableSpecialDetailItems = $tableSpecialUsesSplitLayout
                                            ? $tableSpecialItems->filter(fn ($item) => in_array(($item['area'] ?? ''), ['authors', 'detail'], true))->values()
                                            : collect();
                                        $tableSpecialMetricItems = $tableSpecialUsesSplitLayout
                                            ? $tableSpecialItems->filter(fn ($item) => ($item['area'] ?? '') === 'metric')->values()
                                            : $tableSpecialMetricItems;
                                        $tableSpecialSingleDetail = $tableSpecialUsesSplitLayout
                                            && $tableSpecialLeftItems->isEmpty()
                                            && $tableSpecialMetricItems->isEmpty()
                                            && $tableSpecialDetailItems->count() === 1;
                                        $tableOutputSearch = collect([
                                            $output->title,
                                            $output->type,
                                            $output->year,
                                            $output->project_output,
                                            $tableContributors->implode(' '),
                                            $tableAuthors->pluck('name')->implode(' '),
                                            $tableBookAuthors->pluck('name')->implode(' '),
                                            $tableAwardees->pluck('name')->implode(' '),
                                            $tableConfs->pluck('name')->implode(' '),
                                        ])->filter()->implode(' ');
                                    @endphp
                                    <tr data-back-office-output-row data-back-office-output-id="{{ $output->id }}" data-back-office-search="{{ trim($tableOutputSearch) }}">
                                        <td class="back-office-table-index">{{ $loop->iteration }}</td>
                                        <td class="back-office-output-title-cell">
                                            <div class="back-office-output-title-main font-semibold leading-6 text-slate-900">{{ $output->title }}</div>
                                            @if ($output->project_output)
                                                <div class="back-office-output-title-meta mt-2 line-clamp-2 text-xs leading-5 text-slate-500">{{ $output->project_output }}</div>
                                            @endif
                                            @if ($tableOutputDate !== '-')
                                                <div class="back-office-output-title-meta mt-2 text-xs font-semibold text-slate-500">เผยแพร่ {{ $tableOutputDate }}</div>
                                            @endif
                                        </td>
                                        <td><span class="back-office-table-pill {{ $output->type ? $outputBadgeClass($output->type) : '' }}">{{ $output->type ?: '-' }}</span></td>
                                        <td class="text-xs text-slate-700">
                                            <div class="font-semibold leading-5 text-slate-800">{{ $tableContributors->take(3)->implode(', ') ?: '-' }}</div>
                                            @if ($tableContributors->count() > 3)
                                                <div class="mt-1 text-xs text-slate-500">+{{ $tableContributors->count() - 3 }} คน</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="back-office-table-actions">
                                                @if ($canManageBackOfficeLineItems)
                                                    <button type="button" data-bo-table-edit data-target-card="output-{{ $output->id }}">แก้ไข</button>
                                                @endif
                                                <form action="{{ route('back_office.outputs.destroy', [$task, $output->id]) }}" method="POST"
                                                    data-back-office-output-delete-form
                                                    data-output-id="{{ $output->id }}"
                                                    data-output-title="{{ trim((string) $output->title) }}"
                                                    data-output-type="{{ trim((string) ($output->type ?? '-')) }}"
                                                    data-output-year="{{ trim((string) ($output->year ?? '-')) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger">ลบ</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @if ($tableSpecialItems->isNotEmpty())
                                        <tr class="back-office-output-special-row" data-output-special-for="output-{{ $output->id }}" data-back-office-output-row data-back-office-output-id="{{ $output->id }}" data-back-office-search="{{ trim($tableOutputSearch) }}">
                                            <td colspan="5">
                                                <div class="back-office-output-special-inline {{ $tableSpecialToneClass }} {{ $tableSpecialUsesSplitLayout ? 'back-office-output-special-inline--split' : '' }} {{ $tableSpecialSingleDetail ? 'back-office-output-special-inline--single-detail' : '' }}">
                                                    <div class="back-office-output-special-title">ข้อมูล {{ $tableSpecialTitle }}</div>
                                                    @if ($tableSpecialUsesSplitLayout)
                                                        <div class="back-office-output-special-split-grid">
                                                            <div class="back-office-output-special-left">
                                                                @foreach ($tableSpecialLeftItems as $specialItem)
                                                                    <div class="back-office-output-special-item {{ !empty($specialItem['full']) ? 'back-office-output-special-item--full' : '' }}">
                                                                        <span>{{ $specialItem['label'] }}</span>
                                                                        <strong>{{ $specialItem['value'] }}</strong>
                                                                    </div>
                                                                @endforeach
                                                                @if ($tableSpecialMetricItems->isNotEmpty())
                                                                    <div class="back-office-output-special-metrics">
                                                                        @foreach ($tableSpecialMetricItems as $specialItem)
                                                                            <div class="back-office-output-special-item back-office-output-special-item--compact">
                                                                                <span>{{ $specialItem['label'] }}</span>
                                                                                <strong>{{ $specialItem['value'] }}</strong>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            @foreach ($tableSpecialDetailItems as $specialItem)
                                                                <div class="back-office-output-special-item back-office-output-special-item--authors">
                                                                    <span>{{ $specialItem['label'] }}</span>
                                                                    @if (!empty($specialItem['list']))
                                                                        <ol class="back-office-output-special-list back-office-output-special-list--inline">
                                                                            @foreach (collect($specialItem['value'])->values() as $listIndex => $listValue)
                                                                                <li><span class="back-office-output-special-list__number">{{ $loop->iteration }}.</span> {{ $listValue }}</li>
                                                                            @endforeach
                                                                        </ol>
                                                                    @else
                                                                        <strong>{{ $specialItem['value'] }}</strong>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="back-office-output-special-grid">
                                                            @foreach ($tableSpecialTopItems as $specialItem)
                                                                <div class="back-office-output-special-item {{ !empty($specialItem['compact']) ? 'back-office-output-special-item--compact' : '' }}">
                                                                    <span>{{ $specialItem['label'] }}</span>
                                                                    @if (!empty($specialItem['list']))
                                                                        <ol class="back-office-output-special-list">
                                                                            @foreach (collect($specialItem['value'])->values() as $listIndex => $listValue)
                                                                                <li>{{ $listValue }}</li>
                                                                            @endforeach
                                                                        </ol>
                                                                    @else
                                                                        <strong>{{ $specialItem['value'] }}</strong>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @foreach ($task->outputs as $output)
                    @php
                        $outputUrl = $extractUrl($output->project_output);
                        $openEditOutput = !$drawerMode && (string) old('_edit_output_id') === (string) $output->id;
                        $outputContributorNames = $output->resolvedContributors()->pluck('name')->filter()->values();
                        $isEA101 = str_starts_with((string) ($output->type ?? ''), 'EA101');
                        $isEA103 = str_starts_with((string) ($output->type ?? ''), 'EA103');
                        $isEA118 = str_starts_with((string) ($output->type ?? ''), 'EA118');
                        $isEA119 = str_starts_with((string) ($output->type ?? ''), 'EA119');
                        $authors = collect($output->resolvedAuthors());
                        $confs = collect($output->resolvedConferences());
                        $bookAuthors = collect($output->resolvedBookAuthors());
                        $awardees = collect($output->resolvedAwardRecipients());
                        $outputSearchText = collect([
                            $output->title,
                            $output->type,
                            $output->year,
                            $output->project_output,
                            $outputContributorNames->implode(' '),
                            $authors->pluck('name')->implode(' '),
                            $bookAuthors->pluck('name')->implode(' '),
                            $awardees->pluck('name')->implode(' '),
                            $confs->pluck('name')->implode(' '),
                        ])->filter()->implode(' ');
                    @endphp
                    <div class="back-office-record-card back-office-detail-card bg-slate-50/80" id="output-{{ $output->id }}" data-back-office-output-card data-back-office-search="{{ trim($outputSearchText) }}">
                        <div class="back-office-record-card__top">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-500">
                                        #{{ $loop->iteration }}
                                    </span>
                                    @if ($output->type)
                                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold {{ $outputBadgeClass($output->type) }}">
                                            {{ $output->type }}
                                        </span>
                                    @endif
                                    @if ($output->year)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-500">
                                            เผยแพร่ {{ $output->year }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-3 text-lg font-semibold leading-7 text-slate-900">{{ $output->title }}</div>
                            </div>
                            <div class="back-office-record-card__actions">
                                @if (!$drawerMode && $canManageBackOfficeLineItems)
                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                        data-back-office-output-edit-toggle>
                                        แก้ไข
                                    </button>
                                @endif
                                @if (!$drawerMode)
                                    <form action="{{ route('back_office.outputs.destroy', [$task, $output->id]) }}" method="POST"
                                        data-back-office-output-delete-form
                                        data-output-id="{{ $output->id }}"
                                        data-output-title="{{ trim((string) $output->title) }}"
                                        data-output-type="{{ trim((string) ($output->type ?? '-')) }}"
                                        data-output-year="{{ trim((string) ($output->year ?? '-')) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                                            ลบ
                                        </button>
                                    </form>
                                @endif
                                @if ($outputUrl)
                                    <a href="{{ $outputUrl }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-white px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-50">
                                        เปิดลิงก์ผลงาน
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if ($outputContributorNames->isNotEmpty())
                            <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3" data-bo-card-detail>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ผู้มีส่วนร่วมในงาน</div>
                                <div class="mt-1 text-sm font-semibold text-slate-800">{{ $outputContributorNames->implode(', ') }}</div>
                            </div>
                        @endif

                        @if ($output->project_output)
                            <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3" data-bo-card-detail>
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">รายละเอียด / ลิงก์ผลงาน</div>
                                <div class="mt-1 whitespace-pre-wrap break-words text-slate-700">{{ $output->project_output }}</div>
                            </div>
                        @endif

                        @if ($isEA101)
                            <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4" data-bo-card-detail>
                                <div class="text-sm font-semibold text-blue-900">ข้อมูลบทความวิจัย</div>
                                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Journal</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->journal ?? '-' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Ranking</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->quartile ?? '-' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Impact Factor</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->impact_factor ?? '-' }}</div>
                                    </div>
                                </div>
                                @if ($authors->count())
                                    <div class="mt-4 text-sm font-semibold text-slate-800">Authors</div>
                                    <ul class="mt-2 space-y-1 text-slate-700">
                                        @foreach ($authors as $author)
                                            <li class="rounded-xl bg-white px-3 py-2">
                                                <span class="font-medium">{{ $author['name'] ?? '-' }}</span>
                                                @php
                                                    $authorRoleDisplay = $authorRoleLabel($author['role'] ?? null);
                                                @endphp
                                                @if (!empty($authorRoleDisplay))
                                                    <span class="text-slate-500">({{ $authorRoleDisplay }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        @if ($isEA103)
                            <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50 p-4" data-bo-card-detail>
                                <div class="text-sm font-semibold text-amber-900">ข้อมูลการนำเสนอ</div>
                                @if ($confs->count())
                                    <div class="mt-3 space-y-3">
                                                @foreach ($confs as $conf)
                                                    <div class="rounded-xl bg-white px-3 py-3">
                                                        <div class="font-medium text-slate-800">{{ $conf['name'] ?? '-' }}</div>
                                                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                                            @if (!empty($conf['presentation_type']))
                                                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-700">{{ $conf['presentation_type'] }}</span>
                                                            @endif
                                                            @if (!empty($conf['location']))
                                                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-600">{{ $conf['location'] }}</span>
                                                            @endif
                                                        </div>
                                                        @php
                                                            $presenterNames = collect($conf['presenters'] ?? [])->pluck('name')->filter()->values()->all();
                                                            $presenterDisplay = !empty($presenterNames)
                                                                ? implode(', ', $presenterNames)
                                                                : ($conf['note'] ?? '');
                                                        @endphp
                                                        @if (!empty($presenterDisplay))
                                                            <div class="mt-2 text-sm text-slate-700">
                                                                <span class="font-semibold text-slate-800">ผู้นำเสนอ:</span> {{ $presenterDisplay }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                    </div>
                                @else
                                    <div class="mt-3 rounded-xl bg-white px-3 py-3 text-sm text-slate-600">ไม่มีรายการ Conference</div>
                                @endif
                            </div>
                        @endif

                        @if ($isEA118)
                            <div class="mt-4 rounded-2xl border border-violet-100 bg-violet-50 p-4" data-bo-card-detail>
                                <div class="text-sm font-semibold text-violet-900">ข้อมูลรางวัลและการยกย่อง</div>
                                @if ($awardees->count())
                                    <div class="mt-4 text-sm font-semibold text-slate-800">ผู้ได้รับรางวัล</div>
                                    <ul class="mt-2 space-y-1 text-slate-700">
                                        @foreach ($awardees as $awardee)
                                            <li class="rounded-xl bg-white px-3 py-2">
                                                <span class="font-medium">{{ $awardee['name'] ?? '-' }}</span>
                                                @if (!empty($awardee['person_type']))
                                                    <span class="text-slate-500">({{ $awardPersonTypeLabel($awardee['person_type']) ?? $awardee['person_type'] }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="mt-3 rounded-xl bg-white px-3 py-3 text-sm text-slate-600">ยังไม่มีข้อมูลผู้ได้รับรางวัล</div>
                                @endif
                            </div>
                        @endif

                        @if ($isEA119)
                            <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4" data-bo-card-detail>
                                <div class="text-sm font-semibold text-emerald-900">ข้อมูลหนังสือ / บทในหนังสือ</div>
                                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ชื่อหนังสือ</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->book_title ?? '-' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">สำนักพิมพ์</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->publisher_name ?? '-' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ชื่อบท / เลขหน้า</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->chapter_pages ?? '-' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">ISBN</div>
                                        <div class="mt-1 text-sm font-medium text-slate-800">{{ $output->isbn ?? '-' }}</div>
                                    </div>
                                </div>
                                @if ($bookAuthors->count())
                                    <div class="mt-4 text-sm font-semibold text-slate-800">รายชื่อผู้เขียน</div>
                                    <ul class="mt-2 space-y-1 text-slate-700">
                                        @foreach ($bookAuthors as $author)
                                            <li class="rounded-xl bg-white px-3 py-2">{{ $author['name'] ?? '-' }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        @if (!$drawerMode && $canManageBackOfficeLineItems)
                            <section class="mt-5 rounded-[28px] border border-emerald-100 bg-emerald-50/80 p-5 shadow-inner {{ $openEditOutput ? '' : 'hidden' }}" data-back-office-output-edit-card>
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">แก้ไขผลงานจากหน้านี้</div>
                                        <div class="text-xs text-slate-500">ปรับข้อมูลผลงานได้ทันที โดยไม่ต้องไปหน้า edit</div>
                                    </div>
                                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-100" data-back-office-output-edit-close>ปิดฟอร์ม</button>
                                </div>

                                <form action="{{ route('back_office.outputs.update', [$task, $output->id]) }}" method="POST" class="mt-5 space-y-5" novalidate data-back-office-required-form>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_edit_output_id" value="{{ $output->id }}">
                                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                        <label class="block">
                                            <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน <span class="bo-required">*</span></span>
                                            <select name="type" data-inline-output-type class="{{ $inlineFieldClass }}">
                                                <option value="">กรุณาเลือกประเภทผลงาน</option>
                                                @foreach ($allOutputTypeOptions as $outputType)
                                                    <option value="{{ $outputType }}" @selected(($openEditOutput ? old('type') : $output->type) === $outputType)>{{ $outputType }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="block" data-bo-output-date-field>
                                            <span class="text-xs font-semibold text-slate-600">วันที่เผยแพร่ <span class="bo-required">*</span></span>
                                            <input type="text" name="year" value="{{ $openEditOutput ? old('year') : $output->year }}" class="js-date-be {{ $inlineFieldClass }}" placeholder="วันที่เผยแพร่" autocomplete="off">
                                        </label>

                                        <label class="block md:col-span-2">
                                            <span class="text-xs font-semibold text-slate-600">ชื่อผลงาน <span class="bo-required">*</span></span>
                                            <input name="title" value="{{ $openEditOutput ? old('title') : $output->title }}" required class="{{ $inlineFieldClass }}">
                                        </label>

                                        @php
                                            $inlineContributors = $openEditOutput
                                                ? collect(old('contributors', []))->filter(fn ($item) => !empty($item['user_id']))->values()
                                                : $output->resolvedContributors();
                                            if ($inlineContributors->isEmpty()) {
                                                $inlineContributors = $emptyOutputContributors;
                                            }
                                        @endphp
                                        <div class="md:col-span-2 rounded-2xl border border-sky-200 bg-sky-50/80">
                                            <div class="rounded-t-2xl border-b border-sky-200 bg-sky-100/80 px-4 py-3">
                                                <div class="text-sm font-semibold text-sky-950">ผู้มีส่วนร่วมในงาน <span class="bo-required">*</span></div>
                                            </div>
                                            <div class="relative z-30 space-y-3 p-4" data-output-contributor-list>
                                                @foreach ($inlineContributors as $contributorIndex => $contributor)
                                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_auto]" data-output-contributor-row>
                                                        <label class="block">
                                                            <span class="text-xs font-semibold text-slate-600">ชื่อผู้มีส่วนร่วม <span class="bo-required">*</span></span>
                                                            <select name="contributors[{{ $contributorIndex }}][user_id]" required class="{{ $inlineFieldClass }}" data-back-office-person-select data-placeholder="กรุณาเลือกผู้มีส่วนร่วม" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้มีส่วนร่วม">
                                                                <option value="">กรุณาเลือกผู้มีส่วนร่วม</option>
                                                                @foreach ($thaiPersonOptions as $person)
                                                                    <option value="{{ $person['id'] }}" @selected((string) ($contributor['user_id'] ?? '') === (string) $person['id'])>{{ $person['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                        <div class="flex items-end justify-end">
                                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-output-contributor>ลบ</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="flex justify-center border-t border-sky-100 px-4 py-3">
                                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-sky-300 bg-white px-4 py-2 text-xs font-semibold text-sky-800 hover:bg-sky-50" data-add-output-contributor>เพิ่มรายชื่อ</button>
                                            </div>
                                        </div>

                                        <label class="block md:col-span-2">
                                            <span class="text-xs font-semibold text-slate-600">รายละเอียด / ลิงก์ผลงาน</span>
                                            <input name="project_output" value="{{ $openEditOutput ? old('project_output') : $output->project_output }}" class="{{ $inlineFieldClass }}">
                                        </label>
                                    </div>

                                    <div class="space-y-5 {{ str_starts_with((string) ($openEditOutput ? old('type') : $output->type), 'EA101') ? '' : 'hidden' }}" data-inline-output-ea101>
                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">Journal <span class="bo-required">*</span></span>
                                                <input name="journal" value="{{ $openEditOutput ? old('journal') : $output->journal }}" class="{{ $inlineFieldClass }}">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">Ranking</span>
                                                <select name="quartile" class="{{ $inlineFieldClass }}">
                                                    <option value="">เลือก Ranking</option>
                                                    @foreach (['Q1','Q2','Q3','Q4','TCI 1','TCI 2'] as $q)
                                                        <option value="{{ $q }}" @selected(($openEditOutput ? old('quartile') : $output->quartile) === $q)>{{ $q }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">Impact Factor</span>
                                                <input type="number" step="0.001" name="impact_factor" value="{{ $openEditOutput ? old('impact_factor') : $output->impact_factor }}" class="{{ $inlineFieldClass }}">
                                            </label>
                                        </div>
                                        <div class="rounded-2xl border border-blue-200 bg-white/70 p-4">
                                            <div class="flex flex-col gap-2">
                                                <div>
                                                    <div class="text-sm font-semibold text-blue-900">รายชื่อผู้เขียน (Authors)</div>
                                                    <div class="text-xs text-slate-500">เพิ่มได้หลายคน พร้อมระบุบทบาทของผู้เขียนแต่ละคน</div>
                                                </div>
                                            </div>
                                            @php
                                                $inlineAuthors = $openEditOutput
                                                    ? collect(old('authors', []))->filter(function ($item) {
                                                        return trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['role'] ?? '')) !== '';
                                                    })->values()
                                                    : $authors->filter(fn ($author) => ($author['role'] ?? '') !== 'awardee')->values();
                                                if ($inlineAuthors->isEmpty() && $openEditOutput && (old('journal_author_name') || old('journal_author_role'))) {
                                                    $inlineAuthors = collect([[
                                                        'name' => old('journal_author_name'),
                                                        'role' => old('journal_author_role'),
                                                    ]]);
                                                }
                                                if ($inlineAuthors->isEmpty()) {
                                                    $inlineAuthors = collect([['name' => '', 'role' => '', 'person_type' => '']]);
                                                }
                                            @endphp
                                            <div class="mt-4 space-y-3" data-inline-author-list>
                                                @foreach ($inlineAuthors as $authorIndex => $author)
                                                    @php
                                                        $authorName = trim((string) ($author['name'] ?? ''));
                                                        $authorPersonType = $author['person_type']
                                                            ?? ($authorName === '' ? '' : (collect($researchPersonList)->contains($authorName) ? 'internal' : 'external'));
                                                    @endphp
                                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,180px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-author-row data-conditional-person-row>
                                                        <label class="block">
                                                            <span class="text-xs font-semibold text-slate-600">บทบาท <span class="bo-required">*</span></span>
                                                            <select name="authors[{{ $authorIndex }}][role]" class="{{ $inlineFieldClass }}">
                                                                <option value="">เลือกบทบาท</option>
                                                                <option value="first" @selected(($author['role'] ?? '') === 'first')>First-author</option>
                                                                <option value="co" @selected(($author['role'] ?? '') === 'co')>Co-author</option>
                                                                <option value="corresponding" @selected(($author['role'] ?? '') === 'corresponding')>Corresponding author</option>
                                                            </select>
                                                        </label>
                                                        <label class="block">
                                                            <span class="text-xs font-semibold text-slate-600">ประเภทผู้เขียน <span class="bo-required">*</span></span>
                                                            <select name="authors[{{ $authorIndex }}][person_type]" class="{{ $inlineFieldClass }}" data-person-type-select>
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="internal" @selected($authorPersonType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external" @selected($authorPersonType === 'external')>คนนอก</option>
                                                            </select>
                                                        </label>
                                                        <div class="{{ $authorPersonType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน <span class="bo-required">*</span></span>
                                                            <select class="{{ $inlineFieldClass }}" data-back-office-person-select data-person-search-select data-placeholder="กรุณาเลือกชื่อผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน">
                                                                <option value="">พิมพ์เพื่อค้นหาชื่อผู้เขียน</option>
                                                            @foreach ($researchPersonList as $person)
                                                                <option value="{{ $person }}" @selected($authorPersonType === 'internal' && $authorName === $person)>{{ $person }}</option>
                                                            @endforeach
                                                        </select>
                                                        </div>
                                                        <label class="{{ $authorPersonType === 'internal' ? 'hidden' : '' }}" data-person-manual-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ) <span class="bo-required">*</span></span>
                                                            <input value="{{ $authorPersonType === 'external' ? $authorName : '' }}" class="{{ $inlineFieldClass }}" placeholder="{{ $authorPersonType === '' ? 'เลือกประเภทผู้เขียนก่อน' : 'ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)' }}" data-idle-placeholder="เลือกประเภทผู้เขียนก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)" data-person-manual-input @disabled($authorPersonType !== 'external')>
                                                        </label>
                                                        <input type="hidden" name="authors[{{ $authorIndex }}][name]" value="{{ $authorName }}" data-person-name-hidden>
                                                        <div class="flex items-end justify-end">
                                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-author>
                                                                ลบผู้เขียน
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-5 flex justify-center">
                                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50" data-add-inline-author>
                                                    เพิ่มผู้เขียน
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-5 {{ str_starts_with((string) ($openEditOutput ? old('type') : $output->type), 'EA103') ? '' : 'hidden' }}" data-inline-output-ea103>
                                        <div class="rounded-2xl border border-amber-200 bg-white/70 p-4">
                                            <div class="flex flex-col gap-2">
                                                <div>
                                                    <div class="text-sm font-semibold text-amber-900">ข้อมูลเวทีนำเสนอ</div>
                                                    <div class="text-xs text-slate-500">1 ผลงานมีข้อมูลเวทีนำเสนอ 1 ชุด และเพิ่มผู้นำเสนอได้หลายคนเหมือนหน้า project</div>
                                                </div>
                                            </div>
                                            @php
                                                $inlineConferences = $openEditOutput
                                                    ? collect(old('conferences', []))->filter(function ($item) {
                                                        return trim((string) ($item['name'] ?? '')) !== ''
                                                            || trim((string) ($item['presentation_type'] ?? '')) !== ''
                                                            || trim((string) ($item['location'] ?? '')) !== ''
                                                            || !empty($item['presenters'])
                                                            || trim((string) ($item['note'] ?? '')) !== '';
                                                    })->values()
                                                    : $confs->values();
                                                if ($inlineConferences->isEmpty() && $openEditOutput && (old('conference_name') || old('conference_presentation_type') || old('conference_location') || old('conference_note'))) {
                                                    $inlineConferences = collect([[
                                                        'name' => old('conference_name'),
                                                        'presentation_type' => old('conference_presentation_type'),
                                                        'location' => old('conference_location'),
                                                        'note' => old('conference_note'),
                                                        'presenters' => old('conference_presenters', []),
                                                    ]]);
                                                }
                                                $inlineConference = $inlineConferences->first() ?? ['name' => '', 'presentation_type' => '', 'location' => '', 'note' => '', 'presenter_person_type' => ''];
                                                $inlineConferencePresenters = collect($inlineConference['presenters'] ?? [])->filter(function ($item) {
                                                    return trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['person_type'] ?? '')) !== '';
                                                })->values();
                                                if ($inlineConferencePresenters->isEmpty() && (($inlineConference['note'] ?? '') || ($inlineConference['presenter_person_type'] ?? ''))) {
                                                    $inlineConferencePresenters = collect([[
                                                        'name' => $inlineConference['note'] ?? '',
                                                        'person_type' => $inlineConference['presenter_person_type'] ?? '',
                                                    ]]);
                                                }
                                                if ($inlineConferencePresenters->isEmpty()) {
                                                    $inlineConferencePresenters = collect([['name' => '', 'person_type' => '']]);
                                                }
                                            @endphp
                                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                                                <label class="block md:col-span-3">
                                                    <span class="text-xs font-semibold text-slate-600">ชื่อเวที/Conference <span class="bo-required">*</span></span>
                                                    <input name="conference_name" value="{{ $inlineConference['name'] ?? '' }}" class="{{ $inlineFieldClass }}" placeholder="เช่น PMAC 2025">
                                                </label>
                                                <label class="block">
                                                    <span class="text-xs font-semibold text-slate-600">ประเภทนำเสนอ <span class="bo-required">*</span></span>
                                                    <select name="conference_presentation_type" class="{{ $inlineFieldClass }}">
                                                        <option value="">เลือก</option>
                                                        @foreach (['Oral Presentation', 'Poster Presentation', 'Abstract Presentation', 'Panel Discussion', 'Keynote Speech', 'World café', 'Moderator'] as $presentationType)
                                                            <option value="{{ $presentationType }}" @selected(($inlineConference['presentation_type'] ?? '') === $presentationType)>{{ $presentationType }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <label class="block md:col-span-2">
                                                    <span class="text-xs font-semibold text-slate-600">สถานที่ <span class="bo-required">*</span></span>
                                                    <input name="conference_location" value="{{ $inlineConference['location'] ?? '' }}" class="{{ $inlineFieldClass }}" placeholder="เมือง/ประเทศ">
                                                </label>
                                            </div>
                                            <div class="mt-5 rounded-2xl border border-amber-200/70 bg-white p-4">
                                                <div class="flex flex-col gap-2">
                                                    <div>
                                                        <div class="text-sm font-semibold text-amber-900">รายชื่อผู้นำเสนอ</div>
                                                        <div class="text-xs text-slate-500">เพิ่มได้หลายคน เหมือนรูปแบบของ project</div>
                                                    </div>
                                                </div>
                                            <div class="mt-4 space-y-3" data-inline-conference-presenter-list>
                                                @foreach ($inlineConferencePresenters as $presenterIndex => $presenter)
                                                    @php
                                                        $presenterName = trim((string) ($presenter['name'] ?? ''));
                                                        $presenterType = $presenter['person_type']
                                                            ?? ($presenterName === '' ? '' : (collect($personList)->contains($presenterName) ? 'internal' : 'external'));
                                                    @endphp
                                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-conference-presenter-row data-conditional-person-row data-show-idle-person-select>
                                                        <label class="block">
                                                            <span class="text-xs font-semibold text-slate-600">ประเภทผู้นำเสนอ <span class="bo-required">*</span></span>
                                                            <select name="conference_presenters[{{ $presenterIndex }}][person_type]" class="{{ $inlineFieldClass }}" data-person-type-select>
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="internal" @selected($presenterType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external" @selected($presenterType === 'external')>คนนอก</option>
                                                            </select>
                                                        </label>
                                                        <div class="{{ $presenterType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">เลือกผู้นำเสนอ <span class="bo-required">*</span></span>
                                                            <select class="{{ $inlineFieldClass }}" data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้นำเสนอ" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้นำเสนอ">
                                                                <option value="">เลือกผู้นำเสนอ</option>
                                                                @foreach ($personList as $person)
                                                                    <option value="{{ $person }}" @selected($presenterType === 'internal' && $presenterName === $person)>{{ $person }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <label class="{{ $presenterType === 'internal' ? 'hidden' : '' }}" data-person-manual-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">ชื่อ - นามสกุล ผู้นำเสนอ <span class="bo-required">*</span></span>
                                                            <input value="{{ $presenterType === 'external' ? $presenterName : '' }}" class="{{ $inlineFieldClass }}" placeholder="{{ $presenterType === '' ? 'เลือกประเภทผู้นำเสนอก่อน' : 'ชื่อ - นามสกุล ผู้นำเสนอ' }}" data-idle-placeholder="เลือกประเภทผู้นำเสนอก่อน" data-external-placeholder="ชื่อ - นามสกุล ผู้นำเสนอ" data-person-manual-input @disabled($presenterType !== 'external')>
                                                        </label>
                                                        <input type="hidden" name="conference_presenters[{{ $presenterIndex }}][name]" value="{{ $presenterName }}" data-person-name-hidden>
                                                        <div class="flex items-end justify-end">
                                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-conference-presenter>
                                                                ลบผู้นำเสนอ
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-5 flex justify-center">
                                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-white px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50" data-add-inline-conference-presenter>
                                                    เพิ่มผู้นำเสนอ
                                                </button>
                                            </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 {{ str_starts_with((string) ($openEditOutput ? old('type') : $output->type), 'EA118') ? '' : 'hidden' }}" data-inline-output-ea118>
                                        <div class="md:col-span-2 rounded-2xl border border-violet-200 bg-white/70 p-4">
                                            <div class="flex flex-col gap-2">
                                                <div>
                                                    <div class="text-sm font-semibold text-violet-900">ผู้ได้รับรางวัล</div>
                                                    <div class="text-xs text-slate-500">เพิ่มได้หลายคนในผลงานเดียว</div>
                                                </div>
                                            </div>
                                            @php
                                                $inlineAwardRecipients = $openEditOutput
                                                    ? collect(old('award_recipients', []))->filter(function ($item) {
                                                        return trim((string) ($item['name'] ?? '')) !== '' || trim((string) ($item['person_type'] ?? '')) !== '';
                                                    })->values()
                                                    : $awardees->map(function ($awardee) {
                                                        return [
                                                            'name' => $awardee['name'] ?? '',
                                                            'person_type' => $awardee['person_type'] ?? '',
                                                        ];
                                                    })->values();
                                                if ($inlineAwardRecipients->isEmpty() && $openEditOutput && (old('award_recipient_name') || old('award_recipient_person_type'))) {
                                                    $inlineAwardRecipients = collect([[
                                                        'name' => old('award_recipient_name'),
                                                        'person_type' => old('award_recipient_person_type'),
                                                    ]]);
                                                }
                                                if ($inlineAwardRecipients->isEmpty()) {
                                                    $inlineAwardRecipients = collect([['name' => '', 'person_type' => '']]);
                                                }
                                            @endphp
                                            <div class="mt-4 space-y-3" data-inline-award-list>
                                                @foreach ($inlineAwardRecipients as $recipientIndex => $recipient)
                                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-award-recipient-row data-conditional-person-row data-show-idle-person-select>
                                                        <label class="block">
                                                            <span class="text-xs font-semibold text-slate-600">คนในหน่วย / คนนอก <span class="bo-required">*</span></span>
                                                            <select name="award_recipients[{{ $recipientIndex }}][person_type]" class="{{ $inlineFieldClass }}" data-person-type-select>
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="unit" @selected(($recipient['person_type'] ?? '') === 'unit')>หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="internal" @selected(($recipient['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external" @selected(($recipient['person_type'] ?? '') === 'external')>คนนอก</option>
                                                            </select>
                                                        </label>
                                                        <div class="{{ ($recipient['person_type'] ?? '') === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล <span class="bo-required">*</span></span>
                                                            <select class="{{ $inlineFieldClass }}" data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้ได้รับรางวัล" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้ได้รับรางวัล">
                                                                <option value="">เลือกผู้ได้รับรางวัล</option>
                                                                @foreach ($personList as $person)
                                                                    <option value="{{ $person }}" @selected(($recipient['person_type'] ?? '') === 'internal' && (($recipient['name'] ?? '') === $person))>{{ $person }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <label class="{{ in_array(($recipient['person_type'] ?? ''), ['external', 'unit'], true) ? '' : 'hidden' }}" data-person-manual-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">ชื่อผู้ได้รับรางวัล <span class="bo-required">*</span></span>
                                                            <input
                                                                value="{{ in_array(($recipient['person_type'] ?? ''), ['external', 'unit'], true) ? (($recipient['name'] ?? '') !== '' ? ($recipient['name'] ?? '') : (($recipient['person_type'] ?? '') === 'unit' ? 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' : '')) : '' }}"
                                                                class="{{ $inlineFieldClass }}"
                                                                placeholder="ชื่อผู้ได้รับรางวัล"
                                                                @disabled(! in_array(($recipient['person_type'] ?? ''), ['external', 'unit'], true))
                                                                @readonly(($recipient['person_type'] ?? '') === 'unit')
                                                                data-idle-placeholder="เลือกประเภทผู้ได้รับรางวัลก่อน"
                                                                data-external-placeholder="ชื่อผู้ได้รับรางวัล"
                                                                data-person-manual-input
                                                            >
                                                        </label>
                                                        <input type="hidden" name="award_recipients[{{ $recipientIndex }}][name]" value="{{ (($recipient['person_type'] ?? '') === 'unit' && empty($recipient['name'])) ? 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ' : ($recipient['name'] ?? '') }}" data-person-name-hidden>
                                                        <div class="flex items-end justify-end">
                                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-award-recipient>
                                                                ลบรายชื่อ
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-6 flex justify-center pt-2">
                                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-white px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-50" data-add-inline-award-recipient>
                                                    เพิ่มผู้ได้รับรางวัล
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-5 {{ str_starts_with((string) ($openEditOutput ? old('type') : $output->type), 'EA119') ? '' : 'hidden' }}" data-inline-output-ea119>
                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">ชื่อหนังสือ <span class="bo-required">*</span></span>
                                                <input name="book_title" value="{{ $openEditOutput ? old('book_title') : $output->book_title }}" class="{{ $inlineFieldClass }}">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">สำนักพิมพ์ <span class="bo-required">*</span></span>
                                                <input name="publisher_name" value="{{ $openEditOutput ? old('publisher_name') : $output->publisher_name }}" class="{{ $inlineFieldClass }}">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">ชื่อบท / เลขหน้า <span class="bo-required">*</span></span>
                                                <input name="chapter_pages" value="{{ $openEditOutput ? old('chapter_pages') : $output->chapter_pages }}" class="{{ $inlineFieldClass }}">
                                            </label>
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-600">เลข ISBN</span>
                                                <input name="isbn" value="{{ $openEditOutput ? old('isbn') : $output->isbn }}" class="{{ $inlineFieldClass }}">
                                            </label>
                                        </div>
                                        <div class="rounded-2xl border border-emerald-200 bg-white/70 p-4">
                                            <div class="flex flex-col gap-2">
                                                <div>
                                                    <div class="text-sm font-semibold text-emerald-900">รายชื่อผู้เขียน</div>
                                                    <div class="text-xs text-slate-500">เพิ่มรายชื่อผู้เขียนทีละคนตามข้อมูลหนังสือหรือบทในหนังสือ</div>
                                                </div>
                                            </div>
                                            @php
                                                $inlineBookAuthors = $openEditOutput
                                                    ? collect(old('book_authors', []))->filter(function ($item) {
                                                        return trim((string) ($item['name'] ?? '')) !== '';
                                                    })->values()
                                                    : $bookAuthors->values();
                                                if ($inlineBookAuthors->isEmpty() && $openEditOutput && old('book_author_name')) {
                                                    $inlineBookAuthors = collect([['name' => old('book_author_name')]]);
                                                }
                                                if ($inlineBookAuthors->isEmpty()) {
                                                    $inlineBookAuthors = collect([['name' => '', 'person_type' => '']]);
                                                }
                                            @endphp
                                            <div class="mt-4 space-y-3" data-inline-book-author-list>
                                                @foreach ($inlineBookAuthors as $bookAuthorIndex => $bookAuthor)
                                                    @php
                                                        $bookAuthorName = trim((string) ($bookAuthor['name'] ?? ''));
                                                        $bookAuthorType = $bookAuthor['person_type']
                                                            ?? ($bookAuthorName === '' ? '' : (collect($personList)->contains($bookAuthorName) ? 'internal' : 'external'));
                                                    @endphp
                                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,220px)_minmax(0,1fr)_minmax(0,1fr)_auto]" data-book-author-row data-conditional-person-row data-show-idle-person-select>
                                                        <label class="block">
                                                            <span class="text-xs font-semibold text-slate-600">ประเภทผู้เขียน <span class="bo-required">*</span></span>
                                                            <select name="book_authors[{{ $bookAuthorIndex }}][person_type]" class="{{ $inlineFieldClass }}" data-person-type-select>
                                                                <option value="">กรุณาเลือก</option>
                                                                <option value="internal" @selected($bookAuthorType === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                                <option value="external" @selected($bookAuthorType === 'external')>คนนอก</option>
                                                            </select>
                                                        </label>
                                                        <div class="{{ $bookAuthorType === 'internal' ? '' : 'hidden' }}" data-person-select-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">เลือกชื่อผู้เขียน <span class="bo-required">*</span></span>
                                                            <select class="{{ $inlineFieldClass }}" data-back-office-person-select data-person-search-select data-placeholder="เลือกผู้เขียน" data-search-placeholder="พิมพ์เพื่อค้นหาชื่อผู้เขียน">
                                                                <option value="">เลือกผู้เขียน</option>
                                                                @foreach ($personList as $person)
                                                                    <option value="{{ $person }}" @selected($bookAuthorType === 'internal' && $bookAuthorName === $person)>{{ $person }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <label class="{{ $bookAuthorType === 'external' ? '' : 'hidden' }}" data-person-manual-wrap>
                                                            <span class="text-xs font-semibold text-slate-600">ชื่อผู้เขียน <span class="bo-required">*</span></span>
                                                            <input value="{{ $bookAuthorType === 'external' ? $bookAuthorName : '' }}" class="{{ $inlineFieldClass }}" placeholder="ชื่อผู้เขียน" data-person-manual-input>
                                                        </label>
                                                        <input type="hidden" name="book_authors[{{ $bookAuthorIndex }}][name]" value="{{ $bookAuthorName }}" data-person-name-hidden>
                                                        <div class="flex items-end justify-end">
                                                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50" data-remove-book-author>
                                                                ลบ
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-6 flex justify-center pt-2">
                                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50" data-add-inline-book-author>
                                                    เพิ่มผู้เขียน
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100" data-back-office-output-edit-close>
                                            ยกเลิก
                                        </button>
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                            บันทึกการแก้ไข
                                        </button>
                                    </div>
                                </form>
                            </section>
                        @endif
                    </div>
                @endforeach
                <div class="hidden rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-400" data-back-office-output-no-results>
                    ไม่พบผลงานที่ตรงกับคำค้นหา
                </div>
            </div>
        </section>
    </section>
</div>
