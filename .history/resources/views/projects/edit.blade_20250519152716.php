@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        toast: true,
        position: 'bottom-start',
        icon: 'success',
        title: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        title: 'เกิดข้อผิดพลาด',
        text: '{{ session('error') }}'
    });
</script>
@endif

<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการ</h2>

    <form action="{{ route('projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label>ชื่อโครงการ (ภาษาไทย)*
                <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}" required class="w-full border p-2 rounded">
            </label>
            <label>ชื่อโครงการ (English)
                <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}" class="w-full border p-2 rounded">
            </label>
            <label>ประเภทของโครงการ*
                <input name="project_type" value="{{ old('project_type', $project->project_type) }}" required class="w-full border p-2 rounded">
            </label>
            <label>กลุ่มเนื้อหา (Theme)*
                <input name="theme" value="{{ old('theme', $project->theme) }}" required class="w-full border p-2 rounded">
            </label>
            <label>Shortcut / Google Drive
                <input name="shortcut_link" value="{{ old('shortcut_link', $project->shortcut_link) }}" class="w-full border p-2 rounded">
            </label>
            <label>ขนาดโครงการ
                <input name="size" value="{{ old('size', $project->size) }}" class="w-full border p-2 rounded">
            </label>
            <label>ประเภทแหล่งทุน
                <input name="funding_type" value="{{ old('funding_type', $project->funding_type) }}" class="w-full border p-2 rounded">
            </label>
            <label>ชื่อแหล่งทุน
                <input name="funding_source" value="{{ old('funding_source', $project->funding_source) }}" class="w-full border p-2 rounded">
            </label>
            <label>ประเภทองค์กร
                <input name="funding_org_type" value="{{ old('funding_org_type', $project->funding_org_type) }}" class="w-full border p-2 rounded">
            </label>
            <label>ชื่อทุน
                <input name="funding_name" value="{{ old('funding_name', $project->funding_name) }}" class="w-full border p-2 rounded">
            </label>
            <label>งบประมาณ
                <input name="budget" type="number" step="0.01" value="{{ old('budget', $project->budget) }}" class="w-full border p-2 rounded">
            </label>
            <label>เดือนเริ่มต้น
                <input name="start_month" value="{{ old('start_month', $project->start_month) }}" class="w-full border p-2 rounded">
            </label>
            <label>ปีเริ่มต้น
                <input name="start_year" value="{{ old('start_year', $project->start_year) }}" class="w-full border p-2 rounded">
            </label>
            <label>เดือนสิ้นสุด
                <input name="end_month" value="{{ old('end_month', $project->end_month) }}" class="w-full border p-2 rounded">
            </label>
            <label>ปีสิ้นสุด
                <input name="end_year" value="{{ old('end_year', $project->end_year) }}" class="w-full border p-2 rounded">
            </label>
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">รายชื่อผู้ร่วมวิจัย (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            @foreach($project->fteRoles as $i => $fte)
                <div class="grid grid-cols-3 gap-4 items-center" id="fte-{{ $i }}">
                    <input name="fte_roles[{{ $i }}][role_group]" value="{{ $fte->role_group }}" class="w-full border p-2 rounded" placeholder="กลุ่ม">
                    <input name="fte_roles[{{ $i }}][name]" value="{{ $fte->name }}" class="w-full border p-2 rounded" placeholder="ชื่อ">
                    <input name="fte_roles[{{ $i }}][fte_value]" value="{{ $fte->fte_value }}" class="w-full border p-2 rounded" placeholder="FTE">
                </div>
            @endforeach
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">ผลงานวิจัย (Outputs)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            @foreach($project->outputs as $i => $output)
                <div class="grid grid-cols-3 gap-4 items-center" id="output-{{ $i }}">
                    <input name="outputs[{{ $i }}][type]" value="{{ $output->type }}" class="w-full border p-2 rounded" placeholder="ประเภท">
                    <input name="outputs[{{ $i }}][title]" value="{{ $output->title }}" class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
                    <input name="outputs[{{ $i }}][year]" value="{{ $output->year }}" class="w-full border p-2 rounded" placeholder="ปี">
                </div>
            @endforeach
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            @foreach ($project->irbs as $i => $irb)
                <div class="grid grid-cols-2 gap-2">
                    <input name="irbs[{{ $i }}][code]" value="{{ $irb->code }}" class="w-full border p-2 rounded" placeholder="ECxxxx">
                    <input name="irbs[{{ $i }}][source]" value="{{ $irb->source }}" class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงาน">
                </div>
            @endforeach
        </div>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">รายชื่อผู้ร่วมวิจัย (COPI)</h3>
        <div id="copi-container" class="space-y-2 mb-4">
            @foreach ($project->copis as $i => $copi)
                <div class="grid grid-cols-2 gap-2 items-center" id="copi-{{ $i }}">
                    <input name="copis[{{ $i }}][name]" value="{{ $copi->name }}" class="w-full border p-2 rounded" placeholder="ชื่อ COPI" />
                    <div class="flex gap-2">
                        <input name="copis[{{ $i }}][affiliation]" value="{{ $copi->affiliation }}" class="w-full border p-2 rounded" placeholder="สังกัด COPI" />
                        <button type="button" onclick="removeRow('copi-{{ $i }}')" class="text-red-600 mt-2">✕</button>
                    </div>
                </div>
            @endforeach
        </div>

       <!-- ยุทธศาสตร์ Siriraj Health Policy -->
       <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">ยุทธศาสตร์ Siriraj Health Policy</h3>
       <table class="w-full table-auto text-sm mb-6">
           <thead>
               <tr class="bg-gray-100">
                   <th class="px-3 py-2 text-left">หมวด</th>
                   <th class="px-3 py-2 text-left">รายการ</th>
                   <th class="px-3 py-2 text-center">มี</th>
               </tr>
           </thead>
           <tbody>
               @php
                   $strategies = [
                       '1' => [
                           'label' => 'AA501: Capacity Building',
                           'items' => [
                               '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                               '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                               '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                               '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                               '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                           ]
                       ],
                       '2' => [
                           'label' => 'AA502: Excellence Research',
                           'items' => [
                               '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                               '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                               '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                           ]
                       ],
                       '3' => [
                           'label' => 'AA503: Health Policy Linkage',
                           'items' => [
                               '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                               '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                               '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                               '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                               '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                           ]
                       ],
                       '4' => [
                           'label' => 'AA504: Partnership Engagement',
                           'items' => [
                               '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                               '2' => '4.2 การสร้างความร่วมมือฯ',
                           ]
                       ],
                       '5' => [
                           'label' => 'AA505: System Support',
                           'items' => [
                               '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                               '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                           ]
                       ],
                   ];
               @endphp

               @foreach($strategies as $sKey => $strategy)
                   <tr class="border-t">
                       <td class="px-3 py-2 font-bold" rowspan="{{ count($strategy['items']) + 1 }}">{{ $strategy['label'] }}</td>
                   </tr>
                   @foreach($strategy['items'] as $iKey => $desc)
                       @php $field = "strategy_{$sKey}_{$iKey}"; @endphp
                       <tr class="border-t">
                           <td class="px-3 py-2">{{ $desc }}</td>
                           <td class="px-3 py-2 text-center">
                               <input type="checkbox" name="{{ $field }}" class="mr-1" {{ $project->$field ? 'checked' : '' }}>
                           </td>
                       </tr>
                   @endforeach
               @endforeach
           </tbody>
       </table>


        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">📅 บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<script>
    function removeRow(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }
</script>
@endsection
