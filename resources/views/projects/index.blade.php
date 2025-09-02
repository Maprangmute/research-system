@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'error',
                    title: @json(session('error')),
                    showConfirmButton: false,
                    timer: 7000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    <div class="container mx-auto px-4 mt-11 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">📊 รายการโครงการวิจัย</h2>
            </div>
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 shadow-sm hover:shadow transition">
                ➕ เพิ่มโครงการ
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('projects.index') }}"
              class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-slate-100 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="search" placeholder="ค้นหาชื่อโครงการ" value="{{ request('search') }}"
                   class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
            <input type="text" name="type" placeholder="ประเภทโครงการ" value="{{ request('type') }}"
                   class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
            <input type="text" name="year" placeholder="ปี (พ.ศ.)" value="{{ request('year') }}"
                   class="w-full border border-slate-200 focus:border-indigo-300 focus:ring-indigo-200 focus:ring-2 p-2 rounded-lg">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 shadow-sm">🔍 ค้นหา</button>
                @if(request()->hasAny(['search','type','year','sort','dir']))
                    <a href="{{ route('projects.index') }}"
                       class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">ล้าง</a>
                @endif
            </div>
        </form>

        {{-- Helpers: sort + color badge --}}
        @php
            $currentSort = request('sort','id');
            $currentDir  = request('dir','desc');
            $toggleDir   = fn($field) => ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';
            $sortUrl = function($field) use ($toggleDir) {
                return request()->fullUrlWithQuery(['sort'=>$field,'dir'=>$toggleDir($field),'page'=>1]);
            };
            $sortIcon = function($field) use ($currentSort,$currentDir) {
                if ($currentSort !== $field) return '↕';
                return $currentDir === 'asc' ? '↑' : '↓';
            };

            // แผนที่สี badge ตามประเภท (ถ้าไม่พบ ใช้สีกลาง)
            function type_badge_classes($type) {
                $t = trim(strtolower((string)$type));
                $map = [
                    'clinical'   => 'bg-pink-100 text-pink-800',
                    'policy'     => 'bg-amber-100 text-amber-800',
                    'education'  => 'bg-sky-100 text-sky-800',
                    'innovation' => 'bg-emerald-100 text-emerald-800',
                    'public health' => 'bg-violet-100 text-violet-800',
                    'survey'     => 'bg-indigo-100 text-indigo-800',
                    'basic'      => 'bg-rose-100 text-rose-800',
                    'ai'         => 'bg-fuchsia-100 text-fuchsia-800',
                    'it'         => 'bg-cyan-100 text-cyan-800',
                ];
                foreach ($map as $key => $cls) {
                    if (str_contains($t, $key)) return $cls;
                }
                return 'bg-slate-100 text-slate-800';
            }
        @endphp

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-md ring-1 ring-slate-100 overflow-hidden">
            <div class="px-4 py-3 border-b bg-slate-50 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    แสดงผล {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} จากทั้งหมด {{ $projects->total() }} รายการ
                </div>
                <div class="text-xs text-slate-400">ดับเบิลคลิก = ดูรายละเอียด • คลิกขวา/กดค้าง = เมนูด่วน</div>
            </div>

            <div class="overflow-auto">
                <table class="min-w-full">
                    <colgroup>
                        <col style="width: 72px;">   {{-- ID สั้น ๆ --}}
                        <col style="width: 140px;">  {{-- Project ID (โค้ด) --}}
                        <col style="width: auto;">   {{-- ชื่อโครงการ ยืดได้ --}}
                        <col style="width: 180px;">  {{-- ประเภท: badge --}}
                        <col style="width: 180px;">  {{-- ทุน --}}
                        <col style="width: 220px;">  {{-- Theme --}}
                        <col style="width: 88px;">   {{-- ปี --}}
                    </colgroup>
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold text-slate-600">
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('id') }}" class="hover:text-indigo-700">ID <span class="ml-1">{{ $sortIcon('id') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_code') }}" class="hover:text-indigo-700">Project ID <span class="ml-1">{{ $sortIcon('project_code') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_name_th') }}" class="hover:text-indigo-700">ชื่อโครงการ <span class="ml-1">{{ $sortIcon('project_name_th') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('project_type') }}" class="hover:text-indigo-700">ประเภท <span class="ml-1">{{ $sortIcon('project_type') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('funding_type') }}" class="hover:text-indigo-700">ทุน <span class="ml-1">{{ $sortIcon('funding_type') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('theme') }}" class="hover:text-indigo-700">Theme <span class="ml-1">{{ $sortIcon('theme') }}</span></a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ $sortUrl('start_year') }}" class="hover:text-indigo-700">ปี <span class="ml-1">{{ $sortIcon('start_year') }}</span></a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($projects as $project)
                            @php
                                $showUrl     = route('projects.show', $project->id);
                                $editUrl     = route('projects.edit', $project->id);
                                $pdfUrl      = route('projects.pdf', $project->id);
                                $progressUrl = route('projects.progress_reports.index', $project->id);
                                $deleteFormId= "delete-form-{$project->id}";
                            @endphp
                            <tr class="hover:bg-indigo-50/40 transition cursor-default"
                                data-show-url="{{ $showUrl }}"
                                data-edit-url="{{ $editUrl }}"
                                data-pdf-url="{{ $pdfUrl }}"
                                data-progress-url="{{ $progressUrl }}"
                                data-delete-form-id="{{ $deleteFormId }}"
                                oncontextmenu="return openRowMenu(event, this)"
                                ondblclick="goToDetail(this)"
                                ontouchstart="rowLongPressStart(event, this)"
                                ontouchend="rowLongPressEnd(event)"
                            >
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $project->id }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-slate-700">
                                    <a href="{{ $showUrl }}" class="hover:underline">{{ $project->project_code }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ $showUrl }}" class="text-slate-900 hover:text-indigo-700 font-medium break-words">
                                        {{ $project->project_name_th }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ type_badge_classes($project->project_type) }}">
                                        {{ $project->project_type ?: '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $project->funding_type ?: '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-normal break-words text-slate-700">
                                    {{ $project->theme ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $project->start_year ?: '-' }}</td>

                                {{-- hidden form for delete --}}
                                <td class="hidden">
                                    <form id="{{ $deleteFormId }}" action="{{ route('projects.destroy', $project->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-10 text-slate-500">ไม่มีข้อมูล</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t bg-slate-50">
                {{ $projects->links() }}
            </div>
        </div>
    </div>

    {{-- Context Menu --}}
    <div id="row-menu"
         class="hidden fixed z-50 min-w-[200px] rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5">
        <div class="py-1 text-sm">
            <button type="button" data-action="show"     class="w-full text-left px-3 py-2 hover:bg-indigo-50">🔎 ดูรายละเอียด</button>
            <button type="button" data-action="edit"     class="w-full text-left px-3 py-2 hover:bg-indigo-50">✏️ แก้ไข</button>
            <button type="button" data-action="pdf"      class="w-full text-left px-3 py-2 hover:bg-green-50 text-green-700">🖨️ พิมพ์ PDF</button>
            <button type="button" data-action="progress" class="w-full text-left px-3 py-2 hover:bg-sky-50 text-sky-700">📑 Progress Report</button>
            <div class="my-1 border-t border-slate-100"></div>
            <button type="button" data-action="delete"   class="w-full text-left px-3 py-2 hover:bg-red-50 text-red-600">🗑️ ลบข้อมูล</button>
        </div>
    </div>

    <script>
        function goToDetail(row){ const url=row.getAttribute('data-show-url'); if(url) location.href=url; }

        function openRowMenu(e,row){
            e.preventDefault();
            const menu=document.getElementById('row-menu'); if(!menu) return;

            menu.dataset.showUrl     = row.getAttribute('data-show-url')||'';
            menu.dataset.editUrl     = row.getAttribute('data-edit-url')||'';
            menu.dataset.pdfUrl      = row.getAttribute('data-pdf-url')||'';
            menu.dataset.progressUrl = row.getAttribute('data-progress-url')||'';
            menu.dataset.deleteFormId= row.getAttribute('data-delete-form-id')||'';

            const vw=innerWidth, vh=innerHeight, rect={w:220,h:200};
            let x=e.clientX, y=e.clientY;
            if(x+rect.w>vw) x=vw-rect.w-8;
            if(y+rect.h>vh) y=vh-rect.h-8;
            menu.style.left=`${x}px`; menu.style.top=`${y}px`;
            menu.classList.remove('hidden');

            setTimeout(()=>{ document.addEventListener('click', clickAwayClose, {once:true});
                              addEventListener('scroll', hideMenu, {once:true});
                              addEventListener('resize', hideMenu, {once:true});
                              document.addEventListener('keydown', escClose, {once:true});},0);
            return false;
        }
        function hideMenu(){ const m=document.getElementById('row-menu'); if(m) m.classList.add('hidden'); }
        function clickAwayClose(ev){ const m=document.getElementById('row-menu'); if(m && !m.contains(ev.target)) hideMenu(); }
        function escClose(ev){ if(ev.key==='Escape') hideMenu(); }

        (function bindMenu(){
            const m=document.getElementById('row-menu'); if(!m) return;
            m.addEventListener('click',(ev)=>{
                const btn=ev.target.closest('button[data-action]'); if(!btn) return;
                const act=btn.dataset.action, showUrl=m.dataset.showUrl, editUrl=m.dataset.editUrl,
                      pdfUrl=m.dataset.pdfUrl, progressUrl=m.dataset.progressUrl, delId=m.dataset.deleteFormId;
                hideMenu();
                switch(act){
                    case 'show': if(showUrl) location.href=showUrl; break;
                    case 'edit': if(editUrl) location.href=editUrl; break;
                    case 'pdf': if(pdfUrl) window.open(pdfUrl,'_blank'); break;
                    case 'progress': if(progressUrl) location.href=progressUrl; break;
                    case 'delete':
                        const form=document.getElementById(delId); if(!form) return;
                        Swal.fire({
                            title:'คุณแน่ใจหรือไม่?', text:'การลบไม่สามารถย้อนกลับได้',
                            icon:'warning', showCancelButton:true,
                            confirmButtonColor:'#e3342f', cancelButtonColor:'#6c757d',
                            confirmButtonText:'ใช่, ลบเลย', cancelButtonText:'ยกเลิก'
                        }).then(r=>{ if(r.isConfirmed) form.submit(); });
                        break;
                }
            });
        })();

        // mobile long press
        let longPressTimer=null;
        function rowLongPressStart(e,row){
            if(e.touches && e.touches.length>1) return;
            longPressTimer=setTimeout(()=>{
                const t=e.touches?e.touches[0]:null;
                openRowMenu({preventDefault:()=>{}, clientX:t?t.clientX:20, clientY:t?t.clientY:20}, row);
            },500);
        }
        function rowLongPressEnd(){ if(longPressTimer){ clearTimeout(longPressTimer); longPressTimer=null; } }
    </script>
@endsection
