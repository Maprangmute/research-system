{{-- resources/views/projects/fte.blade.php --}}
@extends('layouts.app')

@section('content')
<div x-data="ftePage()" x-init="init()" class="max-w-[1200px] mx-auto p-4 md:p-6 space-y-5">

  {{-- Header + Summary Sticky --}}
  <div class="sticky top-0 z-20 -mx-4 md:-mx-6 px-4 md:px-6 py-3 bg-white/90 backdrop-blur border-b">
    <div class="flex flex-wrap items-center gap-3">
      <h1 class="text-lg md:text-xl font-bold text-gray-900">
        FTE ผู้มีส่วนร่วม — {{ $project->name }}
      </h1>

      <div class="ml-auto flex items-center gap-2 text-sm">
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-full">
          ทีมวิจัย: <strong x-text="sumResearch"></strong>
        </span>
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 rounded-full">
          ผู้บริหาร: <strong x-text="sumManagement"></strong>
        </span>
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-800 rounded-full">
          รวม: <strong x-text="sumTotal"></strong>
        </span>
      </div>
    </div>
  </div>

  {{-- Filters (optional) --}}
  <div class="flex flex-wrap items-center gap-3">
    <div class="relative">
      <input type="text" x-model="q" placeholder="ค้นหาชื่อ / ภารกิจ / กลุ่ม"
             class="w-72 max-w-full border rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
    </div>
    <div class="text-sm text-gray-500">รายการทั้งหมด: {{ $fteRoles->count() }}</div>
  </div>

  <form method="POST" action="{{ route('projects.fte.update', $project) }}" @submit="beforeSubmit">
    @csrf
    @method('PUT')

    {{-- 2 Columns --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      {{-- LEFT: Research --}}
      <section class="border rounded-xl bg-white shadow-sm">
        <header class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-blue-700 flex items-center gap-2">
            <span>👥 ทีมวิจัย</span>
            <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">แก้ได้เฉพาะเจ้าของ/แอดมิน</span>
          </h2>
        </header>

        <div class="max-h-[70vh] overflow-auto p-4 space-y-3">
          @forelse ($fteRoles as $fte)
            <div x-show="match('{{ $fte->id }}')" class="border rounded-lg p-3 hover:shadow-sm transition">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-medium text-gray-900 truncate">
                    {{ $fte->name }} <span class="text-gray-400">— {{ $fte->role_group }}</span>
                  </div>
                  <div class="text-sm text-gray-600 truncate">{{ $fte->task_detail }}</div>
                  <div class="text-xs text-gray-500">{{ $fte->start_date }} – {{ $fte->end_date }}</div>
                </div>
                @cannot('updateResearch', $fte)
                  <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded">read-only</span>
                @endcannot
              </div>

              <div class="mt-3 flex items-end gap-3">
                <div>
                  <label class="block text-sm text-gray-700 mb-1">FTE (ทีมวิจัย)</label>
                  @can('updateResearch', $fte)
                    <input type="number" step="0.01" min="0" max="1"
                           name="roles[{{ $fte->id }}][fte_value]"
                           value="{{ old("roles.$fte->id.fte_value", $fte->fte_value) }}"
                           x-on:input="recalc()"
                           class="w-28 border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  @else
                    <div class="w-28 text-sm font-medium text-gray-800">
                      {{ number_format($fte->fte_value ?? 0, 2) }}
                    </div>
                  @endcan
                </div>
              </div>
            </div>
          @empty
            <div class="text-sm text-gray-500 p-4">ยังไม่มีรายการ FTE</div>
          @endforelse
        </div>
      </section>

      {{-- RIGHT: Management (Admin) --}}
      <section class="border rounded-xl bg-white shadow-sm">
        <header class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-emerald-700 flex items-center gap-2">
            <span>🏛 ผู้บริหาร</span>
            <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">แก้ได้เฉพาะแอดมิน</span>
          </h2>
        </header>

        <div class="max-h-[70vh] overflow-auto p-4 space-y-3">
          @foreach ($fteRoles as $fte)
            <div x-show="match('{{ $fte->id }}')" class="border rounded-lg p-3 hover:shadow-sm transition">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-medium text-gray-900 truncate">
                    {{ $fte->name }} <span class="text-gray-400">— {{ $fte->role_group }}</span>
                  </div>
                  <div class="text-sm text-gray-600 truncate">{{ $fte->task_detail }}</div>
                </div>
                @cannot('updateManagement', $fte)
                  <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded">read-only</span>
                @endcannot
              </div>

              <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                <div>
                  <label class="block text-sm text-gray-700 mb-1">FTE (ผู้บริหาร)</label>
                  @can('updateManagement', $fte)
                    <input type="number" step="0.01" min="0" max="1"
                           name="roles[{{ $fte->id }}][fte_value_management]"
                           value="{{ old("roles.$fte->id.fte_value_management", $fte->fte_value_management) }}"
                           x-on:input="recalc()"
                           class="w-28 border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                  @else
                    <div class="w-28 text-sm font-medium text-gray-800">
                      {{ number_format($fte->fte_value_management ?? 0, 2) }}
                    </div>
                  @endcan
                </div>

                <div class="flex items-center gap-3 mt-1 sm:mt-6">
                  @can('updateManagement', $fte)
                    <input type="hidden" name="roles[{{ $fte->id }}][management_approved]" value="0">
                    <label class="inline-flex items-center gap-2">
                      <input type="checkbox" name="roles[{{ $fte->id }}][management_approved]" value="1"
                             @checked(old("roles.$fte->id.management_approved", $fte->management_approved))
                             x-on:change="recalc()"
                             class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                      <span class="text-sm text-gray-700">อนุมัติ</span>
                    </label>
                  @else
                    <span class="inline-flex items-center gap-2 text-sm">
                      สถานะ:
                      @if($fte->management_approved)
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full">อนุมัติแล้ว</span>
                      @else
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full">ยังไม่อนุมัติ</span>
                      @endif
                    </span>
                  @endcan
                </div>
              </div>

              @if($fte->approved_by)
                <div class="mt-2 text-xs text-gray-500">
                  อนุมัติโดย #{{ $fte->approved_by }} • {{ \Carbon\Carbon::parse($fte->approved_at)->format('Y-m-d H:i') }}
                </div>
              @endif
            </div>
          @endforeach
        </div>
      </section>

    </div>

    {{-- Sticky footer action --}}
    <div class="sticky bottom-0 z-20 mt-4 -mx-4 md:-mx-6 px-4 md:px-6 py-3 bg-white/90 backdrop-blur border-t">
      <div class="flex items-center gap-3">
        <div class="text-sm text-gray-600">
          รวมทั้งหมด: <span class="font-semibold" x-text="sumTotal"></span>
          <span class="mx-2 text-gray-300">|</span>
          <span class="text-blue-700">ทีมวิจัย</span> <span x-text="sumResearch"></span>
          <span class="mx-2 text-gray-300">/</span>
          <span class="text-emerald-700">ผู้บริหาร</span> <span x-text="sumManagement"></span>
        </div>
        <div class="ml-auto flex items-center gap-2">
          <button type="button" @click="resetFilter"
                  class="px-3 py-2 border rounded-lg text-sm">ล้างตัวกรอง</button>
          <button type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
            บันทึกทั้งหมด
          </button>
        </div>
      </div>
    </div>

  </form>
</div>

{{-- Alpine helpers --}}
<script>
function ftePage(){
  return {
    q: '',
    init(){ this.recalc(); },
    recalc(){
      // คำนวณรวมจาก input ปัจจุบันในหน้า
      const toNum = v => Math.max(0, Math.min(1, parseFloat(v)||0));
      const r = [...document.querySelectorAll('input[name*="[fte_value_research]"]')].reduce((s,el)=>s+toNum(el.value),0);
      const m = [...document.querySelectorAll('input[name*="[fte_value_management]"]')].reduce((s,el)=>s+toNum(el.value),0);
      this.sumResearch = r.toFixed(2);
      this.sumManagement = m.toFixed(2);
      this.sumTotal = (r+m).toFixed(2);
    },
    match(id){
      if(!this.q) return true;
      const row = document.querySelector(`[name="roles[${id}][fte_value_research]"]`)?.closest('.border');
      if(!row) return true;
      const txt = row.innerText.toLowerCase();
      return txt.includes(this.q.toLowerCase());
    },
    resetFilter(){ this.q=''; },
    sumResearch: '0.00',
    sumManagement: '0.00',
    sumTotal: '0.00',
  }
}
</script>
@endsection
