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

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            @foreach ($project->irbs as $i => $irb)
                <div class="grid grid-cols-2 gap-2">
                    <input name="irbs[{{ $i }}][code]" value="{{ $irb->code }}" class="w-full border p-2 rounded" placeholder="ECxxxx">
                    <input name="irbs[{{ $i }}][source]" value="{{ $irb->source }}" class="w-full border p-2 rounded" placeholder="ชื่อหน่วยงาน">
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addIRBRow()" class="mb-4 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่ม IRB</button>

        <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">ผู้ร่วมวิจัย (COPI)</h3>
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
        <button type="button" onclick="addCopiRow()" class="mb-4 px-3 py-1 bg-yellow-600 text-white rounded">+ เพิ่ม COPI</button>

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

    let copiIndex = {{ count($project->copis ?? []) }};
    function addCopiRow() {
        const container = document.getElementById('copi-container');
        const id = `copi-${copiIndex}`;
        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="grid grid-cols-2 gap-2 items-center">
                <input name="copis[${copiIndex}][name]" class="w-full border p-2 rounded" placeholder="ชื่อ COPI" />
                <div class="flex gap-2">
                    <input name="copis[${copiIndex}][affiliation]" class="w-full border p-2 rounded" placeholder="สังกัด COPI" />
                    <button type="button" onclick="removeRow('${id}')" class="text-red-600 mt-2">✕</button>
                </div>
            </div>
        `);
        copiIndex++;
    }

    let irbIndex = {{ count($project->irbs ?? []) }};
    function addIRBRow() {
        const container = document.getElementById('irb-code-container');
        const id = `irb-${irbIndex}`;
        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="grid grid-cols-2 gap-2">
                <input name="irbs[${irbIndex}][code]" placeholder="ECxxxx" class="w-full border p-2 rounded">
                <div class="flex gap-2">
                    <input name="irbs[${irbIndex}][source]" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
                    <button type="button" onclick="removeRow('${id}')" class="text-red-600 mt-2">✕</button>
                </div>
            </div>
        `);
        irbIndex++;
    }
</script>
@endsection
