@extends('layouts.app')

@section('content')
    @php
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
    <div class="max-w-6xl mx-auto p-4 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">งานนอกโครงการ (Back Office)</h2>
                <p class="text-sm text-slate-500">รายการงานที่ไม่เกี่ยวกับโครงการ พร้อมสรุปชั่วโมงและ FTE</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('back_office.create') }}"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">➕ เพิ่มงาน</a>
                <a href="{{ route('fte.index') }}" class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">ดูสรุป FTE</a>
            </div>
        </div>

        <form method="GET" action="{{ route('back_office.index') }}"
            class="bg-white border rounded-2xl p-5 shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหางาน</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ชื่องาน หรือรายละเอียด"
                        class="w-full border border-slate-200 rounded-lg p-2">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">บุคคล</span>
                    <select name="person" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($personList as $person)
                            <option value="{{ $person }}" @selected(request('person') === $person)>{{ $person }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภท</span>
                    <select name="category" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
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
                        @foreach ($categoryOptions as $option)
                            <option value="{{ $option }}" @selected(request('category') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ช่วงวันที่เริ่ม</span>
                    <input type="text" name="start" value="{{ request('start') }}"
                        class="w-full border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันเริ่มต้น" autocomplete="off">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ช่วงวันที่สิ้นสุด</span>
                    <input type="text" name="end" value="{{ request('end') }}"
                        class="w-full border border-slate-200 rounded-lg p-2 js-date-be"
                        placeholder="วันสิ้นสุด" autocomplete="off">
                </label>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm">กรอง</button>
                <a href="{{ route('back_office.index') }}" class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">ล้างตัวกรอง</a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">จำนวนงาน</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($summary['tasks']) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">จำนวนภาระงาน</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($summary['entries']) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">ชั่วโมงรวม</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($summary['hours'], 2) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">FTE รวม</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($summary['fte'], 6) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse ($tasks as $task)
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">
                                <a href="{{ route('back_office.show', $task->id) }}" class="text-blue-700 hover:underline">
                                    {{ $task->title }}
                                </a>
                            </h3>
                            <div class="text-sm text-slate-500">ช่วงเวลา {{ $formatDateBE($task->start_date) }} ถึง {{ $formatDateBE($task->end_date) }}</div>
                            @if ($task->description)
                                <div class="text-sm text-slate-500 mt-1">{{ $task->description }}</div>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('back_office.show', $task->id) }}"
                                class="px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-sm">รายละเอียด</a>
                            <a href="{{ route('back_office.edit', $task->id) }}"
                                class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 text-sm">แก้ไข</a>
                            <a href="{{ route('back_office.pdf', $task->id) }}" target="_blank" rel="noopener"
                                class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-sm">PDF</a>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs text-slate-500">ภาระงาน</div>
                            <div class="text-lg font-semibold text-slate-900">{{ number_format($task->entries_count) }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs text-slate-500">ผลงาน</div>
                            <div class="text-lg font-semibold text-slate-900">{{ number_format($task->outputs_count ?? 0) }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs text-slate-500">ชั่วโมงรวม</div>
                            <div class="text-lg font-semibold text-slate-900">{{ number_format($task->entries_sum_hours ?? 0, 2) }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs text-slate-500">FTE รวม</div>
                            <div class="text-lg font-semibold text-slate-900">{{ number_format($task->entries_sum_fte_value ?? 0, 6) }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="text-xs text-slate-500">ผู้บันทึก</div>
                            <div class="text-lg font-semibold text-slate-900">{{ $task->creator?->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border rounded-2xl p-8 text-center text-slate-400">ไม่พบรายการงาน</div>
            @endforelse
        </div>

        <div>
            {{ $tasks->links('vendor.pagination.th') }}
        </div>
    </div>
@endsection
