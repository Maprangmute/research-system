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
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <a href="{{ route('back_office.index') }}" class="text-blue-700 text-sm hover:underline">← กลับไปยังรายการงาน</a>
                    <h2 class="text-2xl font-semibold text-slate-900 mt-2">{{ $task->title }}</h2>
                    <div class="text-sm text-slate-500">ช่วงเวลา {{ $formatDateBE($task->start_date) }} ถึง {{ $formatDateBE($task->end_date) }}</div>
                    @if ($task->description)
                        <div class="text-sm text-slate-500 mt-1">{{ $task->description }}</div>
                    @endif
                    @if ($task->purpose)
                        <div class="text-sm text-slate-600 mt-2">
                            <span class="font-semibold text-slate-700">จุดประสงค์:</span> {{ $task->purpose }}
                        </div>
                    @endif
                    <div class="text-sm text-slate-600 mt-1">
                        <span class="font-semibold text-slate-700">หัวหน้างานผู้รับผิดชอบ:</span>
                        {{ !empty($task->leaders) ? implode(', ', $task->leaders) : '-' }}
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('back_office.edit', $task->id) }}"
                        class="px-4 py-2 rounded-lg bg-amber-100 text-amber-800 text-sm">แก้ไข</a>
                    <a href="{{ route('back_office.pdf', $task->id) }}" target="_blank" rel="noopener"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm">🖨️ PDF</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">ผู้บันทึก</div>
                <div class="text-2xl font-semibold text-slate-900">{{ $task->creator?->name ?? '-' }}</div>
            </div>
        </div>

        <div class="space-y-4">
            @if ($task->outputs->count())
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 mb-2">ผลงานของงานนอกโครงการ</div>
                    <div class="space-y-2 text-sm">
                        @foreach ($task->outputs as $output)
                            <div class="border rounded-xl p-3 bg-slate-50">
                                <div class="font-semibold text-slate-800">{{ $output->title }}</div>
                                <div class="text-slate-600">
                                    วันที่จัดส่ง: {{ $output->delivered_at ? $formatDateBE($output->delivered_at) : '-' }}
                                </div>
                                @if ($output->description)
                                    <div class="text-slate-600">รายละเอียดเพิ่มเติม: {{ $output->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @forelse ($task->entries as $entry)
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="text-sm text-slate-500">ผู้รับผิดชอบ</div>
                            <div class="text-lg font-semibold text-slate-900">{{ $entry->person_name }}</div>
                            <div class="text-sm text-slate-500">ประเภท: {{ $entry->category }}</div>
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
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">รายละเอียดงาน</div>
                            <div class="text-slate-800 whitespace-pre-wrap">{{ $entry->job_detail }}</div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <div class="text-xs font-semibold text-slate-500">ลิงก์เอกสาร</div>
                                @if ($entry->document_link)
                                    <a href="{{ $entry->document_link }}" class="text-blue-700 underline" target="_blank" rel="noopener">
                                        {{ $entry->document_link }}
                                    </a>
                                @else
                                    <div class="text-slate-400">-</div>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="text-xs font-semibold text-slate-500">วันที่เริ่ม</div>
                                    <div class="text-slate-800">{{ $formatDateBE($entry->start_date) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-slate-500">วันที่สิ้นสุด</div>
                                    <div class="text-slate-800">{{ $formatDateBE($entry->end_date) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($entry->outputs->count())
                        <div class="mt-4 border-t pt-3 text-sm">
                            <div class="text-xs font-semibold text-slate-500">ผลงานของภาระงาน</div>
                            <div class="space-y-2 mt-2">
                                @foreach ($entry->outputs as $output)
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <div class="font-semibold text-slate-800">{{ $output->title }}</div>
                                        <div class="text-slate-600">
                                            วันที่จัดส่ง: {{ $output->delivered_at ? $formatDateBE($output->delivered_at) : '-' }}
                                        </div>
                                        @if ($output->description)
                                            <div class="text-slate-600">รายละเอียดเพิ่มเติม: {{ $output->description }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white border rounded-2xl p-8 text-center text-slate-400">ไม่มีข้อมูลภาระงาน</div>
            @endforelse
        </div>
    </div>
@endsection
