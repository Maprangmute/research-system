@extends('layouts.app')

@php
    $oldOutputs = old('outputs', [[
        'title' => '',
        'type' => '',
        'job_category' => '',
        'note' => '',
        'publish_date' => '',
        'journal' => '',
        'quartile' => '',
        'impact_factor' => '',
        'book_title' => '',
        'publisher_name' => '',
        'chapter_pages' => '',
        'isbn' => '',
        'conference_id' => '',
        'conference_name' => '',
        'conference_presentation_type' => '',
        'conference_location' => '',
        'conference_presenters' => [[
            'id' => '',
            'person_type' => '',
            'note' => '',
            'user_id' => '',
        ]],
        'authors' => [['role' => '', 'person_type' => '', 'name' => '', 'user_id' => '']],
    ]]);
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
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Academic Paper / Presentation / Award / Textbook / Manual นอกเหนือโครงการวิจัยในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        บันทึกผลงานเพิ่มเติมแบบไม่ผูกกับโครงการ โดยใช้โครง EA101, EA103, EA118, EA119 และ EA120 ให้ครบในหน้าเดียว
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.external-academic') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        รายการทั้งหมด
                    </a>
                </div>
            </div>
        </section>

        @if (session('success'))
            <section class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
                {{ session('success') }}
            </section>
        @endif

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

        <form method="POST" action="{{ route('outputs.external-academic.store') }}" id="externalOutputsForm" class="space-y-5" novalidate>
            @csrf

            <div id="externalOutputsList" class="space-y-5">
                @foreach ($oldOutputs as $outputIndex => $output)
                    @php
                        $type = in_array(($output['type'] ?? ''), ['EA101', 'EA103', 'EA118', 'EA119', 'EA120'], true) ? $output['type'] : '';
                        $authors = $output['authors'] ?? [['role' => '', 'name' => '']];
                        $conferencePresenters = $output['conference_presenters'] ?? [[
                            'id' => '',
                            'person_type' => '',
                            'note' => '',
                            'user_id' => '',
                        ]];
                    @endphp
                    <section class="external-output-card rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5" data-output-card>
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-blue-700" data-output-title>ผลงานที่ {{ $loop->iteration }}</h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="rounded-full border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                    data-toggle-collapse>
                                    ย่อแล้ว
                                </button>
                                <button type="button"
                                    class="rounded-full border border-rose-200 px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                                    data-remove-output>
                                    ลบ
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4" data-output-body>
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <label class="block md:col-span-12">
                                    <span class="text-sm font-semibold text-slate-700">ชื่อผลงาน <span class="text-rose-500">*</span></span>
                                    <input type="text"
                                        name="outputs[{{ $outputIndex }}][title]"
                                        value="{{ $output['title'] ?? '' }}"
                                        required
                                        oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')"
                                        oninput="this.setCustomValidity('')"
                                        @if ($errors->has("outputs.$outputIndex.title")) data-server-invalid="1" @endif
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has("outputs.$outputIndex.title") ? ' border-red-500 bg-red-50 ' : '' }}"
                                        placeholder="ระบุชื่อผลงาน">
                                </label>

                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">เลือกประเภทของผลงาน <span class="text-rose-500">*</span></span>
                                    <select name="outputs[{{ $outputIndex }}][type]"
                                        required
                                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผลงาน')"
                                        oninput="this.setCustomValidity('')"
                                        @if ($errors->has("outputs.$outputIndex.type")) data-server-invalid="1" @endif
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has("outputs.$outputIndex.type") ? ' border-red-500 bg-red-50 ' : '' }}"
                                        data-output-type>
                                        <option value="">เลือกประเภทผลงาน</option>
                                        <option value="EA101" @selected($type === 'EA101')>EA101 Research Paper or Journal Article</option>
                                        <option value="EA103" @selected($type === 'EA103')>EA103 Presentations in Academic Conferences</option>
                                        <option value="EA118" @selected($type === 'EA118')>EA118 Award and Recognition</option>
                                        <option value="EA119" @selected($type === 'EA119')>EA119 Textbook / Book chapter</option>
                                        <option value="EA120" @selected($type === 'EA120')>EA120 Manual / Instruction / Guideline</option>
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">Theme <span class="text-rose-500">*</span></span>
                                    <select name="outputs[{{ $outputIndex }}][job_category]"
                                        required
                                        oninvalid="this.setCustomValidity('กรุณาเลือก Theme')"
                                        oninput="this.setCustomValidity('')"
                                        @if ($errors->has("outputs.$outputIndex.job_category")) data-server-invalid="1" @endif
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has("outputs.$outputIndex.job_category") ? ' border-red-500 bg-red-50 ' : '' }}">
                                        <option value="">เลือก Theme</option>
                                        @foreach ($jobCategoryOptions as $jobCategory)
                                            <option value="{{ $jobCategory }}" @selected(($output['job_category'] ?? '') === $jobCategory)>{{ $jobCategory }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">รายละเอียด / หมายเหตุ / LINK</span>
                                    <input type="text"
                                        name="outputs[{{ $outputIndex }}][note]"
                                        value="{{ $output['note'] ?? '' }}"
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                        placeholder="รายละเอียด / หมายเหตุ / LINK">
                                </label>

                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">วันที่ publish <span class="text-rose-500">*</span></span>
                                    <div class="relative">
                                        <input type="text"
                                            readonly
                                            id="external-publish-date-{{ $outputIndex }}"
                                            name="outputs[{{ $outputIndex }}][publish_date]"
                                            value="{{ $output['publish_date'] ?? '' }}"
                                            required
                                            oninvalid="this.setCustomValidity('กรุณาเลือกวันที่ publish')"
                                            oninput="this.setCustomValidity('')"
                                            @if ($errors->has("outputs.$outputIndex.publish_date")) data-server-invalid="1" @endif
                                            class="js-date-be mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-11 text-sm cursor-pointer{{ $errors->has("outputs.$outputIndex.publish_date") ? ' border-red-500 bg-red-50 ' : '' }}"
                                            placeholder="เลือกวันที่ publish">
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" data-open-picker data-target="external-publish-date-{{ $outputIndex }}">📅</button>
                                    </div>
                                </label>
                            </div>

                            <div data-ea101-section @class([
                                'rounded-2xl border p-4 md:p-5',
                                'border-sky-200 bg-sky-50/50' => true,
                                'hidden' => $type !== 'EA101',
                            ])>
                                <div class="grid gap-4 md:grid-cols-4">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">Journal <span class="text-rose-500">*</span></span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][journal]"
                                            value="{{ $output['journal'] ?? '' }}"
                                            data-required-ea101="true"
                                            oninvalid="this.setCustomValidity('กรุณากรอก Journal')"
                                            oninput="this.setCustomValidity('')"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น IEEE Access">
                                    </label>

                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">Ranking</span>
                                        <select name="outputs[{{ $outputIndex }}][quartile]"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <option value="">เลือก Ranking</option>
                                            @foreach (['Q1', 'Q2', 'Q3', 'Q4', 'TCI 1', 'TCI 2'] as $quartile)
                                                <option value="{{ $quartile }}" @selected(($output['quartile'] ?? '') === $quartile)>{{ $quartile }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">Impact Factor</span>
                                        <input type="number"
                                            step="0.001"
                                            name="outputs[{{ $outputIndex }}][impact_factor]"
                                            value="{{ $output['impact_factor'] ?? '' }}"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น 3.215">
                                    </label>
                                </div>

                                <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                                    <div class="mb-3 text-xl font-bold text-blue-700">รายชื่อผู้เขียน (Authors)</div>
                                    <div class="space-y-3" data-authors-list>
                                        @foreach ($authors as $authorIndex => $author)
                                            <div class="author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">บทบาทผู้เขียน (Role)</span>
                                                    <select name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][role]"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                                        <option value="">เลือกบทบาท</option>
                                                        <option value="first" @selected(($author['role'] ?? '') === 'first')>First-author</option>
                                                        <option value="co" @selected(($author['role'] ?? '') === 'co')>Co-author</option>
                                                        <option value="corresponding" @selected(($author['role'] ?? '') === 'corresponding')>Corresponding author</option>
                                                    </select>
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                                                    <select name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][person_type]"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        data-person-type
                                                        data-name-kind="author"
                                                        data-name-mode="research">
                                                        <option value="" @selected(($author['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                                        <option value="internal" @selected(($author['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                        <option value="external" @selected(($author['person_type'] ?? '') === 'external')>คนนอก</option>
                                                    </select>
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                                                    <input type="hidden" name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][user_id]" value="{{ $author['user_id'] ?? '' }}" data-user-id-hidden>
                                                    <input type="text"
                                                        list="externalAcademicAuthorManualNames"
                                                        name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][name]"
                                                        value="{{ $author['name'] ?? '' }}"
                                                        data-name-input
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)">
                                                </label>
                                                <div class="flex items-end">
                                                    <button type="button"
                                                        class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50"
                                                        data-remove-author>
                                                        ลบผู้เขียน
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button type="button"
                                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                                            data-add-author>
                                            + เพิ่มผู้เขียน
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div data-ea103-section @class([
                                'rounded-2xl border p-4 md:p-5',
                                'border-amber-200 bg-amber-50/50' => true,
                                'hidden' => $type !== 'EA103',
                            ])>
                                <input type="hidden" name="outputs[{{ $outputIndex }}][conference_id]" value="{{ $output['conference_id'] ?? '' }}">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">ชื่อเวที/Conference <span class="text-rose-500">*</span></span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][conference_name]"
                                            value="{{ $output['conference_name'] ?? '' }}"
                                            data-required-ea103="true"
                                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อเวที/Conference')"
                                            oninput="this.setCustomValidity('')"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น PMAC 2025">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">ประเภทนำเสนอ</span>
                                        <select name="outputs[{{ $outputIndex }}][conference_presentation_type]"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <option value="">เลือก</option>
                                            @foreach (['Oral Presentation', 'Poster Presentation', 'Abstract Presentation', 'Panel Discussion', 'Keynote Speech', 'World café', 'Moderator'] as $presentationType)
                                                <option value="{{ $presentationType }}" @selected(($output['conference_presentation_type'] ?? '') === $presentationType)>{{ $presentationType }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">สถานที่</span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][conference_location]"
                                            value="{{ $output['conference_location'] ?? '' }}"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เมือง/ประเทศ">
                                    </label>
                                </div>
                                <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                                    <div class="mb-3 text-xl font-bold text-amber-700">รายชื่อผู้นำเสนอ</div>
                                    <div class="space-y-3" data-conference-presenters-list>
                                        @foreach ($conferencePresenters as $presenterIndex => $presenter)
                                            <div class="conference-presenter-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                                <input type="hidden" name="outputs[{{ $outputIndex }}][conference_presenters][{{ $presenterIndex }}][id]" value="{{ $presenter['id'] ?? '' }}">
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                                                    <select name="outputs[{{ $outputIndex }}][conference_presenters][{{ $presenterIndex }}][person_type]"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        data-person-type
                                                        data-name-kind="presenter"
                                                        data-name-mode="full">
                                                        <option value="" @selected(($presenter['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                                        <option value="internal" @selected(($presenter['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                        <option value="external" @selected(($presenter['person_type'] ?? '') === 'external')>คนนอก</option>
                                                    </select>
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ชื่อผู้นำเสนอ</span>
                                                    <input type="hidden" name="outputs[{{ $outputIndex }}][conference_presenters][{{ $presenterIndex }}][user_id]" value="{{ $presenter['user_id'] ?? '' }}" data-user-id-hidden>
                                                    <input type="text"
                                                        list="externalAcademicPresenterManualNames"
                                                        name="outputs[{{ $outputIndex }}][conference_presenters][{{ $presenterIndex }}][note]"
                                                        value="{{ $presenter['note'] ?? '' }}"
                                                        data-name-input
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        placeholder="ชื่อผู้นำเสนอ">
                                                </label>
                                                <div class="flex items-end">
                                                    <button type="button"
                                                        class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50"
                                                        data-remove-conference-presenter>
                                                        ลบผู้นำเสนอ
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button type="button"
                                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                                            data-add-conference-presenter>
                                            + เพิ่มผู้นำเสนอ
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div data-ea118-section @class([
                                'rounded-2xl border p-4 md:p-5',
                                'border-violet-200 bg-violet-50/50' => true,
                                'hidden' => $type !== 'EA118',
                            ])>
                                <div class="mt-1 rounded-2xl border border-slate-200 bg-white/80 p-4">
                                    <div class="mb-3 text-xl font-bold text-violet-700">ผู้ได้รับรางวัล (Award Recipients)</div>
                                    <div class="space-y-3" data-award-recipients-list>
                                        @foreach ($authors as $authorIndex => $author)
                                            <div class="award-recipient-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                                <input type="hidden" name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][role]" value="awardee">
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก / หน่วยงาน</span>
                                                    <select name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][person_type]"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        data-person-type
                                                        data-name-kind="award"
                                                        data-name-mode="full">
                                                        <option value="" @selected(($author['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                                        <option value="unit" @selected(($author['person_type'] ?? '') === 'unit')>หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                        <option value="internal" @selected(($author['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                        <option value="external" @selected(($author['person_type'] ?? '') === 'external')>คนนอก</option>
                                                    </select>
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ชื่อผู้ได้รับรางวัล</span>
                                                    <input type="hidden" name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][user_id]" value="{{ $author['user_id'] ?? '' }}" data-user-id-hidden>
                                                    <input type="text"
                                                        list="externalAcademicAwardRecipientManualNames"
                                                        name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][name]"
                                                        value="{{ $author['name'] ?? '' }}"
                                                        data-name-input
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        placeholder="เลือกหรือพิมพ์ชื่อผู้ได้รับรางวัล">
                                                </label>
                                                <div class="flex items-end">
                                                    <button type="button"
                                                        class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50"
                                                        data-remove-award-recipient>
                                                        ลบรายชื่อ
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button type="button"
                                            class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700"
                                            data-add-award-recipient>
                                            + เพิ่มผู้ได้รับรางวัล
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div data-ea119-section @class([
                                'rounded-2xl border p-4 md:p-5',
                                'border-indigo-200 bg-indigo-50/50' => true,
                                'hidden' => $type !== 'EA119',
                            ])>
                                <div class="grid gap-4 md:grid-cols-3">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">ชื่อหนังสือ <span class="text-rose-500">*</span></span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][book_title]"
                                            value="{{ $output['book_title'] ?? '' }}"
                                            data-required-ea119="true"
                                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อหนังสือ')"
                                            oninput="this.setCustomValidity('')"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น Textbook of Public Health">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">ชื่อสำนักพิมพ์ <span class="text-rose-500">*</span></span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][publisher_name]"
                                            value="{{ $output['publisher_name'] ?? '' }}"
                                            data-required-ea119="true"
                                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อสำนักพิมพ์')"
                                            oninput="this.setCustomValidity('')"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น Elsevier">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">ชื่อบท / เลขหน้า <span class="text-rose-500">*</span></span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][chapter_pages]"
                                            value="{{ $output['chapter_pages'] ?? '' }}"
                                            data-required-ea119="true"
                                            oninvalid="this.setCustomValidity('กรุณากรอกชื่อบท / เลขหน้า')"
                                            oninput="this.setCustomValidity('')"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น Chapter 2, pp. 45-66">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">เลข ISBN</span>
                                        <input type="text"
                                            name="outputs[{{ $outputIndex }}][isbn]"
                                            value="{{ $output['isbn'] ?? '' }}"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                            placeholder="เช่น 978-3-16-148410-0">
                                    </label>
                                </div>
                                <div class="mt-1 rounded-2xl border border-slate-200 bg-white/80 p-4">
                                    <div class="mb-3 text-xl font-bold text-blue-700">รายชื่อผู้เขียน (Authors)</div>
                                    <div class="space-y-3" data-book-authors-list>
                                        @foreach ($authors as $authorIndex => $author)
                                            <div class="book-author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                                                    <select name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][person_type]"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        data-person-type
                                                        data-name-kind="book"
                                                        data-name-mode="full">
                                                        <option value="" @selected(($author['person_type'] ?? '') === '')>กรุณาเลือก</option>
                                                        <option value="internal" @selected(($author['person_type'] ?? '') === 'internal')>คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                                                        <option value="external" @selected(($author['person_type'] ?? '') === 'external')>คนนอก</option>
                                                    </select>
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ชื่อผู้เขียน</span>
                                                    <input type="hidden" name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][user_id]" value="{{ $author['user_id'] ?? '' }}" data-user-id-hidden>
                                                    <input type="text"
                                                        list="externalAcademicBookAuthorManualNames"
                                                        name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][name]"
                                                        value="{{ $author['name'] ?? '' }}"
                                                        data-name-input
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        onfocus="this.dispatchEvent(new Event('input', { bubbles: true }))"
                                                        placeholder="เลือกหรือพิมพ์ชื่อผู้เขียน">
                                                </label>
                                                <div class="flex items-end">
                                                    <button type="button"
                                                        class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50"
                                                        data-remove-book-author>
                                                        ลบผู้เขียน
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button type="button"
                                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                                            data-add-book-author>
                                            + เพิ่มผู้เขียน
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button"
                    id="addExternalOutput"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    + เพิ่ม Output
                </button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>

    <template id="externalOutputTemplate">
        <section class="external-output-card rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5" data-output-card>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-blue-700" data-output-title>ผลงานที่ __NUMBER__</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-full border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50" data-toggle-collapse>ย่อแล้ว</button>
                    <button type="button" class="rounded-full border border-rose-200 px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50" data-remove-output>ลบ</button>
                </div>
            </div>

            <div class="space-y-4" data-output-body>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <label class="block md:col-span-12">
                        <span class="text-sm font-semibold text-slate-700">ชื่อผลงาน <span class="text-rose-500">*</span></span>
                        <input type="text" name="outputs[__INDEX__][title]" required oninvalid="this.setCustomValidity('กรุณากรอกชื่อผลงาน')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ระบุชื่อผลงาน">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">เลือกประเภทของผลงาน <span class="text-rose-500">*</span></span>
                        <select name="outputs[__INDEX__][type]" required oninvalid="this.setCustomValidity('กรุณาเลือกประเภทผลงาน')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-output-type>
                            <option value="">เลือกประเภทผลงาน</option>
                            <option value="EA101">EA101 Research Paper or Journal Article</option>
                            <option value="EA103">EA103 Presentations in Academic Conferences</option>
                            <option value="EA118">EA118 Award and Recognition</option>
                            <option value="EA119">EA119 Textbook / Book chapter</option>
                            <option value="EA120">EA120 Manual / Instruction / Guideline</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Theme <span class="text-rose-500">*</span></span>
                        <select name="outputs[__INDEX__][job_category]" required oninvalid="this.setCustomValidity('กรุณาเลือก Theme')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                            <option value="">เลือก Theme</option>
                            @foreach ($jobCategoryOptions as $jobCategory)
                                <option value="{{ $jobCategory }}">{{ $jobCategory }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">รายละเอียด / หมายเหตุ / LINK</span>
                        <input type="text" name="outputs[__INDEX__][note]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="รายละเอียด / หมายเหตุ / LINK">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">วันที่ publish <span class="text-rose-500">*</span></span>
                        <div class="relative">
                            <input type="text" readonly id="external-publish-date-__INDEX__" name="outputs[__INDEX__][publish_date]" required oninvalid="this.setCustomValidity('กรุณาเลือกวันที่ publish')" oninput="this.setCustomValidity('')" class="js-date-be mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-11 text-sm cursor-pointer" placeholder="เลือกวันที่ publish">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" data-open-picker data-target="external-publish-date-__INDEX__">📅</button>
                        </div>
                    </label>
                </div>

                <div data-ea101-section class="rounded-2xl border border-sky-200 bg-sky-50/50 p-4 md:p-5">
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Journal <span class="text-rose-500">*</span></span>
                            <input type="text" name="outputs[__INDEX__][journal]" data-required-ea101="true" oninvalid="this.setCustomValidity('กรุณากรอก Journal')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น IEEE Access">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Ranking</span>
                            <select name="outputs[__INDEX__][quartile]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
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
                            <span class="text-sm font-semibold text-slate-700">Impact Factor</span>
                            <input type="number" step="0.001" name="outputs[__INDEX__][impact_factor]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น 3.215">
                        </label>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-blue-700">รายชื่อผู้เขียน (Authors)</div>
                        <div class="space-y-3" data-authors-list></div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700" data-add-author>+ เพิ่มผู้เขียน</button>
                        </div>
                    </div>
                </div>

                <div data-ea103-section class="hidden rounded-2xl border border-amber-200 bg-amber-50/50 p-4 md:p-5">
                    <input type="hidden" name="outputs[__INDEX__][conference_id]" value="">
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">ชื่อเวที/Conference <span class="text-rose-500">*</span></span>
                            <input type="text" name="outputs[__INDEX__][conference_name]" data-required-ea103="true" oninvalid="this.setCustomValidity('กรุณากรอกชื่อเวที/Conference')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น PMAC 2025">
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">ประเภทนำเสนอ</span>
                            <select name="outputs[__INDEX__][conference_presentation_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                <option value="">เลือก</option>
                                <option value="Oral Presentation">Oral Presentation</option>
                                <option value="Poster Presentation">Poster Presentation</option>
                                <option value="Abstract Presentation">Abstract Presentation</option>
                                <option value="Panel Discussion">Panel Discussion</option>
                                <option value="Keynote Speech">Keynote Speech</option>
                                <option value="World café">World café</option>
                                <option value="Moderator">Moderator</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">สถานที่</span>
                            <input type="text" name="outputs[__INDEX__][conference_location]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เมือง/ประเทศ">
                        </label>
                    </div>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-amber-700">รายชื่อผู้นำเสนอ</div>
                        <div class="space-y-3" data-conference-presenters-list></div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700" data-add-conference-presenter>+ เพิ่มผู้นำเสนอ</button>
                        </div>
                    </div>
                </div>

                <div data-ea118-section class="hidden rounded-2xl border border-violet-200 bg-violet-50/50 p-4 md:p-5">
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-violet-700">ผู้ได้รับรางวัล (Award Recipients)</div>
                        <div class="space-y-3" data-award-recipients-list></div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700" data-add-award-recipient>+ เพิ่มผู้ได้รับรางวัล</button>
                        </div>
                    </div>
                </div>

                <div data-ea119-section class="hidden rounded-2xl border border-indigo-200 bg-indigo-50/50 p-4 md:p-5">
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">ชื่อหนังสือ <span class="text-rose-500">*</span></span>
                            <input type="text" name="outputs[__INDEX__][book_title]" data-required-ea119="true" oninvalid="this.setCustomValidity('กรุณากรอกชื่อหนังสือ')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น Textbook of Public Health">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">ชื่อสำนักพิมพ์ <span class="text-rose-500">*</span></span>
                            <input type="text" name="outputs[__INDEX__][publisher_name]" data-required-ea119="true" oninvalid="this.setCustomValidity('กรุณากรอกชื่อสำนักพิมพ์')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น Elsevier">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">ชื่อบท / เลขหน้า <span class="text-rose-500">*</span></span>
                            <input type="text" name="outputs[__INDEX__][chapter_pages]" data-required-ea119="true" oninvalid="this.setCustomValidity('กรุณากรอกชื่อบท / เลขหน้า')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น Chapter 2, pp. 45-66">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">เลข ISBN</span>
                            <input type="text" name="outputs[__INDEX__][isbn]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น 978-3-16-148410-0">
                        </label>
                    </div>
                    <div class="mt-1 rounded-2xl border border-slate-200 bg-white/80 p-4">
                        <div class="mb-3 text-xl font-bold text-blue-700">รายชื่อผู้เขียน (Authors)</div>
                        <div class="space-y-3" data-book-authors-list></div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700" data-add-book-author>+ เพิ่มผู้เขียน</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </template>

    <template id="externalAuthorTemplate">
        <div class="author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">บทบาทผู้เขียน (Role)</span>
                <select name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][role]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                    <option value="">เลือกบทบาท</option>
                    <option value="first">First-author</option>
                    <option value="co">Co-author</option>
                    <option value="corresponding">Corresponding author</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                <select name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="author" data-name-mode="research">
                    <option value="">กรุณาเลือก</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                <input type="hidden" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicAuthorManualNames" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][name]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-author>ลบผู้เขียน</button>
            </div>
        </div>
    </template>

    <template id="externalConferencePresenterTemplate">
        <div class="conference-presenter-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <input type="hidden" name="outputs[__OUTPUT_INDEX__][conference_presenters][__PRESENTER_INDEX__][id]" value="">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                <select name="outputs[__OUTPUT_INDEX__][conference_presenters][__PRESENTER_INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="presenter" data-name-mode="full">
                    <option value="">กรุณาเลือก</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อผู้นำเสนอ</span>
                <input type="hidden" name="outputs[__OUTPUT_INDEX__][conference_presenters][__PRESENTER_INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicPresenterManualNames" name="outputs[__OUTPUT_INDEX__][conference_presenters][__PRESENTER_INDEX__][note]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อผู้นำเสนอ">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-conference-presenter>ลบผู้นำเสนอ</button>
            </div>
        </div>
    </template>

    <template id="externalBookAuthorTemplate">
        <div class="book-author-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก</span>
                <select name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="book" data-name-mode="full">
                    <option value="">กรุณาเลือก</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อผู้เขียน</span>
                <input type="hidden" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicBookAuthorManualNames" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][name]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" onfocus="this.dispatchEvent(new Event('input', { bubbles: true }))" placeholder="เลือกหรือพิมพ์ชื่อผู้เขียน">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-book-author>ลบผู้เขียน</button>
            </div>
        </div>
    </template>

    <template id="externalAwardRecipientTemplate">
        <div class="award-recipient-row grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
            <input type="hidden" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][role]" value="awardee">
            <label class="block">
                <span class="text-sm font-medium text-slate-700">คนในหน่วย / คนนอก / หน่วยงาน</span>
                <select name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][person_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" data-person-type data-name-kind="award" data-name-mode="full">
                    <option value="">กรุณาเลือก</option>
                    <option value="unit">หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="internal">คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ</option>
                    <option value="external">คนนอก</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">ชื่อผู้ได้รับรางวัล</span>
                <input type="hidden" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][user_id]" data-user-id-hidden>
                <input type="text" list="externalAcademicAwardRecipientManualNames" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][name]" data-name-input class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เลือกหรือพิมพ์ชื่อผู้ได้รับรางวัล">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-award-recipient>ลบรายชื่อ</button>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const outputsList = document.getElementById('externalOutputsList');
            const addOutputBtn = document.getElementById('addExternalOutput');
            const form = document.getElementById('externalOutputsForm');
            const outputTemplate = document.getElementById('externalOutputTemplate');
            const authorTemplate = document.getElementById('externalAuthorTemplate');
            const conferencePresenterTemplate = document.getElementById('externalConferencePresenterTemplate');
            const bookAuthorTemplate = document.getElementById('externalBookAuthorTemplate');
            const awardRecipientTemplate = document.getElementById('externalAwardRecipientTemplate');
            const userDirectory = Array.isArray(window.outputUserDirectory) ? window.outputUserDirectory : [];
            function getDisplayField(field) {
                if (typeof window.getProjectValidationDisplayTarget === 'function') {
                    return window.getProjectValidationDisplayTarget(field);
                }
                return field;
            }

            function markInvalid(field) {
                const target = getDisplayField(field);
                target.dataset.externalInvalid = '1';
                target.style.setProperty('border-color', '#ef4444', 'important');
                target.style.setProperty('background-color', '#fef2f2', 'important');
                target.style.setProperty('box-shadow', '0 0 0 3px rgba(254, 202, 202, 0.8)', 'important');
                target.style.setProperty('outline', 'none', 'important');
            }

            function clearInvalid(field) {
                const target = getDisplayField(field);
                delete target.dataset.externalInvalid;
                target.style.removeProperty('border-color');
                target.style.removeProperty('background-color');
                target.style.removeProperty('box-shadow');
                target.style.removeProperty('outline');
            }

            function isVisibleField(field) {
                return !!field.offsetParent;
            }

            function validateForm() {
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
            }

            function validateEA119DuplicateAuthors() {
                let invalidField = null;
                outputsList.querySelectorAll('[data-output-card]').forEach((card) => {
                    if (invalidField) return;
                    const type = card.querySelector('[data-output-type]')?.value || '';
                    if (type !== 'EA119') return;

                    const seen = new Set();
                    card.querySelectorAll('.book-author-row input[name*="[authors]"][name$="[name]"]').forEach((input) => {
                        const value = (input.value || '').trim().toLowerCase();
                        if (!value) return;
                        if (seen.has(value) && !invalidField) {
                            markInvalid(input);
                            invalidField = input;
                            return;
                        }
                        seen.add(value);
                    });
                });
                return invalidField;
            }

            function replacePlaceholders(template, replacements) {
                let html = template.innerHTML;
                Object.entries(replacements).forEach(([key, value]) => {
                    html = html.replaceAll(`__${key}__`, value);
                });
                return html;
            }

            function renumberOutputCards() {
                outputsList.querySelectorAll('[data-output-card]').forEach((card, index) => {
                    const title = card.querySelector('[data-output-title]');
                    if (title) title.textContent = `ผลงานที่ ${index + 1}`;
                });
            }

            function reinitThaiDatePicker(scope) {
                if (typeof initDateBE === 'function') {
                    initDateBE(scope || document);
                }
            }

            function findMatchedUserByValue(value, mode) {
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
            }

            function findMatchedUser(input) {
                const value = (input?.value || '').trim();
                const mode = input?.dataset?.userLookup || 'full';

                if (value === '') {
                    return null;
                }

                return findMatchedUserByValue(value, mode);
            }

            function getExternalUserOptionLabel(user, mode) {
                return mode === 'research'
                    ? String(user?.research_display_name || '').trim()
                    : String(user?.full_name || '').trim();
            }

            function ensureExternalUserSearchSelect(row, nameInput, nameMode) {
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
            }

            function syncUserLookupInput(input) {
                const hidden = input?.closest('label, .author-row, .conference-presenter-row, .award-recipient-row')?.querySelector('[data-user-id-hidden]');
                if (!hidden) {
                    return;
                }

                const matchedUser = findMatchedUser(input);
                hidden.value = matchedUser ? String(matchedUser.id) : '';
            }

            function bindUserLookup(scope = document) {
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
            }

            function syncExternalNameRow(row) {
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
            }

            function bindExternalNameRows(scope = document) {
                scope.querySelectorAll('.author-row, .conference-presenter-row, .award-recipient-row, .book-author-row').forEach((row) => {
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
            }

            function syncCardFieldIndexes(card, outputIndex) {
                card.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name
                        .replace(/outputs\[\d+\]/, `outputs[${outputIndex}]`)
                        .replace(/\[authors]\[\d+]/, `[authors][0]`)
                        .replace(/\[conferences]\[\d+]/, `[conferences][0]`);
                });

                card.querySelectorAll('.author-row').forEach((row, authorIndex) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/\[authors]\[\d+]/, `[authors][${authorIndex}]`);
                    });
                });

                card.querySelectorAll('.book-author-row').forEach((row, authorIndex) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/\[authors]\[\d+]/, `[authors][${authorIndex}]`);
                    });
                });

                card.querySelectorAll('.award-recipient-row').forEach((row, authorIndex) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/\[authors]\[\d+]/, `[authors][${authorIndex}]`);
                    });
                });

                card.querySelectorAll('.conference-presenter-row').forEach((row, presenterIndex) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/\[conference_presenters]\[\d+]/, `[conference_presenters][${presenterIndex}]`);
                    });
                });
            }

            function syncAllIndexes() {
                outputsList.querySelectorAll('[data-output-card]').forEach((card, outputIndex) => {
                    syncCardFieldIndexes(card, outputIndex);
                });
                renumberOutputCards();
            }

            function syncType(card) {
                const typeInput = card.querySelector('[data-output-type]');
                const ea101 = card.querySelector('[data-ea101-section]');
                const ea103 = card.querySelector('[data-ea103-section]');
                const ea118 = card.querySelector('[data-ea118-section]');
                const ea119 = card.querySelector('[data-ea119-section]');
                const currentType = typeInput?.value || '';
                const isPaper = currentType === 'EA101';
                const isPresentation = currentType === 'EA103';
                const isAward = currentType === 'EA118';
                const isTextbook = currentType === 'EA119';
                card.querySelectorAll('[data-required-ea101="true"]').forEach((field) => {
                    field.required = isPaper;
                });
                card.querySelectorAll('[data-required-ea103="true"]').forEach((field) => {
                    field.required = isPresentation;
                });
                card.querySelectorAll('[data-required-ea119="true"]').forEach((field) => {
                    field.required = isTextbook;
                });

                const toggleSection = (section, show) => {
                    if (!section) return;
                    section.classList.toggle('hidden', !show);
                    section.querySelectorAll('input, select, textarea, button').forEach((field) => {
                        field.disabled = !show;
                    });
                };

                toggleSection(ea101, isPaper);
                toggleSection(ea103, isPresentation);
                toggleSection(ea118, isAward);
                toggleSection(ea119, isTextbook);
            }

            function addAuthorRow(card) {
                const outputIndex = Array.from(outputsList.children).indexOf(card);
                const authorsList = card.querySelector('[data-authors-list]');
                const authorIndex = authorsList.querySelectorAll('.author-row').length;
                authorsList.insertAdjacentHTML('beforeend', replacePlaceholders(authorTemplate, {
                    OUTPUT_INDEX: outputIndex,
                    AUTHOR_INDEX: authorIndex,
                }));
                bindUserLookup(authorsList);
                bindExternalNameRows(authorsList);
                syncAllIndexes();
            }

            function addConferencePresenterRow(card) {
                const outputIndex = Array.from(outputsList.children).indexOf(card);
                const presentersList = card.querySelector('[data-conference-presenters-list]');
                const presenterIndex = presentersList.querySelectorAll('.conference-presenter-row').length;
                presentersList.insertAdjacentHTML('beforeend', replacePlaceholders(conferencePresenterTemplate, {
                    OUTPUT_INDEX: outputIndex,
                    PRESENTER_INDEX: presenterIndex,
                }));
                bindUserLookup(presentersList);
                bindExternalNameRows(presentersList);
                syncAllIndexes();
            }

            function addBookAuthorRow(card) {
                const outputIndex = Array.from(outputsList.children).indexOf(card);
                const authorsList = card.querySelector('[data-book-authors-list]');
                const authorIndex = authorsList.querySelectorAll('.book-author-row').length;
                authorsList.insertAdjacentHTML('beforeend', replacePlaceholders(bookAuthorTemplate, {
                    OUTPUT_INDEX: outputIndex,
                    AUTHOR_INDEX: authorIndex,
                }));
                bindExternalNameRows(authorsList);
                syncAllIndexes();
            }

            function addAwardRecipientRow(card) {
                const outputIndex = Array.from(outputsList.children).indexOf(card);
                const recipientsList = card.querySelector('[data-award-recipients-list]');
                const authorIndex = recipientsList.querySelectorAll('.award-recipient-row').length;
                recipientsList.insertAdjacentHTML('beforeend', replacePlaceholders(awardRecipientTemplate, {
                    OUTPUT_INDEX: outputIndex,
                    AUTHOR_INDEX: authorIndex,
                }));
                bindUserLookup(recipientsList);
                bindExternalNameRows(recipientsList);
                syncAllIndexes();
            }

            function bindCard(card) {
                card.querySelector('[data-output-type]')?.addEventListener('change', () => syncType(card));
                card.querySelector('[data-toggle-collapse]')?.addEventListener('click', () => {
                    card.querySelector('[data-output-body]')?.classList.toggle('hidden');
                });
                card.querySelector('[data-remove-output]')?.addEventListener('click', () => {
                    if (outputsList.querySelectorAll('[data-output-card]').length <= 1) return;
                    card.remove();
                    syncAllIndexes();
                });
                card.querySelector('[data-add-author]')?.addEventListener('click', () => addAuthorRow(card));
                card.querySelector('[data-add-conference-presenter]')?.addEventListener('click', () => addConferencePresenterRow(card));
                card.querySelector('[data-add-book-author]')?.addEventListener('click', () => addBookAuthorRow(card));
                card.querySelector('[data-add-award-recipient]')?.addEventListener('click', () => addAwardRecipientRow(card));
                card.addEventListener('click', (event) => {
                    if (event.target.matches('[data-remove-author]')) {
                        if (card.querySelectorAll('.author-row').length > 1) {
                            event.target.closest('.author-row')?.remove();
                            syncAllIndexes();
                        }
                    }
                    if (event.target.matches('[data-remove-conference-presenter]')) {
                        if (card.querySelectorAll('.conference-presenter-row').length > 1) {
                            event.target.closest('.conference-presenter-row')?.remove();
                            syncAllIndexes();
                        }
                    }
                    if (event.target.matches('[data-remove-book-author]')) {
                        if (card.querySelectorAll('.book-author-row').length > 1) {
                            event.target.closest('.book-author-row')?.remove();
                            syncAllIndexes();
                        }
                    }
                    if (event.target.matches('[data-remove-award-recipient]')) {
                        if (card.querySelectorAll('.award-recipient-row').length > 1) {
                            event.target.closest('.award-recipient-row')?.remove();
                            syncAllIndexes();
                        }
                    }
                });
                syncType(card);
                reinitThaiDatePicker(card);
                bindUserLookup(card);
                bindExternalNameRows(card);
            }

            addOutputBtn?.addEventListener('click', () => {
                const outputIndex = outputsList.querySelectorAll('[data-output-card]').length;
                outputsList.insertAdjacentHTML('beforeend', replacePlaceholders(outputTemplate, {
                    INDEX: outputIndex,
                    NUMBER: outputIndex + 1,
                }));
                const card = outputsList.lastElementChild;
                bindCard(card);
                addAuthorRow(card);
                addConferencePresenterRow(card);
                addAwardRecipientRow(card);
                addBookAuthorRow(card);
                syncAllIndexes();
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            outputsList.querySelectorAll('[data-output-card]').forEach((card) => bindCard(card));
            syncAllIndexes();
            reinitThaiDatePicker(document);
            bindUserLookup(document);
            bindExternalNameRows(document);
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
