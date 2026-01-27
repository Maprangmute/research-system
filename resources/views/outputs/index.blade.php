@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 mt-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">🧾 รายการ Output</h2>
                <p class="text-sm text-slate-500">เรียงจากบันทึกล่าสุด → เก่าสุด และเชื่อมกลับไปยังโครงการ/งานนอกโครงการ</p>
            </div>
            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-sm text-slate-700 hover:bg-slate-50">
                ← กลับไปโครงการ
            </a>
        </div>

        {{-- Mobile search --}}
        <form method="GET" action="{{ route('outputs.index') }}"
            class="md:hidden bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-100 space-y-3">
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อผลงาน</span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full border border-slate-200 rounded-lg p-2"
                    placeholder="คำค้นในหัวข้อผลงาน">
            </label>
        </form>

        {{-- Mobile filters --}}
        <details class="md:hidden bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-100">
            <summary class="text-sm font-semibold text-slate-700 cursor-pointer">ตัวกรองข้อมูล</summary>
            <form method="GET" action="{{ route('outputs.index') }}" class="space-y-3 mt-4">
                <div class="grid grid-cols-1 gap-3">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน</span>
                        <select name="type" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($typeOptions as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                        <select name="year" class="w-full border rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">โครงการ</span>
                        <select name="project_id" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($projectOptions as $proj)
                                <option value="{{ $proj->id }}" @selected(request('project_id') == $proj->id)>
                                    {{ $proj->project_name_th }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                        <select name="sort" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="created_at" @selected(request('sort') === 'created_at')>บันทึกล่าสุด</option>
                            <option value="year" @selected(request('sort') === 'year')>ปี</option>
                            <option value="type" @selected(request('sort') === 'type')>ประเภท</option>
                            <option value="title" @selected(request('sort') === 'title')>ชื่อผลงาน</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                        <select name="dir" class="w-full border border-slate-200 rounded-lg p-2">
                            <option value="desc" @selected(request('dir', 'desc') === 'desc')>มาก → น้อย</option>
                            <option value="asc" @selected(request('dir') === 'asc')>น้อย → มาก</option>
                        </select>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm">
                        ใช้ตัวกรอง
                    </button>
                    @if (request()->query())
                        <a href="{{ route('outputs.index') }}"
                            class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">
                            ล้าง
                        </a>
                    @endif
                </div>
            </form>
        </details>

        {{-- Desktop filters --}}
        <form method="GET" action="{{ route('outputs.index') }}"
            class="hidden md:block bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-100 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ค้นหาชื่อผลงาน</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full border border-slate-200 rounded-lg p-2"
                        placeholder="คำค้นในหัวข้อผลงาน">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ประเภทผลงาน</span>
                    <select name="type" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($typeOptions as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select name="year" class="w-full border rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">โครงการ</span>
                    <select name="project_id" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="">ทั้งหมด</option>
                        @foreach ($projectOptions as $proj)
                            <option value="{{ $proj->id }}" @selected(request('project_id') == $proj->id)>
                                {{ $proj->project_name_th }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับ</span>
                    <select name="sort" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="created_at" @selected(request('sort') === 'created_at')>บันทึกล่าสุด</option>
                        <option value="year" @selected(request('sort') === 'year')>ปี</option>
                        <option value="type" @selected(request('sort') === 'type')>ประเภท</option>
                        <option value="title" @selected(request('sort') === 'title')>ชื่อผลงาน</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ทิศทาง</span>
                    <select name="dir" class="w-full border border-slate-200 rounded-lg p-2">
                        <option value="desc" @selected(request('dir', 'desc') === 'desc')>มาก → น้อย</option>
                        <option value="asc" @selected(request('dir') === 'asc')>น้อย → มาก</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm">
                        ใช้ตัวกรอง
                    </button>
                    @if (request()->query())
                        <a href="{{ route('outputs.index') }}"
                            class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">
                            ล้าง
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="bg-white rounded-2xl shadow-md ring-1 ring-slate-100 overflow-hidden">
            <div class="px-4 py-3 border-b bg-slate-50 text-sm text-slate-600">
                แสดงผล {{ $outputs->firstItem() ?? 0 }}–{{ $outputs->lastItem() ?? 0 }} จากทั้งหมด
                {{ $outputs->total() }} รายการ
            </div>
            {{-- Mobile cards --}}
            <div class="md:hidden">
                <ul class="divide-y">
                    @forelse ($outputs as $output)
                        <li class="p-4 space-y-2">
                            <div class="text-slate-900 font-semibold">{{ $output->title ?: '-' }}</div>
                            <div class="text-sm text-slate-600">
                                ประเภท: <span class="text-slate-800">{{ $output->type ?: '-' }}</span>
                            </div>
                            <div class="text-sm text-slate-600">ปี: {{ $output->year_be ?: '-' }}</div>
                            <div class="text-sm text-slate-600">
                                โครงการ:
                                @if ($output->link && $output->project_label)
                                    <a href="{{ $output->link }}" class="text-indigo-700 hover:underline">
                                        {{ $output->project_label }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                            @if (!empty($output->detail))
                                <div class="text-xs text-slate-500">ภาระงาน: {{ $output->detail }}</div>
                            @endif
                            <div class="text-xs text-slate-500">
                                @php
                                    $created = $output->created_at ? \Carbon\Carbon::parse($output->created_at) : null;
                                @endphp
                                วันที่ลงข้อมูล: {{ $created ? $created->locale('th')->translatedFormat('j F') . ' ' . ($created->year + 543) : '-' }}
                            </div>
                        </li>
                    @empty
                        <li class="text-center py-10 text-slate-500">ไม่มีข้อมูล</li>
                    @endforelse
                </ul>
            </div>

            {{-- Desktop table --}}
            <div class="hidden md:block overflow-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-3 text-left">ชื่อผลงาน</th>
                            <th class="px-4 py-3 text-left">ประเภท</th>
                            <th class="px-4 py-3 text-center">ปี (พ.ศ.)</th>
                            <th class="px-4 py-3 text-left">โครงการ/งานนอกโครงการ</th>
                            <th class="px-4 py-3 text-center">วันที่ลงข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($outputs as $output)
                            <tr class="hover:bg-indigo-50/40 transition">
                                <td class="px-4 py-3 text-slate-800">
                                    {{ $output->title ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $output->type ?: '-' }}</td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ $output->year_be ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($output->link && $output->project_label)
                                        <a href="{{ $output->link }}" class="text-indigo-700 hover:underline">
                                            {{ $output->project_label }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                    @if (!empty($output->detail))
                                        <div class="text-xs text-slate-500 mt-1">ภาระงาน: {{ $output->detail }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-slate-500">
                                    @php
                                        $created = $output->created_at ? \Carbon\Carbon::parse($output->created_at) : null;
                                    @endphp
                                    {{ $created ? $created->locale('th')->translatedFormat('j F') . ' ' . ($created->year + 543) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-slate-500">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t bg-slate-50">
                {{ $outputs->links('vendor.pagination.th') }}
            </div>
        </div>
    </div>
@endsection
