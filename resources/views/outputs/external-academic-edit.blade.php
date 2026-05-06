@extends('layouts.app')

@php
    $conferencePresenters = collect($externalOutput->conferences->first()?->presenters ?? [])->map(function ($presenter) {
        return [
            'id' => $presenter->id,
            'person_type' => $presenter->user_id ? 'internal' : 'external',
            'note' => $presenter->name,
            'user_id' => $presenter->user_id,
        ];
    })->values()->all();

    $authorSource = $externalOutput->type === 'EA118'
        ? collect($externalOutput->awardRecipients ?? [])
        : ($externalOutput->type === 'EA119'
            ? collect($externalOutput->bookAuthors ?? [])
            : collect($externalOutput->authors ?? []));

    $authorRows = $authorSource->map(function ($author) use ($externalOutput) {
        $supportsUnit = $externalOutput->type === 'EA118';
        $personType = $author->user_id
            ? 'internal'
            : (($supportsUnit && trim((string) $author->name) === 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ') ? 'unit' : 'external');
        $role = $externalOutput->type === 'EA118'
            ? 'awardee'
            : ($author->role ?? '');

        return [
            'role' => $role,
            'person_type' => $personType,
            'name' => $author->name,
            'user_id' => $author->user_id,
        ];
    })->values()->all();

    $formData = [
        'title' => old('title', $externalOutput->title),
        'type' => old('type', $externalOutput->type),
        'job_category' => old('job_category', $externalOutput->job_category),
        'note' => old('note', $externalOutput->note),
        'publish_date' => old('publish_date', optional($externalOutput->publish_date)->format('d-m-Y') ? optional($externalOutput->publish_date)->format('d-m-') . (optional($externalOutput->publish_date)->year + 543) : ''),
        'journal' => old('journal', $externalOutput->journal),
        'quartile' => old('quartile', $externalOutput->quartile),
        'impact_factor' => old('impact_factor', $externalOutput->impact_factor),
        'book_title' => old('book_title', $externalOutput->book_title),
        'publisher_name' => old('publisher_name', $externalOutput->publisher_name),
        'chapter_pages' => old('chapter_pages', $externalOutput->chapter_pages),
        'isbn' => old('isbn', $externalOutput->isbn),
        'conference_id' => old('conference_id', $externalOutput->conferences->first()?->id),
        'conference_name' => old('conference_name', $externalOutput->conferences->first()?->name),
        'conference_presentation_type' => old('conference_presentation_type', $externalOutput->conferences->first()?->presentation_type),
        'conference_location' => old('conference_location', $externalOutput->conferences->first()?->location),
        'conference_presenters' => old('conference_presenters', $conferencePresenters ?: [[
            'id' => '',
            'person_type' => '',
            'note' => '',
            'user_id' => '',
        ]]),
        'authors' => old('authors', $authorRows ?: [['role' => '', 'person_type' => '', 'name' => '', 'user_id' => '']]),
    ];
    $type = in_array(($formData['type'] ?? ''), ['EA101', 'EA103', 'EA118', 'EA119', 'EA120'], true)
        ? $formData['type']
        : 'EA101';
@endphp

@section('content')
    <script>
        window.outputUserDirectory = @json($userDirectory);
    </script>
    <datalist id="externalAcademicAuthorInternalNames">
        @foreach ($userDirectory as $userOption)
            <option value="{{ $userOption['research_display_name'] }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicAuthorManualNames">
        @foreach ($authorOptions as $authorName)
            <option value="{{ $authorName }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicPresenterInternalNames">
        @foreach ($userDirectory as $userOption)
            <option value="{{ $userOption['full_name'] }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicPresenterManualNames">
        @foreach ($presenterOptions as $presenterName)
            <option value="{{ $presenterName }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicBookAuthorInternalNames">
        @foreach ($userDirectory as $userOption)
            <option value="{{ $userOption['full_name'] }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicBookAuthorManualNames">
        @foreach ($bookAuthorOptions as $authorName)
            <option value="{{ $authorName }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicAwardRecipientInternalNames">
        @foreach ($userDirectory as $userOption)
            <option value="{{ $userOption['full_name'] }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicAwardRecipientManualNames">
        @foreach ($presenterOptions as $recipientName)
            <option value="{{ $recipientName }}"></option>
        @endforeach
    </datalist>

    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">แก้ไข Academic Paper / Presentation / Award / Textbook / Manual นอกเนื่องโครงการวิจัยในหน่วย</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        ปรับข้อมูลผลงานเพิ่มเติม และแก้รายละเอียด EA101, EA103, EA118, EA119 หรือ EA120 ได้ในหน้าจอเดียว
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.external-academic.show', $externalOutput) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        ดูรายละเอียด
                    </a>
                    <a href="{{ route('outputs.external-academic') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        กลับไปรายการ
                    </a>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700 shadow-sm">
                <div class="font-semibold">ยังบันทึกไม่ได้</div>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('outputs.external-academic.update', $externalOutput) }}" class="space-y-5" novalidate>
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-blue-700">แก้ไขผลงาน</h2>
                        <p class="mt-1 text-sm text-slate-500">ปรับข้อมูลหลักและรายละเอียดการเผยแพร่ในรายการนี้</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ $type === 'EA101' ? 'Academic Paper' : ($type === 'EA103' ? 'Presentation' : ($type === 'EA118' ? 'Award and Recognition' : ($type === 'EA119' ? 'Textbook / Book chapter' : 'Manual / Instruction / Guideline'))) }}
                    </span>
                </div>

                <div class="space-y-4">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">ชื่อผลงาน <span class="text-rose-500">*</span></span>
                        <input type="text" name="title" value="{{ $formData['title'] }}"
                            required
                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')"
                            oninput="this.setCustomValidity('')"
                            @if ($errors->has('title')) data-server-invalid="1" @endif
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has('title') ? ' border-red-500 bg-red-50 ' : '' }}"
                            placeholder="ระบุชื่อผลงาน">
                    </label>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">เลือกประเภทของผลงาน <span class="text-rose-500">*</span></span>
                            <select name="type" required oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผลงาน')" oninput="this.setCustomValidity('')" @if ($errors->has('type')) data-server-invalid="1" @endif class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has('type') ? ' border-red-500 bg-red-50 ' : '' }}" data-edit-output-type>
                                <option value="EA101" @selected($type === 'EA101')>EA101 Research Paper or Journal Article</option>
                                <option value="EA103" @selected($type === 'EA103')>EA103 Presentations in Academic Conferences</option>
                                <option value="EA118" @selected($type === 'EA118')>EA118 Award and Recognition</option>
                                <option value="EA119" @selected($type === 'EA119')>EA119 Textbook / Book chapter</option>
                                <option value="EA120" @selected($type === 'EA120')>EA120 Manual / Instruction / Guideline</option>
                            </select>
                        </label>

                        <label class="block">
                        <span class="text-sm font-semibold text-slate-700">ไม่แน่ใจ <span class="text-rose-500">*</span></span>
                        <select name="job_category" required oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของงาน')" oninput="this.setCustomValidity('')" @if ($errors->has('job_category')) data-server-invalid="1" @endif class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has('job_category') ? ' border-red-500 bg-red-50 ' : '' }}">
                                <option value="">เลือกประเภทของงาน</option>
                                @foreach ($jobCategoryOptions as $jobCategory)
                                    <option value="{{ $jobCategory }}" @selected(($formData['job_category'] ?? '') === $jobCategory)>{{ $jobCategory }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block xl:col-span-2">
                            <span class="text-sm font-semibold text-slate-700">รายละเอียด / หมายเหตุ / LINK</span>
                            <input type="text" name="note" value="{{ $formData['note'] }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="รายละเอียด / หมายเหตุ / LINK">
                        </label>

                        <label class="block md:max-w-sm">
                            <span class="text-sm font-semibold text-slate-700">วันที่ publish <span class="text-rose-500">*</span></span>
                            <div class="relative">
                                <input type="text" readonly id="external-edit-publish-date" name="publish_date" value="{{ $formData['publish_date'] }}"
                                    required
                                    oninvalid="this.setCustomValidity('กรุณาเลือกวันที่ publish')"
                                    oninput="this.setCustomValidity('')"
                                    @if ($errors->has('publish_date')) data-server-invalid="1" @endif
                                    class="js-date-be mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-11 text-sm cursor-pointer{{ $errors->has('publish_date') ? ' border-red-500 bg-red-50 ' : '' }}"
                                    placeholder="เลือกวันที่ publish">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" data-open-picker data-target="external-edit-publish-date">📅</button>
                            </div>
                        </label>
                    </div>
                </div>

                <div data-edit-ea101-section @class(['mt-5 rounded-2xl border border-sky-200 bg-sky-50/50 p-4 md:p-5', 'hidden' => $type !== 'EA101'])>
                    <div class="mb-4 text-lg font-semibold text-slate-900">Academic Paper</div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Journal <span class="text-rose-500">*</span></span>
                            <input type="text" name="journal" value="{{ $formData['journal'] }}"
                                data-required-ea101="true"
                                oninvalid="this.setCustomValidity('กรุณากรอก Journal')"
                                oninput="this.setCustomValidity('')"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="เช่น IEEE Access">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Ranking</span>
                            <select name="quartile" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                <option value="">เลือก Ranking</option>
                                @foreach (['Q1', 'Q2', 'Q3', 'Q4', 'TCI 1', 'TCI 2'] as $quartile)
                                    <option value="{{ $quartile }}" @selected(($formData['quartile'] ?? '') === $quartile)>{{ $quartile }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Impact Factor</span>
                            <input type="number" step="0.001" name="impact_factor" value="{{ $formData['impact_factor'] }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="เช่น 3.215">
                        </label>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-blue-700">รายชื่อผู้เขียน (Authors)</div>
                        <div class="space-y-3" id="editAuthorsList">
                            @foreach ($formData['authors'] as $authorIndex => $author)
                                <div class="edit-author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">บทบาทผู้เขียน (Role)</span>
                                        <select name="authors[{{ $authorIndex }}][role]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <option value="">เลือกบทบาท</option>
                                            <option value="first" @selected(($author['role'] ?? '') === 'first')>First-author</option>
                                            <option value="co" @selected(($author['role'] ?? '') === 'co')>Co-author</option>
                                            <option value="corresponding" @selected(($author['role'] ?? '') === 'corresponding')>Corresponding author</option>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                                        <select name="authors[{{ $authorIndex }}][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="author" data-name-mode="research">
                                            <option value="" @selected(($author['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                            <option value="internal" @selected(($author['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                            <option value="external" @selected(($author['person_type'] ?? '') === 'external')>คนนอก</option>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                                        <input type="hidden" name="authors[{{ $authorIndex }}][user_id]" value="{{ $author['user_id'] ?? '' }}" data-user-id-hidden>
                                        <input type="text" list="externalAcademicAuthorManualNames" name="authors[{{ $authorIndex }}][name]" value="{{ $author['name'] ?? '' }}"
                                            data-name-input
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)">
                                    </label>
                                    <div class="flex items-end">
                                        <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-author>ลบผู้เขียน</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="addEditAuthor" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">+ เพิ่มผู้เขียน</button>
                        </div>
                    </div>
                </div>

                <div data-edit-ea103-section @class(['mt-5 rounded-2xl border border-amber-200 bg-amber-50/50 p-4 md:p-5', 'hidden' => $type !== 'EA103'])>
                    <div class="mb-4 text-lg font-semibold text-slate-900">Presentation</div>
                    <input type="hidden" name="conference_id" value="{{ $formData['conference_id'] ?? '' }}">
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">ชื่อเวที/Conference <span class="text-rose-500">*</span></span>
                            <input type="text" name="conference_name" value="{{ $formData['conference_name'] ?? '' }}" data-required-ea103="true" oninvalid="this.setCustomValidity('กรุณากรอกชื่อเวที/Conference')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น PMAC 2025">
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">ประเภทนำเสนอ</span>
                            <select name="conference_presentation_type" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                <option value="">เลือก</option>
                                @foreach (['Oral Presentation', 'Poster Presentation', 'Abstract Presentation', 'Panel Discussion', 'Keynote Speech', 'World café', 'Moderator'] as $presentationType)
                                    <option value="{{ $presentationType }}" @selected(($formData['conference_presentation_type'] ?? '') === $presentationType)>{{ $presentationType }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">สถานที่</span>
                            <input type="text" name="conference_location" value="{{ $formData['conference_location'] ?? '' }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เมือง/ประเทศ">
                        </label>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-amber-700">รายชื่อผู้นำเสนอ</div>
                        <div class="space-y-3" id="editConferencePresentersList">
                            @foreach ($formData['conference_presenters'] as $presenterIndex => $presenter)
                                <div class="edit-conference-presenter-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                    <input type="hidden" name="conference_presenters[{{ $presenterIndex }}][id]" value="{{ $presenter['id'] ?? '' }}">
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                                        <select name="conference_presenters[{{ $presenterIndex }}][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="presenter" data-name-mode="full">
                                            <option value="" @selected(($presenter['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                            <option value="internal" @selected(($presenter['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                            <option value="external" @selected(($presenter['person_type'] ?? '') === 'external')>คนนอก</option>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">ชื่อผู้นำเสนอ</span>
                                        <input type="hidden" name="conference_presenters[{{ $presenterIndex }}][user_id]" value="{{ $presenter['user_id'] ?? '' }}" data-user-id-hidden>
                                        <input type="text" list="externalAcademicPresenterManualNames" name="conference_presenters[{{ $presenterIndex }}][note]" value="{{ $presenter['note'] ?? '' }}" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อผู้นำเสนอ">
                                    </label>
                                    <div class="flex items-end">
                                        <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-conference-presenter>ลบผู้นำเสนอ</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="addEditConferencePresenter" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">+ เพิ่มผู้นำเสนอ</button>
                        </div>
                    </div>
                </div>

                <div data-edit-ea118-section @class(['mt-5 rounded-2xl border border-violet-200 bg-violet-50/50 p-4 md:p-5', 'hidden' => $type !== 'EA118'])>
                    <div class="mb-4 text-lg font-semibold text-slate-900">Award and Recognition</div>
                    <div class="rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-violet-700">ผู้ได้รับรางวัล (Award Recipients)</div>
                        <div class="space-y-3" id="editAwardRecipientsList">
                            @foreach ($formData['authors'] as $authorIndex => $author)
                                <div class="edit-award-recipient-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                    <input type="hidden" name="authors[{{ $authorIndex }}][role]" value="awardee">
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก / หน่วยงาน</span>
                                        <select name="authors[{{ $authorIndex }}][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="award" data-name-mode="full">
                                            <option value="" @selected(($author['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                            <option value="unit" @selected(($author['person_type'] ?? '') === 'unit')>หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                            <option value="internal" @selected(($author['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                            <option value="external" @selected(($author['person_type'] ?? '') === 'external')>คนนอก</option>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">ชื่อผู้ได้รับรางวัล</span>
                                        <input type="hidden" name="authors[{{ $authorIndex }}][user_id]" value="{{ $author['user_id'] ?? '' }}" data-user-id-hidden>
                                        <input type="text" list="externalAcademicAwardRecipientManualNames" name="authors[{{ $authorIndex }}][name]" value="{{ $author['name'] ?? '' }}"
                                            data-name-input
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เลือกหรือพิมพ์ชื่อผู้ได้รับรางวัล">
                                    </label>
                                    <div class="flex items-end">
                                        <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-award-recipient>ลบรายชื่อ</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="addEditAwardRecipient" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">+ เพิ่มผู้ได้รับรางวัล</button>
                        </div>
                    </div>
                </div>

                <div data-edit-ea119-section @class(['mt-5 rounded-2xl border border-indigo-200 bg-indigo-50/50 p-4 md:p-5', 'hidden' => $type !== 'EA119'])>
                    <div class="mb-4 text-lg font-semibold text-slate-900">Textbook / Book chapter</div>
                    <div class="mb-4 grid gap-4 md:grid-cols-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">ชื่อหนังสือ <span class="text-rose-500">*</span></span>
                            <input type="text" name="book_title" value="{{ $formData['book_title'] }}"
                                data-required-ea119="true"
                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อหนังสือ')"
                                oninput="this.setCustomValidity('')"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="เช่น Textbook of Public Health">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">ชื่อสำนักพิมพ์ <span class="text-rose-500">*</span></span>
                            <input type="text" name="publisher_name" value="{{ $formData['publisher_name'] }}"
                                data-required-ea119="true"
                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อสำนักพิมพ์')"
                                oninput="this.setCustomValidity('')"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="เช่น Elsevier">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">ชื่อบท / เลขหน้า <span class="text-rose-500">*</span></span>
                            <input type="text" name="chapter_pages" value="{{ $formData['chapter_pages'] }}"
                                data-required-ea119="true"
                                oninvalid="this.setCustomValidity('กรุณากรอกชื่อบท / เลขหน้า')"
                                oninput="this.setCustomValidity('')"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="เช่น Chapter 2, pp. 45-66">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">เลข ISBN</span>
                            <input type="text" name="isbn" value="{{ $formData['isbn'] }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                placeholder="เช่น 978-3-16-148410-0">
                        </label>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-blue-700">รายชื่อผู้เขียน (Authors)</div>
                        <div class="space-y-3" id="editBookAuthorsList">
                            @foreach ($formData['authors'] as $authorIndex => $author)
                                <div class="edit-book-author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                                        <select name="authors[{{ $authorIndex }}][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="book" data-name-mode="full">
                                            <option value="" @selected(($author['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                            <option value="internal" @selected(($author['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                            <option value="external" @selected(($author['person_type'] ?? '') === 'external')>คนนอก</option>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">ชื่อผู้เขียน</span>
                                        <input type="hidden" name="authors[{{ $authorIndex }}][user_id]" value="{{ $author['user_id'] ?? '' }}" data-user-id-hidden>
                                        <input type="text" list="externalAcademicBookAuthorManualNames" name="authors[{{ $authorIndex }}][name]" value="{{ $author['name'] ?? '' }}"
                                            data-name-input
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            onfocus="this.dispatchEvent(new Event('input', { bubbles: true }))"
                                            placeholder="เลือกหรือพิมพ์ชื่อผู้เขียน">
                                    </label>
                                    <div class="flex items-end">
                                        <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-book-author>ลบผู้เขียน</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="addEditBookAuthor" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">+ เพิ่มผู้เขียน</button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500">ตรวจสอบประเภทและวันที่ publish ก่อนบันทึก เพื่อให้รายการไปแสดงผลถูกหมวด</div>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>

    <template id="editAuthorTemplate">
        <div class="edit-author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">บทบาทผู้เขียน (Role)</span>
                <select name="authors[__INDEX__][role]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                    <option value="">เลือกบทบาท</option>
                    <option value="first">First-author</option>
                    <option value="co">Co-author</option>
                    <option value="corresponding">Corresponding author</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                <select name="authors[__INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="author" data-name-mode="research">
                    <option value="">กรุณาเลือก</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                <input type="hidden" name="authors[__INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicAuthorManualNames" name="authors[__INDEX__][name]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-author>ลบผู้เขียน</button>
            </div>
        </div>
    </template>

    <template id="editConferencePresenterTemplate">
        <div class="edit-conference-presenter-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <input type="hidden" name="conference_presenters[__INDEX__][id]" value="">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                <select name="conference_presenters[__INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="presenter" data-name-mode="full">
                    <option value="">กรุณาเลือก</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อผู้นำเสนอ</span>
                <input type="hidden" name="conference_presenters[__INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicPresenterManualNames" name="conference_presenters[__INDEX__][note]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อผู้นำเสนอ">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-conference-presenter>ลบผู้นำเสนอ</button>
            </div>
        </div>
    </template>

    <template id="editBookAuthorTemplate">
        <div class="edit-book-author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                <select name="authors[__INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="book" data-name-mode="full">
                    <option value="">กรุณาเลือก</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อผู้เขียน</span>
                <input type="hidden" name="authors[__INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicBookAuthorManualNames" name="authors[__INDEX__][name]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" onfocus="this.dispatchEvent(new Event('input', { bubbles: true }))" placeholder="เลือกหรือพิมพ์ชื่อผู้เขียน">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-book-author>ลบผู้เขียน</button>
            </div>
        </div>
    </template>

    <template id="editAwardRecipientTemplate">
        <div class="edit-award-recipient-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <input type="hidden" name="authors[__INDEX__][role]" value="awardee">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก / หน่วยงาน</span>
                <select name="authors[__INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="award" data-name-mode="full">
                    <option value="">กรุณาเลือก</option>
                    <option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อผู้ได้รับรางวัล</span>
                <input type="hidden" name="authors[__INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicAwardRecipientManualNames" name="authors[__INDEX__][name]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เลือกหรือพิมพ์ชื่อผู้ได้รับรางวัล">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-edit-award-recipient>ลบรายชื่อ</button>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('outputs.external-academic.update', $externalOutput) }}"]');
            const typeSelect = document.querySelector('[data-edit-output-type]');
            const ea101Section = document.querySelector('[data-edit-ea101-section]');
            const ea103Section = document.querySelector('[data-edit-ea103-section]');
            const ea118Section = document.querySelector('[data-edit-ea118-section]');
            const ea119Section = document.querySelector('[data-edit-ea119-section]');
            const authorsList = document.getElementById('editAuthorsList');
            const conferencePresentersList = document.getElementById('editConferencePresentersList');
            const awardRecipientsList = document.getElementById('editAwardRecipientsList');
            const bookAuthorsList = document.getElementById('editBookAuthorsList');
            const userDirectory = Array.isArray(window.outputUserDirectory) ? window.outputUserDirectory : [];
            const getDisplayField = (field) => {
                if (typeof window.getProjectValidationDisplayTarget === 'function') {
                    return window.getProjectValidationDisplayTarget(field);
                }
                return field;
            };
            const markInvalid = (field) => {
                const target = getDisplayField(field);
                target.dataset.externalInvalid = '1';
                target.style.setProperty('border-color', '#ef4444', 'important');
                target.style.setProperty('background-color', '#fef2f2', 'important');
                target.style.setProperty('box-shadow', '0 0 0 3px rgba(254, 202, 202, 0.8)', 'important');
                target.style.setProperty('outline', 'none', 'important');
            };
            const clearInvalid = (field) => {
                const target = getDisplayField(field);
                delete target.dataset.externalInvalid;
                target.style.removeProperty('border-color');
                target.style.removeProperty('background-color');
                target.style.removeProperty('box-shadow');
                target.style.removeProperty('outline');
            };
            const isVisibleField = (field) => !!field.offsetParent;

            const findMatchedUserByValue = (value, mode) => {
                const trimmed = (value || '').trim();
                if (trimmed === '') {
                    return null;
                }

                return userDirectory.find((user) => {
                    if (mode === 'research') {
                        return (user.research_display_name || '').trim() === trimmed;
                    }

                    return (user.full_name || '').trim() === trimmed;
                }) || null;
            };

            const findMatchedUser = (input) => {
                const value = (input?.value || '').trim();
                const mode = input?.dataset?.userLookup || 'full';

                if (value === '') {
                    return null;
                }

                return findMatchedUserByValue(value, mode);
            };

            const getExternalUserOptionLabel = (user, mode) => mode === 'research'
                ? String(user?.research_display_name || '').trim()
                : String(user?.full_name || '').trim();

            const ensureExternalUserSearchSelect = (row, nameInput, nameMode) => {
                if (!(row instanceof HTMLElement) || !(nameInput instanceof HTMLInputElement)) {
                    return null;
                }

                let select = row.querySelector('[data-external-user-search-select]');
                if (!(select instanceof HTMLSelectElement)) {
                    select = document.createElement('select');
                    select.className = 'mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm hidden';
                    select.dataset.externalUserSearchSelect = '1';
                    nameInput.insertAdjacentElement('afterend', select);
                }

                const matchedUser = findMatchedUserByValue(nameInput.value, nameMode);
                select.innerHTML = '<option value="">เลือกจากรายชื่อบุคลากร</option>';

                userDirectory.forEach((user) => {
                    const label = getExternalUserOptionLabel(user, nameMode);
                    const id = String(user?.id || '').trim();
                    if (label === '' || id === '') {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = label;
                    option.selected = !!(matchedUser && String(matchedUser.id) === id);
                    select.appendChild(option);
                });

                const dropdown = typeof window.ensureProjectSearchDropdown === 'function'
                    ? window.ensureProjectSearchDropdown(select, {
                        placeholder: 'เลือกจากรายชื่อบุคลากร',
                        searchPlaceholder: 'พิมพ์เพื่อค้นหาชื่อบุคลากร',
                    })
                    : null;

                if (select.dataset.externalUserSearchBound !== '1') {
                    select.dataset.externalUserSearchBound = '1';
                    select.addEventListener('change', () => {
                        const selectedUser = userDirectory.find((user) => String(user?.id || '') === String(select.value || '')) || null;
                        nameInput.value = selectedUser ? getExternalUserOptionLabel(selectedUser, nameMode) : '';
                        const hidden = row.querySelector('[data-user-id-hidden]');
                        if (hidden instanceof HTMLInputElement) {
                            hidden.value = selectedUser ? String(selectedUser.id) : '';
                        }
                    });
                }

                dropdown?.refreshOptions?.();
                return select;
            };

            const syncUserLookupInput = (input) => {
                const hidden = input?.closest('label, .edit-author-row, .edit-conference-presenter-row, .edit-award-recipient-row')?.querySelector('[data-user-id-hidden]');
                if (!hidden) {
                    return;
                }

                const matchedUser = findMatchedUser(input);
                hidden.value = matchedUser ? String(matchedUser.id) : '';
            };

            const bindUserLookup = (scope = document) => {
                scope.querySelectorAll('input[data-user-lookup]').forEach((input) => {
                    if (input.dataset.userLookupBound === '1') {
                        syncUserLookupInput(input);
                        return;
                    }

                    input.dataset.userLookupBound = '1';
                    ['input', 'change', 'blur'].forEach((eventName) => {
                        input.addEventListener(eventName, () => syncUserLookupInput(input));
                    });
                    syncUserLookupInput(input);
                });
            };

            const syncExternalNameRow = (row) => {
                if (!(row instanceof HTMLElement)) return;

                const typeSelect = row.querySelector('[data-person-type]');
                const nameInput = row.querySelector('[data-name-input]');
                const userIdInput = row.querySelector('[data-user-id-hidden]');
                if (!(typeSelect instanceof HTMLSelectElement) || !(nameInput instanceof HTMLInputElement) || !(userIdInput instanceof HTMLInputElement)) {
                    return;
                }

                const mode = String(typeSelect.value || '').trim();
                const nameMode = String(typeSelect.dataset.nameMode || 'full').trim();
                const kind = String(typeSelect.dataset.nameKind || '').trim();
                const unitName = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                const userSearchSelect = ensureExternalUserSearchSelect(row, nameInput, nameMode);
                const toggleUserSearch = (show) => {
                    if (!(userSearchSelect instanceof HTMLSelectElement)) {
                        return;
                    }

                    userSearchSelect.classList.toggle('hidden', !show);
                    userSearchSelect.disabled = !show;
                    userSearchSelect.tomselect?.wrapper?.classList.toggle('hidden', !show);
                    if (show) {
                        userSearchSelect.tomselect?.enable?.();
                        userSearchSelect.tomselect?.refreshOptions?.();
                    } else {
                        userSearchSelect.tomselect?.disable?.();
                    }
                };
                const setManualInputState = (enabled, placeholder) => {
                    nameInput.disabled = !enabled;
                    nameInput.readOnly = !enabled;
                    nameInput.placeholder = placeholder;
                    nameInput.classList.toggle('bg-slate-100', !enabled);
                    nameInput.classList.toggle('text-slate-500', !enabled);
                    nameInput.classList.toggle('cursor-not-allowed', !enabled);
                };
                const disabledPlaceholder = kind === 'author'
                    ? 'เลือกคนในหน่วย / คนนอกก่อน'
                    : kind === 'presenter'
                        ? 'เลือกคนในหน่วย / คนนอกก่อน'
                        : kind === 'award'
                            ? 'เลือกคนในหน่วย / คนนอก / หน่วยงานก่อน'
                            : 'เลือกคนในหน่วย / คนนอกก่อน';

                if (mode === '') {
                    userIdInput.value = '';
                    nameInput.value = '';
                    nameInput.dataset.userLookup = '';
                    nameInput.setAttribute('list', '');
                    nameInput.classList.remove('hidden');
                    toggleUserSearch(false);
                    setManualInputState(false, disabledPlaceholder);
                    return;
                }

                if (mode === 'unit') {
                    userIdInput.value = '';
                    nameInput.value = unitName;
                    nameInput.dataset.userLookup = '';
                    nameInput.setAttribute('list', '');
                    nameInput.classList.remove('hidden');
                    toggleUserSearch(false);
                    setManualInputState(false, kind === 'author' ? 'ชื่อหน่วยงานผู้เขียน' : 'ชื่อหน่วยงาน');
                    return;
                }

                nameInput.classList.remove('hidden');

                if (mode === 'internal') {
                    nameInput.dataset.userLookup = nameMode;
                    nameInput.setAttribute('list', '');
                    nameInput.classList.add('hidden');
                    toggleUserSearch(true);
                    setManualInputState(false,
                        kind === 'author'
                            ? 'เลือกชื่อผู้เขียนจากบุคลากร'
                            : kind === 'presenter'
                                ? 'เลือกชื่อผู้นำเสนอจากบุคลากร'
                                : kind === 'award'
                                    ? 'เลือกชื่อผู้ได้รับรางวัลจากบุคลากร'
                                    : 'เลือกชื่อผู้เขียนจากบุคลากร');
                    const matchedUser = findMatchedUserByValue(nameInput.value, nameMode);
                    userIdInput.value = matchedUser ? String(matchedUser.id) : '';
                    if (userSearchSelect instanceof HTMLSelectElement) {
                        const selectedValue = matchedUser ? String(matchedUser.id) : '';
                        userSearchSelect.value = selectedValue;
                        userSearchSelect.tomselect?.setValue?.(selectedValue, true);
                    }
                    return;
                }

                nameInput.dataset.userLookup = '';
                toggleUserSearch(false);
                nameInput.setAttribute('list',
                    kind === 'author'
                        ? 'externalAcademicAuthorManualNames'
                        : kind === 'presenter'
                            ? 'externalAcademicPresenterManualNames'
                            : kind === 'award'
                                ? 'externalAcademicAwardRecipientManualNames'
                                : 'externalAcademicBookAuthorManualNames');
                setManualInputState(true,
                    kind === 'author'
                        ? 'ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)'
                        : kind === 'presenter'
                            ? 'ชื่อผู้นำเสนอ'
                            : kind === 'award'
                                ? 'ชื่อผู้ได้รับรางวัล'
                                : 'ชื่อผู้เขียน');
                userIdInput.value = '';
            };

            const bindExternalNameRows = (scope = document) => {
                scope.querySelectorAll('.edit-author-row, .edit-conference-presenter-row, .edit-award-recipient-row, .edit-book-author-row').forEach((row) => {
                    const typeSelect = row.querySelector('[data-person-type]');
                    const nameInput = row.querySelector('[data-name-input]');
                    if (!(typeSelect instanceof HTMLSelectElement) || !(nameInput instanceof HTMLInputElement)) {
                        return;
                    }

                    if (typeSelect.dataset.boundExternalType !== '1') {
                        typeSelect.dataset.boundExternalType = '1';
                        typeSelect.addEventListener('change', () => syncExternalNameRow(row));
                    }

                    if (nameInput.dataset.boundExternalName !== '1') {
                        nameInput.dataset.boundExternalName = '1';
                        ['input', 'change', 'blur'].forEach((eventName) => {
                            nameInput.addEventListener(eventName, () => syncExternalNameRow(row));
                        });
                    }

                    syncExternalNameRow(row);
                });
            };

            const validateForm = () => {
                let firstInvalid = null;
                form.querySelectorAll('[required]').forEach((field) => {
                    if (!isVisibleField(field)) {
                        clearInvalid(field);
                        return;
                    }
                    const value = (field.value || '').trim();
                    if (!value) {
                        markInvalid(field);
                        firstInvalid = firstInvalid || field;
                    } else {
                        clearInvalid(field);
                    }
                });
                return firstInvalid;
            };

            const syncType = () => {
                const currentType = typeSelect?.value || '';
                const isPaper = currentType === 'EA101';
                const isPresentation = currentType === 'EA103';
                const isAward = currentType === 'EA118';
                const isTextbook = currentType === 'EA119';
                document.querySelectorAll('[data-required-ea101="true"]').forEach((field) => {
                    field.required = isPaper;
                });
                document.querySelectorAll('[data-required-ea103="true"]').forEach((field) => {
                    field.required = isPresentation;
                });
                document.querySelectorAll('[data-required-ea119="true"]').forEach((field) => {
                    field.required = isTextbook;
                });

                const toggleSection = (section, show) => {
                    if (!section) return;
                    section.classList.toggle('hidden', !show);
                    section.querySelectorAll('input, select, textarea, button').forEach((field) => {
                        field.disabled = !show;
                    });
                };

                toggleSection(ea101Section, isPaper);
                toggleSection(ea103Section, isPresentation);
                toggleSection(ea118Section, isAward);
                toggleSection(ea119Section, isTextbook);
            };

            const validateEA119DuplicateAuthors = () => {
                if ((typeSelect?.value || '') !== 'EA119') return null;
                const seen = new Set();
                let invalidField = null;
                bookAuthorsList?.querySelectorAll('input[name^="authors["][name$="[name]"]').forEach((input) => {
                    if (invalidField) return;
                    const value = (input.value || '').trim().toLowerCase();
                    if (!value) return;
                    if (seen.has(value)) {
                        markInvalid(input);
                        invalidField = input;
                        return;
                    }
                    seen.add(value);
                });
                return invalidField;
            };

            const reindexRows = () => {
                authorsList?.querySelectorAll('.edit-author-row').forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/authors\[\d+]/, `authors[${index}]`);
                    });
                });
                conferencePresentersList?.querySelectorAll('.edit-conference-presenter-row').forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/conference_presenters\[\d+]/, `conference_presenters[${index}]`);
                    });
                });
                bookAuthorsList?.querySelectorAll('.edit-book-author-row').forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/authors\[\d+]/, `authors[${index}]`);
                    });
                });
                awardRecipientsList?.querySelectorAll('.edit-award-recipient-row').forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/authors\[\d+]/, `authors[${index}]`);
                    });
                });
            };

            document.getElementById('addEditAuthor')?.addEventListener('click', () => {
                const index = authorsList.querySelectorAll('.edit-author-row').length;
                authorsList.insertAdjacentHTML('beforeend', document.getElementById('editAuthorTemplate').innerHTML.replaceAll('__INDEX__', index));
                bindUserLookup(authorsList);
                bindExternalNameRows(authorsList);
                reindexRows();
            });

            document.getElementById('addEditConferencePresenter')?.addEventListener('click', () => {
                const index = conferencePresentersList.querySelectorAll('.edit-conference-presenter-row').length;
                conferencePresentersList.insertAdjacentHTML('beforeend', document.getElementById('editConferencePresenterTemplate').innerHTML.replaceAll('__INDEX__', index));
                bindUserLookup(conferencePresentersList);
                bindExternalNameRows(conferencePresentersList);
                reindexRows();
            });

            document.getElementById('addEditBookAuthor')?.addEventListener('click', () => {
                const index = bookAuthorsList.querySelectorAll('.edit-book-author-row').length;
                bookAuthorsList.insertAdjacentHTML('beforeend', document.getElementById('editBookAuthorTemplate').innerHTML.replaceAll('__INDEX__', index));
                bindExternalNameRows(bookAuthorsList);
                reindexRows();
            });

            document.getElementById('addEditAwardRecipient')?.addEventListener('click', () => {
                const index = awardRecipientsList.querySelectorAll('.edit-award-recipient-row').length;
                awardRecipientsList.insertAdjacentHTML('beforeend', document.getElementById('editAwardRecipientTemplate').innerHTML.replaceAll('__INDEX__', index));
                bindUserLookup(awardRecipientsList);
                bindExternalNameRows(awardRecipientsList);
                reindexRows();
            });

            document.addEventListener('click', (event) => {
                if (event.target.matches('[data-remove-edit-author]')) {
                    if (authorsList.querySelectorAll('.edit-author-row').length > 1) {
                        event.target.closest('.edit-author-row')?.remove();
                        reindexRows();
                    }
                }
                if (event.target.matches('[data-remove-edit-conference-presenter]')) {
                    if (conferencePresentersList.querySelectorAll('.edit-conference-presenter-row').length > 1) {
                        event.target.closest('.edit-conference-presenter-row')?.remove();
                        reindexRows();
                    }
                }
                if (event.target.matches('[data-remove-edit-book-author]')) {
                    if (bookAuthorsList.querySelectorAll('.edit-book-author-row').length > 1) {
                        event.target.closest('.edit-book-author-row')?.remove();
                        reindexRows();
                    }
                }
                if (event.target.matches('[data-remove-edit-award-recipient]')) {
                    if (awardRecipientsList.querySelectorAll('.edit-award-recipient-row').length > 1) {
                        event.target.closest('.edit-award-recipient-row')?.remove();
                        reindexRows();
                    }
                }
            });

            typeSelect?.addEventListener('change', syncType);
            syncType();
            bindUserLookup(document);
            bindExternalNameRows(document);
            if (typeof window.initThaiDatePickers === 'function') {
                window.initThaiDatePickers(document);
            }
            form?.querySelectorAll('[data-server-invalid="1"]').forEach((field) => markInvalid(field));

            form?.querySelectorAll('input, select, textarea').forEach((field) => {
                field.addEventListener('input', () => clearInvalid(field));
                field.addEventListener('change', () => clearInvalid(field));
            });

            form?.addEventListener('submit', (event) => {
                let firstInvalid = null;
                form.querySelectorAll('[required]').forEach((field) => {
                    if (field.disabled || field.type === 'hidden' || !isVisibleField(field)) {
                        clearInvalid(field);
                        return;
                    }

                    const value = (field.value || '').trim();
                    const invalid = !value || !field.checkValidity();
                    const displayTarget = getDisplayField(field);

                    if (invalid) {
                        markInvalid(field);
                        if (!firstInvalid) {
                            firstInvalid = displayTarget;
                        }
                    } else {
                        clearInvalid(field);
                    }
                });
                if (!firstInvalid) {
                    const duplicateAuthorField = validateEA119DuplicateAuthors();
                    if (!duplicateAuthorField) {
                        return;
                    }
                    event.preventDefault();
                    duplicateAuthorField.focus({ preventScroll: true });
                    duplicateAuthorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    Swal.fire({
                        icon: 'error',
                        title: 'รายชื่อผู้เขียนซ้ำ',
                        text: 'EA119 ไม่สามารถกรอกชื่อผู้เขียนซ้ำในรายการเดียวกันได้',
                        confirmButtonText: 'ตกลง',
                    });
                    return;
                }

                event.preventDefault();
                firstInvalid.focus({ preventScroll: true });
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณากรอกข้อมูลที่จำเป็นให้ครบก่อนบันทึก',
                    confirmButtonText: 'ตกลง',
                });
            });

            @if ($errors->any())
                setTimeout(() => {
                    validateForm();
                    Swal.fire({
                        icon: 'error',
                        title: 'บันทึกข้อมูลไม่สำเร็จ',
                        text: 'กรุณาตรวจสอบช่องที่กรอบสีแดงแล้วลองใหม่อีกครั้ง',
                        confirmButtonText: 'ตกลง',
                    });
                }, 50);
            @endif
        });
    </script>
@endsection
