@extends('layouts.app')

@section('content')
{{-- libs --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<style>
  body { background:#f3f4f6; }

  .dash-bg{
    min-height:100vh;
    background:linear-gradient(180deg,#f9fafb,#f3f4f6);
  }

  .widget-card{
    background:#fff;
    isolation:isolate;
  }

  .grab{cursor:grab}
  .grab:active{cursor:grabbing}
</style>

<div class="dash-bg"
     x-data="rmDash()"
     x-init="boot()">

  <div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">🤖 AI Research Dashboard</h1>
        <p class="text-sm text-gray-500">
          RED ZONE • 18 กราฟ • Drag / Collapse • AI-driven Visualization
        </p>
      </div>
      <div class="flex gap-2">
        <button @click="resetLayout()"
          class="px-3 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm">
          ♻️ รีเซ็ต Layout
        </button>
        <button @click="refresh()"
          class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">
          🔄 รีเฟรช
        </button>
      </div>
    </div>

    {{-- Ask AI --}}
    <div class="bg-white border rounded-2xl p-4 shadow-sm mb-6">
      <div class="flex justify-between items-center">
        <div class="font-semibold">ถาม AI</div>
        <div class="text-xs text-gray-500" x-text="modeHint"></div>
      </div>

      <div class="grid md:grid-cols-12 gap-3 mt-3">
        <textarea x-model="prompt" rows="3"
          class="md:col-span-9 w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200"
          placeholder="auto / RED ZONE / งบปีล่าสุด / outputs / สถานะโครงการ"></textarea>

        <div class="md:col-span-3 flex md:flex-col gap-2">
          <button @click="ask()"
            class="flex-1 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
            <span x-show="!loading">ส่ง</span>
            <span x-show="loading">กำลังประมวลผล…</span>
          </button>
          <button @click="prompt='auto'; ask()"
            class="flex-1 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50">
            Auto
          </button>
        </div>
      </div>

      <template x-if="error">
        <div class="mt-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl p-3"
             x-text="error"></div>
      </template>
    </div>

    {{-- Insights --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <template x-for="(i,idx) in insights" :key="idx">
        <div class="border rounded-xl p-4"
          :class="{
            'bg-red-50 border-red-300': i.level==='danger',
            'bg-yellow-50 border-yellow-300': i.level==='warning',
            'bg-blue-50 border-blue-300': i.level==='info'
          }">
          <div class="font-semibold" x-text="i.title"></div>
          <div class="text-sm mt-1" x-text="i.message"></div>
        </div>
      </template>
    </div>

    {{-- Reply --}}
    <div class="bg-white border rounded-2xl p-4 shadow-sm mb-6">
      <div class="flex justify-between items-center">
        <div class="font-semibold">คำตอบจาก AI</div>
        <div class="text-xs text-gray-500" x-text="lastUpdated"></div>
      </div>
      <div class="mt-3 prose max-w-none" x-html="reply"></div>
    </div>

    {{-- Widgets --}}
    <div class="flex justify-between text-xs text-gray-500 mb-2">
      <div>ลากเพื่อจัดตำแหน่ง • คลิกหัวการ์ดเพื่อพับ</div>
      <div>layout saved</div>
    </div>

    <div id="widgetGrid"
         class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">

      <template x-for="w in widgets" :key="w.id">
        <div class="widget-card border rounded-2xl shadow-sm"
             :data-id="w.id">

          {{-- header --}}
          <div class="px-4 py-3 border-b flex justify-between items-center grab"
               @click="toggle(w.id)">
            <div class="min-w-0">
              <div class="font-semibold truncate" x-text="w.title"></div>
              <div class="text-xs text-gray-500" x-show="w.note" x-text="w.note"></div>
            </div>
            <div class="text-xs text-gray-400 flex gap-2">
              <span class="px-2 py-0.5 rounded-full border bg-gray-50" x-text="w.kind"></span>
              <span x-text="collapsed[w.id] ? '▾' : '▴'"></span>
            </div>
          </div>

          {{-- body --}}
          <div x-collapse x-show="!collapsed[w.id]" class="p-4">

            {{-- Gauge --}}
            <template x-if="w.kind==='gauge'">
              <div>
                <div class="flex justify-between items-end">
                  <div class="text-sm text-gray-600" x-text="w.payload.subtitle"></div>
                  <div class="text-2xl font-bold" x-text="w.payload.value+'%'"></div>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden mt-2">
                  <div class="h-3 rounded-full"
                       :style="`width:${w.payload.value}%`"
                       :class="w.payload.value>=30?'bg-red-500':(w.payload.value>=15?'bg-yellow-500':'bg-green-500')">
                  </div>
                </div>
              </div>
            </template>

            {{-- Timeline --}}
            <template x-if="w.kind==='timeline'">
              <div class="space-y-2">
                <template x-for="(v,k) in w.payload.buckets" :key="k">
                  <div>
                    <div class="flex justify-between text-xs">
                      <span x-text="k"></span><span x-text="v"></span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded overflow-hidden">
                      <div class="h-2"
                        :style="`width:${barPct(w.payload.buckets,v)}%`"
                        :class="k==='Overdue'?'bg-red-500':'bg-blue-500'">
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </template>

            {{-- Chart --}}
            <template x-if="w.kind==='chart'">
              <canvas :id="'cv_'+w.id" height="150"></canvas>
            </template>

            {{-- Table --}}
            <template x-if="w.kind==='table'">
              <div class="overflow-x-auto rounded-xl border">
                <table class="min-w-full text-sm">
                  <thead class="bg-gray-100">
                    <tr>
                      <template x-for="h in w.payload.headers">
                        <th class="px-3 py-2 text-left font-semibold" x-text="h"></th>
                      </template>
                    </tr>
                  </thead>
                  <tbody>
                    <template x-for="row in w.payload.rows">
                      <tr class="border-t hover:bg-gray-50">
                        <template x-for="f in w.payload.fields">
                          <td class="px-3 py-2" x-text="row[f] ?? '-'"></td>
                        </template>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </template>

          </div>
        </div>
      </template>

    </div>
  </div>
</div>

<script>
function rmDash(){
  return {
    prompt:'auto',
    loading:false,
    error:'',
    reply:'',
    modeHint:'',
    lastUpdated:'',
    insights:[],
    widgets:[],
    collapsed:{},
    charts:{},
    sortable:null,

    boot(){
      this.ask();
      this.initSortable();
    },

    refresh(){ this.ask(); },

    ask(){
      this.loading=true; this.error='';
      fetch('/rm/ai-dashboard/analyze',{
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({prompt:this.prompt})
      })
      .then(r=>r.json())
      .then(res=>{
        this.reply=res.reply||'';
        this.insights=res.insights||[];
        this.widgets=res.widgets||[];
        this.lastUpdated=new Date().toLocaleString('th-TH');
        this.$nextTick(()=>this.renderCharts());
      })
      .catch(e=>this.error=e.message||'error')
      .finally(()=>this.loading=false);
    },

    toggle(id){
      this.collapsed[id]=!this.collapsed[id];
      this.$nextTick(()=>this.renderCharts());
    },

    renderCharts(){
      Object.values(this.charts).forEach(c=>{try{c.destroy()}catch(e){}});
      this.charts={};

      this.widgets.forEach(w=>{
        if(w.kind!=='chart' || this.collapsed[w.id]) return;
        const el=document.getElementById('cv_'+w.id);
        if(!el) return;
        this.charts[w.id]=new Chart(el.getContext('2d'),w.payload);
      });
    },

    initSortable(){
      const grid=document.getElementById('widgetGrid');
      if(!grid) return;
      this.sortable=new Sortable(grid,{
        handle:'.grab',
        animation:180
      });
    },

    resetLayout(){
      this.widgets=[...this.widgets];
    },

    barPct(b,v){
      const s=Object.values(b).reduce((a,c)=>a+Number(c||0),0)||1;
      return Math.round((v/s)*100);
    }
  }
}
</script>
@endsection
