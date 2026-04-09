@php
    $drawerMode = $drawerMode ?? false;

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
                    <a href="{{ route('back_office.show', $task->id) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        เปิดหน้าเต็ม
                    </a>
                    <a href="{{ route('back_office.edit', $task->id) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-amber-100 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-200">
                        แก้ไข
                    </a>
                    <a href="{{ route('back_office.pdf', $task->id) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        🖨️ PDF
                    </a>
                </div>
            </div>
        </section>
    @endif

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">จำนวนภาระงาน</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($summary['entries']) }}</div>
        </article>
        <article class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ชั่วโมงรวม</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($summary['hours'], 2) }}</div>
        </article>
        <article class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">FTE รวม</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($summary['fte'], 6) }}</div>
        </article>
        <article class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ผู้บันทึก</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $task->creator?->name ?? '-' }}</div>
        </article>
    </section>

    @if ($task->outputs->count())
        <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 text-sm font-semibold text-slate-800">ผลงานของงานนอกโครงการ</div>
            <div class="space-y-3 text-sm">
                @foreach ($task->outputs as $output)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="font-semibold text-slate-800">{{ $output->title }}</div>
                        @if ($output->type)
                            <div class="mt-1 text-slate-500">ประเภท: {{ $output->type }}</div>
                        @endif
                        @if ($output->year)
                            <div class="mt-1 text-slate-600">วันที่เผยแพร่: {{ $output->year }}</div>
                        @endif
                        @if ($output->project_output)
                            <div class="mt-1 text-slate-600">รายละเอียด/ลิงก์: {{ $output->project_output }}</div>
                        @endif

                        @php
                            $isEA101 = str_starts_with((string) ($output->type ?? ''), 'EA101');
                            $isEA103 = str_starts_with((string) ($output->type ?? ''), 'EA103');
                            $isEA119 = str_starts_with((string) ($output->type ?? ''), 'EA119');
                            $authors = collect($output->authors ?? []);
                            $confs = collect($output->conferences ?? []);
                            $bookAuthors = collect($output->book_authors ?? []);
                        @endphp

                        @if ($isEA101)
                            <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 p-3 text-xs">
                                <div class="text-slate-700">Journal: {{ $output->journal ?? '-' }}</div>
                                <div class="text-slate-700">Quartile: {{ $output->quartile ?? '-' }}</div>
                                <div class="text-slate-700">Impact Factor: {{ $output->impact_factor ?? '-' }}</div>
                                @if ($authors->count())
                                    <div class="mt-2 font-semibold text-slate-800">Authors</div>
                                    <ul class="mt-1 list-disc list-inside text-slate-700">
                                        @foreach ($authors as $author)
                                            <li>{{ $author['name'] ?? '-' }} {{ !empty($author['role']) ? '(' . $author['role'] . ')' : '' }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        @if ($isEA103)
                            <div class="mt-3 rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs">
                                <div class="font-semibold text-slate-800">Conferences</div>
                                @if ($confs->count())
                                    <ul class="mt-1 list-disc list-inside text-slate-700">
                                        @foreach ($confs as $conf)
                                            <li>
                                                {{ $conf['name'] ?? '-' }}
                                                @if (!empty($conf['presentation_type'])) • {{ $conf['presentation_type'] }} @endif
                                                @if (!empty($conf['location'])) • {{ $conf['location'] }} @endif
                                                @if (!empty($conf['note'])) • {{ $conf['note'] }} @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="mt-1 text-slate-600">- ไม่มีรายการ Conference -</div>
                                @endif
                            </div>
                        @endif

                        @if ($isEA119)
                            <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-xs">
                                <div class="text-slate-700">ชื่อหนังสือ: {{ $output->book_title ?? '-' }}</div>
                                <div class="text-slate-700">ชื่อสำนักพิมพ์: {{ $output->publisher_name ?? '-' }}</div>
                                <div class="text-slate-700">ชื่อบท / เลขหน้า: {{ $output->chapter_pages ?? '-' }}</div>
                                @if ($bookAuthors->count())
                                    <div class="mt-2 font-semibold text-slate-800">รายชื่อผู้เขียน</div>
                                    <ul class="mt-1 list-disc list-inside text-slate-700">
                                        @foreach ($bookAuthors as $author)
                                            <li>{{ $author['name'] ?? '-' }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-4">
        @forelse ($task->entries as $entry)
            <article class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="text-sm text-slate-500">ผู้รับผิดชอบ</div>
                        <div class="text-lg font-semibold text-slate-900">{{ $entry->person_name }}</div>
                        <div class="text-sm text-slate-500">ประเภท: {{ $entry->category }}</div>
                        @if ($entry->job_name)
                            <div class="text-sm text-slate-500">ชื่อภาระงาน: {{ $entry->job_name }}</div>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
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

                <div class="mt-4 grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                    <div>
                        @if ($entry->job_name)
                            <div class="mb-3">
                                <div class="text-xs font-semibold text-slate-500">ชื่อภาระงาน</div>
                                <div class="mt-1 whitespace-pre-wrap text-slate-800">{{ $entry->job_name }}</div>
                            </div>
                        @endif
                        <div class="text-xs font-semibold text-slate-500">รายละเอียดงาน</div>
                        <div class="mt-1 whitespace-pre-wrap text-slate-800">{{ $entry->job_detail }}</div>
                    </div>
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="text-xs font-semibold text-slate-500">วันที่เริ่ม</div>
                                <div class="mt-1 text-slate-800">{{ $formatDateBE($entry->start_date) }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-500">วันที่สิ้นสุด</div>
                                <div class="mt-1 text-slate-800">{{ $formatDateBE($entry->end_date) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </article>
        @empty
            <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-400">
                ไม่มีข้อมูลภาระงาน
            </div>
        @endforelse
    </section>
</div>
