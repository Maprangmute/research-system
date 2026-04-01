@extends('layouts.app')

@section('content')
    @php
        $currentSort = $currentSort ?? request('sort', 'created_at');
        $currentDir = $currentDir ?? request('dir', 'desc');
        $toggleDir = fn($field) => $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';
        $sortUrl = fn($field) => request()->fullUrlWithQuery(['sort' => $field, 'dir' => $toggleDir($field), 'page' => 1]);
        $sortIcon = fn($field) => $currentSort !== $field ? '↕' : ($currentDir === 'asc' ? '↑' : '↓');
    @endphp

    <div class="container mx-auto mt-11 space-y-6 px-4 pb-8">
        <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-5 px-5 py-6 md:px-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">🎤 Presentations in Academic Conferences</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 md:text-[15px]">
                        ค้นหาจากชื่อผู้นำเสนอ, ประเภทการนำเสนอ, conference และย้อนกลับไปยัง project detail ได้ทันที
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('outputs.papers') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Research Papers
                    </a>
                    <a href="{{ route('outputs.presentations') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm">
                        Presentations
                    </a>
                </div>
            </div>
        </section>

        <form method="GET" action="{{ route('outputs.presentations') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <input type="hidden" name="dir" value="{{ $currentDir }}">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <label class="block xl:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Output / presenter / conference / project">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ชื่อผู้นำเสนอ</span>
                    <input type="text" name="person" value="{{ request('person') }}" list="presenter-list"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Presenter name">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">กิจกรรม</span>
                    <select name="activity" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($activityOptions as $option)
                            <option value="{{ $option }}" @selected(request('activity') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">Conference</span>
                    <input type="text" name="conference" value="{{ request('conference') }}" list="conference-list"
                        class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm"
                        placeholder="Conference name">
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="created_at" @selected($currentSort === 'created_at')>บันทึกล่าสุด</option>
                        <option value="year" @selected($currentSort === 'year')>ปี</option>
                        <option value="title" @selected($currentSort === 'title')>ชื่อผลงาน</option>
                        <option value="conference" @selected($currentSort === 'conference')>Conference</option>
                        <option value="activity" @selected($currentSort === 'activity')>กิจกรรม</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" class="mt-1 w-full rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="desc" @selected($currentDir === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected($currentDir === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        ค้นหา
                    </button>
                    <a href="{{ route('outputs.presentations') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        ล้างฟิลเตอร์
                    </a>
                </div>

                <a href="{{ route('outputs.presentations.pdf', request()->query()) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700">
                    Export PDF
                </a>
            </div>
        </form>

        <datalist id="presenter-list">
            @foreach ($presenterOptions as $presenter)
                <option value="{{ $presenter }}"></option>
            @endforeach
        </datalist>

        <datalist id="conference-list">
            @foreach ($conferenceOptions as $conference)
                <option value="{{ $conference }}"></option>
            @endforeach
        </datalist>

        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presentations</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['total_presentations']) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presenters</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($summary['unique_presenters']) }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Presenter</div>
                <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_presenter'] ?? '-' }}</div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Conference</div>
                <div class="mt-2 text-base font-semibold text-slate-900">{{ $summary['top_conference'] ?? '-' }}</div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-4 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-800">รายการการนำเสนอ</div>
                    <div class="mt-1 text-sm text-slate-500">
                        แสดงผล {{ $outputs->firstItem() ?? 0 }}–{{ $outputs->lastItem() ?? 0 }} จากทั้งหมด {{ $outputs->total() }} รายการ
                    </div>
                </div>
            </div>

            @if ($outputs->count() > 0)
                <div class="overflow-auto">
                    <table class="min-w-full table-fixed">
                        <colgroup>
                            <col style="width: 22%;">
                            <col style="width: 16%;">
                            <col style="width: 14%;">
                            <col style="width: 20%;">
                            <col style="width: 10%;">
                            <col style="width: 18%;">
                        </colgroup>
                        <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600">
                            <tr>
                                <th class="px-4 py-3"><a href="{{ $sortUrl('title') }}">ชื่อผลงาน <span class="ml-1">{{ $sortIcon('title') }}</span></a></th>
                                <th class="px-4 py-3">ชื่อผู้นำเสนอ</th>
                                <th class="px-4 py-3"><a href="{{ $sortUrl('activity') }}">กิจกรรม <span class="ml-1">{{ $sortIcon('activity') }}</span></a></th>
                                <th class="px-4 py-3"><a href="{{ $sortUrl('conference') }}">Conference <span class="ml-1">{{ $sortIcon('conference') }}</span></a></th>
                                <th class="px-4 py-3 text-center"><a href="{{ $sortUrl('year') }}">ปี <span class="ml-1">{{ $sortIcon('year') }}</span></a></th>
                                <th class="px-4 py-3">Project Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($outputs as $row)
                                <tr class="align-top hover:bg-indigo-50/40">
                                    <td class="px-4 py-4 text-sm text-slate-900">
                                        <div class="font-semibold leading-6">{{ $row->title }}</div>
                                        @if ($row->location !== '-')
                                            <div class="mt-1 text-xs text-slate-500">Location: {{ $row->location }}</div>
                                        @endif
                                        @if (!empty($row->detail))
                                            <div class="mt-2 text-xs leading-5 text-slate-600 break-words">
                                                รายละเอียด / ลิงก์:
                                                @if (filter_var($row->detail, FILTER_VALIDATE_URL))
                                                    <a href="{{ $row->detail }}" target="_blank" rel="noopener"
                                                        class="font-medium text-indigo-700 hover:underline">
                                                        {{ $row->detail }}
                                                    </a>
                                                @else
                                                    {{ $row->detail }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ $row->presenter_name }}</td>
                                    <td class="px-4 py-4 text-sm font-medium text-slate-700">{{ $row->presentation_type }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ $row->conference_name }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-700">{{ $row->year_be ?: '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <div class="font-medium">{{ $row->project_label }}</div>
                                        @if ($row->project_code)
                                            <div class="mt-1 text-xs text-slate-500">{{ $row->project_code }}</div>
                                        @endif
                                        @if ($row->project_link)
                                            <a href="{{ $row->project_link }}"
                                                class="mt-2 inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                                เปิด Project Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                    {{ $outputs->links('vendor.pagination.th') }}
                </div>
            @else
                <div class="px-4 py-10 text-center text-sm text-slate-500">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
            @endif
        </section>
    </div>
@endsection
