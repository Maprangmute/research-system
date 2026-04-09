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
        'authors' => [['role' => '', 'name' => '']],
        'conferences' => [[
            'name' => '',
            'presentation_type' => '',
            'location' => '',
            'presenter_name' => '',
        ]],
    ]]);
@endphp

@section('content')
    <datalist id="externalAcademicAuthorNames">
        @foreach ($authorOptions as $authorName)
            <option value="{{ $authorName }}"></option>
        @endforeach
    </datalist>

    <datalist id="externalAcademicPresenterNames">
        @foreach ($presenterOptions as $presenterName)
            <option value="{{ $presenterName }}"></option>
        @endforeach
    </datalist>

    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Academic Paper / Presentation / Textbook / Manual นอกเนื่องโครงการวิจัยในหน่วย</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        บันทึกผลงานเพิ่มเติมแบบไม่ผูกกับโครงการ โดยใช้โครง EA101, EA103, EA119 และ EA120 ให้ครบในหน้าเดียว
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
                        $type = in_array(($output['type'] ?? ''), ['EA101', 'EA103', 'EA119', 'EA120'], true) ? $output['type'] : '';
                        $authors = $output['authors'] ?? [['role' => '', 'name' => '']];
                        $conferences = $output['conferences'] ?? [[
                            'name' => '',
                            'presentation_type' => '',
                            'location' => '',
                            'presenter_name' => '',
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
                                        <option value="EA119" @selected($type === 'EA119')>EA119 Textbook / Book chapter</option>
                                        <option value="EA120" @selected($type === 'EA120')>EA120 Manual / Instruction / Guideline</option>
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">ไม่แน่ใจ <span class="text-rose-500">*</span></span>
                                    <select name="outputs[{{ $outputIndex }}][job_category]"
                                        required
                                        oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของงาน')"
                                        oninput="this.setCustomValidity('')"
                                        @if ($errors->has("outputs.$outputIndex.job_category")) data-server-invalid="1" @endif
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm{{ $errors->has("outputs.$outputIndex.job_category") ? ' border-red-500 bg-red-50 ' : '' }}">
                                        <option value="">เลือกประเภทของงาน</option>
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
                                <div class="grid gap-4 md:grid-cols-3">
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
                                        <span class="text-sm font-semibold text-slate-700">Quartile (Q)</span>
                                        <select name="outputs[{{ $outputIndex }}][quartile]"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <option value="">เลือก Quartile</option>
                                            @foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quartile)
                                                <option value="{{ $quartile }}" @selected(($output['quartile'] ?? '') === $quartile)>{{ $quartile }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">Impact Factor (IF)</span>
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
                                            <div class="author-row grid gap-3 md:grid-cols-[minmax(0,1.1fr)_minmax(0,1.1fr)_auto]">
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
                                                    <span class="text-sm font-medium text-slate-700">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                                                    <input type="text"
                                                        list="externalAcademicAuthorNames"
                                                        name="outputs[{{ $outputIndex }}][authors][{{ $authorIndex }}][name]"
                                                        value="{{ $author['name'] ?? '' }}"
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
                                <div class="space-y-3" data-conferences-list>
                                    @foreach ($conferences as $conferenceIndex => $conference)
                                        <div class="conference-row rounded-2xl border border-slate-200 bg-white/80 p-4">
                                            <div class="grid gap-4 md:grid-cols-2">
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ชื่อเวที/Conference <span class="text-rose-500">*</span></span>
                                                    <input type="text"
                                                        name="outputs[{{ $outputIndex }}][conferences][{{ $conferenceIndex }}][name]"
                                                        value="{{ $conference['name'] ?? '' }}"
                                                        data-required-ea103="true"
                                                        oninvalid="this.setCustomValidity('กรุณากรอกชื่อเวที/Conference')"
                                                        oninput="this.setCustomValidity('')"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        placeholder="เช่น PMAC 2025">
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ประเภทการนำเสนอ</span>
                                                    <select name="outputs[{{ $outputIndex }}][conferences][{{ $conferenceIndex }}][presentation_type]"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                                        <option value="">เลือก</option>
                                                        @foreach (['Oral Presentation', 'Poster Presentation', 'Panel Discussion', 'Keynote Speech', 'World café'] as $presentationType)
                                                            <option value="{{ $presentationType }}" @selected(($conference['presentation_type'] ?? '') === $presentationType)>{{ $presentationType }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">สถานที่</span>
                                                    <input type="text"
                                                        name="outputs[{{ $outputIndex }}][conferences][{{ $conferenceIndex }}][location]"
                                                        value="{{ $conference['location'] ?? '' }}"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        placeholder="เมือง/ประเทศ">
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-700">ชื่อผู้นำเสนอ</span>
                                                    <input type="text"
                                                        list="externalAcademicPresenterNames"
                                                        name="outputs[{{ $outputIndex }}][conferences][{{ $conferenceIndex }}][presenter_name]"
                                                        value="{{ $conference['presenter_name'] ?? '' }}"
                                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                                        placeholder="ชื่อผู้นำเสนอ">
                                                </label>
                                            </div>
                                            <div class="mt-3 flex justify-end">
                                                <button type="button"
                                                    class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50"
                                                    data-remove-conference>
                                                    ลบรายการ
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <button type="button"
                                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                                        data-add-conference>
                                        + เพิ่มรายการ
                                    </button>
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
                            <option value="EA119">EA119 Textbook / Book chapter</option>
                            <option value="EA120">EA120 Manual / Instruction / Guideline</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">ไม่แน่ใจ <span class="text-rose-500">*</span></span>
                        <select name="outputs[__INDEX__][job_category]" required oninvalid="this.setCustomValidity('กรุณาเลือกประเภทของงาน')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                            <option value="">เลือกประเภทของงาน</option>
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
                            <span class="text-sm font-semibold text-slate-700">Quartile (Q)</span>
                            <select name="outputs[__INDEX__][quartile]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                <option value="">เลือก Quartile</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Impact Factor (IF)</span>
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
                    <div class="space-y-3" data-conferences-list></div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700" data-add-conference>+ เพิ่มรายการ</button>
                    </div>
                </div>
            </div>
        </section>
    </template>

    <template id="externalAuthorTemplate">
        <div class="author-row grid gap-3 md:grid-cols-[minmax(0,1.1fr)_minmax(0,1.1fr)_auto]">
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
                <span class="text-sm font-medium text-slate-700">ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)</span>
                <input type="text" list="externalAcademicAuthorNames" name="outputs[__OUTPUT_INDEX__][authors][__AUTHOR_INDEX__][name]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อ - นามสกุล ผู้เขียน (อังกฤษ)">
            </label>
            <div class="flex items-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-author>ลบผู้เขียน</button>
            </div>
        </div>
    </template>

    <template id="externalConferenceTemplate">
        <div class="conference-row rounded-2xl border border-slate-200 bg-white/80 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">ชื่อเวที/Conference <span class="text-rose-500">*</span></span>
                    <input type="text" name="outputs[__OUTPUT_INDEX__][conferences][__CONFERENCE_INDEX__][name]" data-required-ea103="true" oninvalid="this.setCustomValidity('กรุณากรอกชื่อเวที/Conference')" oninput="this.setCustomValidity('')" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เช่น PMAC 2025">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">ประเภทการนำเสนอ</span>
                    <select name="outputs[__OUTPUT_INDEX__][conferences][__CONFERENCE_INDEX__][presentation_type]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        <option value="">เลือก</option>
                        <option value="Oral Presentation">Oral Presentation</option>
                        <option value="Poster Presentation">Poster Presentation</option>
                        <option value="Panel Discussion">Panel Discussion</option>
                        <option value="Keynote Speech">Keynote Speech</option>
                        <option value="World café">World café</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">สถานที่</span>
                    <input type="text" name="outputs[__OUTPUT_INDEX__][conferences][__CONFERENCE_INDEX__][location]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="เมือง/ประเทศ">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">ชื่อผู้นำเสนอ</span>
                    <input type="text" list="externalAcademicPresenterNames" name="outputs[__OUTPUT_INDEX__][conferences][__CONFERENCE_INDEX__][presenter_name]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="ชื่อผู้นำเสนอ">
                </label>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="button" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50" data-remove-conference>ลบรายการ</button>
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
            const conferenceTemplate = document.getElementById('externalConferenceTemplate');
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

                card.querySelectorAll('.conference-row').forEach((row, conferenceIndex) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/\[conferences]\[\d+]/, `[conferences][${conferenceIndex}]`);
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
                const currentType = typeInput?.value || '';
                const isPaper = currentType === 'EA101';
                const isPresentation = currentType === 'EA103';
                card.querySelectorAll('[data-required-ea101="true"]').forEach((field) => {
                    field.required = isPaper;
                });
                card.querySelectorAll('[data-required-ea103="true"]').forEach((field) => {
                    field.required = isPresentation;
                });
                ea101?.classList.toggle('hidden', !isPaper);
                ea103?.classList.toggle('hidden', !isPresentation);
            }

            function addAuthorRow(card) {
                const outputIndex = Array.from(outputsList.children).indexOf(card);
                const authorsList = card.querySelector('[data-authors-list]');
                const authorIndex = authorsList.querySelectorAll('.author-row').length;
                authorsList.insertAdjacentHTML('beforeend', replacePlaceholders(authorTemplate, {
                    OUTPUT_INDEX: outputIndex,
                    AUTHOR_INDEX: authorIndex,
                }));
                syncAllIndexes();
            }

            function addConferenceRow(card) {
                const outputIndex = Array.from(outputsList.children).indexOf(card);
                const conferencesList = card.querySelector('[data-conferences-list]');
                const conferenceIndex = conferencesList.querySelectorAll('.conference-row').length;
                conferencesList.insertAdjacentHTML('beforeend', replacePlaceholders(conferenceTemplate, {
                    OUTPUT_INDEX: outputIndex,
                    CONFERENCE_INDEX: conferenceIndex,
                }));
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
                card.querySelector('[data-add-conference]')?.addEventListener('click', () => addConferenceRow(card));
                card.addEventListener('click', (event) => {
                    if (event.target.matches('[data-remove-author]')) {
                        if (card.querySelectorAll('.author-row').length > 1) {
                            event.target.closest('.author-row')?.remove();
                            syncAllIndexes();
                        }
                    }
                    if (event.target.matches('[data-remove-conference]')) {
                        if (card.querySelectorAll('.conference-row').length > 1) {
                            event.target.closest('.conference-row')?.remove();
                            syncAllIndexes();
                        }
                    }
                });
                syncType(card);
                reinitThaiDatePicker(card);
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
                addConferenceRow(card);
                syncAllIndexes();
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            outputsList.querySelectorAll('[data-output-card]').forEach((card) => bindCard(card));
            syncAllIndexes();
            reinitThaiDatePicker(document);
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
